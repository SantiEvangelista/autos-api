<?php

namespace App\Services;

use App\Models\InfoautoCatalog;
use App\Models\InfoautoPriceHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InfoautoCatalogReader
{
    public function search(string $query, int $perPage = 25): LengthAwarePaginator
    {
        $terms = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);

        $builder = InfoautoCatalog::query();

        foreach ($terms as $word) {
            $term = '%' . $word . '%';
            $builder->where(function ($q) use ($term) {
                $q->where('brand_name', 'ilike', $term)
                    ->orWhere('model_name', 'ilike', $term)
                    ->orWhere('version_name_raw', 'ilike', $term);
            });
        }

        return $builder->orderBy('brand_name')
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
}
