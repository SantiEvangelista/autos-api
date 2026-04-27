<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use App\Services\InfoautoCatalogReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @tags InfoAuto
 */
class InfoautoCatalogController extends Controller
{
    public function __construct(
        private readonly InfoautoCatalogReader $reader,
        private readonly ExchangeRateService $exchangeRate,
    ) {
    }

    /**
     * Grilla de precios para una entrada del catálogo InfoAuto.
     *
     * Devuelve la historia de precios para el `external_id` dado
     * (prefijo `ia_` + id numérico), con origen `real` priorizado sobre `predicted`.
     *
     * El campo `price` viene en la moneda solicitada (`currency=USD|ARS`, default USD).
     * Si la fila tiene `price_usd` poblado en DB se devuelve ese; si es null y hay
     * `price_ars_thousands`, se calcula on-the-fly: ARS literal cuando `currency=ARS`
     * o convertido vía Bluelytics oficial cuando `currency=USD`.
     */
    public function prices(Request $request, string $externalId): JsonResponse
    {
        $catalog = $this->reader->findByExternalId($externalId);
        if ($catalog === null) {
            throw new NotFoundHttpException("External id not found: {$externalId}");
        }

        $prices = $this->reader->getPricesFor($externalId);
        $currency = strtoupper((string) $request->query('currency', 'USD'));
        $rate = $currency === 'USD' ? $this->exchangeRate->getOfficialSellRate() : null;

        return response()->json([
            'data' => $prices->map(fn ($p) => [
                'year' => (int) $p->year,
                'price' => $this->resolvePrice($p, $currency, $rate),
                'price_ars_thousands' => $p->price_ars_thousands,
                'origin' => $p->origin,
                'source' => $p->source,
                'recorded_at' => $p->recorded_at->toDateString(),
            ])->all(),
            'meta' => [
                'external_id' => $catalog->external_id,
                'brand' => $catalog->brand_name,
                'model' => $catalog->model_name,
                'version' => $catalog->version_name_public ?? $catalog->version_name_raw,
                'currency' => $currency,
                'source_refs' => [
                    'codia' => $catalog->codia,
                    'product_id' => $catalog->product_id,
                ],
            ],
        ]);
    }

    private function resolvePrice($entry, string $currency, ?float $rate): ?float
    {
        // 1) DB price_usd explícito gana siempre cuando currency=USD
        if ($currency === 'USD' && $entry->price_usd !== null) {
            return (float) $entry->price_usd;
        }

        $arsThousands = $entry->price_ars_thousands;
        if ($arsThousands === null) {
            return null;
        }

        $arsAbsolute = (float) $arsThousands * 1000.0;

        if ($currency === 'ARS') {
            return round($arsAbsolute, 2);
        }

        // currency=USD sin price_usd en DB → convertir on-the-fly
        if ($rate === null || $rate <= 0) {
            return null;
        }

        return round($arsAbsolute / $rate, 2);
    }
}
