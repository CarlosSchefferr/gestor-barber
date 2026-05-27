# Design Patterns - Gestor Barber

Documento de referência com todos os padrões visuais, componentes e convenções utilizadas no projeto.

---

## 1. Tipografia (Fontes)

### Fontes Disponíveis
**Tailwind Config:**
```javascript
fontFamily: {
  sans: ['Figtree', ...defaultTheme.fontFamily.sans],      // Padrão
  display: ['Merriweather', 'Georgia', 'serif']            // Headings (não utilizado)
}
```

### Uso Recomendado
- **Figtree (sans)**: Todos os elementos do projeto (padrão da aplicação)
- **Merriweather**: Reservado para displays especiais (futuros)

### Tamanhos e Pesos Comuns

| Contexto | Classe Tailwind | Uso |
|----------|-----------------|-----|
| Labels/Placeholders | `text-xs font-semibold` | Labels de inputs, cabeçalhos de tabela |
| Texto pequeno | `text-sm font-semibold` | Descrições, subtextos |
| Texto padrão | `text-base` / `text-sm` | Inputs, conteúdo |
| Títulos grandes | `text-3xl font-bold` | Título de página |
| Títulos seções | `text-lg font-bold` | Subtítulos de card |
| Uppercase labels | `text-xs uppercase tracking-wide` | Headers de tabela, status |

---

## 2. Paleta de Cores

### Cor Principal - Barber
```javascript
barber: {
  50: '#fdf7f1',      // Muito claro (backgrounds)
  100: '#f8ebd9',
  200: '#efd3ab',
  300: '#e6b97f',
  400: '#db934c',
  500: '#c96f1f',     // ⭐ PRIMARY (burnt orange)
  600: '#a45317',     // Hover
  700: '#7b3b0f',     // Darker
  800: '#4f2a0a',
  900: '#2b1704',     // Muito escuro
  red: '#6b0f0f',     // Específico
  black: '#0b0b0b'
}
```

### Cores do Sistema Tailwind

| Cor | Uso | Exemplo |
|-----|-----|---------|
| `zinc-50` | Backgrounds claros | Inputs, cards background |
| `zinc-100` | Backgrounds neutros | Borders, separadores |
| `zinc-200` | Borders | `border-zinc-200` |
| `zinc-400` / `zinc-500` | Texto descritivo | Labels, hints |
| `zinc-700` / `zinc-900` | Texto principal | Títulos, conteúdo |
| `emerald-50/100/600/700` | Status ativo/sucesso | Status badges, alerts positivos |
| `red-50/100/600/700` | Status inativo/erro | Delete buttons, alerts negativos |
| `orange-50/100/600` | Avisos | Modal duplicatas |
| `blue-50/100/600/700` | Info | Status "agendado" |

### Referências de Uso

```html
<!-- Texto descritivo pequeno -->
<p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Mais atendido</p>

<!-- Status ativo -->
<span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
  Ativo
</span>

<!-- Status inativo -->
<span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">
  Inativo
</span>

<!-- Status cancelado -->
<span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
  Cancelado
</span>
```

---

## 3. Espaçamento

### Convenções Gerais

| Elemento | Padding | Gap |
|----------|---------|-----|
| Cards padrão | `p-5` (20px) | `gap-4` ou `gap-5` |
| Cards grandes | `p-6` / `p-8` | `gap-6` |
| Modal body | `p-6 sm:p-8` | `gap-6` |
| Container principal | `px-4 sm:px-6 lg:px-8` | - |
| Seções internas | `py-6` / `py-4` | - |

### Margin/Spacing Padrão

```html
<!-- Header com espaçamento -->
<div class="mb-8 rounded-3xl border...">
  <div class="flex flex-col gap-5">
    <div>
      <p class="text-xs...">SEÇÃO</p>
      <h1 class="mt-2 text-3xl...">Título</h1>
    </div>
    <div class="flex gap-3">
      <!-- Botões, filtros -->
    </div>
  </div>
</div>

<!-- Entre cards -->
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
  <!-- Cards -->
</div>

<!-- Dentro de grupos -->
<div class="space-y-6">
  <!-- Cada elemento terá mt-6 automaticamente -->
</div>
```

### Border Radius

| Componente | Radius |
|-----------|--------|
| Inputs / Custom Select | `rounded-2xl` |
| Cards / Modals | `rounded-3xl` |
| Botões | `rounded-2xl` |
| Avatares | `rounded-full` |
| Ícones pequenos | `rounded-lg` / `rounded-xl` |

---

## 4. Componentes Principais

### 4.1 Input Padrão

```html
<!-- Classe base reutilizável -->
$inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';

<!-- Uso -->
<label class="text-sm font-semibold text-zinc-700">Nome completo <span class="text-red-500">*</span></label>
<input type="text" name="nome" class="{{ $inputClass }}" placeholder="Digite o nome">
```

**Características:**
- Border color: `zinc-200`
- Background: `zinc-50` (padrão), `white` (focus)
- Padding: `px-4 py-3`
- Border radius: `rounded-2xl`
- Shadow: `shadow-sm`
- Focus state: ring `barber-500` + background white

### 4.2 Custom Select

```html
<x-custom-select
  name="cargo"
  :options="['' => 'Selecione', 'Barbeiro' => 'Barbeiro', 'Gerente' => 'Gerente']"
  :value="old('cargo', '')"
  placeholder="Selecione o cargo"
  required
/>
```

**Features:**
- Auto-search quando > 3 opções
- Keyboard navigation (arrow keys, enter, escape)
- Posicionamento dinâmico (dropdown up/down)
- Hidden input para form submission
- Search com filtro em tempo real

### 4.3 Card Padrão

```html
<!-- Classe base reutilizável -->
$cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';

<!-- Uso -->
<div class="{{ $cardClass }} p-6 sm:p-8">
  <div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-zinc-900">Título</h2>
    <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">
      Badge
    </span>
  </div>
  <!-- Conteúdo -->
</div>
```

**Características:**
- Border: `border-zinc-200`
- Background: `bg-white/95` (com transparência)
- Shadow: `shadow-sm`
- Border radius: `rounded-3xl`
- Padding: `p-5` até `p-8`

### 4.4 Botões

#### Botão Primary (Barber)
```html
<button class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
  Ação Principal
</button>
```

#### Botão Secondary
```html
<button class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
  Ação Secundária
</button>
```

#### Botão Danger
```html
<button class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-red-700">
  Excluir
</button>
```

#### Botão Ícone (Tabela/Lista)
```html
<button class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-barber-600" title="Editar">
  <svg class="h-4 w-4"><!-- ícone --></svg>
</button>
```

---

## 5. Indicadores (Status & Stats)

### 5.1 Cards de Estatísticas

```html
<!-- Card de métrica -->
<div class="rounded-3xl border border-zinc-200 bg-white/95 shadow-sm p-5">
  <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Mais atendido</p>
  <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $value }}</p>
  <p class="mt-1 text-sm text-zinc-500">Descrição adicional</p>
</div>

<!-- Com cor diferente -->
<div class="rounded-3xl border border-zinc-200 bg-white/95 shadow-sm p-5">
  <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Mais lucrativo</p>
  <p class="mt-3 text-2xl font-bold text-emerald-700">R$ 1.234,50</p>
  <p class="mt-1 text-sm text-zinc-500">Faturamento</p>
</div>
```

**Estrutura:**
1. Label (xs, semibold, uppercase, tracking-wide)
2. Valor (3xl bold, peut ser colorido)
3. Descrição (xs, text-zinc-500)

### 5.2 Status Badges

```html
<!-- Ativo -->
<span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
  Ativo
</span>

<!-- Inativo -->
<span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">
  Inativo
</span>

<!-- Atendido -->
<span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
  Atendido
</span>

<!-- Agendado -->
<span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
  Agendado
</span>

<!-- Cancelado -->
<span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
  Cancelado
</span>

<!-- Não compareceu -->
<span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-700">
  Não compareceu
</span>
```

### 5.3 Avisos (Alerts)

```html
<!-- Sucesso -->
<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
  <p class="text-sm font-medium text-emerald-700">{{ message }}</p>
</div>

<!-- Erro -->
<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
  <ul class="list-disc pl-5 text-sm text-red-700">
    <li>{{ erro }}</li>
  </ul>
</div>

<!-- Info -->
<div class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
  Mensagem informativa
</div>
```

---

## 6. Cards de Filtros

### Estrutura Padrão

```html
<div class="rounded-3xl border border-zinc-200 bg-white/95 shadow-sm mb-8 p-6 sm:p-7">
  <!-- Header -->
  <div class="mb-5 flex items-center justify-between">
    <h2 class="text-lg font-bold text-zinc-900">Filtros</h2>
    <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">
      Busca avançada
    </span>
  </div>

  <!-- Formulário -->
  <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <!-- Campos de filtro -->
    <div>
      <label class="text-sm font-semibold text-zinc-700 block mb-2">Buscar por nome</label>
      <input type="text" name="search" class="{{ $inputClass }} !mt-0" placeholder="Digite...">
    </div>

    <!-- Custom Select -->
    <div>
      <label class="text-sm font-semibold text-zinc-700 block mb-2">Filtro</label>
      <x-custom-select name="filtro" :options="[...]" placeholder="Selecione" />
    </div>

    <!-- Botões -->
    <div class="md:col-span-3 flex flex-wrap items-center justify-center gap-3 pt-2">
      <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white transition hover:bg-barber-600">
        Aplicar filtros
      </button>
      <a href="{{ route }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
        Limpar
      </a>
    </div>
  </form>
</div>
```

**Características:**
- Header com título + badge de status
- Grid responsivo (1 coluna mobile, múltiplas em desktop)
- Label com font-semibold específica
- Botões centralizados com gap-3
- Padding superior nos botões (pt-2)

### Grid Responsivo Padrão

```
Mobile: md:grid-cols-1
Tablet: md:grid-cols-2 lg:grid-cols-3
Desktop: lg:grid-cols-4
```

---

## 7. Tabelas

### Estrutura Padrão

```html
<div class="rounded-3xl border border-zinc-200 bg-white/95 shadow-sm overflow-hidden">
  <!-- Header Info -->
  <div class="border-b border-zinc-200 px-6 py-4">
    <h3 class="text-lg font-bold text-zinc-900">Título da tabela</h3>
    <p class="mt-1 text-sm text-zinc-500">Descrição</p>
  </div>

  <!-- Tabela Wrapper -->
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <!-- Header -->
      <thead class="bg-zinc-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">
            Coluna 1
          </th>
          <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">
            Coluna 2
          </th>
          <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-zinc-500">
            Ações
          </th>
        </tr>
      </thead>

      <!-- Body -->
      <tbody class="divide-y divide-zinc-100 bg-white">
        <tr class="transition hover:bg-zinc-50">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <!-- Conteúdo -->
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-zinc-900">
            Valor
          </td>
          <td class="px-6 py-4 text-right">
            <!-- Ações -->
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Paginação (opcional) -->
  <div class="border-t border-zinc-200 bg-white px-6 py-4">
    {{ $items->links() }}
  </div>
</div>
```

**Características:**
- Thead: `bg-zinc-50` com spacing específico
- Tbody: `divide-y divide-zinc-100`
- Rows: `hover:bg-zinc-50` em transição
- Padding: `px-6 py-3` (head), `px-6 py-4` (body)
- Text: `text-xs font-bold uppercase` (head)
- Empty state: colspan com centralized content

### Empty State

```html
<tr>
  <td colspan="6" class="px-6 py-12 text-center">
    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
      <svg class="h-6 w-6 text-zinc-400"><!-- ícone --></svg>
    </div>
    <h3 class="text-sm font-bold text-zinc-900">Nenhum {{ item }} encontrado</h3>
    <p class="mt-1 text-sm text-zinc-500">Comece cadastrando um novo {{ item }}.</p>
    <button class="mt-4 ...">Novo {{ item }}</button>
  </td>
</tr>
```

---

## 8. Modais

### Estrutura Modal Padrão

```html
<div id="modalName" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4">
  <div class="w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white shadow-2xl flex flex-col max-h-[90vh]">
    
    <!-- Header -->
    <div class="p-6 sm:p-8 pb-5 shrink-0">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Seção</p>
        <h3 class="mt-2 text-2xl font-bold text-zinc-900">Título Modal</h3>
      </div>
    </div>

    <!-- Abas (se necessário) -->
    <div class="flex shrink-0 px-6 sm:px-8 pb-6 gap-2 border-b border-zinc-200">
      <button 
        @click="abaAtiva = 'dados'" 
        :class="abaAtiva === 'dados' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500'"
        class="flex-1 py-2.5 text-sm font-medium transition-all rounded-xl border"
      >
        Aba 1
      </button>
    </div>

    <!-- Content -->
    <form class="px-6 sm:px-8 py-6 overflow-y-auto flex-1 border-t border-zinc-200">
      @csrf
      
      <!-- Conteúdo dinâmico com x-show -->
      <div x-show="abaAtiva === 'dados'" class="space-y-6">
        <!-- Campos -->
      </div>
    </form>

    <!-- Footer com Buttons -->
    <div class="flex justify-center gap-3 border-t border-zinc-200 p-6 sm:p-8 shrink-0">
      <button type="button" onclick="closeModal()" class="rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 hover:bg-zinc-100">
        Fechar
      </button>
      <button type="button" class="rounded-2xl bg-barber-500 px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white hover:bg-barber-600">
        Salvar
      </button>
    </div>
  </div>
</div>
```

**Características:**
- Overlay: `bg-zinc-900/60 backdrop-blur-sm`
- Container: `fixed inset-0 z-50`
- Modal box: `rounded-3xl` com shadow
- Max-width: `max-w-2xl` até `max-w-7xl` (varia conforme tamanho)
- Max-height: `max-h-[90vh]` com overflow-y-auto
- Flex layout: `flex flex-col` para header/content/footer

### Variações de Tamanho

| Tamanho | Max-width | Uso |
|---------|-----------|-----|
| Pequeno | `max-w-sm` | Confirmações, alerts |
| Médio | `max-w-2xl` | Forms padrão |
| Grande | `max-w-5xl` | Forms com abas (dados + serviços) |
| XL | `max-w-7xl` | Tabelas dentro do modal |

### Modal com Confirmação

```html
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4">
  <div class="w-full max-w-md rounded-3xl border border-zinc-200 bg-white shadow-2xl">
    <div class="p-6 sm:p-8 text-center">
      <!-- Ícone -->
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
        <svg class="h-7 w-7 text-red-600"><!-- ícone --></svg>
      </div>

      <!-- Título e mensagem -->
      <h3 class="text-xl font-bold text-zinc-900 mb-2">Confirmar ação</h3>
      <p class="text-sm text-zinc-600 mb-6">Tem certeza que deseja continuar?</p>

      <!-- Botões -->
      <div class="flex justify-center gap-3">
        <button onclick="closeModal()" class="rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold text-zinc-700 hover:bg-zinc-100">
          Cancelar
        </button>
        <button class="rounded-2xl bg-red-600 px-4 py-3 text-xs font-bold text-white hover:bg-red-700">
          Confirmar
        </button>
      </div>
    </div>
  </div>
</div>
```

### Modal com Abas (Alpine.js)

```html
<div x-data="{ abaAtiva: 'dados' }">
  <!-- Abas -->
  <div class="flex gap-2 border-b border-zinc-200 pb-6">
    <button 
      @click="abaAtiva = 'dados'"
      :class="abaAtiva === 'dados' ? 'bg-barber-50 border-barber-500 text-barber-600' : 'bg-white border-zinc-300'"
      class="flex-1 py-2.5 rounded-xl border"
    >
      Dados
    </button>
    <button 
      @click="abaAtiva = 'servicos'"
      :class="abaAtiva === 'servicos' ? 'bg-barber-50 border-barber-500 text-barber-600' : 'bg-white border-zinc-300'"
      class="flex-1 py-2.5 rounded-xl border"
    >
      Serviços
    </button>
  </div>

  <!-- Conteúdo -->
  <div x-show="abaAtiva === 'dados'" class="space-y-6">
    <!-- Dados -->
  </div>
  
  <div x-show="abaAtiva === 'servicos'" class="space-y-4">
    <!-- Serviços -->
  </div>
</div>
```

---

## 9. Boas Práticas

### ✅ DO's (Fazer)

```html
<!-- ✅ Reutilizar classes base -->
$inputClass = '...'
<input class="{{ $inputClass }}">

<!-- ✅ Componentes Blade componíveis -->
<x-custom-select name="campo" :options="$options" />

<!-- ✅ Grid responsivo -->
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">

<!-- ✅ Espaçamento consistente -->
<div class="mb-8 space-y-6">

<!-- ✅ Status badges com cores apropriadas -->
<span class="inline-flex rounded-full bg-emerald-100 text-emerald-700">Ativo</span>

<!-- ✅ Fallback para dados vazios -->
<p class="text-sm text-zinc-500">{{ $valor ?? '-' }}</p>

<!-- ✅ Títulos com pré-label -->
<p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Label</p>
<h1 class="mt-2 text-3xl font-bold">Título</h1>
```

### ❌ DON'Ts (Não Fazer)

```html
<!-- ❌ Inputs sem classe padrão -->
<input class="p-2 border">

<!-- ❌ Cards sem shadow/border -->
<div class="bg-white p-4">

<!-- ❌ Cores hardcoded diferentes -->
<button class="bg-green-500">X</button> <!-- Usar barber-500 -->

<!-- ❌ Espaçamento inconsistente -->
<div class="mb-2"><button></button></div>
<div class="mb-8"><button></button></div>

<!-- ❌ Status sem badge -->
<span>{{ $status }}</span> <!-- Deve estar em badge -->

<!-- ❌ Modais sem backdrop blur -->
<div class="fixed bg-black/50">

<!-- ❌ Tabelas sem header styling -->
<thead><tr><th>X</th></thead>
```

---

## 10. Resumo Rápido

### Cores
- **Primary**: barber-500 (#c96f1f)
- **Hover**: barber-600
- **Backgrounds**: zinc-50, zinc-100
- **Borders**: zinc-200
- **Text**: zinc-900 (dark), zinc-500 (light)
- **Status Ativo**: emerald
- **Status Inativo**: zinc
- **Status Erro**: red
- **Status Info**: blue / orange

### Spacing
- **Cards**: p-5 até p-8
- **Gaps**: gap-3, gap-4, gap-5, gap-6
- **Top**: mt-2, mt-3, mb-6, mb-8
- **Vertical stacks**: space-y-4, space-y-6

### Components
- **Input**: `{{ $inputClass }}`
- **Card**: `{{ $cardClass }}`
- **Modal**: `fixed inset-0 z-50 hidden bg-zinc-900/60 backdrop-blur-sm`
- **Button**: `rounded-2xl px-4 py-3 text-xs font-bold uppercase`
- **Badge**: `inline-flex rounded-full px-2.5 py-1 text-xs font-semibold`

### Radiuses
- **Inputs**: rounded-2xl
- **Cards**: rounded-3xl
- **Buttons**: rounded-2xl
- **Icons**: rounded-xl / rounded-lg
- **Avatars**: rounded-full

---



