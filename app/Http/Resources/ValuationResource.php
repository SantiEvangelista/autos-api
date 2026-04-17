<?php

namespace App\Http\Resources;

use App\Models\Valuation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Valuation */
class ValuationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'version_id' => $this->version_id,
            'year' => $this->year,
            'price' => $this->price,
        ];

        if (isset($this->resource->price_formatted)) {
            $data['price_formatted'] = $this->resource->price_formatted;
        }

        if (array_key_exists('acara_price', $this->resource->getAttributes())) {
            $data['acara_price'] = $this->resource->acara_price;
        }

        if (isset($this->resource->acara_price_formatted)) {
            $data['acara_price_formatted'] = $this->resource->acara_price_formatted;
        }

        if (array_key_exists('infoauto_price', $this->resource->getAttributes())) {
            $data['infoauto_price'] = $this->resource->infoauto_price;
            $data['infoauto_price_origin'] = $this->resource->infoauto_price_origin ?? null;
            $data['infoauto_price_raw_ars'] = $this->resource->infoauto_price_raw_ars ?? null;
        }

        if (isset($this->resource->infoauto_price_formatted)) {
            $data['infoauto_price_formatted'] = $this->resource->infoauto_price_formatted;
        }

        return $data;
    }
}
