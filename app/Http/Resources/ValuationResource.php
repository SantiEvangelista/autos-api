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

        return $data;
    }
}
