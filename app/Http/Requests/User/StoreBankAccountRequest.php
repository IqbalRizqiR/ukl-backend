<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankAccountRequest extends FormRequest
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
            'bank_name' => ['required', 'string', 'max:50'],
            'account_number' => ['required', 'string', 'max:30'],
            'account_holder_name' => ['required', 'string', 'max:100'],
        ];
    }
}
