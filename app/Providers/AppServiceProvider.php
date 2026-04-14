<?php

namespace App\Providers;

use App\Services\RankingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());

        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        View::composer('app', function ($view) {
            $view->with('rankings', app(RankingService::class)->getCached());
            $view->with('bodyTypes', config('body-types'));
            $view->with('stats', Cache::remember('api:stats', 300, function () {
                return [
                    'brands' => DB::table('brands')->count(),
                    'models' => DB::table('car_models')->count(),
                    'versions' => DB::table('versions')->count(),
                    'last_updated' => DB::table('valuations')->max('updated_at'),
                ];
            }));
        });
    }
}
