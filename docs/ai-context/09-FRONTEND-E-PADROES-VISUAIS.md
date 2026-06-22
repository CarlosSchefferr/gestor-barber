# Frontend e padrões visuais

## Tecnologia

- Blade server-rendered.
- Alpine.js inicializado em resources/js/app.js.
- Tailwind CSS 3 com plugin forms.
- Vite para app.css, app.js e products.js.
- Axios configurado, embora vários fluxos usem fetch.
- Bootstrap Icons via CDN na área autenticada.

Não há Livewire, Vue, React ou TypeScript.

## Identidade

tailwind.config.js define:

- sans: Figtree + fallback Tailwind;
- display: Merriweather/Georgia, registrado mas pouco usado;
- barber-500 #c96f1f como cor primária;
- barber-600 #a45317 como hover;
- escala clara barber-50 a escura barber-900;
- barber-red #6b0f0f e barber-black #0b0b0b.

Neutros usam principalmente zinc. Sucesso usa emerald; erro/cancelamento usa red; agendado usa blue.

Fonte adicional: DESIGN_PATTERNS.md.

## Estrutura da área autenticada

layouts/app.blade.php oferece topbar ou sidebar conforme User.navigation_layout. O conteúdo usa:

- v2-shell;
- v2-container max-w-7xl;
- v2-main;
- painéis brancos;
- footer com relógio de Brasília.

Navigation usa fundo zinc escuro e links/barber como destaque.

## Componentes reutilizáveis

- x-custom-select: seletor Alpine pesquisável, teclado e atributos ARIA.
- x-modal: modal Alpine genérico.
- x-primary-button, x-secondary-button, x-danger-button.
- x-text-input e x-input-error.
- x-currency-input, x-percent-input e x-duration-input.
- x-tabbed-card, x-page-title, x-icon-action.
- components/ui/menu-link, nav-link e surface.

Há componentes antigos, como barber-card e barber-button, coexistindo com o sistema v2.

## Formulários

Padrão predominante:

- label text-sm font-semibold text-zinc-700;
- input rounded-2xl;
- border zinc-200;
- background zinc-50;
- px-4 py-3;
- foco barber-500 com ring translúcido;
- erro red-500/red-700.

Campos obrigatórios usam asterisco vermelho. Alguns módulos antigos de serviços ainda usam rounded-md e gray em vez do padrão v2.

## Botões

- Primário: rounded-2xl, barber-500, branco, uppercase pequeno e tracking.
- Secundário: branco, border zinc-300, texto zinc-700.
- Perigo: red-600/red-700.
- Ações compactas: quadrados rounded-xl com ícone e tooltip/title.

No futuro chat, ações principais devem usar barber, e confirmação concluída pode usar emerald como a página pública atual.

## Cards e superfícies

Padrão atual:

- rounded-3xl;
- border zinc-200;
- bg-white ou bg-white/95;
- shadow-sm;
- p-6 ou p-8;
- cabeçalho separado por border-bottom.

## Modais

Há dois padrões:

1. x-modal legado do Breeze, rounded-lg.
2. modais de módulos recentes, overlay zinc-900/60, backdrop blur, rounded-3xl, max-height 90vh e footer fixo.

Para novas telas, o segundo padrão é o mais consistente com DESIGN_PATTERNS.md.

## Alertas e feedback

- Sucesso: bloco emerald-50/200/700.
- Erro: bloco red-50/200/700 com lista.
- Avisos: orange.
- Não há biblioteca global de toast identificada. agenda-config/index.blade.php implementa um toast Alpine local.
- Vários fluxos usam alert() e confirm() nativos.
- Estados carregando aparecem pontualmente como texto Processando... ou elementos animate-spin.

A configuração de agenda atual usa fetch com FormData/JSON, toast local, upload por clique/drag-and-drop, estados saving/uploading/deleting e modal próprio de exclusão.

Para chat, feedback de rede deve ficar dentro da conversa/rodapé, sem depender apenas de alert.

## Estados vazios

Listas recentes usam ícone em círculo zinc-100, título Nenhum ... encontrado e ação primária. O carrossel público mostra ícone e Nenhuma imagem.

## Agenda

agendamentos/index.blade.php implementa calendário próprio em JavaScript, lista, modal de detalhes e modais de criação/edição. Preferências de visualização são guardadas no localStorage.

Status visuais:

- agendado: azul;
- atendido: emerald;
- cancelado: vermelho;
- não compareceu: zinc.

## Responsividade

Padrões:

- containers px-4 sm:px-6 lg:px-8;
- grids começam em uma coluna;
- sm/md/lg adicionam colunas;
- tabelas ficam em overflow-x-auto;
- headers passam de coluna para linha em sm/lg;
- sidebar só é fixa em md+.

O chat deve ser testado pelo menos em mobile estreito, tablet e desktop de duas colunas.

## Acessibilidade observada

Pontos positivos:

- custom select usa listbox, aria-expanded e aria-selected;
- icon-action usa aria-label;
- foco visível em controles v2;
- imagens importantes possuem alt.

Lacunas:

- muitos modais manuais não apresentam gestão completa de foco;
- alguns controles clicáveis são div;
- mensagens públicas não possuem aria-live;
- ícones inline nem sempre têm aria-hidden;
- redução de movimento não é tratada globalmente.

## Identidade específica da página pública

A landing pública atual não replica integralmente a paleta autenticada:

- header navy em gradiente, de #121a2a a #303d56;
- destaque de abas/preços em azul #155dfc;
- chat inspirado em WhatsApp com cabeçalho/botão #075e54;
- fundo do chat #e5ddd5;
- bolha do cliente #dcf8c6 e bolha do sistema branca;
- cards com rounded 10px, em vez de rounded-3xl;
- Inter/system UI no CSS inline.

Isso é comportamento real do worktree e deve ser preservado em mudanças estreitas na página pública, salvo redesign explícito.

## Regras visuais para o chat

- Reutilizar a coluna lateral atual de 450px em desktop e largura total no mobile.
- Manter cabeçalho verde, fundo texturizado e bolhas verde-claro/brancas se o chat evoluir sem redesign.
- Não misturar inadvertidamente a paleta pública navy/blue/green com a paleta barber da área autenticada.
- Manter texto legível, largura de bolha em até 80% e área rolável.
- Usar loaders discretos e estado desabilitado durante ações.
- Separar mensagem conversacional de cards de serviço/horário.
- Manter resumo de confirmação como superfície estruturada.
- Preservar landing e chat empilhados no mobile e o aside direito no desktop.
