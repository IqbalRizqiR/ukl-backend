<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use App\Enums\MessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreMessageRequest extends FormRequest
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
            'body' => ['required_without:image', 'nullable', 'string', 'max:1000'],
            'type' => ['nullable', new Enum(MessageType::class)],
            'image' => ['required_without:body', 'nullable', 'image', 'max:2048'],
        ];
    }
}
