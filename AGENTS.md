# AGENTS.md

## Projeto

Gestor Barber é uma aplicação Laravel para gestão de barbearia: agenda interna, autoagendamento público, clientes, profissionais, serviços, produtos/estoque, financeiro, metas e relatórios.

## Stack

- PHP 8.2+; ambiente analisado com PHP 8.4.19.
- Laravel 12.33.0, Blade, Eloquent e Laravel Breeze.
- Vite 7, Tailwind CSS 3, Alpine.js 3 e Axios.
- MySQL no ambiente local; Pest 4 com SQLite em memória nos testes.

## Antes de alterar

Leia, nesta ordem:

1. docs/ai-context/00-LEIA-PRIMEIRO.md
2. docs/ai-context/06-REGRAS-DE-NEGOCIO.md
3. docs/ai-context/07-FLUXO-DE-AGENDAMENTO.md
4. o documento específico do módulo alterado
5. DESIGN_PATTERNS.md para mudanças de interface

## Comandos essenciais

- composer install
- npm install
- composer run dev
- php artisan migrate:status
- php artisan route:list --except-vendor
- php artisan test
- npm run build

## Regras para agentes

- Preserve a arquitetura MVC, os componentes Blade, a paleta barber e as convenções existentes.
- Não invente tabelas, serviços, relacionamentos, permissões ou regras. Confirme tudo no código.
- Nunca exponha valores de .env, credenciais, tokens ou dados pessoais.
- Não trate o frontend como fonte de verdade para disponibilidade.
- Hoje não há uma regra central e transacional que impeça sobreposição de agendamentos. Não afirme o contrário.
- A duração do serviço, o expediente da agenda, a jornada/pausa profissional e os agendamentos existentes devem ser reconciliados no backend antes de qualquer automação de agenda.
- O modelo de IA nunca deve acessar o banco diretamente nem criar um agendamento sem confirmação explícita.
- Toda mutação de agenda deve ser revalidada no servidor e protegida contra concorrência.
- Execute testes proporcionais à mudança e registre falhas preexistentes separadamente.
- O chat com IA de agendamento foi implementado (Responses API + tools controladas pelo Laravel). Ver docs/ai-context/16-CHAT-IA-IMPLEMENTACAO.md. A disponibilidade é centralizada em App\Services\Agenda\AvailabilityService e a criação é transacional em App\Services\Agenda\BookingService, compartilhadas pelo chat e pelo formulário tradicional.

Toda a documentação técnica está em docs/ai-context.
