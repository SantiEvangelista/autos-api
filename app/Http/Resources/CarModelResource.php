<?php

namespace App\Http\Resources;

use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CarModel */
class CarModelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $relations = (array) $request->input('relations', []);

        return [
            'id' => $this->id,
            'brand_id' => $this->brand_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->when(in_array('brand', $relations), fn() => [
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ]),
        ];
    }
}
