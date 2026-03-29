<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VersionValuationsRequest;
use App\Http\Resources\PriceSnapshotResource;
use App\Http\Resources\ValuationResource;
use App\Models\PriceSnapshot;
use App\Models\Version;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Versiones
 */
class VersionController extends Controller
{
    /**
     * Valuaciones de una versión.
     *
     * Retorna las valuaciones (precio por año-modelo) de una versión específica, ordenadas por año descendente.
     * Los precios se almacenan en **USD** y pueden convertirse a **ARS** usando el tipo de cambio oficial (fuente: Bluelytics).
     *
     * - Usá `currency=ARS` para obtener precios en pesos argentinos.
     * - Usá `format_price=true` para incluir el precio formateado (ej: `$56.000.000,00` o `US$40.000,00`).
     * - Usá `relations[]` para incluir metadata de la versión, modelo y/o marca. Valores: `version`, `model`, `brand`.
     *
     * El año `0` representa vehículos **0km**.
     */
    public function valuations(VersionValuationsRequest $request, Version $version, ExchangeRateService $exchangeRate): JsonResponse|AnonymousResourceCollection
    {
        $currency = $request->validated('currency', 'USD');

        // History mode: return price snapshots instead of current valuations
        if (in_array($request->validated('history'), ['true', '1'], true)) {
            return $this->priceHistory($request, $version, $exchangeRate, $currency);
        }

        $valuations = $version->valuations()
            ->orderByDesc('year')
            ->get();

        $rate = null;

        if ($currency === 'ARS') {
            $rate = $exchangeRate->getOfficialSellRate();

            if (!$rate) {
                return response()->json(['error' => 'Exchange rate temporarily unavailable'], 503);
            }
        }

        // Attach ACARA prices if sources includes 'acara'
        $sources = $request->validated('sources', []);
        $includeAcara = in_array('acara', $sources);

        if ($includeAcara) {
            $this->attachAcaraPrices($valuations, $version->id);
        }

        if ($rate) {
            $valuations->transform(function ($val) use ($rate) {
                $val->price = round($val->price * $rate, 2);
                if (isset($val->acara_price) && $val->acara_price !== null) {
                    $val->acara_price = number_format(round((float) $val->acara_price * $rate, 2), 2, '.', '');
                }
                return $val;
            });
        }

        $formatPrice = in_array($request->validated('format_price'), ['true', '1'], true);

        if ($formatPrice) {
            $symbol = $currency === 'ARS' ? '$' : 'US$';

            $valuations->transform(function ($val) use ($symbol) {
                $val->price_formatted = $symbol . number_format($val->price, 2, ',', '.');
                if (isset($val->acara_price) && $val->acara_price !== null) {
                    $val->acara_price_formatted = $symbol . number_format((float) $val->acara_price, 2, ',', '.');
                }
                return $val;
            });
        }

        $meta = ['currency' => $currency];

        $relations = $request->validated('relations', []);

        if (!empty($relations)) {
            $version->load('carModel.brand');

            if (in_array('version', $relations)) {
                $meta['version'] = $version->name;
            }
            if (in_array('model', $relations)) {
                $meta['model'] = ['name' => $version->carModel->name, 'slug' => $version->carModel->slug];
            }
            if (in_array('brand', $relations)) {
                $meta['brand'] = ['name' => $version->carModel->brand->name, 'slug' => $version->carModel->brand->slug];
            }
        }

        if ($rate) {
            $meta['exchange_rate'] = [
                'source' => 'bluelytics',
                'type' => 'oficial_sell',
                'ars_per_usd' => $rate,
            ];
        }

        return ValuationResource::collection($valuations)->additional(['meta' => $meta]);
    }

    private function attachAcaraPrices($valuations, int $versionId): void
    {
        $years = $valuations->pluck('year')->toArray();

        // Get the latest ACARA snapshot per year
        $acaraPrices = PriceSnapshot::where('version_id', $versionId)
            ->where('source', 'acara')
            ->whereIn('year', $years)
            ->orderByDesc('recorded_at')
            ->get()
            ->unique('year')
            ->keyBy('year');

        $valuations->transform(function ($val) use ($acaraPrices) {
            $acara = $acaraPrices->get($val->year);
            $val->acara_price = $acara ? $acara->price : null;
            return $val;
        });
    }

    private function priceHistory(VersionValuationsRequest $request, Version $version, ExchangeRateService $exchangeRate, string $currency): JsonResponse|AnonymousResourceCollection
    {
        $from = $request->validated('from', now()->subDays(30)->toDateString());
        $to = $request->validated('to', now()->toDateString());
        $source = $request->validated('source');

        $query = $version->priceSnapshots()
            ->whereBetween('recorded_at', [$from, $to])
            ->orderByDesc('recorded_at')
            ->orderByDesc('year');

        if ($source) {
            $query->where('source', $source);
        }

        $snapshots = $query->get();

        $rate = null;

        if ($currency === 'ARS') {
            $rate = $exchangeRate->getOfficialSellRate();

            if (!$rate) {
                return response()->json(['error' => 'Exchange rate temporarily unavailable'], 503);
            }

            $snapshots->transform(function ($snapshot) use ($rate) {
                $snapshot->price = round($snapshot->price * $rate, 2);
                return $snapshot;
            });
        }

        $formatPrice = in_array($request->validated('format_price'), ['true', '1'], true);

        if ($formatPrice) {
            $symbol = $currency === 'ARS' ? '$' : 'US$';

            $snapshots->transform(function ($snapshot) use ($symbol) {
                $snapshot->price_formatted = $symbol . number_format($snapshot->price, 2, ',', '.');
                return $snapshot;
            });
        }

        $meta = ['currency' => $currency];

        if ($rate) {
            $meta['exchange_rate'] = [
                'source' => 'bluelytics',
                'type' => 'oficial_sell',
                'ars_per_usd' => $rate,
            ];
        }

        return PriceSnapshotResource::collection($snapshots)->additional(['meta' => $meta]);
    }
}
