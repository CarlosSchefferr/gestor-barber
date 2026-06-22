# Banco de dados

## Fonte e estado

O esquema foi reconstruído a partir de database/migrations e app/Models. Todas as 27 migrations existentes aparecem como executadas no banco local em php artisan migrate:status.

O ambiente local usa MySQL; os testes usam SQLite em memória. Não foi usado conteúdo de registros reais para esta documentação.

## Tabelas de domínio

### users

Representa contas e profissionais.

Colunas principais: id, name, email único, email_verified_at, password, role, remember_token, avatar, date_of_birth, phone, navigation_layout, sidebar_collapsed, cpf único e anulável, professional_name, gender enum M/F/O, salary, cargo, usuario_admin e timestamps.

Regras estruturais:

- role tem default barber, mas os valores owner e barber são validados pelo AdminController.
- navigation_layout default top.
- cpf é único quando preenchido.
- não há soft delete.

Fontes: migrations 0001_01_01_000000, 2025_10_10_000003, 2025_10_10_000004, 2025_11_28_120000, 2026_03_31_000001 e 2026_04_01_000001; app/Models/User.php.

### clientes

Colunas: id, nome, data_nascimento, email anulável, telefone anulável no banco, cep, bairro, observacoes, foto, active default true, last_appointment_at, created_by, updated_by e timestamps.

Chaves:

- created_by e updated_by referenciam users; ao excluir usuário tornam-se null.

Não há unicidade para e-mail, telefone ou nome. Não há soft delete.

Fontes: migrations 2025_10_10_000001, 2025_12_05_000002 e 2026_04_03_000001; app/Models/Cliente.php.

### agendamentos

Colunas: id, cliente_id, barbeiro_id, user_id anulável, starts_at, ends_at anulável, status default agendado, servico anulável, color, price anulável, observacoes, public_token UUID anulável e único, timestamps.

Chaves:

- cliente_id → clientes, cascade delete.
- barbeiro_id → users, cascade delete.
- user_id → users, set null.

Não há:

- service_id;
- índice por barbeiro_id + starts_at;
- constraint de ends_at maior que starts_at;
- constraint de status;
- constraint de não sobreposição;
- soft delete.

servico guarda um nome textual e pode divergir do catálogo services.

Fontes: migrations 2025_10_10_000002, 2025_11_29_000001 e 2026_03_21_000003; app/Models/Agendamento.php.

### services

Colunas: id, type default service, name, description, duration inteiro em minutos default 30, price, active, commission, return_alert_days, observations e timestamps.

type é validado como service ou combo no ServiceController, mas é string sem constraint de banco.

Fonte: migrations 2025_11_28_121500, 2025_11_28_130000, 2025_12_05_000003 e 2026_04_24_174532; app/Models/Service.php.

### combo_services

Pivot autorreferente de services: id, combo_id, service_id e timestamps. Ambas as FKs usam cascade delete. Não há índice único declarado para o par.

### professional_services

Configuração de serviço por profissional: id, user_id, service_id, time_minutes default 30, price, commission_percentage default 0 e timestamps.

O par user_id + service_id é único. Ambas as FKs usam cascade delete.

Fonte: migration 2026_04_01_000002; app/Models/ProfessionalService.php.

### professional_schedules

Jornada por usuário: id, user_id único, entry_time, exit_time, break_start, break_end e timestamps. Horários são anuláveis. A FK usa cascade delete.

Não há dias da semana, folgas, exceções ou validações de ordenação no banco.

Fonte: migration 2026_04_01_000003; app/Models/ProfessionalSchedule.php.

### agenda_configs

Configuração pública: id, user_id único, nome_barbearia, descricao, telefone, endereco, horario_inicio default 08:00, horario_fim default 18:00, intervalo_slots default 30, dias_atendimento JSON, ativa default true, public_token UUID único e timestamps.

user_id referencia users com cascade delete. Existe no máximo uma configuração por usuário.

Fonte: migration 2026_03_21_000001; app/Models/AgendaConfig.php.

### agenda_imagens

Colunas: id, agenda_config_id, caminho_imagem, ordem default 0 e timestamps. A FK usa cascade delete. O model ordena imagens por ordem.

### products

Colunas: id, name, description, brand, product_unit_id, registration_type, usage_type, price, commission_percentage, quantity, minimum_stock, image_path, barcode, active e timestamps.

Índices explícitos em brand, registration_type, usage_type, barcode e active. product_unit_id torna-se null ao excluir unidade.

### product_units

id, name único, abbreviation, active indexado e timestamps.

### product_combo_items

id, combo_product_id, product_id e timestamps. Par combo_product_id + product_id único; FKs em products com cascade delete.

### product_stock_movements

Auditoria de estoque: product_id, type, quantity, stock_before, stock_after, origin_type, origin_id, reason, created_by e timestamps. Índices em type, origin_type, origin_id e product_id + created_at.

### product_price_histories

Histórico de preço: product_id, type, value, created_by e timestamps. Índice composto product_id + type + created_at.

### agendamento_produto

Pivot entre agendamentos e products: quantity, price e timestamps. FKs com cascade delete; índice composto não único em agendamento_id + produto_id.

### transacoes

id, descricao, tipo, valor, data, status default Confirmado e timestamps. tipo é validado como receita ou despesa no controller, sem constraint no banco.

### metas

id, nome, descricao, valor_meta, valor_atual, data_inicio, data_limite, quem_tem_acesso default all, tipo default outro, created_by anulável e timestamps. created_by não possui FK declarada na migration.

## Tabelas de infraestrutura

- password_reset_tokens.
- sessions.
- cache e cache_locks.
- jobs, job_batches e failed_jobs.
- migrations.

Embora existam tabelas de fila, o ambiente observado usa QUEUE_CONNECTION sync e as notificações não implementam ShouldQueue.

## Relacionamentos Eloquent confirmados

~~~text
User -> possui muitos -> Agendamento, como barbeiro_id
User -> possui muitos -> ProfessionalService
User -> possui um -> ProfessionalSchedule
User -> possui uma configuração possível -> AgendaConfig (relação inversa apenas declarada em AgendaConfig)

Cliente -> possui muitos -> Agendamento
Cliente -> possui um último -> Agendamento por starts_at
Cliente -> pertence opcionalmente a -> User, como creator/updater

Agendamento -> pertence a -> Cliente
Agendamento -> pertence a -> User, como barbeiro
Agendamento -> pertence a -> User, como criador user
Agendamento -> pertence a muitos -> Product, por agendamento_produto

AgendaConfig -> pertence a -> User
AgendaConfig -> possui muitas -> AgendaImagem
AgendaImagem -> pertence a -> AgendaConfig

ProfessionalService -> pertence a -> User
ProfessionalService -> pertence a -> Service
Service -> possui muitos serviços de combo -> Service, por combo_services

Product -> pertence a -> ProductUnit
Product -> possui muitos -> ProductStockMovement
Product -> possui muitos -> ProductPriceHistory
Product -> compõe muitos -> Product, por product_combo_items
~~~

## Status e enums

- Agendamento.status validado: agendado, atendido, cancelado, não compareceu.
- AdminController contém uma métrica que procura concluido, valor que não pertence à validação de agendamento. É uma inconsistência.
- User.role: owner ou barber no cadastro administrativo.
- User.gender: M, F ou O.
- Service.type: service ou combo.
- Product.registration_type e usage_type são strings validadas pelo ProductController.
- Transacao.tipo: receita ou despesa.

## Integridade e exclusão

Nenhum model usa SoftDeletes. Clientes são desativados com active; produtos são desativados no destroy; serviços e agendamentos são apagados fisicamente. Excluir Cliente ou barbeiro apaga em cascata seus agendamentos. Isso pode afetar histórico e financeiro.

## Estruturas ausentes para disponibilidade

Não foram encontradas tabelas de bloqueios, folgas, feriados, indisponibilidades, reservas temporárias ou calendário por dia do profissional. Bloqueio e folga são Não confirmado como funcionalidades.
