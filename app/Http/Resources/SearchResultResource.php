<?php

namespace App\Http\Resources;

use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Version */
class SearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'version_id' => $this->id,
            'brand' => $this->carModel->brand->name,
            'brand_slug' => $this->carModel->brand->slug,
            'model' => $this->carModel->name,
            'model_slug' => $this->carModel->slug,
            'version' => $this->name,
            'price' => $this->reference_price,
            'price_year' => $this->reference_year !== null ? (int) $this->reference_year : null,
        ];
    }
}
