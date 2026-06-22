# Visão geral do projeto

## Objetivo

O sistema centraliza a operação de uma barbearia. O núcleo é formado por agenda, clientes, equipe profissional e serviços. Ao redor dele existem estoque/produtos, financeiro, metas, relatórios e configuração da página pública.

Fontes principais: routes/web.php, app/Http/Controllers, app/Models e resources/views.

## Perfis

### Proprietário

Representado por User.role igual a owner. Possui acesso à agenda e às áreas protegidas pelo middleware owner: CRUD de clientes, usuários, serviços e produtos, financeiro, metas, apresentações e configuração da agenda pública.

Fontes: app/Models/User.php; app/Http/Middleware/EnsureUserIsOwner.php; routes/web.php.

### Barbeiro

Representado por User.role igual a barber. Acessa dashboard, agenda, listagem de clientes relacionados a atendimentos e perfil. Na listagem de agenda vê apenas agendamentos cujo barbeiro_id seja seu ID. Pode editar e excluir apenas os próprios agendamentos.

Fontes: app/Models/User.php; AgendamentoController::index(), edit(), update() e destroy(); ClienteController::index().

### Cliente público

Não possui conta de autenticação. Acessa uma URL com public_token de AgendaConfig, fornece nome, e-mail, telefone, serviço, data, hora e observações, e gera ou reutiliza um Cliente por e-mail.

Fonte: PublicAgendamentoController::show() e submitAgendamento().

Não há papel de atendente implementado em User.role. A string attendants aparece apenas como opção de acesso a metas, sem papel correspondente confirmado.

## Módulos existentes

- Dashboard: métricas e próximos agendamentos.
- Agenda interna: calendário/lista, filtros, criação, edição, status e exclusão.
- Página pública: landing com indicadores, carrosséis de serviços/produtos/equipe e fluxo guiado de autoagendamento.
- Clientes: cadastro, edição, ativação/inativação, duplicidade consultiva, histórico e estatísticas.
- Equipe: usuários, papéis, dados profissionais, jornada/pausa e configuração de serviços por profissional.
- Serviços: serviço simples ou combo, duração, preço, comissão, alerta de retorno e observações.
- Produtos/estoque: unidades, produto/combo, preço, saldo, movimentos e histórico de preço.
- Financeiro: transações, metas e indicadores derivados de agendamentos.
- Apresentação mensal: preview HTML, PDF e classe geradora de PowerPoint.
- Perfil e preferências: dados, senha, avatar, layout de navegação.
- Autenticação: login, logout, verificação de e-mail e recuperação de senha.

## Principais fluxos

1. Usuário autenticado consulta e mantém a agenda.
2. Proprietário mantém clientes, profissionais, serviços e produtos.
3. Proprietário configura informações e link público da agenda.
4. Visitante usa o link público para criar um Cliente, se necessário, e um Agendamento.
5. Financeiro agrega agendamentos atendidos e transações para indicadores.
6. Proprietário gera preview/PDF mensal; textos opcionais podem vir da OpenAI.

## Área pública versus autenticada

| Aspecto | Pública | Autenticada |
|---|---|---|
| Entrada | token UUID em /t/{public_token} | sessão web |
| Usuário | visitante | owner ou barber |
| Interface | Blade independente com Alpine inline | layouts/app.blade.php |
| Agendamento | somente criação | listagem, criação, edição e exclusão |
| Cliente | busca por e-mail e criação mínima | CRUD e histórico |
| Autorização | agenda ativa e token válido | auth, verified no dashboard e owner em áreas administrativas |
| Disponibilidade real | não implementada | não implementada |

## Funcionalidades parcialmente implementadas

- Disponibilidade: há configuração de expediente, dias, slots, jornada, pausa e duração, mas não há composição dessas fontes nem bloqueio de conflitos.
- Agendamento público: permite escolher profissional quando há mais de um; com um único profissional, a etapa é pulada.
- Avaliações públicas: a média exibida é o valor fixo 4.8 quando existem serviços executados, sem entidade de avaliação.
- Token de Agendamento: migration e atribuição existem, mas o model não permite mass assignment do campo.
- Reagendamento/cancelamento público: inexistentes.
- Método show de agendamento: a rota existe, o método não.
- PowerPoint: há gerador, mas nenhuma rota/controller o invoca no fluxo encontrado.

## Pontos de extensão baseados no projeto

- Extrair uma camada central de disponibilidade e criação de agendamento, hoje espalhada entre controllers e frontend.
- Reutilizar Service, User, ProfessionalService, ProfessionalSchedule, AgendaConfig, Cliente e Agendamento como fontes.
- Introduzir endpoints públicos controlados para catálogo, disponibilidade, confirmação e criação.
- Reutilizar a configuração OpenAI de config/services.php, mas separar o caso de uso do chat do serviço financeiro.
- Evoluir a coluna lateral pública de 450px, preservando o comportamento responsivo e a identidade específica da landing.

Esses pontos são propostas futuras, não funcionalidades existentes.
