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
        $data = [
            'version_id' => $this->id,
            'brand' => $this->carModel->brand->name,
            'brand_slug' => $this->carModel->brand->slug,
            'model' => $this->carModel->name,
            'model_slug' => $this->carModel->slug,
            'version' => $this->display_name,
            'version_raw' => $this->name,
            'price' => $this->reference_price,
            'price_year' => $this->reference_year !== null ? (int) $this->reference_year : null,
        ];

        if (array_key_exists('reference_origin', $this->resource->getAttributes())) {
            $data['price_origin'] = $this->resource->reference_origin;
        }

        if (array_key_exists('reference_raw_ars_thousands', $this->resource->getAttributes())) {
            $rawThousands = $this->resource->reference_raw_ars_thousands;
            $data['price_raw_ars'] = $rawThousands !== null ? round((float) $rawThousands * 1000, 2) : null;
        }

        return $data;
    }
}
