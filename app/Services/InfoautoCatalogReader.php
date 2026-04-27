<?php

namespace App\Services;

use App\Models\InfoautoCatalog;
use App\Models\InfoautoPriceHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InfoautoCatalogReader
{
    /**
     * Search the catalog deduplicating by canonical key.
     *
     * Canonical key: (lower(brand_name), lower(trim(version_name_raw))).
     * When two rows share the canonical key, the row with more information wins:
     *
     *   1. source_system priority (resolved from config('infoauto-sources.priority')).
     *   2. years history: more entries in `years` wins.
     *   3. last_seen_at: most recent wins.
     *   4. id ascending as deterministic tiebreaker.
     *
     * Rows with NULL or empty version_name_raw are not deduplicated
     * (id is used as fallback partition); avoids collapsing distinct legacy rows.
     */
    public function search(string $query, int $perPage = 25): LengthAwarePaginator
    {
        $terms = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);

        $partitionKey = "(lower(brand_name), lower(coalesce(nullif(trim(version_name_raw), ''), '__id_' || id)))";

        $inner = InfoautoCatalog::query()
            ->select(DB::raw("DISTINCT ON {$partitionKey} infoauto_catalog.*"));

        foreach ($terms as $word) {
            $term = '%' . $word . '%';
            $inner->where(function ($q) use ($term) {
                $q->where('brand_name', 'ilike', $term)
                    ->orWhere('model_name', 'ilike', $term)
                    ->orWhere('version_name_raw', 'ilike', $term);
            });
        }

        $inner->orderByRaw('lower(brand_name)');
        $inner->orderByRaw("lower(coalesce(nullif(trim(version_name_raw), ''), '__id_' || id))");

        $this->applySourcePriorityOrder($inner);

        $inner->orderByRaw('jsonb_array_length(years) DESC NULLS LAST');
        $inner->orderByRaw('last_seen_at DESC NULLS LAST');
        $inner->orderByRaw('id ASC');

        return InfoautoCatalog::query()
            ->fromSub($inner, 'infoauto_catalog')
            ->orderBy('brand_name')
            ->orderBy('model_name')
            ->orderBy('version_name_raw')
            ->paginate($perPage);
    }

    public function findByExternalId(string $externalId): ?InfoautoCatalog
    {
        if (! preg_match('/^ia_(\d+)$/', $externalId, $matches)) {
            return null;
        }

        return InfoautoCatalog::find((int) $matches[1]);
    }

    public function getPricesFor(string $externalId): Collection
    {
        $catalog = $this->findByExternalId($externalId);
        if ($catalog === null) {
            return collect();
        }

        return InfoautoPriceHistory::where('infoauto_catalog_id', $catalog->id)
            ->orderBy('year')
            ->orderByRaw("CASE WHEN origin = 'real' THEN 0 ELSE 1 END")
            ->orderBy('recorded_at', 'desc')
            ->get();
    }

    /**
     * Append a CASE WHEN ordering clause based on the configured source priority.
     * Identifiers come from config and are bound as parameters (no SQL interpolation
     * of the values themselves).
     */
    private function applySourcePriorityOrder(Builder $query): void
    {
        $priorities = (array) config('infoauto-sources.priority', []);

        if (empty($priorities)) {
            return;
        }

        $whens = [];
        $bindings = [];
        foreach ($priorities as $rank => $identifier) {
            $whens[] = "WHEN ? THEN {$rank}";
            $bindings[] = $identifier;
        }
        $fallbackRank = count($priorities);

        $sql = 'CASE source_system ' . implode(' ', $whens) . " ELSE {$fallbackRank} END";

        $query->orderByRaw($sql, $bindings);
    }
}
