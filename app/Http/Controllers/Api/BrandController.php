<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandModelsRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CarModelResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Marcas
 */
class BrandController extends Controller
{
    /**
     * Listar marcas.
     *
     * Retorna todas las marcas de vehículos ordenadas alfabéticamente, con paginación.
     * La respuesta no incluye `total` (usa `simplePaginate` por performance).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 63), 100);

        $brands = Brand::orderBy('name')->simplePaginate($perPage);

        return BrandResource::collection($brands);
    }

    /**
     * Modelos de una marca.
     *
     * Retorna los modelos que pertenecen a una marca, identificada por su ID, con paginación.
     * Usá `relations[]=brand` para incluir los datos de la marca en cada modelo.
     * La respuesta no incluye `total` (usa `simplePaginate` por performance).
     */
    public function models(BrandModelsRequest $request, Brand $brand): AnonymousResourceCollection
    {
        $relations = $request->validated('relations', []);
        $perPage = min($request->integer('per_page', 25), 100);

        $models = $brand->carModels()->orderBy('name')->simplePaginate($perPage);

        if (in_array('brand', $relations)) {
            $models->each(fn($m) => $m->setRelation('brand', $brand));
        }

        return CarModelResource::collection($models);
    }
}
