<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Schema;
use Illuminate\Support\ServiceProvider;

class ScrambleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Scramble::routes(function (\Illuminate\Routing\Route $route) {
            return str_starts_with($route->uri(), 'api/v1/')
                && ! str_contains($route->uri(), 'admin/');
        });

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $examples = [
                '/brands' => [
                    'data' => [
                        ['id' => 1, 'name' => 'AUDI', 'slug' => 'audi'],
                        ['id' => 2, 'name' => 'BMW', 'slug' => 'bmw'],
                        ['id' => 3, 'name' => 'CHEVROLET', 'slug' => 'chevrolet'],
                    ],
                    'links' => ['first' => '/api/v1/brands?page=1', 'last' => null, 'prev' => null, 'next' => '/api/v1/brands?page=2'],
                    'meta' => ['current_page' => 1, 'from' => 1, 'path' => '/api/v1/brands', 'per_page' => 50, 'to' => 50],
                ],
                '/brands/{brand}/models' => [
                    'data' => [
                        ['id' => 10, 'brand_id' => 1, 'name' => 'COROLLA', 'slug' => 'corolla'],
                        ['id' => 11, 'brand_id' => 1, 'name' => 'HILUX', 'slug' => 'hilux'],
                    ],
                    'links' => ['first' => '/api/v1/brands/1/models?page=1', 'last' => null, 'prev' => null, 'next' => null],
                    'meta' => ['current_page' => 1, 'from' => 1, 'path' => '/api/v1/brands/1/models', 'per_page' => 25, 'to' => 2],
                ],
                '/models/{carModel}/versions' => [
                    'data' => [
                        ['id' => 100, 'car_model_id' => 10, 'name' => '4P 1.8 XLI MT'],
                        ['id' => 101, 'car_model_id' => 10, 'name' => '4P 2.0 XEI CVT'],
                    ],
                    'links' => ['first' => '/api/v1/models/10/versions?page=1', 'last' => null, 'prev' => null, 'next' => null],
                    'meta' => ['current_page' => 1, 'from' => 1, 'path' => '/api/v1/models/10/versions', 'per_page' => 25, 'to' => 2],
                ],
                '/versions/{version}/valuations' => [
                    'data' => [
                        ['id' => 500, 'version_id' => 100, 'year' => 2025, 'price' => 40000.00],
                        ['id' => 501, 'version_id' => 100, 'year' => 2024, 'price' => 35000.00],
                        ['id' => 502, 'version_id' => 100, 'year' => 0, 'price' => 42000.00],
                    ],
                    'meta' => ['currency' => 'USD'],
                ],
                '/search' => [
                    'data' => [
                        ['version_id' => 100, 'brand' => 'TOYOTA', 'brand_slug' => 'toyota', 'model' => 'COROLLA', 'model_slug' => 'corolla', 'version' => '4P 2.0 XEI CVT'],
                    ],
                    'links' => ['first' => '/api/v1/search?q=corolla&page=1', 'last' => null, 'prev' => null, 'next' => null],
                    'meta' => ['current_page' => 1, 'from' => 1, 'path' => '/api/v1/search', 'per_page' => 25, 'to' => 1],
                ],
                '/health' => [
                    'status' => 'healthy',
                    'checks' => ['database' => 'ok', 'redis' => 'ok', 'exchange_rate_cached' => 'available'],
                ],
            ];

            foreach ($openApi->paths as $path) {
                foreach ($path->operations as $operation) {
                    $pathKey = $path->path;
                    if (isset($examples[$pathKey], $operation->responses[200])) {
                        $response = $operation->responses[200];
                        foreach ($response->content as $content) {
                            if ($content instanceof Schema && $content->type) {
                                $content->type->example($examples[$pathKey]);
                            } elseif (method_exists($content, 'setExample')) {
                                $content->setExample($examples[$pathKey]);
                            }
                        }
                    }
                }
            }

            $schemaExamples = [
                'BrandResource' => ['id' => 1, 'name' => 'AUDI', 'slug' => 'audi'],
                'CarModelResource' => ['id' => 10, 'brand_id' => 1, 'name' => 'COROLLA', 'slug' => 'corolla'],
                'VersionResource' => ['id' => 100, 'car_model_id' => 10, 'name' => '4P 2.0 XEI CVT'],
                'ValuationResource' => ['id' => 500, 'version_id' => 100, 'year' => 2025, 'price' => 40000.00],
                'SearchResultResource' => ['version_id' => 100, 'brand' => 'TOYOTA', 'brand_slug' => 'toyota', 'model' => 'COROLLA', 'model_slug' => 'corolla', 'version' => '4P 2.0 XEI CVT'],
            ];

            foreach ($openApi->components->schemas as $name => $schema) {
                if (isset($schemaExamples[$name]) && $schema->type) {
                    $schema->type->example($schemaExamples[$name]);
                }
            }

            foreach ($openApi->paths as $path) {
                foreach ($path->operations as $operation) {
                    foreach ($operation->parameters as $param) {
                        if ($param->name === 'relations[]') {
                            $param->setStyle('form');
                            $param->setExplode(true);
                            if (property_exists($param->schema, 'type') && $param->schema->type) {
                                $param->schema->type->enum = [];
                            }
                        }
                    }
                }
            }
        });
    }
}
