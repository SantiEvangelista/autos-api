<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;

/**
 * @tags Cotizaciones
 */
class ExchangeRateController extends Controller
{
    /**
     * Cotizaciones USD/ARS.
     *
     * Retorna las cotizaciones del dólar oficial y blue (venta).
     * El dólar oficial se actualiza cada 15 minutos.
     * El dólar blue se fija semanalmente para mantener precios consistentes.
     */
    public function __invoke(ExchangeRateService $exchangeRate): JsonResponse
    {
        $rates = $exchangeRate->getAllRates();

        if ($rates['oficial'] === null && $rates['blue'] === null) {
            return response()->json(['error' => 'Exchange rates temporarily unavailable'], 503);
        }

        return response()->json([
            'oficial' => $rates['oficial'],
            'blue' => $rates['blue'],
            'source' => 'bluelytics',
        ]);
    }
}
