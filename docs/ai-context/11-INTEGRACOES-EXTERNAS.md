# Integrações externas

## OpenAI

### Estado

OpenAI está integrada em dois fluxos independentes:

1. **Apresentações financeiras** — `MonthlyPresentationInsightService`, via Chat Completions (`/chat/completions`), inalterado.
2. **Chat IA de agendamento (página pública)** — implementado via **Responses API** (`/responses`) + function calling + moderação (`/moderations`). Camada própria e desacoplada (`App\Services\OpenAI\OpenAiClient` + `App\Services\Chat\*`), configuração centralizada em `config/chat.php`. Ver `16-CHAT-IA-IMPLEMENTACAO.md`. Reutiliza a mesma `OPENAI_API_KEY`; modelo configurável em `OPENAI_CHAT_MODEL`.

### Responsável

app/Services/Financeiro/MonthlyPresentationInsightService.php:

- generate() cria textos e fallbacks;
- buildPrompts() monta contexto agregado;
- callOpenAi() chama /chat/completions.

config/services.php define services.ai.

### Configuração

- OPENAI_API_KEY.
- OPENAI_ENDPOINT, default https://api.openai.com/v1.
- OPENAI_MODEL, default gpt-4o-mini.

Nenhum valor foi lido ou documentado.

### Contrato

Usa Illuminate\Support\Facades\Http, bearer token, timeout de 25 segundos, temperature 0.6, max_tokens 80 e mensagens system/user.

### Falhas, retries e logs

- Sem chave: retorna fallback local.
- Resposta HTTP não bem-sucedida: retorna fallback.
- Conteúdo vazio: retorna fallback.
- Não há retry, log, métrica de custo ou captura explícita de exceção de conexão.
- Não há webhook.

### Riscos

As métricas agregadas são enviadas ao provedor. Não incluem PII direta no contexto construído, mas nomes de barbeiro/serviço aparecem nos fallbacks e parte das métricas; o contexto enviado inclui nome de serviço e barbeiro destaque. Política de privacidade: Não confirmada.

## E-mail

Laravel Mail é configurado por config/mail.php. O ambiente observado usa SMTP.

Usos:

- EmployeeInvitationNotification: credenciais provisórias e link de login.
- ResetPasswordNotification: link de redefinição.

Ambas usam canal mail de forma síncrona. Queueable é incluído, mas as notificações não implementam ShouldQueue.

Variáveis: MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS e MAIL_FROM_NAME.

Não há notificação de agendamento, cancelamento ou reagendamento.

## Browsershot

FinanceiroPresentationController::downloadPDF() renderiza uma view Blade e usa Spatie Browsershot para gerar PDF A4 paisagem.

Configuração declarada em config/services.php:

- BROWSERSHOT_NODE_BINARY;
- BROWSERSHOT_NPM_BINARY;
- BROWSERSHOT_CHROME_PATH;
- BROWSERSHOT_NO_SANDBOX;
- BROWSERSHOT_TIMEOUT.

O controller analisado não aplica explicitamente esses valores ao builder; uso efetivo dessas opções é Não confirmado.

Não há retry ou tratamento customizado de erro.

## PHPPresentation

PowerPointGeneratorService cria PPTX 16:9 e salva em storage/app/temp. A classe existe, porém nenhuma rota/controller encontrada a invoca. Integração no fluxo de usuário: Não confirmada.

## Fontes e ícones por CDN

- Figtree via fonts.bunny.net nos layouts.
- Bootstrap Icons 1.11.3 via jsDelivr no layout autenticado.
- Views financeiras de apresentação referenciam Google Fonts.

Falhas de CDN podem afetar tipografia/ícones. Não há fallback local de Bootstrap Icons.

## Armazenamento

Uploads usam o disco public do Laravel:

- agenda-imagens;
- avatars;
- clientes/fotos;
- imagens de produtos.

AWS/S3 está disponível na configuração padrão do Laravel, mas uso ativo não foi confirmado.

## WhatsApp

A configuração da agenda sugere compartilhar o link via WhatsApp. Não existe cliente, API, webhook ou envio automatizado para WhatsApp.

## APIs internas

Os caminhos /api/barbeiros, /api/servicos e /api/produtos são endpoints web autenticados, não integração externa. A página pública usa endpoints sob /t/{token}/api.

## Ausências

Não foram encontrados pagamentos, calendário externo, SMS, Google Calendar, Meta/WhatsApp API, webhook de terceiros ou serviço de notificação de agendamento.
