<?php

namespace App\Docs;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;

class ScrambleExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $this->addValuations503($operation, $routeInfo);
        $this->addHealth503($operation, $routeInfo);
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
}
