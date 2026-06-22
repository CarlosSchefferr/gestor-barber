# Pendências e dúvidas

## Prioridade crítica para o futuro chat

| Item | Evidência/módulo | Pergunta ou ação de decisão |
|---|---|---|
| Não há disponibilidade real | AgendamentoController; PublicAgendamentoController; AgendaConfig::getAvailableSlots() | Qual deve ser a regra oficial de conflito e concorrência? |
| Horários públicos apenas teóricos | public/agendamento.blade.php, gerarHorarios() | A UI hoje segue expediente global; como aplicar jornada, duração, dias e ocupação? |
| Duração divergente | Service.duration, ProfessionalService.time_minutes, AgendaConfig.intervalo_slots | Qual fonte tem precedência e qual é o fallback? |
| Sem vínculo de unidade | User, AgendaConfig, getAgendaConfig() | O sistema é uma única barbearia ou multiunidade/multitenant? |
| Serviço textual no agendamento | agendamentos.servico | O futuro fluxo manterá snapshot textual ou adicionará vínculo canônico? |
| ends_at anulável | migration de agendamentos | Como conflitos tratam registros sem fim? |
| Timezone | config/app.php e layouts/app.blade.php | UTC ou America/Sao_Paulo é a regra oficial de armazenamento/exibição? |
| Bloqueios/folgas ausentes | nenhuma migration/model/rota | Essas regras existem fora do sistema ou precisam ser projetadas? |

## Inconsistências confirmadas

### Token do agendamento

PublicAgendamentoController::submitAgendamento() envia public_token para Agendamento::create(), mas app/Models/Agendamento.php não inclui public_token em fillable.

### Rota show sem método

Route::resource('agendamentos') cria agendamentos.show, mas AgendamentoController não tem show().

### Rota delete de cliente sem método

O resource de clientes cria destroy, porém ClienteController declara que destroy foi removido.

### Registro desativado com testes ativos

routes/auth.php comenta /register; RegisteredUserController, view e RegistrationTest permanecem.

### Status concluido

AdminController::show() conta status concluido, mas validação e views usam atendido.

### Agenda pública e catálogo

O controller retorna todos os profissionais owner/barber globais; não há escopo por agenda. ownerId é usado somente nos indicadores.

### Configuração parcialmente aplicada

horario_inicio, horario_fim e intervalo_slots são usados para gerar candidatos no navegador. dias_atendimento não é retornado nem aplicado; jornada, pausa, duração e ocupação continuam ignoradas.

### Indicador de avaliação sem fonte

media_avaliacoes é 4.8 sempre que há serviços executados. Não existe entidade de avaliação encontrada.

### Relação carregada sem uso

getAgendaConfig() eager-loads user.agendamentos futuros, mas a coleção não participa do JSON/disponibilidade.

## Regras espalhadas

- status aparece em controllers, views, models financeiros e seeder;
- duração aparece em Service, ProfessionalService, AgendaConfig e campos manuais da agenda;
- expediente aparece em AgendaConfig, ProfessionalSchedule e cálculos fixos de AdminController;
- cliente ativo aparece em Cliente, controllers e listagens;
- escopo barber é aplicado manualmente em vários métodos, sem Policy.

## Código duplicado ou acoplado

- AgendamentoController repete validação em store/update.
- A página pública concentra landing, carrosséis e conversa em um único Blade com JavaScript inline.
- Agenda interna mantém muito JavaScript inline em um Blade de mais de mil linhas.
- AdminController concentra listagem, métricas, jornada, serviços e convite.
- Validações de usuário e normalização permanecem no controller.
- Padrões v2 coexistem com views/componentes antigos gray/rounded-md.

## Débitos técnicos e riscos

- Sem transação na criação pública/interna de agenda.
- Sem constraints/índices temporais úteis para busca de conflito.
- Exclusões em cascata removem histórico.
- E-mail não é único em clientes, embora o público o use como identidade.
- storeInline inventa data de nascimento de 25 anos atrás.
- getBarbeiros interno retorna todos os users, não só barbeiros.
- verificações de autorização estão em controllers, não centralizadas.
- exceções/logs não são customizados.
- chamadas OpenAI não têm retry, log nem captura explícita de falha de conexão.
- configuração Browsershot é declarada, mas não aplicada explicitamente no controller.
- PowerPointGeneratorService não tem rota de uso encontrada.
- README é o padrão do Laravel e não explica o produto.

## Funcionalidades incompletas ou não confirmadas

- cancelamento/reagendamento público;
- notificação de agendamento;
- escolha pública de profissional;
- bloqueios, folgas, feriados e férias;
- horários por dia da semana do profissional;
- política de não comparecimento;
- janela mínima/máxima de agendamento;
- política de cancelamento;
- cobrança/pagamento;
- WhatsApp API;
- avaliações reais; media_avaliacoes é 4.8 fixa quando há serviços;
- uso de products no agendamento público;
- uso do campo usuario_admin;
- papel atendente;
- uso de return_alert_days;
- atualização automática de Cliente.last_appointment_at;
- finalidade operacional de Agendamento.public_token.

## Testes pendentes

Não há cobertura aparente para a página pública, disponibilidade, concorrência, agenda config, jornada, serviços profissionais, owner, estoque, financeiro ou OpenAI. A suite atual tem 8 falhas; ver 12-TESTES-E-QUALIDADE.md.

## Perguntas ao responsável

1. O produto atende uma única barbearia ou várias unidades/proprietários isolados?
2. Owner também é profissional agendável?
3. Quem define a duração: serviço geral ou configuração por profissional?
4. intervalo_slots é granularidade de início ou duração?
5. O agendamento pode começar hoje?
6. Cancelado deve liberar o horário? E não compareceu?
7. Qual antecedência e horizonte máximo?
8. Como tratar almoço, folga, férias e feriados?
9. O cliente pode escolher profissional ou aceitar qualquer disponível?
10. Qual dado identifica cliente: e-mail, telefone ou combinação?
11. Deve haver atualização dos dados do cliente existente?
12. O preço precisa ser congelado no agendamento?
13. Exclusão física de agenda é intencional?
14. Qual timezone oficial?
15. O histórico de conversa pode ser armazenado e por quanto tempo?
16. A confirmação deve ser uma mensagem Sim ou um botão inequívoco?
17. Qual orçamento/limite por conversa para OpenAI?
18. Deve haver atendimento humano como fallback?
