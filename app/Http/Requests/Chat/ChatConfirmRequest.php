<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class ChatConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_token' => ['required', 'uuid'],
            'proposal_token' => ['required', 'uuid'],
            'idempotency_key' => ['required', 'string', 'min:8', 'max:80'],
        ];
    }
}
