# Fluxo de agendamento

## Visão geral

Existem dois fluxos: interno autenticado e público por token. Nenhum deles executa uma consulta real de disponibilidade ou protege a criação contra concorrência.

## Fluxo público atual

### Origem

O proprietário obtém a URL por AgendaConfig::getPublicUrl() na tela resources/views/agenda-config/index.blade.php. A rota contém o UUID public_token da agenda.

### Etapas da interface

1. Visitante abre GET /t/{public_token}.
2. PublicAgendamentoController::show() exige AgendaConfig ativa e retorna public/agendamento.blade.php.
3. Alpine chama GET /t/{token}/api/config.
4. O frontend inicia uma conversa guiada e pergunta nome.
5. Pergunta e-mail.
6. Pergunta telefone.
7. Mostra serviço.
8. Pergunta profissional quando há mais de um; com apenas um, define o ID e pula a pergunta.
9. Pergunta data, com data mínima de amanhã calculada no navegador.
10. Gera horários entre horario_inicio e horario_fim, em passos de intervalo_slots.
11. Pergunta horário.
12. Pergunta observação.
13. O botão com ícone de confirmação envia POST /api/submit.

### Processamento no servidor

1. Localiza AgendaConfig ativa pelo token ou retorna 404.
2. Valida os oito campos da requisição.
3. Procura Cliente pelo e-mail.
4. Se não encontrar, cria Cliente com nome, e-mail, telefone e active true.
5. Combina data e hora com Carbon::parse().
6. Define fim somando intervalo_slots da AgendaConfig.
7. Cria Agendamento com status agendado.
8. Retorna JSON de sucesso com ID.
9. O frontend mostra estado de sucesso.

### Dados obrigatórios

Nome, e-mail, telefone, ID de barbeiro, serviço textual, data futura e hora. Observação é opcional.

### Validações realmente executadas

- token válido e agenda ativa;
- formatos e presença;
- data posterior ao dia atual;
- user existente para barbeiro_id.

Não são executadas validações de:

- papel ou vínculo do profissional;
- serviço existente/ativo;
- aptidão do profissional;
- dia de atendimento;
- expediente;
- pausa;
- duração real;
- conflito;
- disponibilidade;
- confirmação resumida dos dados.

### Confirmação e notificações

O clique no botão final é a única confirmação explícita. Não existe etapa de resumo para o usuário revisar todos os campos. Nenhuma notificação de agendamento é enviada.

### Falhas

- Validação retorna 422 JSON, mas o frontend não verifica response.ok; ele tenta interpretar o JSON e mostra erro genérico.
- Falha de rede cai no catch e libera o botão.
- Não há tratamento específico para slot ocupado.
- A chamada de configuração possui catch, mas apenas encerra o estado de carregamento sem mensagem específica.

### Concorrência

Duas requisições com o mesmo profissional e intervalo podem criar dois registros. Não há transação, lock, reserva temporária, constraint ou verificação imediatamente anterior ao insert.

## Fluxograma público confirmado

~~~mermaid
flowchart TD
    A["GET /t/{public_token}"] --> B{"Agenda ativa e token válido?"}
    B -- "Não" --> C["404"]
    B -- "Sim" --> D["Renderiza public/agendamento.blade.php"]
    D --> E["GET /api/config"]
    E --> F["Formulário conversacional coleta dados"]
    F --> G["POST /api/submit"]
    G --> H{"Validação HTTP passa?"}
    H -- "Não" --> I["Resposta 422"]
    H -- "Sim" --> J{"Cliente encontrado por e-mail?"}
    J -- "Não" --> K["Cria Cliente"]
    J -- "Sim" --> L["Reutiliza Cliente"]
    K --> M["Calcula ends_at com intervalo_slots"]
    L --> M
    M --> N["Cria Agendamento status agendado"]
    N --> O["Retorna JSON de sucesso"]
~~~

O fluxograma não contém uma etapa de disponibilidade porque ela não existe no código atual.

## Fluxo interno atual

### Origem

Usuário autenticado abre /agendamentos. A mesma tela oferece calendário, lista, filtros e modal Novo agendamento.

### Criação

1. Controller carrega clientes, todos os users e todos os services.
2. A view mostra cliente, barbeiro, início, fim, serviço, preço, status e observações.
3. Para barber, o seletor visual de barbeiro contém apenas o próprio usuário.
4. Serviço selecionado pode preencher preço no navegador.
5. POST /agendamentos valida campos.
6. user_id recebe o usuário autenticado.
7. Agendamento::create() grava diretamente.
8. Redirect volta à agenda com mensagem de sucesso.

Não há cálculo de fim por duração, disponibilidade ou transação.

### Edição, cancelamento e reagendamento

- Editar usa PUT/PATCH /agendamentos/{id}.
- Para barber, controller exige que barbeiro_id atual pertença ao usuário.
- Alterar datas funciona como reagendamento.
- Alterar status para cancelado funciona como cancelamento.
- Excluir remove fisicamente.

Não há validação de propriedade para owner porque owner pode operar todos.

## Cadastro de cliente durante agenda

O formulário legado _form.blade.php pode usar POST /clientes/inline. Esse endpoint aceita data de nascimento opcional e preenche 25 anos atrás quando ausente. O modal principal da agenda analisado seleciona clientes existentes.

## Requisitos para o futuro

Antes de conectar IA, o sistema precisa de uma operação única de backend que:

1. receba IDs canônicos de serviço e profissional;
2. calcule duração efetiva;
3. aplique dias e expediente;
4. aplique jornada e pausa;
5. desconte conflitos e futuros bloqueios confirmados;
6. revalide sob transação;
7. grave somente após confirmação;
8. retorne erro de conflito recuperável.

Esses itens são plano, não descrição do comportamento atual.
