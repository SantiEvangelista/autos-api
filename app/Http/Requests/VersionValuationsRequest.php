<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesRelationsParam;
use Illuminate\Foundation\Http\FormRequest;

class VersionValuationsRequest extends FormRequest
{
    use HandlesRelationsParam;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency' => ['sometimes', 'string', 'in:ARS,USD'],
            'format_price' => ['sometimes', 'in:true,false,1,0'],
            'relations' => ['sometimes', 'array'],
            'relations.*' => ['string', 'in:version,model,brand'],
            'sources' => ['sometimes', 'array'],
            'sources.*' => ['string', 'in:infoauto,acara,cca'],
            'history' => ['sometimes', 'in:true,false,1,0'],
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
            'source' => ['sometimes', 'string', 'in:infoauto,acara,cca'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency')) {
            $this->merge(['currency' => strtoupper($this->input('currency'))]);
        }

        $this->prepareRelationsParam();
        $this->prepareSourcesParam();
    }

    private function prepareSourcesParam(): void
    {
        if ($this->has('sources') && is_string($this->input('sources'))) {
            $this->merge(['sources' => array_filter(array_map('trim', explode(',', $this->input('sources'))))]);
        }
    }
}
