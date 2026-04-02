@component('mail::message')

## Bem-vindo ao {{ config('app.name') }}!

Olá {{ $user->name ?? '' }},

Sua conta foi criada com sucesso! Abaixo estão suas credenciais de acesso para começar a utilizar o sistema:

**E-mail:** {{ $user->email }}

**Senha temporária:** `{{ $password }}`

Faça login clicando no link abaixo:

<div style="text-align:center; margin:18px 0;">
    <a href="{{ $loginUrl }}" style="background:#c96f1f;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;">Acessar o Sistema</a>
</div>

**Importante:** Esta é uma senha temporária. Recomendamos alterar a senha na primeira vez que acessar o sistema pelo seu perfil.

Qualquer dúvida, entre em contato com o proprietário do sistema.

Atenciosamente,

{{ config('app.name') }}

@endcomponent
