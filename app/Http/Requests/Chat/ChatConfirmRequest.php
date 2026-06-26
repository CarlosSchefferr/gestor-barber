<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida a confirmação do agendamento: tokens de sessão e proposta + a
 * idempotency_key que evita criar o mesmo agendamento duas vezes.
 */
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
