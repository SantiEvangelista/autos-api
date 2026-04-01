<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class MetricsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $apiTotal = Redis::hgetall('metrics:api:total') ?: [];
        $frontendTotal = (int) (Redis::hget('metrics:frontend:total', 'visits') ?: 0);
        $uniqueVisitorsTotal = (int) Redis::pfcount('metrics:visitors:total');
        $brandsTotal = Redis::hgetall('metrics:brands:total') ?: [];
        $modelsTotal = Redis::hgetall('metrics:models:total') ?: [];

        arsort($apiTotal);
        arsort($brandsTotal);
        arsort($modelsTotal);

        $days = (int) $request->query('days', 1);
        $days = min(max($days, 1), 90);

        $daily = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->toDateString();
            $apiDay = Redis::hgetall("metrics:api:{$date}") ?: [];
            $frontendDay = (int) (Redis::hget("metrics:frontend:{$date}", 'visits') ?: 0);
            $uniqueDay = (int) Redis::pfcount("metrics:visitors:{$date}");
            $brandsDay = Redis::hgetall("metrics:brands:{$date}") ?: [];
            $modelsDay = Redis::hgetall("metrics:models:{$date}") ?: [];

            arsort($apiDay);
            arsort($brandsDay);
            arsort($modelsDay);

            $daily[$date] = [
                'api_requests' => array_sum(array_map('intval', $apiDay)),
                'frontend_visits' => $frontendDay,
                'unique_visitors' => $uniqueDay,
                'endpoints' => array_map('intval', $apiDay),
                'popular_brands' => array_map('intval', $brandsDay),
                'popular_models' => array_map('intval', $modelsDay),
            ];
        }

        return response()->json([
            'totals' => [
                'api_requests' => array_sum(array_map('intval', $apiTotal)),
                'frontend_visits' => $frontendTotal,
                'unique_visitors' => $uniqueVisitorsTotal,
                'endpoints' => array_map('intval', $apiTotal),
                'popular_brands' => array_map('intval', $brandsTotal),
                'popular_models' => array_map('intval', $modelsTotal),
            ],
            'daily' => $daily,
        ]);
    }
}
