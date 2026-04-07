<?php

namespace App\Http\Resources;

use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Version */
class VersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $relations = (array) $request->input('relations', []);
        $include = !empty($relations);

        return [
            'id' => $this->id,
            'car_model_id' => $this->car_model_id,
            'name' => $this->display_name,
            'name_raw' => $this->name,
            'model' => $this->when($include && in_array('model', $relations), fn() => [
                'name' => $this->carModel->name,
                'slug' => $this->carModel->slug,
            ]),
            'brand' => $this->when($include && in_array('brand', $relations), fn() => [
                'name' => $this->carModel->brand->name,
                'slug' => $this->carModel->brand->slug,
            ]),
        ];
    }
}
