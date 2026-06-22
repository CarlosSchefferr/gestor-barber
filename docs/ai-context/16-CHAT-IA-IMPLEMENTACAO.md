# Chat IA de agendamento — implementação

Estado: **implementado**. Substitui o plano de `13-PLANO-CHAT-IA-AGENDAMENTO.md`, que permanece como referência de princípios.

## Visão geral

A coluna direita da página pública (`/t/{public_token}`) passou a ser um chat real com IA que conduz o agendamento usando a OpenAI **Responses API** com function calling. A OpenAI nunca acessa o banco: ela apenas solicita ferramentas controladas pelo Laravel, que validam tudo. O Laravel é a única fonte de verdade para serviços, preços, duração, profissionais, disponibilidade e criação do agendamento.

Sem credenciais (ou com `OPENAI_CHAT_ENABLED=false`), o chat cai automaticamente no **fluxo tradicional**, que compartilha o mesmo backend seguro.

## Arquitetura

```
Frontend (Blade+Alpine)
  -> POST /t/{token}/api/chat/{start,message,proposal/customer,confirm}
     -> ChatController
        -> ChatOrchestrator (loop Responses API + tools)
           -> OpenAiClient (HTTP: /responses, /moderations)
           -> ToolRegistry -> Tools (catálogo, disponibilidade, prepare_booking)
              -> AvailabilityService (fonte única de disponibilidade)
        -> ChatBookingService.confirm (transacional + idempotente)
           -> BookingService.create (lock + revalidação) -> Agendamento
```

### Disponibilidade central — `App\Services\Agenda\AvailabilityService`

Fonte **única** reutilizada pelo chat e pelo formulário tradicional. Considera: escopo (barbeiro/owner), dias de atendimento, expediente da agenda, jornada e pausa do profissional (`ProfessionalSchedule`), duração real (`ProfessionalService.time_minutes` ou `Service.duration`), ocupação por agendamentos não cancelados, timezone oficial, antecedência mínima e horizonte máximo. `ends_at` nulo é tratado como ocupação mínima de `intervalo_slots`.

Decisões documentadas:
- Sistema é **uma única barbearia**; profissionais = usuários `barber`/`owner`. Não há vínculo profissional↔agenda no schema; se houver multiunidade no futuro, criar esse vínculo.
- Aptidão = `professional_services`; sem nenhum vínculo para o serviço, fallback para todos os profissionais.
- Horários gravados são interpretados como wall-clock no fuso oficial (a app roda em UTC), evitando deslocamento.

### Criação transacional — `App\Services\Agenda\BookingService`

`DB::transaction` + `lockForUpdate` na linha do profissional (serializa criações concorrentes) + revalidação de conflito + criação/reuso de cliente por e-mail. Lança `SlotUnavailableException` (recuperável) em conflito.

### Confirmação idempotente — `App\Services\Chat\ChatBookingService`

`confirm()` faz lock da proposta, idempotência (proposta já confirmada devolve o mesmo agendamento), revalidação integral e delega ao `BookingService`. `attachCustomer()` grava dados pessoais (nunca enviados ao modelo).

### Orquestrador — `App\Services\Chat\ChatOrchestrator`

Monta `instructions` (prompt estático versionado, no início para cache) + histórico mascarado + mensagem. Loop de function calling limitado por `max_tool_iterations`. Modera entrada, registra consumo (`chat_usages`) e auditoria de tools (`chat_tool_calls`). Erros da OpenAI viram mensagem segura + fallback.

### Tools (allowlist) — `App\Services\Chat\Tools`

`get_business_information`, `list_services`, `list_professionals`, `get_available_dates`, `get_available_times`, `prepare_booking`. Schemas estritos (`strict: true`, `additionalProperties: false`, `required` completo). IDs validados no backend; escopo resolvido do token, nunca de argumentos. `prepare_booking` cria a proposta e devolve o token **apenas à interface**, nunca ao modelo. Não existe nenhuma tool genérica (sem `run_sql`/`execute_query`).

## Arquivos

- Config: `config/chat.php`; `.env.example` (sem segredos).
- Migrations: `2026_06_21_000001..000006` (origin+índices em agendamentos; `chat_sessions`, `chat_messages`, `chat_booking_proposals`, `chat_tool_calls`, `chat_usages`).
- Models: `ChatSession`, `ChatMessage`, `ChatBookingProposal`, `ChatToolCall`, `ChatUsage`; `Agendamento` ganhou `public_token` e `origin` em `fillable`.
- Services: `Agenda\{AvailabilityService,BookingService,ResolvedSlot,Exceptions\SlotUnavailableException}`; `OpenAI\{OpenAiClient,Exceptions\OpenAiException}`; `Chat\{ChatOrchestrator,ChatSessionManager,ChatBookingService,SystemPrompt,Support\PiiMasker,Exceptions\ChatBookingException,Tools\*}`.
- HTTP: `Controllers\Public\ChatController`; `Requests\Chat\{ChatMessageRequest,ChatProposalCustomerRequest,ChatConfirmRequest}`; `PublicAgendamentoController::submitAgendamento` refatorado.
- Rotas: grupo `t/{public_token}/api/chat/*` (`public.chat.*`), com throttle. Submit tradicional ganhou `throttle:public-booking`.
- Rate limiters: `AppServiceProvider` (`chat-start`, `chat-message`, `chat-confirm`, `public-booking`).
- Command: `chat:purge-expired` (agendado em `routes/console.php`, diário 03:30).
- Frontend: `resources/views/public/agendamento.blade.php` (modo `ai` | `tradicional` | `loading`).
- Testes: `tests/Feature/Chat/*` + helpers em `tests/Pest.php`.

## OpenAI

- API: **Responses API** (`POST /v1/responses`) + function calling + JSON Schema estrito. Moderação: `POST /v1/moderations` com `omni-moderation-latest` (gratuita).
- Não usa Assistants API nem Chat Completions (o financeiro segue com Chat Completions, intocado).
- Modelo configurável em `OPENAI_CHAT_MODEL` (default econômico `gpt-5-mini`). Centralizado em `config/chat.php`; o nome não é espalhado pelo código.
- Timeout/connect-timeout/retries, `max_output_tokens`, histórico limitado e respostas de tool compactas controlam custo.

## Segurança

- Isolamento por barbearia: escopo sempre do `public_token` → `AgendaConfig`; sessão e proposta amarradas à barbearia e à sessão.
- Prompt injection: entrada e conteúdo de banco tratados como dados; validação de servidor independe do prompt.
- XSS: respostas do modelo renderizadas como texto (`x-text`), nunca `innerHTML`; serviços/horários/proposta vêm de dados estruturados do backend.
- PII: dados pessoais coletados em formulário seguro, nunca enviados ao modelo; mascaramento em histórico e logs. Chave nunca chega ao navegador.
- Concorrência: lock + revalidação transacional + idempotency key. Confirmação exige ação explícita do frontend.

## Limites e custo

`config/chat.php > limits` e `rate_limit`: iterações de tool, mensagens por sessão, tamanho da mensagem, janela de histórico, TTL de sessão/proposta, retenção, limites diários (opcionais), throttle por IP/sessão. Consumo registrado em `chat_usages` (tokens in/out/cache, latência, status, response_id).

## Testes

`tests/Feature/Chat`: disponibilidade (livre/ocupado/sobreposição/duração/dia/passado/aptidão/inativa/cancelado), booking tradicional + 409, concorrência/idempotência/proposta expirada/sem dados, tools (escopo/validação/token não exposto), endpoints (fallback/410/honeypot/404), orquestrador com `Http::fake` (tool loop, 429, tool desconhecida, iterações, usage) e fluxo completo de confirmação (idempotência, token manipulado, sessão cruzada). 40 testes novos, todos verdes.

Falhas preexistentes (8) não relacionadas permanecem: `RegistrationTest` (2), `PasswordResetTest` (3), `ClientesTest` (1), `ExampleTest` (1), `StylesTest` (1).

## Riscos remanescentes

- Modelo default (`gpt-5-mini`) é um placeholder econômico: confirme o nome exato disponível na sua conta em `OPENAI_CHAT_MODEL`.
- Teste de concorrência real (duas conexões simultâneas) não roda em SQLite; a garantia é validada logicamente (lock + revalidação + idempotência) e pelo design MySQL.
- A lista de horários do **fallback tradicional** é gerada no cliente como conveniência; a autoridade é o backend (rejeita com 409). O chat IA usa a disponibilidade real via tool.
- Sem sistema de avaliações: `media_avaliacoes` segue placeholder fora do escopo desta tarefa.

## Configuração necessária (o que preencher no `.env`)

```env
OPENAI_API_KEY=sk-...            # sua chave (https://platform.openai.com/api-keys)
OPENAI_CHAT_MODEL=gpt-5-mini     # modelo econômico com function calling/Structured Outputs
OPENAI_CHAT_ENABLED=true
# Opcionais: OPENAI_PROJECT, OPENAI_ORGANIZATION, limites e timeouts (ver .env.example)
```

Passos:
1. Preencher as variáveis acima no `.env`.
2. `php artisan config:clear`.
3. (Uma vez) `php artisan migrate`.
4. Acessar `/t/{public_token}` — o chat com IA aparece automaticamente; sem chave, o formulário tradicional continua funcionando.
5. Agendar o scheduler em produção (`php artisan schedule:run` via cron) para a limpeza diária.
