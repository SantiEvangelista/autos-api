<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VersionValuationsRequest;
use App\Http\Resources\ValuationResource;
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

        $valuations = $version->valuations()
            ->orderByDesc('year')
            ->get();

        $rate = null;

        if ($currency === 'ARS') {
            $rate = $exchangeRate->getOfficialSellRate();

            if (!$rate) {
                return response()->json(['error' => 'Exchange rate temporarily unavailable'], 503);
            }

            $valuations->transform(function ($val) use ($rate) {
                $val->price = round($val->price * $rate, 2);
                return $val;
            });
        }

        $formatPrice = in_array($request->validated('format_price'), ['true', '1'], true);

        if ($formatPrice) {
            $symbol = $currency === 'ARS' ? '$' : 'US$';

            $valuations->transform(function ($val) use ($symbol) {
                $val->price_formatted = $symbol . number_format($val->price, 2, ',', '.');
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
}
