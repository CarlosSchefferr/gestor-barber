<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida os dados pessoais anexados à proposta (nome/email/telefone/obs.).
 * Coletados em campo seguro da interface — nunca passam pelo modelo de IA.
 */
class ChatProposalCustomerRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
            'observacoes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
