<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Models\Valuation;
use App\Models\Version;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Búsqueda
 */
class SearchController extends Controller
{
    /**
     * Buscar versiones, modelos y marcas.
     *
     * Búsqueda full-text e insensible a mayúsculas sobre nombres de versiones, modelos y marcas.
     * Retorna resultados paginados con la información de marca, modelo y versión aplanada en cada resultado.
     *
     * El término de búsqueda debe tener al menos 2 caracteres.
     */
    public function __invoke(SearchRequest $request): AnonymousResourceCollection
    {
        $query = $request->validated('q');
        $perPage = $request->validated('per_page', 25);
        $term = '%' . $query . '%';

        $paginated = Version::query()
            ->with(['carModel.brand'])
            ->addSelect(['versions.*'])
            ->addSelect(['reference_price' => Valuation::select('price')
                ->whereColumn('version_id', 'versions.id')
                ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
                ->orderBy('year', 'desc')
                ->limit(1),
            ])
            ->addSelect(['reference_year' => Valuation::select('year')
                ->whereColumn('version_id', 'versions.id')
                ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
                ->orderBy('year', 'desc')
                ->limit(1),
            ])
            ->where(function ($q) use ($term) {
                $q->where('name', 'ilike', $term)
                    ->orWhereHas('carModel', fn($q) => $q->where('name', 'ilike', $term))
                    ->orWhereHas('carModel.brand', fn($q) => $q->where('name', 'ilike', $term));
            })
            ->simplePaginate($perPage);

        return SearchResultResource::collection($paginated);
    }
}
