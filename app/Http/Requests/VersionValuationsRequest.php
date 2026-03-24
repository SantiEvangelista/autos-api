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
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency')) {
            $this->merge(['currency' => strtoupper($this->input('currency'))]);
        }

        $this->prepareRelationsParam();
    }
}
