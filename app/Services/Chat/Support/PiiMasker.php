<?php

namespace App\Services\Chat\Support;

/**
 * Mascara dados pessoais (e-mail, telefone, CPF) antes de persistir/registrar
 * conteúdo de conversa. Nunca devemos logar PII integral.
 */
class PiiMasker
{
    public static function mask(string $text): string
    {
        // E-mails -> a***@dominio
        $text = preg_replace_callback('/([\w.+-])[\w.+-]*(@[\w.-]+)/u', function ($m) {
            return $m[1].'***'.$m[2];
        }, $text) ?? $text;

        // CPF (com ou sem máscara) -> ***.***.***-**
        $text = preg_replace('/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/u', '***.***.***-**', $text) ?? $text;

        // Telefones (10-13 dígitos, com símbolos) -> mantém só os 2 últimos
        $text = preg_replace_callback('/(\+?\d[\d\s().-]{8,}\d)/u', function ($m) {
            $digits = preg_replace('/\D/', '', $m[1]);
            if (strlen($digits) < 10) {
                return $m[1];
            }

            return '••••'.substr($digits, -2);
        }, $text) ?? $text;

        return $text;
    }

    public static function maskPhone(?string $phone): ?string
    {
        if (! $phone) {
            return $phone;
        }
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return strlen($digits) >= 2 ? '••••'.substr($digits, -2) : '••••';
    }

    public static function maskEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return $email;
        }
        [$user, $domain] = explode('@', $email, 2);

        return mb_substr($user, 0, 1).'***@'.$domain;
    }
}
