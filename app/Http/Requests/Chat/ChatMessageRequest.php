<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida a mensagem enviada ao chat: token de sessão, texto dentro do limite
 * de caracteres e o campo honeypot "website" (deve vir vazio — se preenchido,
 * é bot).
 */
class ChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxChars = (int) config('chat.limits.max_message_chars', 1000);

        return [
            'session_token' => ['required', 'uuid'],
            'message' => ['required', 'string', 'max:'.$maxChars],
            // Honeypot anti-bot: deve permanecer vazio.
            'website' => ['nullable', 'prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.max' => 'Mensagem muito longa.',
            'website.prohibited' => 'Requisição inválida.',
        ];
    }
}
