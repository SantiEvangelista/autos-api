<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InfoautoCatalogReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @tags InfoAuto
 */
class InfoautoCatalogController extends Controller
{
    public function __construct(private readonly InfoautoCatalogReader $reader)
    {
    }

    /**
     * Grilla de precios para una entrada del catálogo InfoAuto.
     *
     * Devuelve la historia de precios para el `external_id` dado
     * (prefijo `ia_` + id numérico), con origen `real` priorizado sobre `predicted`.
     */
    public function prices(Request $request, string $externalId): JsonResponse
    {
        $catalog = $this->reader->findByExternalId($externalId);
        if ($catalog === null) {
            throw new NotFoundHttpException("External id not found: {$externalId}");
        }

        $prices = $this->reader->getPricesFor($externalId);

        return response()->json([
            'data' => $prices->map(fn ($p) => [
                'year' => (int) $p->year,
                'price' => $p->price_usd !== null ? (float) $p->price_usd : null,
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
                'currency' => strtoupper((string) $request->query('currency', 'USD')),
                'source_refs' => [
                    'codia' => $catalog->codia,
                    'product_id' => $catalog->product_id,
                ],
            ],
        ]);
    }
}
