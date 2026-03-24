<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @tags Estadísticas
 */
class StatsController extends Controller
{
    /**
     * Estadísticas generales.
     *
     * Retorna el conteo de marcas, modelos y versiones disponibles,
     * junto con la fecha de la última actualización de valuaciones.
     */
    public function __invoke(): JsonResponse
    {
        $stats = Cache::remember('api:stats', 300, function () {
            return [
                'brands' => DB::table('brands')->count(),
                'models' => DB::table('car_models')->count(),
                'versions' => DB::table('versions')->count(),
                'last_updated' => DB::table('valuations')->max('updated_at'),
            ];
        });

        return response()->json($stats);
    }
}
