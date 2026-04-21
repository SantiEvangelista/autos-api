<?php

namespace App\Http\Resources;

use App\Models\InfoautoCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InfoautoCatalog */
class InfoautoSearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Priorizar real sobre predicted. Si hay cualquier row real, tomar el más
        // reciente de ellos (por recorded_at). Si no hay real, fallback al más
        // reciente de predicted. `sortBy`+`sortByDesc` encadenados NO funcionan
        // porque el segundo sort reordena todo, anulando la priorización por origen.
        $real = $this->priceHistory
            ->where('origin', 'real')
            ->sortByDesc('recorded_at')
            ->first();

        $latest = $real ?? $this->priceHistory
            ->sortByDesc('recorded_at')
            ->first();

        $priceArsThousands = $latest?->price_ars_thousands;
        $priceUsd = $latest?->price_usd;

        return [
            'external_id' => $this->external_id,
            'brand' => $this->brand_name,
            'model' => $this->model_name,
            'version' => $this->version_name_public ?? $this->version_name_raw,
            'version_raw' => $this->version_name_raw,
            'price' => $priceUsd !== null ? (float) $priceUsd : null,
            'price_raw_ars' => $priceArsThousands !== null ? round((float) $priceArsThousands * 1000, 2) : null,
            'price_year' => $latest?->year,
            'source_refs' => [
                'codia' => $this->codia,
                'product_id' => $this->product_id,
            ],
        ];
    }
}
