<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class TrackRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/v1/admin/*', 'docs/*', 'up')) {
            return $response;
        }

        try {
            $date = now()->toDateString();
            $ip = $request->ip();

            if ($request->is('api/*')) {
                $endpoint = $request->method() . ' /' . ltrim($request->path(), '/');
                Redis::hincrby('metrics:api:total', $endpoint, 1);
                Redis::hincrby("metrics:api:{$date}", $endpoint, 1);

                $this->trackBrandAndModel($request, $date);
            } else {
                Redis::hincrby('metrics:frontend:total', 'visits', 1);
                Redis::hincrby("metrics:frontend:{$date}", 'visits', 1);
            }

            Redis::pfadd('metrics:visitors:total', [$ip]);
            Redis::pfadd("metrics:visitors:{$date}", [$ip]);
        } catch (\Exception) {
            // Redis down — don't break the request
        }

        return $response;
    }

    private function trackBrandAndModel(Request $request, string $date): void
    {
        $route = $request->route();
        if (! $route) {
            return;
        }

        $brand = $route->parameter('brand');
        $carModel = $route->parameter('carModel');
        $version = $route->parameter('version');

        if ($brand) {
            Redis::hincrby('metrics:brands:total', $brand->name, 1);
            Redis::hincrby("metrics:brands:{$date}", $brand->name, 1);
        }

        if ($carModel) {
            $modelBrand = $carModel->brand;
            $label = $modelBrand->name . ' ' . $carModel->name;
            Redis::hincrby('metrics:models:total', $label, 1);
            Redis::hincrby("metrics:models:{$date}", $label, 1);

            if (! $brand) {
                Redis::hincrby('metrics:brands:total', $modelBrand->name, 1);
                Redis::hincrby("metrics:brands:{$date}", $modelBrand->name, 1);
            }
        }

        if ($version) {
            $versionModel = $version->carModel;
            $versionBrand = $versionModel->brand;
            $brandName = $versionBrand->name;
            $modelLabel = $brandName . ' ' . $versionModel->name;

            if (! $brand) {
                Redis::hincrby('metrics:brands:total', $brandName, 1);
                Redis::hincrby("metrics:brands:{$date}", $brandName, 1);
            }
            if (! $carModel) {
                Redis::hincrby('metrics:models:total', $modelLabel, 1);
                Redis::hincrby("metrics:models:{$date}", $modelLabel, 1);
            }
        }
    }
}
