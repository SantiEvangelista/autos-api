<?php

namespace App\Http\Resources;

use App\Models\PriceSnapshot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PriceSnapshot */
class PriceSnapshotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'version_id' => $this->version_id,
            'year' => $this->year,
            'price' => $this->price,
            'source' => $this->source,
            'recorded_at' => $this->recorded_at->toDateString(),
        ];

        if (isset($this->resource->price_formatted)) {
            $data['price_formatted'] = $this->resource->price_formatted;
        }

        return $data;
    }
}
