<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap:xml', 3600, function () {
            $lastUpdated = DB::table('valuations')->max('updated_at');
            $lastmod = $lastUpdated ? date('Y-m-d', strtotime($lastUpdated)) : date('Y-m-d');

            return view('sitemap', ['lastmod' => $lastmod])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
