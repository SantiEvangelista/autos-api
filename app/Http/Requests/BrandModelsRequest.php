<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandModelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relations' => ['sometimes', 'array'],
            'relations.*' => ['string', 'in:brand'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('relations') && is_string($this->input('relations'))) {
            $this->merge(['relations' => [$this->input('relations')]]);
        }
    }
}
