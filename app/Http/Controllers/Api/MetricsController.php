<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class MetricsController extends Controller
{
    private const TOP_BOT_PATHS_LIMIT = 50;

    private const TOP_REFERRERS_LIMIT = 30;

    public function __invoke(Request $request): JsonResponse
    {
        $days = min(max((int) $request->query('days', 1), 1), 90);

        return response()->json([
            'totals' => $this->buildTotals(),
            'daily' => $this->buildDaily($days),
        ]);
    }

    private function buildTotals(): array
    {
        $endpoints = $this->getSortedHash('metrics:api:total');

        return [
            'api_requests' => array_sum($endpoints),
            'frontend_visits' => (int) (Redis::hget('metrics:frontend:total', 'visits') ?: 0),
            'unique_visitors' => (int) Redis::pfcount('metrics:visitors:total'),
            'endpoints' => $endpoints,
            'popular_brands' => $this->getSortedHash('metrics:brands:total'),
            'popular_models' => $this->getSortedHash('metrics:models:total'),
            'traffic_breakdown' => $this->getHash('metrics:traffic:total'),
            'top_bot_paths' => $this->getSortedHash('metrics:bot_paths:total', self::TOP_BOT_PATHS_LIMIT),
            'referrers' => $this->getSortedHash('metrics:referrers:total', self::TOP_REFERRERS_LIMIT),
        ];
    }

    private function buildDaily(int $days): array
    {
        $daily = [];

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->toDateString();
            $endpoints = $this->getSortedHash("metrics:api:{$date}");

            $daily[$date] = [
                'api_requests' => array_sum($endpoints),
                'frontend_visits' => (int) (Redis::hget("metrics:frontend:{$date}", 'visits') ?: 0),
                'unique_visitors' => (int) Redis::pfcount("metrics:visitors:{$date}"),
                'endpoints' => $endpoints,
                'popular_brands' => $this->getSortedHash("metrics:brands:{$date}"),
                'popular_models' => $this->getSortedHash("metrics:models:{$date}"),
                'traffic_breakdown' => $this->getHash("metrics:traffic:{$date}"),
                'top_bot_paths' => $this->getSortedHash("metrics:bot_paths:{$date}", self::TOP_BOT_PATHS_LIMIT),
                'referrers' => $this->getSortedHash("metrics:referrers:{$date}", self::TOP_REFERRERS_LIMIT),
            ];
        }

        return $daily;
    }

    private function getSortedHash(string $key, ?int $limit = null): array
    {
        $data = array_map('intval', Redis::hgetall($key) ?: []);
        arsort($data);

        return $limit !== null ? array_slice($data, 0, $limit, true) : $data;
    }

    private function getHash(string $key): array
    {
        return array_map('intval', Redis::hgetall($key) ?: []);
    }
}
