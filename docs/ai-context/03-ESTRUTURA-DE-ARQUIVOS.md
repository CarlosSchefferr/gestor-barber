# Estrutura de arquivos

## Árvore resumida

~~~text
.
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   └── Auth
│   │   ├── Middleware
│   │   └── Requests
│   ├── Models
│   ├── Notifications
│   ├── Providers
│   ├── Services
│   │   └── Financeiro
│   └── View/Components
├── bootstrap
├── config
├── database
│   ├── factories
│   ├── migrations
│   └── seeders
├── public
│   └── images
├── resources
│   ├── css
│   ├── js
│   ├── lang
│   └── views
│       ├── admin
│       ├── agenda-config
│       ├── agendamentos
│       ├── auth
│       ├── clientes
│       ├── components
│       ├── emails
│       ├── financeiro
│       ├── layouts
│       ├── products
│       ├── profile
│       ├── public
│       └── services
├── routes
├── tests
│   ├── Feature/Auth
│   └── Unit
├── composer.json
├── package.json
├── tailwind.config.js
└── vite.config.js
~~~

vendor, node_modules, public/build, storage e caches foram omitidos por serem dependências ou gerados.

## app/Http/Controllers

Recebe requisições, valida dados, consulta models e escolhe respostas. Deve ser alterado quando o contrato HTTP ou a orquestração do caso de uso mudar.

Arquivos centrais:

- AgendamentoController.php: agenda autenticada.
- PublicAgendamentoController.php: página e submit públicos.
- AgendaConfigController.php: identidade, expediente e imagens da agenda pública.
- ClienteController.php: clientes, histórico e APIs auxiliares.
- AdminController.php: equipe, jornada e serviços profissionais.
- ServiceController.php: catálogo de serviços.
- ProductController.php: produtos e estoque.
- Financeiro*Controller.php: indicadores e apresentação.

## app/Http/Middleware

EnsureUserIsOwner.php implementa a única autorização customizada. O alias owner é registrado em bootstrap/app.php. Altere somente ao mudar política de acesso global de proprietário.

## app/Http/Requests

LoginRequest.php encapsula validação e rate limiting de login. ProfileUpdateRequest.php valida perfil. Os demais fluxos validam dentro dos controllers.

## app/Models

Entidades Eloquent, fillable, casts e relações. Mudanças aqui precisam ser comparadas com migrations, controllers, factories e views.

Para agenda: AgendaConfig, AgendaImagem, Agendamento, Cliente, User, Service, ProfessionalService e ProfessionalSchedule.

## app/Notifications

EmployeeInvitationNotification e ResetPasswordNotification enviam e-mail síncrono pelo canal mail. Não existe notificação de agendamento.

## app/Services/Financeiro

- MonthlyPresentationDataService: agrega métricas.
- MonthlyPresentationInsightService: chama OpenAI e aplica fallbacks.
- PowerPointGeneratorService: gera PPTX em storage/app/temp.

É a única área com serviços de aplicação explícitos. O futuro chat deve manter separação equivalente, sem reutilizar o serviço financeiro como serviço conversacional.

## database/migrations

Fonte versionada do esquema. Antes de alterar uma entidade, leia a migration de criação e todas as migrations posteriores que tocam a tabela.

## database/factories

Factories de User, Cliente e Agendamento. Úteis em testes, mas algumas não incluem campos hoje obrigatórios em controllers.

## database/seeders

DatabaseSeeder cria um owner de desenvolvimento. RealisticBarbershopSeeder produz grande massa fictícia, profissionais, serviços, agenda, produtos, financeiro e página pública. Não use valores do seeder como regra de produção.

## resources/views

Templates Blade. A área autenticada herda layouts/app.blade.php; autenticação usa layouts/guest.blade.php; public/agendamento.blade.php é documento HTML independente.

Altere:

- components quando o padrão é reutilizável;
- layouts para estrutura global;
- a pasta do módulo para uma página específica;
- public apenas para a experiência pública.

## resources/css

app.css contém Tailwind base/components/utilities e componentes v2/nav/select. tailwind.config.js define fontes e paleta barber.

## resources/js

- app.js inicializa Alpine e comportamentos globais.
- bootstrap.js configura Axios e X-Requested-With.
- products.js concentra interações do módulo de produtos.

Grande parte do JavaScript de agenda permanece inline nas views.

## routes

- web.php: rotas de domínio públicas e autenticadas.
- auth.php: login, logout, verificação e reset.
- console.php: apenas o comando inspire.

Não existe routes/api.php neste projeto.

## tests

Pest com testes Feature e exemplos Unit. Os testes usam SQLite em memória. Consulte 12-TESTES-E-QUALIDADE.md antes de tomar uma suite verde como premissa.

## config e bootstrap

config contém drivers e nomes de variáveis. bootstrap/app.php configura rotas, alias owner, health check e exceções padrão. bootstrap/providers.php registra providers.

## public

index.php é a entrada web; images contém logos e imagens estáticas. Uploads são gravados no disco public e servidos via storage link.
