<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VersionValuationsRequest extends FormRequest
{
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

        if ($this->has('relations') && is_string($this->input('relations'))) {
            $this->merge(['relations' => [$this->input('relations')]]);
        }
    }
}
