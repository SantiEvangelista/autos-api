<?php

namespace App\Services;

use App\Models\PriceSnapshot;
use Illuminate\Support\Collection;

class PriceResolverService
{
    public const INFOAUTO_FEAT_DATE = '2026-04-17';

    /**
     * Resolve infoauto prices for a version across a set of years.
     *
     *
     * @param  int[]  $years
     * @return Collection<int, object{price: string, raw_price_ars_thousands: ?string, origin: string, recorded_at: mixed}>
     */
    public function resolveInfoautoPrices(int $versionId, array $years): Collection
    {
        if (empty($years)) {
            return collect();
        }

        $snapshots = PriceSnapshot::where('version_id', $versionId)
            ->whereIn('year', $years)
            ->whereIn('source', ['infoauto', 'infoauto_predicted'])
            ->where(function ($q) {
                $q->where('source', 'infoauto_predicted')
                    ->orWhere(function ($qq) {
                        $qq->where('source', 'infoauto')
                            ->where('recorded_at', '>=', self::INFOAUTO_FEAT_DATE);
                    });
            })
            ->orderByDesc('recorded_at')
            ->orderBy('id', 'desc')
            ->get();

        $resolved = collect();

        foreach ($snapshots as $snapshot) {
            $year = (int) $snapshot->year;
            $isReal = $snapshot->source === 'infoauto';
            $origin = $isReal ? 'real' : 'predicted';

            if (! $resolved->has($year)) {
                $resolved->put($year, (object) [
                    'price' => $snapshot->price,
                    'raw_price_ars_thousands' => $snapshot->raw_price_ars_thousands,
                    'origin' => $origin,
                    'recorded_at' => $snapshot->recorded_at,
                ]);
                continue;
            }

            $current = $resolved->get($year);
            if ($current->origin === 'predicted' && $origin === 'real') {
                $resolved->put($year, (object) [
                    'price' => $snapshot->price,
                    'raw_price_ars_thousands' => $snapshot->raw_price_ars_thousands,
                    'origin' => $origin,
                    'recorded_at' => $snapshot->recorded_at,
                ]);
            }
        }

        return $resolved;
    }
}
