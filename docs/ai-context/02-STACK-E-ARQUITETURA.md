# Stack e arquitetura

## Versões verificadas

| Tecnologia | Restrição declarada | Versão instalada/analisada |
|---|---:|---:|
| PHP | ^8.2 | 8.4.19 CLI |
| Laravel | ^12.0 | 12.33.0 |
| Laravel Breeze | ^2.3 | 2.3.8 |
| Pest | ^4.1 | 4.1.2 |
| Pest Laravel | ^4.0 | 4.0.0 |
| Vite | ^7.0.7 | 7.1.9 |
| Tailwind CSS | ^3.1.0 | 3.4.18 |
| Alpine.js | ^3.4.2 | 3.15.0 |
| Axios | ^1.11.0 | 1.12.2 |
| Browsershot | ^5.2 | 5.2.3 |
| PHPPresentation | ^1.2 | 1.2.0 |

Fontes: composer.json, composer.lock, package.json, package-lock.json e comandos de versão.

Observação: @tailwindcss/vite 4.1.14 está instalado, mas vite.config.js usa apenas laravel-vite-plugin; o CSS usa as diretivas tradicionais do Tailwind 3.

## Linguagens e tecnologias

- PHP para domínio, HTTP, Eloquent, notificações e relatórios.
- Blade para templates.
- JavaScript ES modules.
- Alpine.js para estado e interação no navegador.
- CSS com Tailwind e classes componentes em resources/css/app.css.
- SQL gerado por migrations/Eloquent; conexão local observada como MySQL.

## Padrão arquitetural

Predomina MVC do Laravel:

- routes define entrada e middleware.
- controllers concentram validação, queries e orquestração.
- models Eloquent representam persistência e relações.
- views Blade renderizam HTML e incorporam Alpine/JavaScript.

Há uma camada de serviços apenas no módulo financeiro, em app/Services/Financeiro. Não há repositories, actions, DTOs ou uma camada de domínio para agenda. Form Requests existem para login e perfil; a maioria dos controllers valida diretamente com Request::validate().

## Fluxo de requisição

1. public/index.php inicializa bootstrap/app.php.
2. routes/web.php ou routes/auth.php casa a rota.
3. O grupo web aplica sessão, cookies e CSRF do Laravel.
4. Middlewares auth, verified, guest, signed, throttle ou owner restringem a rota.
5. O controller valida os dados e consulta Eloquent.
6. A resposta é Blade, redirect, JSON, PDF ou e-mail.

No fluxo público:

1. GET /t/{public_token} carrega PublicAgendamentoController::show().
2. Alpine consulta GET /t/{public_token}/api/config.
3. Alpine envia POST /t/{public_token}/api/submit com CSRF.
4. submitAgendamento() busca/cria Cliente e cria Agendamento.

## Organização de responsabilidades

- app/Http/Controllers: regras de aplicação e HTTP.
- app/Http/Middleware: autorização owner.
- app/Http/Requests: login e perfil.
- app/Models: entidades e relações.
- app/Notifications: convite e redefinição de senha por e-mail.
- app/Services/Financeiro: métricas, insights e apresentação.
- database/migrations: esquema oficial versionado.
- resources/views: páginas e componentes.
- resources/js: Alpine global, Axios e scripts de produtos.
- resources/css: sistema visual compartilhado.
- routes: web, autenticação e comando console.
- tests: Pest Feature e Unit.

## Convenções encontradas

- Classes e models em inglês ou singular: User, Service, Product.
- Entidades históricas da agenda em português: Cliente, Agendamento, Transacao, Meta.
- Colunas da agenda usam starts_at e ends_at; catálogos usam name, duration e price.
- Rotas administrativas usam prefixo admin e nomes admin.*.
- Componentes Blade usam x-componente.
- Paleta principal usa barber-500 e barber-600.
- Controllers normalmente retornam mensagens de sessão success ou error.

## Ambientes e drivers

O comando artisan about indicou:

- banco MySQL;
- cache database;
- sessão database;
- fila sync;
- mail smtp;
- logs stack/single;
- broadcasting log.

phpunit.xml substitui banco por SQLite em memória, cache por array, sessão por array, e-mail por array e fila por sync.

config/app.php fixa timezone em UTC. A interface exibe horário de Brasília explicitamente no layout, e o contexto operacional do projeto é America/Sao_Paulo. A regra oficial de timezone de agendamento é Não confirmado.

## Variáveis de ambiente importantes

Somente nomes; valores nunca devem entrar na documentação:

- Aplicação: APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL, APP_LOCALE.
- Banco: DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD.
- Sessão/cache/fila: SESSION_DRIVER, CACHE_STORE, QUEUE_CONNECTION.
- E-mail: MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS, MAIL_FROM_NAME.
- OpenAI: OPENAI_API_KEY, OPENAI_ENDPOINT, OPENAI_MODEL.
- Browsershot: BROWSERSHOT_NODE_BINARY, BROWSERSHOT_NPM_BINARY, BROWSERSHOT_CHROME_PATH, BROWSERSHOT_NO_SANDBOX, BROWSERSHOT_TIMEOUT.
- Arquivos/AWS: FILESYSTEM_DISK, AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET.
- Logs: LOG_CHANNEL, LOG_STACK, LOG_LEVEL.

Fontes: config/*.php e .env.example.

## Tratamento de exceções

bootstrap/app.php não registra handlers customizados. São usados abort(403), firstOrFail(), validação do Laravel e tratamento padrão. O JavaScript público reduz falhas a mensagens genéricas e console.error. Não há exception classes próprias.
