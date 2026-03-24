<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * @tags Estado del servicio
 */
class HealthController extends Controller
{
    /**
     * Health check.
     *
     * Verifica la conectividad con las dependencias del servicio: PostgreSQL, Redis,
     * y la disponibilidad del tipo de cambio en cache.
     *
     * Retorna `200` cuando todas las dependencias críticas están operativas,
     * o `503` cuando al menos una falla (estado degradado).
     *
     * @return JsonResponse{status: string, checks: array{database: string, redis: string, exchange_rate_cached: string}}
     */
    public function __invoke(): JsonResponse
    {
        $checks = [];

        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception) {
            $checks['database'] = 'error';
        }

        try {
            Redis::connection()->ping();
            $checks['redis'] = 'ok';
        } catch (\Exception) {
            $checks['redis'] = 'error';
        }

        $checks['exchange_rate_cached'] = Cache::has('exchange_rate:usd_ars_oficial') ? 'available' : 'not_cached';

        $healthy = $checks['database'] === 'ok' && $checks['redis'] === 'ok';

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }
}
