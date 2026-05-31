<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Enums\ProductCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'size' => ['required', 'string', 'max:10'],
            'condition' => ['required', new Enum(ProductCondition::class)],
            'color' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:1000'],
            'weight_grams' => ['required', 'integer', 'min:1'],
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ['image', 'max:2048'],
        ];
    }
}
