# Regras de negócio

Cada item abaixo distingue regra aplicada de ausência confirmada.

## Agenda e criação

### Dados mínimos internos

Um agendamento interno exige Cliente existente, User existente como barbeiro e starts_at válido. ends_at, serviço, preço e status podem ser omitidos.

Fonte: app/Http/Controllers/AgendamentoController.php, métodos store() e update().

Impacto: a agenda permite registros sem fim e sem serviço; não há cálculo automático no backend.

### Dados mínimos públicos

O submit público exige nome, e-mail, telefone, barbeiro_id, serviço textual, data futura e hora H:i. A data precisa ser after:today, logo o mesmo dia é recusado pelo servidor.

Fonte: PublicAgendamentoController::submitAgendamento().

Exceção: o input HTML usa min igual ao dia atual, mas o servidor exige depois de hoje. Hoje pode aparecer selecionável e ser rejeitado.

### Cliente público por e-mail

O cliente é localizado pelo primeiro registro com e-mail igual. Se não existir, cria-se cliente ativo com nome, e-mail e telefone. Se já existir, nome e telefone enviados não são atualizados.

Fonte: PublicAgendamentoController::submitAgendamento().

Impacto: e-mail funciona como identificador de aplicação, mas não é único no banco.

### Criador e proprietário

No fluxo interno, user_id recebe Auth::id(). No público, user_id recebe AgendaConfig.user_id. barbeiro_id vem da requisição pública.

Fontes: AgendamentoController::store(); PublicAgendamentoController::submitAgendamento().

Não confirmado: se user_id representa proprietário, criador ou unidade em todos os registros.

## Duração

Service.duration armazena minutos. ServiceController converte HH:MM para minutos. ProfessionalService.time_minutes permite duração específica por profissional.

Fontes: ServiceController::store()/update(); migrations de services e professional_services.

No submit público, ends_at é starts_at + AgendaConfig.intervalo_slots, não Service.duration nem ProfessionalService.time_minutes.

No formulário interno, starts_at e ends_at são campos independentes; não há validação de ordem nem cálculo por serviço.

## Disponibilidade e conflito

### Geração de slots

AgendaConfig::getAvailableSlots() gera horários entre horario_inicio e horario_fim em passos de intervalo_slots. O parâmetro date não é usado e nenhuma consulta de agendamentos é feita.

Fonte: app/Models/AgendaConfig.php, método getAvailableSlots().

### Página pública

public/agendamento.blade.php gera, no navegador, horários de horario_inicio até antes de horario_fim, em passos de intervalo_slots, com defaults 08:00, 18:00 e 30 minutos.

Fonte: resources/views/public/agendamento.blade.php, função gerarHorarios().

Ela usa horario_inicio, horario_fim e intervalo_slots, mas não usa dias_atendimento, serviço, profissional ou ocupação.

### Sobreposição

Não existe consulta de conflito em AgendamentoController ou PublicAgendamentoController. Não existe constraint que evite dois horários iguais e não há transaction/lock na criação de agendamento.

Impacto: agendamentos simultâneos ou sobrepostos são aceitos, inclusive sob concorrência.

### Expediente, pausa e dias

AgendaConfig armazena expediente global e dias de atendimento. ProfessionalSchedule armazena entrada, saída e pausa do profissional. AdminController mantém esses dados.

Nenhum desses dados é aplicado à criação pública ou interna.

Fontes: AgendaConfigController; AdminController::store()/update(); models AgendaConfig e ProfessionalSchedule.

### Bloqueios e folgas

Não foram encontrados model, migration, controller ou rota para bloqueios, folgas, férias ou feriados. Funcionalidade: Não confirmada.

## Profissionais e serviços

O cadastro administrativo aceita role owner ou barber e mantém uma jornada e serviços por profissional.

Fonte: AdminController::validationRules(), store() e update().

professional_services impõe uma configuração por par profissional/serviço.

A API pública retorna todos os users com papel owner ou barber, sem vínculo explícito com AgendaConfig.user_id. O submit aceita qualquer user existente, mesmo que não seja owner/barber.

Fonte: PublicAgendamentoController::getAgendaConfig() e submitAgendamento().

Não há regra de tenant/unidade confirmada no esquema.

Serviços públicos são filtrados por active true no controller atual. O submit, porém, recebe nome textual e não verifica catálogo, atividade ou aptidão do profissional.

## Status e atendimento

Status aceitos em agenda: agendado, atendido, cancelado e não compareceu.

Fontes: AgendamentoController::store()/update() e opções das views.

Cliente.total_appointments e total_revenue contam/somam apenas status atendido.

Fonte: app/Models/Cliente.php.

MonthlyPresentationDataService também usa agendamentos atendidos para faturamento e métricas. AdminController::show() procura concluido em uma métrica isolada; esse valor é inconsistente com o restante.

Não existe máquina de estados: qualquer status aceito pode substituir qualquer outro.

## Cancelamento e reagendamento

Na área interna, cancelar significa editar status para cancelado. Reagendar significa editar starts_at/ends_at pelo update geral.

Não existem endpoints dedicados, motivo obrigatório, prazo, auditoria ou validação de conflito.

Na área pública, não há cancelamento nem reagendamento.

## Clientes

- store exige nome, data_nascimento e telefone.
- storeInline torna data opcional e usa uma data fictícia de 25 anos atrás quando ausente.
- submit público não preenche data_nascimento.
- telefone e nome duplicados são apenas consultados por endpoints; não bloqueiam gravação.
- cliente é desativado por active, não apagado no controller.
- barbeiro só lista clientes com algum agendamento seu e só pode ver/trocar status de cliente que já atendeu.

Fontes: ClienteController.

Inconsistência: store público cria cliente sem data_nascimento embora o CRUD interno a exija.

## Papéis e permissões

owner acessa administração, configuração e financeiro. barber tem escopo limitado em listagem/edição/exclusão da agenda.

Fonte: routes/web.php, EnsureUserIsOwner e AgendamentoController.

Criação interna por barber usa um seletor travado no próprio usuário na view, mas o controller store não força barbeiro_id igual ao usuário autenticado. Uma requisição manipulada pode escolher outro user.

Não há policies ou gates.

## Exclusões

- AgendamentoController::destroy apaga fisicamente.
- ServiceController::destroy apaga fisicamente.
- ProductController::destroy apenas torna active false.
- ClienteController não implementa destroy apesar da rota.
- excluir Cliente ou User pode apagar agendamentos em cascata pelo banco.

Impacto: apagar usuário/cliente pode remover histórico operacional e dados usados no financeiro.

## Notificações

Há e-mails para convite de funcionário e redefinição de senha. Não há notificação de criação, cancelamento ou reagendamento.

Fonte: app/Notifications e chamadas em AdminController/User.

## Timezone

config/app.php usa UTC. O layout mostra America/Sao_Paulo, e inputs usam datas locais do navegador. Não há normalização explícita no fluxo de agendamento.

Regra oficial de armazenamento/exibição: Não confirmada.
