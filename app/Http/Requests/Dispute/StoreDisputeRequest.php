<?php

declare(strict_types=1);

namespace App\Http\Requests\Dispute;

use App\Enums\DisputeReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDisputeRequest extends FormRequest
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
            'reason' => ['required', new Enum(DisputeReason::class)],
            'description' => ['required', 'string', 'max:2000'],
            'evidences' => ['nullable', 'array', 'max:5'],
            'evidences.*.image' => ['nullable', 'image', 'max:2048'],
            'evidences.*.description' => ['nullable', 'string'],
        ];
    }
}
