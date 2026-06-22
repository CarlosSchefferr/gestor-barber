# Página pública

## Rota e controller

- GET /t/{public_token}: PublicAgendamentoController::show().
- GET /t/{public_token}/api/config: PublicAgendamentoController::getAgendaConfig().
- POST /t/{public_token}/api/submit: PublicAgendamentoController::submitAgendamento().

As três rotas estão em routes/web.php. show e config exigem AgendaConfig.ativa igual a true; submit também.

## View

resources/views/public/agendamento.blade.php é um HTML completo e não herda layouts/app.blade.php. Carrega resources/css/app.css e resources/js/app.js pelo Vite e mantém CSS/JavaScript específicos inline.

## Dados carregados

O primeiro GET recebe apenas agendaConfig. Depois, o navegador consulta a configuração JSON.

O JSON atual inclui:

- identificação: nome, descrição, telefone e endereço;
- expediente: horario_inicio, horario_fim e intervalo_slots;
- carrossel: imagens ordenadas;
- profissionais: todos os User com role owner ou barber;
- serviços ativos: ID, nome, descrição, duração e preço;
- produtos vendáveis: ID, nome, descrição, marca, preço e imagem;
- indicadores: clientes distintos atendidos, serviços executados e nota fixa 4.8 quando há atendimentos.

Não inclui dias_atendimento nem slots disponíveis.

## Conteúdo exibido

- Header com nome, endereço e faixa de horário.
- Indicadores de clientes atendidos, serviços executados e média de avaliações.
- Abas Barbeiros, Produtos e Serviços.
- Carrossel horizontal de cards para a aba ativa.
- Serviços com nome, duração, preço e ação de agendar.
- Produtos com imagem, nome, marca e preço.
- Profissionais com avatar/inicial, nome e cargo.
- Coluna lateral de chat com mensagens e inputs por etapa.

## Escolha de serviço e profissional

O serviço é mostrado em um select com nome e preço. A ação de um card também preenche formData.servico. O valor enviado continua sendo o nome textual, não service_id.

O profissional é mostrado em um select quando há mais de um. Com exatamente um, carregarDados() preenche barbeiro_id e o fluxo pula a etapa. O backend continua aceitando qualquer ID existente em users.

## Escolha de data e horário

- O input date usa amanhã, calculado por Date.now() + 24 horas e toISOString(), portanto sujeito a UTC do navegador.
- O servidor exige after:today no timezone da aplicação.
- gerarHorarios() usa horario_inicio, horario_fim e intervalo_slots recebidos da configuração, com defaults 08:00, 18:00 e 30.
- Nenhum endpoint de disponibilidade é chamado.
- dias_atendimento, jornada, pausa, duração do serviço e ocupação não são aplicados.

## Formulário e validação

Etapas: nome, e-mail, telefone, serviço, barbeiro quando necessário, data, hora e observações.

No navegador, apenas a presença do valor avança a maioria das etapas. Não há validação explícita de formato de e-mail/telefone antes do submit. O servidor executa a validação descrita em 05-ROTAS-E-ENDPOINTS.md.

O submit envia JSON com Content-Type application/json e X-CSRF-TOKEN obtido da meta tag.

## Scripts

O componente Alpine barbeariaApp() mantém:

- config;
- passo;
- messages;
- formData;
- servicos, produtos e barbeiros;
- abaAtiva e estado do carrossel;
- horariosDisponiveis;
- carregando;
- data mínima amanhã.

Ele usa fetch, setTimeout para ritmo das mensagens, scroll automático e mensagens genéricas de erro.

Uma única chamada de configuração abastece landing e conversa. Os carrosséis usam scroll horizontal, dots e imagens de agenda como fallback visual dos serviços.

## Padrão visual

- Landing clara com header navy em gradiente.
- Destaques, abas e preços em azul #155dfc.
- Cards brancos com rounded 10px e bordas cinza.
- Coluna de chat inspirada no WhatsApp.
- Cabeçalho e ações do chat em #075e54.
- Fundo do chat #e5ddd5, bolha do cliente #dcf8c6 e bolha do bot branca.
- Tipografia Inter/system UI definida inline, sobrescrevendo parcialmente Figtree.

## Responsividade

- Layout em coluna no mobile e linha em lg.
- A landing aparece antes do chat no mobile.
- Em lg, a landing rola independentemente e o chat ocupa 450px de largura e 100vh.
- Cards do carrossel têm largura fixa de 235px; navegação por scroll funciona em telas estreitas.
- Inputs e botões ocupam a largura inteira.

Risco visual: no mobile, a landing completa precede o chat; a descoberta do agendamento depende de rolagem.

## Acessibilidade

Existem meta viewport e textos visíveis. Pontos não confirmados/limitados:

- inputs não possuem labels associados;
- mensagens não usam aria-live;
- não há controle explícito de foco entre etapas;
- animações não respeitam prefers-reduced-motion;
- imagens de cards usam alt vazio;
- os dots do carrossel são divs clicáveis sem semântica de botão.

## Ponto para o futuro chat

O aside direito é o encaixe visual natural. A função barbeariaApp e o bloco de mensagens podem ser substituídos/evoluídos, preservando:

- largura de 450px em desktop;
- cabeçalho verde;
- área rolável;
- fundo texturizado e bolhas branco/verde-claro;
- rodapé de entrada;
- empilhamento responsivo.

O estado conversacional não deve continuar sendo a autoridade de agenda. Ele deve chamar endpoints controlados de catálogo, disponibilidade e confirmação. Landing, indicadores e catálogos podem permanecer independentes.
