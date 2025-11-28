@component('mail::message')



## Redefina sua senha

Olá {{ $user->name ?? '' }},

Recebemos uma solicitação para redefinir a senha da sua conta. Clique no botão abaixo para criar uma nova senha:

<div style="text-align:center; margin:18px 0;">
    <a href="{{ $url }}" style="background:#c96f1f;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;">Redefinir senha</a>
</div>

Se você não solicitou essa alteração, pode ignorar este e-mail — a sua senha permanecerá inalterada.

Atenciosamente,

{{ config('app.name') }}

@endcomponent
