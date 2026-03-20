<x-guest-layout>
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Confirmação de conta</p>
        <h1 class="mt-3 text-3xl font-bold leading-tight text-zinc-900">Verifique seu e-mail</h1>
        <p class="mt-2 text-sm text-zinc-600">
            Enviamos um link de verificação para seu e-mail. Clique nele para liberar seu acesso ao sistema.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
            <p class="text-sm font-medium text-emerald-700">Novo link de verificação enviado com sucesso.</p>
        </div>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-sm font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600 focus:outline-none focus:ring-2 focus:ring-barber-500 focus:ring-offset-2"
            >
                Reenviar e-mail de verificação
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
            >
                Sair da conta
            </button>
        </form>
    </div>
</x-guest-layout>
