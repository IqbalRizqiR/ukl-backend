<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'full_address' => ['required', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
