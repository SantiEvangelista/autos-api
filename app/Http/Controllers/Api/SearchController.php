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
use App\Services\PriceResolverService;
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
     *
     * ## Parámetros
     *
     * - `source=cca` (default): referencia desde `valuations` (CCA).
     * - `source=acara`: último snapshot `source=acara`.
     * - `source=infoauto`: snapshot `source=infoauto`
     */
    public function __invoke(SearchRequest $request, InfoautoCatalogReader $infoautoReader): AnonymousResourceCollection
    {
        $query = $request->validated('q');
        $perPage = $request->validated('per_page', 25);
        $source = $request->validated('source', 'cca');

        if ($source === 'infoauto_v2') {
            $paginated = $infoautoReader->search($query, $perPage);
            $paginated->load('priceHistory');

            return InfoautoSearchResultResource::collection($paginated);
        }

        $terms = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);

        [$priceSub, $yearSub, $originSub, $rawArsSub] = $this->referenceSubqueries($source);

        $builder = Version::query()
            ->with(['carModel.brand'])
            ->join('car_models', 'car_models.id', '=', 'versions.car_model_id')
            ->join('brands', 'brands.id', '=', 'car_models.brand_id')
            ->select(['versions.*'])
            ->addSelect(['reference_price' => $priceSub])
            ->addSelect(['reference_year' => $yearSub]);

        if ($originSub !== null) {
            $builder->addSelect(['reference_origin' => $originSub]);
        }

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
     * @return array{price: \Illuminate\Database\Eloquent\Builder, year: \Illuminate\Database\Eloquent\Builder, origin: ?\Illuminate\Database\Eloquent\Builder, raw_ars: ?\Illuminate\Database\Eloquent\Builder}
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

            return [$price, $year, null, null];
        }

        if ($source === 'acara') {
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

            return [$price, $year, null, $rawArs];
        }

        // source === 'infoauto': real feat > predicted
        $hito = PriceResolverService::INFOAUTO_FEAT_DATE;
        $rank = "CASE WHEN source='infoauto' AND recorded_at >= ? THEN 0 ELSE 1 END";

        $price = PriceSnapshot::select('price')
            ->whereColumn('version_id', 'versions.id')
            ->whereIn('source', ['infoauto', 'infoauto_predicted'])
            ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
            ->orderBy('year', 'desc')
            ->orderByRaw($rank, [$hito])
            ->orderBy('recorded_at', 'desc')
            ->limit(1);

        $year = PriceSnapshot::select('year')
            ->whereColumn('version_id', 'versions.id')
            ->whereIn('source', ['infoauto', 'infoauto_predicted'])
            ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
            ->orderBy('year', 'desc')
            ->orderByRaw($rank, [$hito])
            ->orderBy('recorded_at', 'desc')
            ->limit(1);

        $origin = PriceSnapshot::selectRaw("CASE WHEN source='infoauto' AND recorded_at >= ? THEN 'real' ELSE 'predicted' END", [$hito])
            ->whereColumn('version_id', 'versions.id')
            ->whereIn('source', ['infoauto', 'infoauto_predicted'])
            ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
            ->orderBy('year', 'desc')
            ->orderByRaw($rank, [$hito])
            ->orderBy('recorded_at', 'desc')
            ->limit(1);

        $rawArs = PriceSnapshot::select('raw_price_ars_thousands')
            ->whereColumn('version_id', 'versions.id')
            ->whereIn('source', ['infoauto', 'infoauto_predicted'])
            ->orderByRaw('CASE WHEN year = 0 THEN 0 ELSE 1 END')
            ->orderBy('year', 'desc')
            ->orderByRaw($rank, [$hito])
            ->orderBy('recorded_at', 'desc')
            ->limit(1);

        return [$price, $year, $origin, $rawArs];
    }
}
