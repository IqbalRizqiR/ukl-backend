<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BankAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_name' => $this->bank_name,
            'account_number' => $this->maskedAccountNumber(),
            'account_holder_name' => $this->account_holder_name,
            'is_default' => $this->is_default,
            'is_verified' => $this->is_verified,
        ];
    }

    protected function maskedAccountNumber(): string
    {
        $number = $this->account_number;

        if (Str::length($number) <= 4) {
            return $number;
        }

        return str_repeat('*', Str::length($number) - 4) . Str::substr($number, -4);
    }
}
