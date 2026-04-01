<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceExplorerRequest;
use App\Http\Resources\SearchResultResource;
use App\Models\Valuation;
use App\Models\Version;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Explorador de Precios
 */
class PriceExplorerController extends Controller
{
    /**
     * Explorar vehículos por rango de precio.
     *
     * Retorna vehículos cuyo precio cae dentro del rango especificado,
     * ordenados de menor a mayor precio. Soporta filtro por año.
     */
    public function __invoke(PriceExplorerRequest $request): AnonymousResourceCollection
    {
        $maxPrice = $request->validated('max_price');
        $minPrice = $request->validated('min_price', 0);
        $year = $request->validated('year');
        $perPage = $request->validated('per_page', 25);

        $valuationConstraints = function ($q) use ($minPrice, $maxPrice, $year) {
            $q->whereBetween('price', [$minPrice, $maxPrice]);
            if ($year !== null) {
                $q->where('year', $year);
            }
        };

        $subqueryBase = fn () => Valuation::select('price')
            ->whereColumn('version_id', 'versions.id')
            ->whereBetween('price', [$minPrice, $maxPrice])
            ->when($year !== null, fn ($q) => $q->where('year', $year))
            ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
            ->orderBy('year', 'desc')
            ->limit(1);

        $paginated = Version::query()
            ->with(['carModel.brand'])
            ->addSelect(['versions.*'])
            ->addSelect(['reference_price' => $subqueryBase()])
            ->addSelect(['reference_year' => Valuation::select('year')
                ->whereColumn('version_id', 'versions.id')
                ->whereBetween('price', [$minPrice, $maxPrice])
                ->when($year !== null, fn ($q) => $q->where('year', $year))
                ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
                ->orderBy('year', 'desc')
                ->limit(1),
            ])
            ->whereHas('valuations', $valuationConstraints)
            ->orderBy('reference_price')
            ->simplePaginate($perPage);

        return SearchResultResource::collection($paginated);
    }
}
