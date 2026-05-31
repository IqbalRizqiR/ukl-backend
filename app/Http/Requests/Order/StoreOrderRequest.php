<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Enums\ShipmentCourier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOrderRequest extends FormRequest
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
            'product_id' => ['required', 'exists:products,id'],
            'shipping_address_id' => ['required', 'exists:user_addresses,id'],
            'courier' => ['required', new Enum(ShipmentCourier::class)],
            'service' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
