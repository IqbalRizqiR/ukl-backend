<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Enums\ProductCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends FormRequest
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
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'title' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'size' => ['nullable', 'string', 'max:10'],
            'condition' => ['nullable', new Enum(ProductCondition::class)],
            'color' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:1000'],
            'weight_grams' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
