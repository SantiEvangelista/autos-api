<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModelVersionsRequest;
use App\Http\Resources\VersionResource;
use App\Models\CarModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Modelos
 */
class CarModelController extends Controller
{
    /**
     * Versiones de un modelo.
     *
     * Retorna todas las versiones de un modelo de vehículo, con paginación.
     * Usá `relations[]` para incluir datos del modelo y/o la marca en cada versión.
     * Valores permitidos: `model`, `brand`.
     */
    public function versions(ModelVersionsRequest $request, CarModel $carModel): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 25), 100);
        $relations = $request->validated('relations', []);
        $include = !empty($relations);

        $query = $carModel->versions()->orderBy('name');

        if ($include) {
            $carModel->load('brand');
            $query->with('carModel.brand');
        }

        $paginated = $query->simplePaginate($perPage);

        if ($include) {
            $paginated->getCollection()->each(function ($version) use ($carModel) {
                $version->setRelation('carModel', $carModel);
            });
        }

        return VersionResource::collection($paginated);
    }
}
