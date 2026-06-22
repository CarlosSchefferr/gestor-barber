# Plano do chat com IA para agendamento

> **Atualização:** o chat com IA foi **implementado**. A descrição da arquitetura final, arquivos, rotas, tools, segurança, testes e configuração está em `16-CHAT-IA-IMPLEMENTACAO.md`. Este documento permanece como registro dos princípios e do plano original.

## Escopo

Este é um plano técnico. Os componentes abaixo foram implementados conforme `16-CHAT-IA-IMPLEMENTACAO.md`.

## Princípios inegociáveis

1. O backend é a única fonte oficial de verdade.
2. O modelo nunca acessa o banco diretamente.
3. O modelo nunca decide sozinho que um horário está disponível.
4. O modelo nunca cria registro sem confirmação explícita do cliente.
5. Toda tool valida autenticação/escopo, entrada e regras novamente.
6. Disponibilidade exibida é temporária e deve ser revalidada no momento da confirmação.
7. Texto do usuário e conteúdo do banco são dados não confiáveis, nunca instruções de sistema.

## Base real reutilizável

- routes/web.php: grupo público por public_token.
- PublicAgendamentoController: resolução da agenda pública.
- AgendaConfig: identidade, expediente, dias e intervalo.
- AgendaImagem: galeria.
- User: profissionais.
- ProfessionalSchedule: jornada e pausa.
- ProfessionalService: serviço, duração, preço e comissão por profissional.
- Service: catálogo e duração padrão.
- Cliente: cadastro/reuso.
- Agendamento: persistência atual.
- resources/views/public/agendamento.blade.php: landing à esquerda e encaixe visual do chat na coluna lateral direita.
- resources/css/app.css e tailwind.config.js: identidade.
- config/services.php: nomes de configuração OpenAI já existentes.
- MonthlyPresentationInsightService: referência de fallback/timeout, mas não deve ser usado como serviço de chat.

Antes do chat, é necessário resolver as lacunas de disponibilidade descritas em 06 e 14.

## Arquitetura recomendada

~~~mermaid
flowchart LR
    UI["Chat público Blade + Alpine"] --> API["Endpoint de conversa"]
    API --> ORQ["Orquestrador de conversa"]
    ORQ --> OAI["OpenAI Responses API"]
    OAI --> ORQ
    ORQ --> TOOLS["Tools allowlisted"]
    TOOLS --> CAT["Catálogo"]
    TOOLS --> AV["Disponibilidade"]
    TOOLS --> BOOK["Confirmação e reserva"]
    CAT --> DB["Eloquent / banco"]
    AV --> DB
    BOOK --> DB
~~~

A documentação oficial recomenda Responses API para novos projetos e descreve function calling para ligar o modelo a funções da aplicação. Usar tool schemas estritos evita argumentos fora do contrato, mas não substitui validação do servidor:

- https://developers.openai.com/api/docs/guides/migrate-to-responses
- https://developers.openai.com/api/docs/guides/function-calling
- https://developers.openai.com/api/docs/guides/structured-outputs

## Componentes provavelmente necessários

Nomes são propostas e devem ser adaptados ao padrão escolhido na implementação:

- serviço central de disponibilidade;
- serviço transacional de criação/reagendamento;
- orquestrador OpenAI separado do serviço financeiro;
- controller público de conversa;
- sessão/conversa persistida com expiração;
- DTOs ou schemas de tool;
- rate limiter dedicado;
- auditoria de tools;
- testes de concorrência e contrato.

Se novas tabelas forem necessárias para conversa, idempotência, holds ou auditoria, elas devem ser projetadas em etapa própria. Nenhuma existe hoje.

## Separação de responsabilidades

### Conversa

Responsável por linguagem natural, tom, perguntas, correções e resumo. Pode decidir qual informação pedir, mas não validar regras finais.

### Interpretação de intenção

Transforma a fala em candidatos estruturados: serviço, profissional, data, turno e dados do cliente. IDs canônicos só devem vir de tools.

### Execução

Funções PHP determinísticas consultam catálogo/disponibilidade e, após confirmação, criam o agendamento. O resultado da tool é a única base para respostas factuais.

## Tools mínimas

### get_barbershop_info

Entrada: agenda pública resolvida no servidor, sem aceitar user_id arbitrário.

Saída: nome, descrição, contato, endereço e política de atendimento confirmada.

### list_services

Entrada opcional: busca textual.

Saída: service_id, nome, descrição, duração/preço aplicáveis e profissionais aptos.

Nunca usar nomes inventados nem aceitar preço do modelo.

### list_professionals

Entrada opcional: service_id.

Saída: apenas profissionais pertencentes ao escopo real da agenda e aptos ao serviço. O vínculo de unidade ainda precisa ser definido, pois não existe no esquema atual.

### get_available_slots

Entrada: service_id, professional_id opcional, data ou intervalo de datas.

Saída: slots calculados naquele instante, com identificador opaco/expiração se adotado.

Deve compor:

- AgendaConfig.ativa;
- dias_atendimento;
- horario_inicio/horario_fim;
- ProfessionalSchedule.entry_time/exit_time;
- break_start/break_end;
- duração de ProfessionalService ou fallback Service.duration;
- agendamentos não cancelados que se sobreponham;
- timezone oficial;
- futuros bloqueios/folgas somente quando existirem no código.

### prepare_booking

Entrada: IDs canônicos, slot, nome, e-mail, telefone e observação.

Saída: resumo sem gravar Agendamento. Pode normalizar dados e retornar preço/duração oficiais.

### create_booking

Entrada: token/id do resumo confirmado e idempotency key.

Pré-condições:

- confirmação explícita armazenada no turno atual;
- revalidação integral;
- transação;
- proteção de concorrência;
- cliente criado/reutilizado de modo determinístico;
- insert único;
- auditoria.

Saída: ID, horário final, profissional e serviço confirmados. Nunca aceitar uma tool call espontânea como confirmação humana.

## Algoritmo de disponibilidade

1. Resolver AgendaConfig pelo token e exigir ativa.
2. Normalizar data no timezone oficial.
3. Validar dia em dias_atendimento.
4. Resolver profissional dentro do escopo.
5. Resolver serviço ativo e aptidão.
6. Determinar duração efetiva: ProfessionalService.time_minutes se existir; caso contrário, regra de fallback explicitamente aprovada.
7. Intersectar expediente global e jornada profissional.
8. Remover pausa.
9. Gerar candidatos conforme granularidade definida.
10. Remover qualquer intervalo com sobreposição a agendamento relevante.
11. Remover passado e janela mínima, se uma regra vier a ser aprovada.
12. Retornar apenas candidatos calculados pelo servidor.

Sobreposição padrão a formalizar:

existing.starts_at < requested.ends_at e existing.ends_at > requested.starts_at.

Como ends_at é hoje anulável, a política para registros sem fim precisa ser decidida.

## Concorrência e duplicidade

Uma consulta seguida de insert sem lock é insuficiente. A implementação deve escolher e testar uma estratégia compatível com MySQL:

- transação e lock de um recurso serializável por profissional/data;
- tabela de slots/holds com constraint única;
- ou outra estratégia atômica comprovada.

Também usar idempotency key para repetição de clique/rede. Constraint apenas em barbeiro_id + starts_at não impede sobreposição parcial e não basta para serviços com durações diferentes.

## Confirmação explícita

Antes de create_booking, mostrar:

- serviço e duração;
- profissional;
- data, início e fim;
- preço oficial, quando aplicável;
- nome, e-mail e telefone mascarado;
- observações.

Perguntar de forma inequívoca se o cliente confirma. Correções invalidam o resumo anterior e exigem nova disponibilidade.

## Correções durante a conversa

Manter slots estruturados separados do texto:

- selected_service_id;
- selected_professional_id;
- selected_slot;
- customer fields;
- confirmation_version.

Se o cliente mudar serviço, invalidar profissional/slot incompatíveis. Se mudar profissional, invalidar slot. Se mudar data, consultar novamente. Nunca editar apenas o texto do resumo.

## Prevenção de invenções

- Instrução de sistema explícita: fatos operacionais só vêm de tools.
- Tool schemas com strict true, required completo e additionalProperties false.
- Enums e IDs opacos quando possível.
- Não colocar todo o banco no prompt.
- Responder Não encontrei quando a tool retorna vazio.
- Não converter conhecimento geral do modelo em regra da barbearia.
- Preço, duração e disponibilidade sempre renderizados a partir da saída server-side.

## Prompt injection

Tratar toda mensagem como dado. O usuário não pode:

- redefinir instruções;
- solicitar chave/segredo;
- pedir consulta arbitrária;
- escolher nome de tool;
- enviar SQL;
- trocar agenda/tenant;
- pular confirmação.

As tools não devem aceitar SQL, nomes de classe, URI interna ou user_id bruto fora do contexto resolvido.

A documentação oficial recomenda limitar entrada e saída e restringir valores a fontes confiáveis:
https://developers.openai.com/api/docs/guides/safety-best-practices

## Rate limiting e custos

- limitar mensagens por minuto e por conversa;
- limitar conversas por IP/token de agenda;
- limitar tamanho de entrada e tokens de saída;
- encerrar conversa ociosa;
- impedir loops de tool;
- cachear somente catálogos não sensíveis com expiração;
- definir teto diário por agenda;
- registrar tokens/latência/status sem conteúdo pessoal excessivo;
- usar modelo configurável por ambiente;
- não reutilizar OPENAI_MODEL silenciosamente sem avaliar impacto no módulo financeiro.

## Histórico e privacidade

- armazenar identificador de sessão opaco;
- guardar o mínimo necessário;
- separar dados estruturados de mensagens;
- criptografar/limitar acesso conforme política a definir;
- definir retenção e descarte;
- mascarar PII em logs;
- nunca enviar senha, CPF, salário ou agenda de outros clientes;
- obter confirmação antes da gravação de dados pessoais.

Uso de store do provedor versus histórico local precisa de decisão de privacidade. Não confirmado.

## Logs e auditoria

Registrar:

- conversation_id e agenda_config_id;
- tool solicitada/permitida;
- IDs canônicos envolvidos;
- hash/idempotency key;
- resultado de disponibilidade;
- confirmação explícita;
- criação ou conflito;
- latência e status OpenAI;
- falha/fallback.

Não registrar chave API, prompt de sistema completo, senha ou PII integral.

## Falha e fallback

Se a OpenAI estiver indisponível:

- não perder dados já digitados;
- não alegar disponibilidade;
- oferecer o fluxo tradicional somente depois de ele usar o mesmo backend seguro;
- permitir contato por telefone;
- exibir mensagem neutra;
- nunca criar agendamento por suposição.

Se a IA não compreender:

1. pedir esclarecimento curto;
2. oferecer opções vindas de tool;
3. após tentativas limitadas, encaminhar ao fluxo estruturado/contato.

## Estratégia de testes

- unitários do cálculo de interseção, pausa, duração e timezone;
- feature de cada tool;
- contrato dos schemas;
- conversa com troca de serviço/profissional/data;
- confirmação ausente;
- prompt injection;
- tool inexistente/argumentos extras;
- agenda inativa/token inválido;
- concorrência real no MySQL;
- idempotência;
- falha e timeout OpenAI;
- fallback sem chave;
- rate limit e limites de contexto;
- privacidade/logs;
- testes E2E mobile/desktop.

Evals conversacionais devem medir: resolução correta, ausência de invenção, número de turnos, tool correta, confirmação e recuperação de erro.

## Critérios de aceite

- nenhum slot é exibido sem cálculo de backend;
- nenhum insert ocorre sem confirmação;
- duas confirmações concorrentes não duplicam horário;
- mudar dados invalida resumo antigo;
- serviço/profissional inválido não chega ao banco;
- agenda inativa bloqueia conversa operacional;
- fallback não cria registro;
- logs não expõem segredos/PII integral;
- autorização e tenant são derivados do token, não do modelo;
- suite existente e novos testes passam, com falhas preexistentes tratadas conscientemente.

## Riscos técnicos

- ausência atual de tenant explícito;
- ends_at anulável;
- serviço textual no Agendamento;
- divergência de duração entre Service, ProfessionalService e AgendaConfig;
- ausência de bloqueios/folgas;
- timezone indefinido;
- falta de constraint de conflito;
- contrato público frontend/controller divergente;
- API atual usa Chat Completions no financeiro, enquanto o novo fluxo recomendado usa Responses;
- custo e latência;
- indisponibilidade do provedor;
- retenção de PII;
- prompt injection;
- regressão na página pública.
