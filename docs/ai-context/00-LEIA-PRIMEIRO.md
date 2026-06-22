# Leia primeiro

## Resumo

Gestor Barber é uma aplicação web Laravel para operação de uma barbearia. O sistema reúne agenda interna, página pública de autoagendamento, cadastro de clientes, usuários/profissionais, catálogo de serviços, produtos e estoque, financeiro, metas e apresentações mensais.

A página pública já apresenta uma conversa guiada em Alpine.js, mas ela é um formulário sequencial, não uma inteligência artificial. Há uma integração OpenAI separada, usada apenas para gerar pequenos textos de apresentações financeiras.

## Tecnologias principais

- PHP 8.2+ e Laravel 12.
- Blade e Eloquent.
- Laravel Breeze para autenticação.
- Vite, Tailwind CSS 3 e Alpine.js 3.
- MySQL na configuração local observada; SQLite em memória no ambiente de testes.
- Pest 4.
- OpenAI Chat Completions no módulo financeiro.
- Browsershot para PDF e PHPPresentation para PowerPoint.

Fontes: composer.json, composer.lock, package.json, package-lock.json, config/database.php, phpunit.xml e app/Services/Financeiro.

## Ordem recomendada de leitura

1. Este índice.
2. 01-VISAO-GERAL-DO-PROJETO.md.
3. 02-STACK-E-ARQUITETURA.md.
4. 04-BANCO-DE-DADOS.md.
5. 05-ROTAS-E-ENDPOINTS.md.
6. 06-REGRAS-DE-NEGOCIO.md.
7. 07-FLUXO-DE-AGENDAMENTO.md.
8. 08-PAGINA-PUBLICA.md.
9. 09-FRONTEND-E-PADROES-VISUAIS.md.
10. 10-AUTENTICACAO-PERMISSOES-E-SEGURANCA.md.
11. 11-INTEGRACOES-EXTERNAS.md.
12. 12-TESTES-E-QUALIDADE.md.
13. 13-PLANO-CHAT-IA-AGENDAMENTO.md.
14. 14-PENDENCIAS-E-DUVIDAS.md.
15. 15-GLOSSARIO.md.

Use 03-ESTRUTURA-DE-ARQUIVOS.md para localização rápida.

## Como uma IA deve estudar o projeto

1. Partir de routes/web.php e routes/auth.php.
2. Seguir a rota até o controller e o método exato.
3. Conferir validação, queries e mutações no controller.
4. Conferir fillable, casts e relacionamentos no model.
5. Confirmar a estrutura em todas as migrations relacionadas.
6. Conferir a view e o JavaScript que enviam os dados.
7. Procurar testes, mas diferenciar existência de teste de cobertura real.
8. Se uma regra não aparecer nesse percurso, registrá-la como Não confirmado.

## Regras obrigatórias ao modificar

- Não criar comportamento com base apenas no texto da interface.
- Não confiar em IDs, preços, duração, status ou disponibilidade enviados pelo navegador ou pelo modelo.
- Reutilizar os padrões Blade, Tailwind e Alpine existentes.
- Preservar os perfis owner e barber e o middleware owner.
- Não expor chaves, tokens públicos completos, dados de clientes ou conteúdo de .env.
- Não introduzir acesso direto do modelo de IA ao banco.
- Não confirmar horário sem uma consulta atual ao backend.
- Não criar agendamento antes da confirmação explícita do cliente.
- Usar transação e estratégia de concorrência na futura operação de reserva.
- Adicionar testes para fluxos felizes, validação, autorização, conflito e concorrência.

## Pontos críticos

1. Não existe hoje verificação de sobreposição no AgendamentoController ou no PublicAgendamentoController.
2. Não existe índice único que impeça dois agendamentos do mesmo profissional no mesmo horário.
3. AgendaConfig::getAvailableSlots() apenas gera horários; não consulta ocupação e ignora o parâmetro de data.
4. A página pública gera horários no navegador a partir de horario_inicio, horario_fim e intervalo_slots, mas não consulta ocupação.
5. dias_atendimento, ProfessionalSchedule e ProfessionalService estão cadastrados, mas não são aplicados ao submit público.
6. A duração real de Service não define ends_at no submit público; usa-se intervalo_slots.
7. O resource de agendamentos registra rota show, porém AgendamentoController não possui método show().
8. Agendamento possui coluna public_token, mas ela não está em fillable; a atribuição via create() no submit público não é persistida.
9. A média pública de avaliações é fixada em 4.8 quando há serviços executados; não foi encontrado cadastro de avaliações.
10. A página pública permite escolher profissional, mas o backend aceita qualquer users.id existente e não confirma papel, vínculo ou aptidão.

## Arquivos deste diretório

- 01-VISAO-GERAL-DO-PROJETO.md: módulos, perfis e fluxos.
- 02-STACK-E-ARQUITETURA.md: tecnologias e organização técnica.
- 03-ESTRUTURA-DE-ARQUIVOS.md: árvore comentada.
- 04-BANCO-DE-DADOS.md: tabelas, colunas e relações.
- 05-ROTAS-E-ENDPOINTS.md: catálogo de rotas e contratos.
- 06-REGRAS-DE-NEGOCIO.md: regras confirmadas e lacunas.
- 07-FLUXO-DE-AGENDAMENTO.md: fluxo atual detalhado.
- 08-PAGINA-PUBLICA.md: implementação pública e pontos de extensão.
- 09-FRONTEND-E-PADROES-VISUAIS.md: identidade e componentes.
- 10-AUTENTICACAO-PERMISSOES-E-SEGURANCA.md: acesso e riscos.
- 11-INTEGRACOES-EXTERNAS.md: OpenAI, e-mail, fontes, ícones e relatórios.
- 12-TESTES-E-QUALIDADE.md: cobertura verificada e comandos.
- 13-PLANO-CHAT-IA-AGENDAMENTO.md: plano futuro sem implementação.
- 14-PENDENCIAS-E-DUVIDAS.md: ambiguidades e débitos.
- 15-GLOSSARIO.md: vocabulário real do projeto.

## Estado da análise

Documentação baseada no worktree de 21/06/2026. Durante a análise, o worktree continha mudanças funcionais concorrentes em AgendaConfigController.php, PublicAgendamentoController.php, agenda-config/index.blade.php e public/agendamento.blade.php, além de .claude/launch.json. Elas não foram produzidas nem alteradas por esta tarefa; foram preservadas e o estado final desses arquivos foi relido antes da revisão.
