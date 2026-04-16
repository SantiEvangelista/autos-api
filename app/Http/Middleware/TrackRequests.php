<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class TrackRequests
{
    private const BOT_UA_PATTERN = '/bot|crawl|spider|slurp|curl\/|wget\/|python|go-http|java\/|scanner|nikto|zgrab|masscan|semrush|ahrefs|dotbot|mj12bot|petalbot|facebookexternalhit|whatsapp|telegram|gptbot|claudebot|perplexity|scrape|phantom|headless/i';

    private const RECON_PATH_PATTERN = '/\.env|\.git\/|phpinfo|info\.php|phpmyadmin|wp-(admin|login|content|includes)|xmlrpc|secrets\.|config\.(php|json)/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/v1/admin/*', 'docs/*', 'up')) {
            return $response;
        }

        if (in_array($request->ip(), config('app.metrics_excluded_ips', []), true)) {
            return $response;
        }

        try {
            $date = now()->toDateString();
            $ip = $request->ip();
            $clientType = $this->classifyClient($request);
            $referrer = $this->extractReferrerDomain($request);
            $isApi = $request->is('api/*');

            Redis::pipeline(function ($pipe) use ($request, $date, $ip, $clientType, $referrer, $isApi) {
                $pipe->hincrby('metrics:traffic:total', $clientType, 1);
                $pipe->hincrby("metrics:traffic:{$date}", $clientType, 1);

                if ($clientType === 'bot') {
                    $pipe->hincrby('metrics:bot_paths:total', $request->path(), 1);
                    $pipe->hincrby("metrics:bot_paths:{$date}", $request->path(), 1);
                }

                $pipe->hincrby('metrics:referrers:total', $referrer, 1);
                $pipe->hincrby("metrics:referrers:{$date}", $referrer, 1);

                if ($isApi) {
                    $endpoint = $request->method().' /'.ltrim($request->path(), '/');
                    $pipe->hincrby('metrics:api:total', $endpoint, 1);
                    $pipe->hincrby("metrics:api:{$date}", $endpoint, 1);
                } else {
                    $pipe->hincrby('metrics:frontend:total', 'visits', 1);
                    $pipe->hincrby("metrics:frontend:{$date}", 'visits', 1);
                }

                $pipe->pfadd('metrics:visitors:total', [$ip]);
                $pipe->pfadd("metrics:visitors:{$date}", [$ip]);
            });

            if ($isApi) {
                $this->trackBrandAndModel($request, $date);
            }
        } catch (\Exception) {
            // Redis down — don't break the request
        }

        return $response;
    }

    private function classifyClient(Request $request): string
    {
        if (preg_match(self::RECON_PATH_PATTERN, $request->path())) {
            return 'bot';
        }

        $ua = $request->userAgent() ?? '';

        if ($ua === '' || preg_match(self::BOT_UA_PATTERN, $ua)) {
            return 'bot';
        }

        if ($request->is('api/*') && ! str_starts_with($ua, 'Mozilla/')) {
            return 'api_client';
        }

        return 'browser';
    }

    private function extractReferrerDomain(Request $request): string
    {
        $value = $request->header('referer') ?? $request->header('origin');

        if (! $value) {
            return 'direct';
        }

        $host = parse_url($value, PHP_URL_HOST);

        if (! $host) {
            return 'direct';
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        return $host === $appHost ? 'direct' : $host;
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
            $label = $modelBrand->name.' '.$carModel->name;
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
            $modelLabel = $brandName.' '.$versionModel->name;

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
