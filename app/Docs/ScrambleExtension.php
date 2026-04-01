<?php

namespace App\Docs;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\BooleanType;
use Dedoc\Scramble\Support\Generator\Types\IntegerType;
use Dedoc\Scramble\Support\Generator\Types\NumberType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;

class ScrambleExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $this->addValuations200($operation, $routeInfo);
        $this->addValuations503($operation, $routeInfo);
        $this->addHealth503($operation, $routeInfo);
        $this->addMetricsAuth($operation, $routeInfo);
    }

    private function addValuations200(Operation $operation, RouteInfo $routeInfo): void
    {
        if ($routeInfo->route->getName() !== 'versions.valuations') {
            return;
        }

        $valuationType = (new ObjectType)
            ->addProperty('id', (new IntegerType)->example(12264))
            ->addProperty('version_id', (new IntegerType)->example(3932))
            ->addProperty('year', (new IntegerType)->example(2025))
            ->addProperty('price', (new StringType)->example('40000.00'))
            ->addProperty('price_formatted', (new StringType)->example('US$40.000,00'))
            ->addProperty('acara_price', (new StringType)->example('38000.00')->nullable(true))
            ->addProperty('acara_price_formatted', (new StringType)->example('US$38.000,00')->nullable(true));

        $exchangeRateType = (new ObjectType)
            ->addProperty('source', (new StringType)->example('bluelytics'))
            ->addProperty('type', (new StringType)->example('oficial_sell'))
            ->addProperty('ars_per_usd', (new NumberType)->example(1404));

        $modelType = (new ObjectType)
            ->addProperty('name', (new StringType)->example('COROLLA'))
            ->addProperty('slug', (new StringType)->example('corolla'));

        $brandType = (new ObjectType)
            ->addProperty('name', (new StringType)->example('TOYOTA'))
            ->addProperty('slug', (new StringType)->example('toyota'));

        $metaType = (new ObjectType)
            ->addProperty('currency', (new StringType)->example('USD'))
            ->addProperty('version', (new StringType)->example('4P 2.0 SEG CVT'))
            ->addProperty('model', $modelType)
            ->addProperty('brand', $brandType)
            ->addProperty('exchange_rate', $exchangeRateType);

        $responseType = (new ObjectType)
            ->addProperty('data', (new ArrayType)->setItems($valuationType))
            ->addProperty('meta', $metaType);

        $response = Response::make(200)
            ->description('Valuaciones de la versión. Incluye `acara_price` cuando se usa `sources=acara`. Los campos `price_formatted`, `acara_price_formatted` aparecen con `format_price=true`. Los campos `version`, `model`, `brand` en `meta` aparecen con `relations=version,model,brand`. El campo `exchange_rate` en `meta` aparece con `currency=ARS`.');

        $response->setContent('application/json', Schema::fromType($responseType));

        $operation->addResponse($response);
    }

    private function addValuations503(Operation $operation, RouteInfo $routeInfo): void
    {
        if ($routeInfo->route->getName() !== 'versions.valuations') {
            return;
        }

        $errorType = (new ObjectType)
            ->addProperty('error', (new StringType)->example('Exchange rate temporarily unavailable'));

        $response = Response::make(503)
            ->description('Tipo de cambio no disponible. Solo se retorna cuando `currency=ARS` y la API externa de cotización está caída.');

        $response->setContent('application/json', Schema::fromType($errorType));

        $operation->addResponse($response);
    }

    private function addHealth503(Operation $operation, RouteInfo $routeInfo): void
    {
        if ($routeInfo->route->getName() !== 'health') {
            return;
        }

        $type = (new ObjectType)
            ->addProperty('status', (new StringType)->example('degraded'))
            ->addProperty('checks', (new ObjectType)
                ->addProperty('database', (new StringType)->example('ok'))
                ->addProperty('redis', (new StringType)->example('error'))
                ->addProperty('exchange_rate_cached', (new StringType)->example('not_cached')));

        $response = Response::make(503)
            ->description('Servicio degradado. Al menos una dependencia crítica (base de datos o Redis) no está disponible.');

        $response->setContent('application/json', Schema::fromType($type));

        $operation->addResponse($response);
    }

    private function addMetricsAuth(Operation $operation, RouteInfo $routeInfo): void
    {
        if ($routeInfo->route->getName() !== 'admin.metrics') {
            return;
        }

        $headerParam = Parameter::make('X-Admin-Token', 'header')
            ->setSchema(Schema::fromType((new StringType)->example('tu-token-secreto')))
            ->required(true)
            ->description('Token de administrador');

        $operation->addParameters([$headerParam]);

        $errorType = (new ObjectType)
            ->addProperty('message', (new StringType)->example('Unauthorized.'));

        $response = Response::make(401)
            ->description('Token ausente o inválido. Se requiere el header `X-Admin-Token` con el valor configurado en `ADMIN_API_TOKEN`.');

        $response->setContent('application/json', Schema::fromType($errorType));

        $operation->addResponse($response);
    }
}
