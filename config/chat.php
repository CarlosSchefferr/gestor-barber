<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chat IA de agendamento (página pública)
    |--------------------------------------------------------------------------
    |
    | Configuração centralizada do assistente de agendamento com IA da página
    | pública. A chave da OpenAI é reaproveitada de services.ai (mesma conta)
    | para não duplicar credenciais. Todos os valores são lidos apenas aqui;
    | nenhum env() deve aparecer fora de arquivos de configuração.
    |
    */

    // Liga/desliga o chat com IA. Quando false (ou sem chave), o frontend usa
    // o fluxo tradicional de agendamento, que compartilha o mesmo backend.
    'enabled' => env('OPENAI_CHAT_ENABLED', true),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'project' => env('OPENAI_PROJECT'),
        // Mantém compatibilidade com OPENAI_ENDPOINT já usado no financeiro.
        'base_url' => rtrim((string) env('OPENAI_BASE_URL', env('OPENAI_ENDPOINT', 'https://api.openai.com/v1')), '/'),

        // Modelo econômico e configurável. Deve suportar Responses API,
        // function calling e Structured Outputs. Defina OPENAI_CHAT_MODEL no
        // .env com o tier econômico atual da sua conta (ex.: um modelo "mini").
        'model' => env('OPENAI_CHAT_MODEL', 'gpt-5-mini'),

        'timeout' => (int) env('OPENAI_CHAT_TIMEOUT', 20),
        'connect_timeout' => (int) env('OPENAI_CHAT_CONNECT_TIMEOUT', 5),
        'max_output_tokens' => (int) env('OPENAI_CHAT_MAX_OUTPUT_TOKENS', 500),
        'max_retries' => (int) env('OPENAI_CHAT_MAX_RETRIES', 2),
    ],

    'moderation' => [
        'enabled' => env('OPENAI_MODERATION_ENABLED', true),
        'model' => env('OPENAI_MODERATION_MODEL', 'omni-moderation-latest'),
    ],

    // Limites de conversa / orquestração.
    'limits' => [
        'max_tool_iterations' => (int) env('OPENAI_CHAT_MAX_TOOL_ITERATIONS', 4),
        'max_messages_per_session' => (int) env('OPENAI_CHAT_MAX_MESSAGES_PER_SESSION', 40),
        'max_message_chars' => (int) env('OPENAI_CHAT_MAX_MESSAGE_CHARS', 1000),
        // Histórico de turnos enviado ao modelo (controle de custo/contexto).
        'history_window' => (int) env('OPENAI_CHAT_HISTORY_WINDOW', 16),
        'session_ttl_minutes' => (int) env('OPENAI_CHAT_SESSION_TTL_MINUTES', 120),
        'retention_days' => (int) env('OPENAI_CHAT_RETENTION_DAYS', 30),
        'daily_request_limit' => env('OPENAI_CHAT_DAILY_REQUEST_LIMIT'),
        'daily_budget_usd' => env('OPENAI_CHAT_DAILY_BUDGET_USD'),
        // Validade da proposta de agendamento antes da confirmação (minutos).
        'proposal_ttl_minutes' => (int) env('OPENAI_CHAT_PROPOSAL_TTL_MINUTES', 10),
    ],

    // Rate limiting das rotas públicas do chat.
    'rate_limit' => [
        'messages_per_minute' => (int) env('OPENAI_CHAT_MESSAGES_PER_MINUTE', 12),
        'sessions_per_hour_per_ip' => (int) env('OPENAI_CHAT_SESSIONS_PER_HOUR_PER_IP', 20),
        'min_seconds_between_messages' => (int) env('OPENAI_CHAT_MIN_SECONDS_BETWEEN_MESSAGES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Regras de agendamento (disponibilidade)
    |--------------------------------------------------------------------------
    |
    | Decisões documentadas em docs/ai-context. Centralizadas aqui para que o
    | chat e o formulário tradicional compartilhem exatamente as mesmas regras.
    |
    */
    'scheduling' => [
        // Timezone oficial para cálculo e exibição de horários.
        'timezone' => env('AGENDA_TIMEZONE', 'America/Sao_Paulo'),
        // Antecedência mínima (minutos) entre agora e o início do atendimento.
        'min_lead_minutes' => (int) env('AGENDA_MIN_LEAD_MINUTES', 60),
        // Horizonte máximo de agendamento (dias a partir de hoje).
        'max_horizon_days' => (int) env('AGENDA_MAX_HORIZON_DAYS', 60),
        // Status de agendamento que ocupam o horário (bloqueiam o slot).
        'blocking_statuses' => ['agendado', 'atendido'],
        // Origem registrada para agendamentos criados pelo chat com IA.
        'chat_origin' => 'chat_ia',
    ],
];
