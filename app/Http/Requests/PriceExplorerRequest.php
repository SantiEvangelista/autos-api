<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceExplorerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'max_price' => ['required', 'numeric', 'min:0'],
            'min_price' => ['sometimes', 'numeric', 'min:0', 'lte:max_price'],
            'year' => ['sometimes', 'integer', 'min:0'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
