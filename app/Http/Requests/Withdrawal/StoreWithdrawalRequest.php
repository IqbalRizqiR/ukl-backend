<?php

declare(strict_types=1);

namespace App\Http\Requests\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
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
            'bank_account_id' => ['required', 'exists:user_bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:10000'],
        ];
    }
}
