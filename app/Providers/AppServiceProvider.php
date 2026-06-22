<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        // Mensagens do chat: por sessão (quando presente) e por IP.
        RateLimiter::for('chat-message', function (Request $request) {
            $perMinute = (int) config('chat.rate_limit.messages_per_minute', 12);
            $key = $request->input('session_token') ?: $request->ip();

            return [
                Limit::perMinute($perMinute)->by('chat-msg:'.$key),
                Limit::perMinute($perMinute * 3)->by('chat-msg-ip:'.$request->ip()),
            ];
        });

        // Início de sessão: por IP/hora.
        RateLimiter::for('chat-start', function (Request $request) {
            $perHour = (int) config('chat.rate_limit.sessions_per_hour_per_ip', 20);

            return Limit::perHour($perHour)->by('chat-start:'.$request->ip());
        });

        // Confirmação de agendamento: protege contra rajadas mantendo idempotência.
        RateLimiter::for('chat-confirm', function (Request $request) {
            return Limit::perMinute(10)->by('chat-confirm:'.($request->input('session_token') ?: $request->ip()));
        });

        // Submit público tradicional (antes sem throttle algum).
        RateLimiter::for('public-booking', function (Request $request) {
            return Limit::perMinute(8)->by('public-booking:'.$request->ip());
        });
    }
}
