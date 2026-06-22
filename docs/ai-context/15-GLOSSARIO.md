# Glossário

## Agenda

Conjunto de agendamentos e sua representação em lista/calendário. Também aparece em AgendaConfig como configuração da página pública.

## AgendaConfig

Entidade que guarda nome da barbearia, contato, expediente global, intervalo de slots, dias de atendimento, status e public_token.

## AgendaImagem

Imagem ordenada do carrossel da página pública.

## Agendamento

Registro ligado a Cliente, barbeiro e opcionalmente usuário criador. Possui início, fim, status, serviço textual, preço, cor e observações.

## Atendido

Status de Agendamento usado como atendimento concluído nas métricas de cliente e financeiro.

## Atendimento

Termo de interface para um agendamento executado ou para o histórico do cliente. Não existe model Atendimento separado.

## Barbeiro

User com role barber. Em agendamentos, é o User referenciado por barbeiro_id. Owner também é retornado como profissional na API pública atual.

## Bloqueio

Indisponibilidade deliberada de agenda. Não existe entidade ou fluxo confirmado no código.

## Cancelado

Status permitido de Agendamento. O cancelamento interno é feito alterando status; não há endpoint dedicado.

## Cliente

Pessoa atendida, armazenada em clientes. Na página pública é localizada/criada pelo e-mail.

## Combo de produto

Product com registration_type de combo e itens em product_combo_items.

## Combo de serviço

Service com type combo e componentes em combo_services.

## Disponibilidade

Conjunto de horários possíveis. Hoje não há cálculo completo: o model e a página pública geram slots teóricos a partir do expediente, sem ocupação.

## Duração

Tempo em minutos. Pode existir em Service.duration e ProfessionalService.time_minutes. O submit público usa AgendaConfig.intervalo_slots como duração, o que é uma divergência.

## Expediente

Intervalo global horario_inicio–horario_fim de AgendaConfig. A jornada profissional usa entry_time–exit_time.

## Folga

Dia sem trabalho de um profissional. Não há estrutura confirmada.

## Intervalo de slots

AgendaConfig.intervalo_slots, inteiro de 15 a 120 minutos. O model o usa como passo e o submit público como duração.

## Jornada profissional

ProfessionalSchedule: entrada, saída, início e fim da pausa de um User.

## Não compareceu

Status permitido de Agendamento.

## Owner / proprietário

User com role owner. Possui acesso administrativo via middleware owner.

## Página pública

URL /t/{public_token} que exibe identidade da barbearia e autoagendamento guiado sem login.

## ProfessionalService

Configuração específica entre User e Service: duração, preço e comissão.

## Profissional

Termo geral para User agendável. No código, profissionais são normalmente users owner/barber.

## Public token

UUID usado para acessar AgendaConfig pública. Agendamento também possui coluna public_token, cuja finalidade não está confirmada.

## Reagendamento

Alteração de starts_at/ends_at pelo update geral na área autenticada. Não há fluxo público dedicado.

## Serviço

Item de catálogo em services, com nome, duração, preço, status e tipo. Agendamento guarda apenas o nome textual em servico.

## Slot

Horário candidato de início. AgendaConfig::getAvailableSlots() gera slots teóricos sem verificar ocupação.

## Status

Estado textual do Agendamento: agendado, atendido, cancelado ou não compareceu.

## Usuário criador

Agendamento.user_id. No fluxo interno é Auth::id(); no público é AgendaConfig.user_id. O significado uniforme é Não confirmado.
