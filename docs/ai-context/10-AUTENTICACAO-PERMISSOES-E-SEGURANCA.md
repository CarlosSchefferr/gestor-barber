# Autenticação, permissões e segurança

## Autenticação

Laravel Breeze com guard web, provider Eloquent User e sessão. Login usa e-mail/senha, regenera a sessão no sucesso e invalida/regenera CSRF no logout.

Fontes: config/auth.php; AuthenticatedSessionController; LoginRequest.

O registro público está desativado nas rotas. Usuários são criados pelo proprietário em AdminController e recebem convite por e-mail com senha provisória.

## Recuperação e verificação

- Reset de senha usa password_reset_tokens, expira em 60 minutos e possui throttle de geração de 60 segundos.
- User sobrescreve a notificação padrão por ResetPasswordNotification.
- Verificação de e-mail usa URL assinada e throttle 6 por minuto.
- Apenas /dashboard exige middleware verified.

## Rate limiting

- Login: máximo de 5 tentativas pela chave e-mail normalizado + IP; LoginRequest.
- Reenvio/verificação: throttle 6,1.
- Rotas públicas de agendamento: sem throttle explícito.

## Autorização

- auth protege área interna.
- owner é alias de EnsureUserIsOwner.
- AdminController também checa role owner internamente.
- Barbeiro tem verificações manuais em AgendamentoController e ClienteController.
- Não existem Policy ou Gate customizados.

### Matriz resumida

| Recurso | Owner | Barber | Público |
|---|---:|---:|---:|
| Dashboard | sim | sim | não |
| Agenda/lista | todos | próprios | não |
| Criar agenda | sim | sim | via token |
| Editar/excluir agenda | todos | próprios | não |
| Administração | sim | não | não |
| Financeiro | sim | não | não |
| Config agenda pública | sim | não | não |
| Clientes | CRUD | listagem relacionada e endpoints parciais | criação indireta no submit |

## CSRF

Rotas estão no grupo web. Formulários Blade usam @csrf. O submit público lê o token da meta tag e o envia em X-CSRF-TOKEN. Isso mitiga submissões cross-site em navegadores com sessão/cookie, mas não substitui rate limiting.

## Validação

Validação é feita majoritariamente nos controllers. Login/Profile usam Form Request. IDs usam exists em vários pontos, mas a existência não prova autorização nem vínculo com a barbearia.

## Dados pessoais

O sistema armazena nome, e-mail, telefone, data de nascimento, CPF, endereço/bairro, foto, observações, salário e dados de agenda.

Não foram encontrados:

- política de retenção;
- consentimento;
- criptografia de campos;
- anonimização;
- trilha de acesso;
- serviço de exclusão LGPD;
- mascaramento estruturado em logs.

Não confirmado se requisitos jurídicos externos são tratados fora do código.

## Logs e exceções

Configuração padrão stack/single. Não há handlers customizados, logs de auditoria da agenda ou logs explícitos da chamada OpenAI. failed_jobs existe, mas fila atual é sync.

## Riscos confirmados

### Agendamento público sem controle de abuso

POST público não tem throttle, limite por token/IP/e-mail, CAPTCHA ou cota. Pode criar clientes e agendamentos repetidos.

### Ausência de concorrência

Sem verificação e lock, duas submissões podem reservar o mesmo intervalo.

### Escopo de profissional

barbeiro_id público só precisa existir em users. A configuração retorna todos os owner/barber do banco, sem relação de unidade confirmada.

### Serviço textual

servico público é texto livre validado apenas por tamanho. Preço e duração canônicos não são vinculados ao agendamento.

### Criação interna por barber

A view limita o seletor, mas AgendamentoController::store() não força barbeiro_id igual a Auth::id().

### Exclusão em cascata

Excluir cliente ou profissional pode apagar agendamentos e alterar histórico/financeiro.

### PII em APIs internas

ClienteController::show() retorna o model de Cliente em JSON. Endpoints de duplicidade retornam dados pessoais. A autorização deve ser mantida e revista.

### Token público

AgendaConfig usa UUID único e agenda ativa. É razoavelmente imprevisível, porém funciona como bearer URL: quem possui o link acessa e submete. Não há expiração/rotação exposta na interface.

### Timezone

Aplicação UTC, UI Brasília e datas do navegador podem produzir divergências nos limites de dia.

### Uploads

Imagens de agenda e avatar são validadas por tipo/tamanho e gravadas no disco public. Não foi encontrada varredura de malware ou reprocessamento de imagem.

### Configuração de debug

APP_DEBUG é variável de ambiente. Em produção deve permanecer false para não expor stack/ambiente.

## Futuro chat: controles necessários

- throttle específico por agenda, IP e sessão anônima;
- limite de mensagens, tamanho e duração de conversa;
- validação server-side de cada tool;
- allowlist de tools e schemas estritos;
- nunca interpolar instruções do cliente em mensagens de sistema;
- não enviar ao modelo CPF, salário, senhas ou dados de outros clientes;
- minimizar histórico enviado;
- proteção contra prompt injection e exfiltração;
- idempotency key e transação na confirmação;
- auditoria sem conteúdo sensível excessivo;
- timeout, retries controlados e circuit breaker;
- política de custos e cota por agenda;
- resposta segura quando OpenAI falhar.

Essas medidas não estão implementadas no chat, pois o chat ainda não existe.
