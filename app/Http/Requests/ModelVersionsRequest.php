<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesRelationsParam;
use Illuminate\Foundation\Http\FormRequest;

class ModelVersionsRequest extends FormRequest
{
    use HandlesRelationsParam;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relations' => ['sometimes', 'array'],
            'relations.*' => ['string', 'in:model,brand'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareRelationsParam();
    }
}
