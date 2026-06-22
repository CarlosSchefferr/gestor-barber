# Rotas e endpoints

## Convenções

Todas as rotas de domínio estão em routes/web.php e usam o grupo web, portanto possuem sessão e CSRF. Não existe routes/api.php. Os caminhos chamados api ainda são rotas web autenticadas.

## Públicas

| Método e URI | Nome | Responsável | Entrada/saída |
|---|---|---|---|
| GET / | gerado | closure | redireciona para dashboard se autenticado, senão login |
| GET /t/{public_token} | public.agendamento.show | PublicAgendamentoController::show | Blade public.agendamento; 404 se token não existir ou agenda inativa |
| GET /t/{public_token}/api/config | public.agendamento.config | PublicAgendamentoController::getAgendaConfig | JSON de configuração, imagens, profissionais, serviços, produtos e indicadores |
| POST /t/{public_token}/api/submit | public.agendamento.submit | PublicAgendamentoController::submitAgendamento | JSON; valida cliente e dados de agenda; cria Cliente/Agendamento |
| GET /up | gerado | health check Laravel | resposta de saúde |

### Submit público

Entrada validada:

- cliente_nome: obrigatório, string, máximo 255.
- cliente_email: obrigatório, e-mail.
- cliente_telefone: obrigatório, string, máximo 20.
- barbeiro_id: obrigatório, inteiro, existe em users.
- servico: obrigatório, string, máximo 255.
- data_agendamento: obrigatória, data estritamente depois de hoje.
- hora_agendamento: obrigatória, formato H:i.
- observacoes: opcional, string.

Saída de sucesso: success, message e agendamento_id.

Não valida papel do barbeiro, vínculo com a barbearia, existência/atividade do serviço, disponibilidade, duração, dias ou expediente. Não há throttle explícito.

### Config público

Retorna nome_barbearia, descricao, telefone, endereco, horario_inicio, horario_fim, intervalo_slots, imagens, barbeiros, servicos, produtos e indicadores.

No worktree analisado, servicos é coleção de objetos com id, nome, descrição, duração e preço; a view consome nome e preço desses objetos. dias_atendimento não é retornado.

## Autenticação

Rotas guest:

- GET/POST /login.
- GET/POST /forgot-password.
- GET /reset-password/{token}.
- POST /reset-password.

Rotas auth:

- POST /logout.
- GET/POST /confirm-password.
- PUT /password.
- GET /verify-email.
- GET /verify-email/{id}/{hash}, com signed e throttle:6,1.
- POST /email/verification-notification, com throttle:6,1.

O cadastro público /register está comentado em routes/auth.php, embora o controller e a view permaneçam.

## Dashboard e perfil

| Método | URI | Nome | Middleware |
|---|---|---|---|
| GET | /dashboard | dashboard | auth, verified |
| GET | /profile | profile.edit | auth |
| PATCH | /profile | profile.update | auth |
| DELETE | /profile | profile.destroy | auth |
| GET | /profile/settings | profile.settings | auth |
| PATCH | /profile/settings | profile.settings.update | auth |
| PATCH | /profile/preferences | profile.preferences.update | auth |

## Agenda autenticada

Resource /agendamentos, middleware auth:

- GET /agendamentos → index.
- POST /agendamentos → store.
- GET /agendamentos/create → create.
- GET /agendamentos/{agendamento} → show.
- GET /agendamentos/{agendamento}/edit → edit.
- PUT/PATCH /agendamentos/{agendamento} → update.
- DELETE /agendamentos/{agendamento} → destroy.

Entrada de store/update:

- cliente_id e barbeiro_id obrigatórios e existentes.
- starts_at obrigatório e date.
- ends_at opcional e date.
- servico string opcional.
- color hexadecimal opcional.
- price numérico opcional.
- observacoes string opcional.
- status opcional dentro dos quatro status conhecidos.

Saída: redirect para agendamentos.index com mensagem de sessão.

AgendamentoController não implementa show(), portanto a rota GET individual é inconsistente.

## Clientes

Com auth:

- GET /clientes: listagem; barbeiro vê clientes ligados a seus agendamentos.
- PATCH /clientes/{cliente}/toggle-status.
- POST /clientes/check-phone e /check-name: JSON consultivo de duplicidade.
- GET /clientes/{cliente}/statistics e /history: JSON.
- POST /clientes/inline: criação simplificada por qualquer autenticado.
- GET /api/barbeiros, /api/servicos e /api/produtos: JSON auxiliar.

Com auth + owner:

- POST /clientes.
- GET /clientes/create.
- GET /clientes/{cliente}.
- GET /clientes/{cliente}/edit.
- PUT/PATCH /clientes/{cliente}.
- DELETE /clientes/{cliente}.

A rota DELETE existe, mas ClienteController não implementa destroy(). O próprio arquivo informa que clientes devem ser ativados/inativados.

## Configuração pública da agenda

Todas com auth + owner:

- GET /agenda/configuracoes → index.
- PUT /agenda/configuracoes → update.
- POST /agenda/imagens → uploadImages.
- PATCH /agenda/imagens/reorder → reorderImages.
- DELETE /agenda/imagens/{imagem} → deleteImage.

update valida horário em H:i, intervalo entre 15 e 120, dias válidos e exige ao menos um dia por validação manual. update, uploadImages e deleteImage aceitam o fluxo Blade tradicional ou retornam JSON quando a requisição pede JSON; as assinaturas usam a união RedirectResponse|JsonResponse.

## Administração

Com prefixo /admin e auth + owner:

- GET /admin.
- POST /admin/users.
- GET /admin/users/create.
- GET /admin/users/{user}.
- GET /admin/users/{user}/edit.
- PUT /admin/users/{user}.
- DELETE /admin/users/{user}.
- Resource /admin/services.
- POST /admin/services/inline.
- Resource /admin/products.
- POST /admin/products/adjust-stock.
- POST /admin/products/bulk-action.
- POST /admin/products/units.
- PATCH /admin/products/units/{unit}/toggle.
- PATCH /admin/products/{product}/toggle-status.

AdminController ainda aplica uma checagem owner interna, redundante com a rota.

## Financeiro

Com auth + owner:

- GET /financeiro.
- POST /transacoes.
- POST /metas.
- GET /financeiro/apresentacao/mensal.
- GET /financeiro/apresentacao/mensal/pdf.

O preview e o PDF aceitam mes, ano, barbearia_nome e sections.

## Permissões resumidas

- Público: somente as três rotas /t/{token}.
- Autenticado: agenda, perfil, APIs auxiliares, parte de clientes.
- Barber: filtros adicionais e verificações dentro de controllers.
- Owner: toda administração e financeiro.
- verified: exigido apenas no dashboard; outras rotas auth não exigem e-mail verificado.
