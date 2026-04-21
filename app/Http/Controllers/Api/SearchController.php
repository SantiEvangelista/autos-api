<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\InfoautoSearchResultResource;
use App\Http\Resources\SearchResultResource;
use App\Models\PriceSnapshot;
use App\Models\Valuation;
use App\Models\Version;
use App\Services\InfoautoCatalogReader;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Búsqueda
 */
class SearchController extends Controller
{
    /**
     * Buscar versiones, modelos y marcas.
     *
     * Búsqueda full-text insensible a mayúsculas. Shape **bifurcado por source**:
     *
     * - `source=cca` (default) → shape aplanado con `version_id` desde `valuations`.
     * - `source=acara` → shape aplanado con `version_id` desde `price_snapshots source=acara`.
     * - `source=infoauto` → shape con `external_id` (`ia_<id>`) desde el read model InfoAuto
     *   (`infoauto_catalog` + `infoauto_price_history`). Incluye `source_refs` con `codia` y `product_id`.
     */
    public function __invoke(SearchRequest $request, InfoautoCatalogReader $infoautoReader): AnonymousResourceCollection
    {
        $query = $request->validated('q');
        $perPage = $request->validated('per_page', 25);
        $source = $request->validated('source', 'cca');

        if ($source === 'infoauto') {
            $paginated = $infoautoReader->search($query, $perPage);
            $paginated->load('priceHistory');

            return InfoautoSearchResultResource::collection($paginated);
        }

        $terms = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);

        [$priceSub, $yearSub, $rawArsSub] = $this->referenceSubqueries($source);

        $builder = Version::query()
            ->with(['carModel.brand'])
            ->join('car_models', 'car_models.id', '=', 'versions.car_model_id')
            ->join('brands', 'brands.id', '=', 'car_models.brand_id')
            ->select(['versions.*'])
            ->addSelect(['reference_price' => $priceSub])
            ->addSelect(['reference_year' => $yearSub]);

        if ($rawArsSub !== null) {
            $builder->addSelect(['reference_raw_ars_thousands' => $rawArsSub]);
        }

        foreach ($terms as $word) {
            $term = '%' . $word . '%';
            $builder->where(function ($q) use ($term) {
                $q->where('versions.name', 'ilike', $term)
                    ->orWhere('car_models.name', 'ilike', $term)
                    ->orWhere('brands.name', 'ilike', $term);
            });
        }

        $paginated = $builder->simplePaginate($perPage);

        return SearchResultResource::collection($paginated);
    }

    /**
     * Subqueries para los shapes de `source=cca` y `source=acara`.
     * `source=infoauto` se maneja por separado via `InfoautoCatalogReader`.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Builder, 1: \Illuminate\Database\Eloquent\Builder, 2: ?\Illuminate\Database\Eloquent\Builder}
     */
    private function referenceSubqueries(string $source): array
    {
        if ($source === 'cca') {
            $price = Valuation::select('price')
                ->whereColumn('version_id', 'versions.id')
                ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
                ->orderBy('year', 'desc')
                ->limit(1);

            $year = Valuation::select('year')
                ->whereColumn('version_id', 'versions.id')
                ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
                ->orderBy('year', 'desc')
                ->limit(1);

            return [$price, $year, null];
        }

        // source === 'acara'
        $price = PriceSnapshot::select('price')
            ->whereColumn('version_id', 'versions.id')
            ->where('source', 'acara')
            ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
            ->orderBy('year', 'desc')
            ->orderBy('recorded_at', 'desc')
            ->limit(1);

        $year = PriceSnapshot::select('year')
            ->whereColumn('version_id', 'versions.id')
            ->where('source', 'acara')
            ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
            ->orderBy('year', 'desc')
            ->orderBy('recorded_at', 'desc')
            ->limit(1);

        $rawArs = PriceSnapshot::select('raw_price_ars_thousands')
            ->whereColumn('version_id', 'versions.id')
            ->where('source', 'acara')
            ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
            ->orderBy('year', 'desc')
            ->orderBy('recorded_at', 'desc')
            ->limit(1);

        return [$price, $year, $rawArs];
    }
}
