<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\PriceSnapshot;
use App\Models\Valuation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RankingService
{
    private const CACHE_KEY = 'rankings:data';
    private const CACHE_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private ExchangeRateService $exchangeRateService,
    ) {}

    public function getCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, fn () => $this->compute());
    }

    private function compute(): array
    {
        $snapshotDates = $this->getLastTwoSnapshotDates();
        $latestUsedYear = Valuation::where('year', '>', 0)->max('year');

        return [
            'cheapest_0km' => $this->cheapest0km(),
            'most_expensive_0km' => $this->mostExpensive0km(),
            'cheapest_overall' => $this->cheapestOverall(),
            'most_expensive_overall' => $this->mostExpensiveOverall(),
            'brand_most_variety' => $this->brandMostVariety(),
            'biggest_price_increase' => $this->biggestPriceChange($snapshotDates, 'increase'),
            'biggest_price_decrease' => $this->biggestPriceChange($snapshotDates, 'decrease'),
            'most_aggressive_depreciation' => $this->depreciationExtreme($latestUsedYear, 'asc'),
            'least_depreciation' => $this->depreciationExtreme($latestUsedYear, 'desc'),
            'average_0km_price' => $this->average0kmPrice(),
            'most_expensive_brand_avg' => $this->brandByAvgPrice('desc'),
            'most_affordable_brand_avg' => $this->brandByAvgPrice('asc'),
            'biggest_mom_drop' => $this->biggestPriceChange($snapshotDates, 'decrease'),
            'market_price_change_pct' => $this->marketPriceChangePct($snapshotDates),
            'meta' => [
                'current_date' => $snapshotDates['current'],
                'previous_date' => $snapshotDates['previous'],
            ],
        ];
    }

    private function getLastTwoSnapshotDates(): array
    {
        $dates = PriceSnapshot::where('source', 'cca')
            ->selectRaw('DISTINCT recorded_at')
            ->orderByDesc('recorded_at')
            ->limit(2)
            ->pluck('recorded_at');

        return [
            'current' => isset($dates[0]) ? $dates[0]->toDateString() : null,
            'previous' => isset($dates[1]) ? $dates[1]->toDateString() : null,
        ];
    }

    private function cheapest0km(): ?array
    {
        $val = Valuation::where('year', 0)
            ->where('price', '>', 0)
            ->orderBy('price')
            ->with('version.carModel.brand')
            ->first();

        return $val ? $this->formatValuationResult($val) : null;
    }

    private function mostExpensive0km(): ?array
    {
        $val = Valuation::where('year', 0)
            ->where('price', '>', 0)
            ->orderByDesc('price')
            ->with('version.carModel.brand')
            ->first();

        return $val ? $this->formatValuationResult($val) : null;
    }

    private function cheapestOverall(): ?array
    {
        $val = Valuation::where('price', '>', 0)
            ->orderBy('price')
            ->with('version.carModel.brand')
            ->first();

        return $val ? $this->formatValuationResult($val, includeYear: true) : null;
    }

    private function mostExpensiveOverall(): ?array
    {
        $val = Valuation::where('price', '>', 0)
            ->orderByDesc('price')
            ->with('version.carModel.brand')
            ->first();

        return $val ? $this->formatValuationResult($val, includeYear: true) : null;
    }

    private function brandMostVariety(): ?array
    {
        $brand = Brand::select('brands.*')
            ->selectSub(
                DB::table('car_models')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('car_models.brand_id', 'brands.id'),
                'models_count'
            )
            ->selectSub(
                DB::table('versions')
                    ->join('car_models', 'car_models.id', '=', 'versions.car_model_id')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('car_models.brand_id', 'brands.id'),
                'versions_count'
            )
            ->orderByDesc('versions_count')
            ->first();

        if (! $brand) {
            return null;
        }

        return [
            'brand' => $brand->name,
            'models_count' => (int) $brand->models_count,
            'versions_count' => (int) $brand->versions_count,
        ];
    }

    private function biggestPriceChange(array $dates, string $direction): ?array
    {
        if (! $dates['current'] || ! $dates['previous']) {
            return null;
        }

        $orderDirection = $direction === 'increase' ? 'desc' : 'asc';

        $result = DB::table('price_snapshots as curr')
            ->join('price_snapshots as prev', function ($join) use ($dates) {
                $join->on('curr.version_id', '=', 'prev.version_id')
                    ->on('curr.year', '=', 'prev.year')
                    ->where('prev.source', 'cca')
                    ->where('prev.recorded_at', $dates['previous']);
            })
            ->join('versions', 'versions.id', '=', 'curr.version_id')
            ->join('car_models', 'car_models.id', '=', 'versions.car_model_id')
            ->join('brands', 'brands.id', '=', 'car_models.brand_id')
            ->where('curr.source', 'cca')
            ->where('curr.recorded_at', $dates['current'])
            ->where('prev.price', '>', 0)
            ->where('curr.price', '>', 0)
            ->selectRaw('
                brands.name as brand_name,
                car_models.name as model_name,
                versions.name as version_name,
                curr.price as current_price,
                prev.price as previous_price,
                (curr.price - prev.price) as diff,
                ROUND(((curr.price - prev.price) / prev.price) * 100, 2) as diff_pct
            ')
            ->orderBy('diff_pct', $orderDirection)
            ->limit(1)
            ->first();

        if (! $result) {
            return null;
        }

        if ($direction === 'increase' && $result->diff <= 0) {
            return null;
        }
        if ($direction === 'decrease' && $result->diff >= 0) {
            return null;
        }

        return [
            'brand' => $result->brand_name,
            'model' => $result->model_name,
            'version' => VersionDisplayService::humanize($result->version_name),
            'current_price' => (float) $result->current_price,
            'previous_price' => (float) $result->previous_price,
            'diff_usd' => (float) $result->diff,
            'diff_pct' => (float) $result->diff_pct,
        ];
    }

    private function depreciationExtreme(?int $latestYear, string $order): ?array
    {
        if (! $latestYear) {
            return null;
        }

        $result = DB::table('valuations as v0')
            ->join('valuations as v1', function ($join) use ($latestYear) {
                $join->on('v0.version_id', '=', 'v1.version_id')
                    ->where('v1.year', $latestYear);
            })
            ->join('versions', 'versions.id', '=', 'v0.version_id')
            ->join('car_models', 'car_models.id', '=', 'versions.car_model_id')
            ->join('brands', 'brands.id', '=', 'car_models.brand_id')
            ->where('v0.year', 0)
            ->where('v0.price', '>', 0)
            ->where('v1.price', '>', 0)
            ->selectRaw('
                brands.name as brand_name,
                car_models.name as model_name,
                versions.name as version_name,
                v0.price as price_0km,
                v1.price as price_1yr,
                ROUND(((v1.price - v0.price) / v0.price) * 100, 2) as diff_pct
            ')
            ->orderBy('diff_pct', $order)
            ->limit(1)
            ->first();

        if (! $result) {
            return null;
        }

        return [
            'brand' => $result->brand_name,
            'model' => $result->model_name,
            'version' => VersionDisplayService::humanize($result->version_name),
            'price_0km' => (float) $result->price_0km,
            'price_1yr' => (float) $result->price_1yr,
            'diff_pct' => (float) $result->diff_pct,
        ];
    }

    private function average0kmPrice(): ?array
    {
        $avgUsd = Valuation::where('year', 0)
            ->where('price', '>', 0)
            ->avg('price');

        if (! $avgUsd) {
            return null;
        }

        $rate = $this->exchangeRateService->getOfficialSellRate();

        return [
            'usd' => round((float) $avgUsd, 2),
            'ars' => $rate ? round((float) $avgUsd * $rate, 2) : null,
            'exchange_rate' => $rate,
        ];
    }

    private function brandByAvgPrice(string $direction): ?array
    {
        $result = DB::table('valuations')
            ->join('versions', 'versions.id', '=', 'valuations.version_id')
            ->join('car_models', 'car_models.id', '=', 'versions.car_model_id')
            ->join('brands', 'brands.id', '=', 'car_models.brand_id')
            ->where('valuations.year', 0)
            ->where('valuations.price', '>', 0)
            ->groupBy('brands.id', 'brands.name')
            ->havingRaw('COUNT(*) >= 3')
            ->selectRaw('brands.name as brand_name, ROUND(AVG(valuations.price), 2) as avg_price')
            ->orderBy('avg_price', $direction)
            ->limit(1)
            ->first();

        if (! $result) {
            return null;
        }

        return [
            'brand' => $result->brand_name,
            'avg_price_usd' => (float) $result->avg_price,
        ];
    }

    private function marketPriceChangePct(array $dates): ?array
    {
        if (! $dates['current'] || ! $dates['previous']) {
            return null;
        }

        $avgCurrent = PriceSnapshot::where('source', 'cca')
            ->where('recorded_at', $dates['current'])
            ->where('price', '>', 0)
            ->avg('price');

        $avgPrevious = PriceSnapshot::where('source', 'cca')
            ->where('recorded_at', $dates['previous'])
            ->where('price', '>', 0)
            ->avg('price');

        if (! $avgCurrent || ! $avgPrevious) {
            return null;
        }

        $pct = round((($avgCurrent - $avgPrevious) / $avgPrevious) * 100, 2);

        return [
            'pct' => $pct,
            'direction' => $pct <= 0 ? 'down' : 'up',
        ];
    }

    private function formatValuationResult(Valuation $val, bool $includeYear = false): array
    {
        $result = [
            'brand' => $val->version->carModel->brand->name,
            'model' => $val->version->carModel->name,
            'version' => $val->version->display_name,
            'price_usd' => (float) $val->price,
        ];

        if ($includeYear) {
            $result['year'] = $val->year === 0 ? '0km' : (string) $val->year;
        }

        return $result;
    }
}
