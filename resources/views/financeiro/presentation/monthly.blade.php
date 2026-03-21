<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apresentacao Mensal - {{ $barbeariaNome }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0a0a;
            --surface: #141414;
            --surface-soft: #1f1f1f;
            --border: rgba(255, 255, 255, 0.1);
            --gold: #d4a24a;
            --gold-light: #f0cf8d;
            --gold-dark: #a67c32;
            --text: #ffffff;
            --text-muted: #a1a1a1;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Toolbar */
        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: rgba(10, 10, 10, 0.98);
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .toolbar.hidden { transform: translateY(-100%); }

        .toolbar-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold-dark), var(--gold));
            color: #000;
        }

        .btn-primary:hover { opacity: 0.9; }

        .btn-secondary {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover { background: var(--surface-soft); }

        .btn-fullscreen {
            background: var(--surface);
            color: var(--gold);
            border: 1px solid var(--gold);
        }

        .btn-fullscreen:hover { background: rgba(212, 162, 74, 0.1); }

        /* Deck */
        .deck {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 80px 24px 40px;
        }

        body.fullscreen-mode .deck {
            padding: 0;
            max-width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        /* Slides */
        .slide {
            min-height: 680px;
            margin-bottom: 32px;
            padding: 48px 56px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold));
        }

        /* Fullscreen slide navigation */
        body.fullscreen-mode .slide {
            display: none;
            margin-bottom: 0;
            border-radius: 0;
            min-height: 100vh;
            border: none;
        }

        body.fullscreen-mode .slide.active {
            display: block;
        }

        body.fullscreen-mode .toolbar,
        body.fullscreen-mode .slide-header,
        body.fullscreen-mode .slide-number,
        body.fullscreen-mode .category-badge {
            display: none !important;
        }

        .slide-number {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 12px;
        }

        .slide-number::before {
            content: '';
            width: 20px;
            height: 1px;
            background: var(--gold);
        }

        .slide-title {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1px;
            color: var(--text);
        }

        .slide-subtitle {
            font-size: 16px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .slide-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .category-badge {
            display: inline-flex;
            padding: 6px 14px;
            background: rgba(212, 162, 74, 0.15);
            border: 1px solid rgba(212, 162, 74, 0.4);
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--gold-light);
        }

        /* Cards */
        .card-grid {
            display: grid;
            gap: 16px;
            margin-top: 28px;
        }

        .card-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .card-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .card-grid-4 { grid-template-columns: repeat(4, 1fr); }

        .metric-card {
            background: var(--surface-soft);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
        }

        .metric-card.highlight {
            border-color: rgba(212, 162, 74, 0.4);
            background: rgba(212, 162, 74, 0.08);
        }

        .metric-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .metric-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }

        .metric-value.gold { color: var(--gold-light); }
        .metric-value.success { color: var(--success); }
        .metric-value.danger { color: var(--danger); }
        .metric-value.large { font-size: 48px; }
        .metric-value.huge { font-size: 64px; }

        .metric-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        /* Hero */
        .hero-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-height: 500px;
        }

        .hero-brand {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 56px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -2px;
            margin-bottom: 12px;
            color: var(--text);
        }

        .hero-period {
            font-size: 20px;
            color: var(--text-muted);
        }

        .hero-value {
            font-size: 80px;
            font-weight: 900;
            line-height: 1;
            margin: 28px 0;
            color: var(--gold-light);
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--gold);
            border-bottom: 1px solid var(--border);
        }

        .data-table td {
            padding: 14px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        .data-table tr:last-child td { border-bottom: none; }

        /* Progress */
        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 12px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }

        .progress-fill.gold { background: var(--gold); }
        .progress-fill.success { background: var(--success); }
        .progress-fill.warning { background: var(--warning); }
        .progress-fill.danger { background: var(--danger); }

        /* Ranking */
        .ranking-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            background: var(--surface-soft);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .ranking-item:first-child {
            background: rgba(212, 162, 74, 0.1);
            border-color: rgba(212, 162, 74, 0.4);
        }

        .ranking-position {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
        }

        .ranking-item:first-child .ranking-position {
            background: var(--gold);
            color: #000;
        }

        .ranking-info { flex: 1; }
        .ranking-name { font-weight: 600; font-size: 15px; }
        .ranking-stats { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .ranking-value { font-size: 18px; font-weight: 700; color: var(--gold-light); }

        /* Insight */
        .insight-box {
            margin-top: 24px;
            padding: 20px;
            background: rgba(212, 162, 74, 0.08);
            border: 1px solid rgba(212, 162, 74, 0.25);
            border-radius: 14px;
        }

        .insight-text {
            font-size: 16px;
            line-height: 1.5;
            color: var(--text);
        }

        /* Meta card */
        .meta-card {
            background: var(--surface-soft);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 10px;
        }

        .meta-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .meta-name { font-weight: 600; font-size: 14px; }

        .meta-status {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .meta-status.concluida { background: rgba(34, 197, 94, 0.2); color: var(--success); }
        .meta-status.em_andamento { background: rgba(245, 158, 11, 0.2); color: var(--warning); }
        .meta-status.pendente { background: rgba(239, 68, 68, 0.2); color: var(--danger); }

        /* Layout */
        .split-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            margin-top: 28px;
        }

        .slide-footer {
            position: absolute;
            bottom: 28px;
            left: 56px;
            right: 56px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: var(--text-muted);
        }

        /* Fullscreen Navigation */
        .fullscreen-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            padding: 40px 24px 24px;
        }

        body.fullscreen-mode .fullscreen-nav {
            display: block;
        }

        .fullscreen-nav-inner {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .nav-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .nav-btn:hover:not(:disabled) {
            background: var(--gold);
            border-color: var(--gold);
            color: #000;
        }

        .nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .nav-btn svg {
            width: 20px;
            height: 20px;
        }

        .slide-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .slide-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            cursor: pointer;
            transition: all 0.2s;
        }

        .slide-dot.active {
            background: var(--gold);
            width: 24px;
            border-radius: 4px;
        }

        .slide-dot:hover:not(.active) {
            background: rgba(255,255,255,0.5);
        }

        .slide-counter {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            min-width: 60px;
            text-align: center;
        }

        .exit-fullscreen-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            display: none;
            width: 44px;
            height: 44px;
            padding: 0;
            background: rgba(0,0,0,0.5);
            border: 1px solid var(--border);
            border-radius: 50%;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            align-items: center;
            justify-content: center;
        }

        .exit-fullscreen-btn:hover {
            background: rgba(255,255,255,0.1);
            color: var(--text);
            border-color: rgba(255,255,255,0.2);
        }

        .exit-fullscreen-btn svg {
            width: 20px;
            height: 20px;
        }

        body.fullscreen-mode .exit-fullscreen-btn {
            display: flex;
        }

        /* Print styles */
        @media print {
            .toolbar, .fullscreen-nav, .exit-fullscreen-btn { display: none !important; }
            body { background: #0a0a0a !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .deck { max-width: none; padding: 0; }
            .slide {
                margin: 0;
                border-radius: 0;
                border: none;
                min-height: 100vh;
                page-break-after: always;
                page-break-inside: avoid;
            }
            .slide:last-child { page-break-after: auto; }
        }

        @media (max-width: 768px) {
            .card-grid-4, .card-grid-3 { grid-template-columns: repeat(2, 1fr); }
            .split-layout { grid-template-columns: 1fr; }
            .slide { padding: 32px 24px; }
            .slide-title { font-size: 32px; }
            .hero-title { font-size: 40px; }
            .hero-value { font-size: 56px; }
            .metric-value.huge { font-size: 48px; }
            .metric-value.large { font-size: 36px; }
            .slide-footer { left: 24px; right: 24px; bottom: 20px; }
        }
    </style>
</head>
<body>
    <div class="toolbar" id="toolbar">
        <span class="toolbar-title">{{ $barbeariaNome }} - {{ $metrics['periodo']['mes_ano'] }}</span>
        <div class="toolbar-actions">
            <a href="{{ route('financeiro.index') }}" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
            <button onclick="toggleFullscreen()" class="btn btn-fullscreen" id="fullscreenBtn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                </svg>
                Apresentar
            </button>
            <button onclick="downloadPDF()" class="btn btn-primary" id="downloadBtn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Baixar PDF
            </button>
        </div>
    </div>

    <button class="exit-fullscreen-btn" onclick="toggleFullscreen()" title="Sair (ESC)">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <div class="deck" id="presentation">
        @php $slideNumber = 0; $slides = []; @endphp

        @if(in_array('capa', $sections))
        @php $slides[] = 'capa'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="hero-section">
                <p class="hero-brand">Relatorio Mensal</p>
                <h1 class="hero-title">{{ $barbeariaNome }}</h1>
                <p class="hero-period">{{ $metrics['periodo']['mes_ano'] }}</p>
                <p class="hero-value">R$ {{ number_format($metrics['faturamento_total'], 2, ',', '.') }}</p>
                <p class="insight-text" style="max-width: 600px; text-align: center;">{{ $insights['frase_faturamento'] }}</p>
            </div>
            <div class="slide-footer">
                <span>Gerado em {{ $generatedAt->format('d/m/Y H:i') }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('resumo', $sections))
        @php $slides[] = 'resumo'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Visao Geral</p>
                    <h2 class="slide-title">Resumo Executivo</h2>
                    <p class="slide-subtitle">Principais indicadores do periodo</p>
                </div>
                <span class="category-badge">Resumo</span>
            </div>

            <div class="card-grid card-grid-4" style="margin-top: 36px;">
                <div class="metric-card highlight">
                    <p class="metric-label">Faturamento</p>
                    <p class="metric-value gold">R$ {{ number_format($metrics['faturamento_total'], 0, ',', '.') }}</p>
                    @php $evFat = $metrics['evolucao_percentual']; @endphp
                    <p class="metric-subtitle" style="color: {{ $evFat >= 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $evFat >= 0 ? '+' : '' }}{{ number_format($evFat, 1) }}%</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Atendimentos</p>
                    <p class="metric-value">{{ $metrics['quantidade_atendimentos'] }}</p>
                    @php $evAt = $metrics['evolucao_atendimentos']; @endphp
                    <p class="metric-subtitle" style="color: {{ $evAt >= 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $evAt >= 0 ? '+' : '' }}{{ number_format($evAt, 1) }}%</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Ticket Medio</p>
                    <p class="metric-value">R$ {{ number_format($metrics['ticket_medio'], 0, ',', '.') }}</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Lucro Liquido</p>
                    <p class="metric-value {{ $metrics['lucro_liquido'] >= 0 ? 'success' : 'danger' }}">R$ {{ number_format($metrics['lucro_liquido'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="card-grid card-grid-4" style="margin-top: 16px;">
                <div class="metric-card">
                    <p class="metric-label">Novos Clientes</p>
                    <p class="metric-value success">{{ $metrics['novos_clientes'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Clientes Ativos</p>
                    <p class="metric-value">{{ $metrics['clientes_ativos'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Metas</p>
                    <p class="metric-value">{{ $metrics['metas']['concluidas'] }}/{{ $metrics['metas']['total'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Despesas</p>
                    <p class="metric-value danger">R$ {{ number_format($metrics['despesas'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="slide-footer">
                <span>{{ $barbeariaNome }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('faturamento', $sections))
        @php $slides[] = 'faturamento'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Financeiro</p>
                    <h2 class="slide-title">Faturamento</h2>
                    <p class="slide-subtitle">Analise financeira detalhada</p>
                </div>
                <span class="category-badge">Financeiro</span>
            </div>

            <div class="split-layout">
                <div>
                    <p class="metric-label" style="margin-top: 16px;">Receita Total</p>
                    <p class="metric-value huge gold">R$ {{ number_format($metrics['faturamento_total'], 2, ',', '.') }}</p>

                    <div style="margin-top: 28px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                            <span style="color: var(--text-muted);">Mes anterior</span>
                            <span style="font-weight: 600;">R$ {{ number_format($metrics['faturamento_mes_anterior'], 2, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                            <span style="color: var(--text-muted);">Despesas</span>
                            <span style="font-weight: 600; color: var(--danger);">-R$ {{ number_format($metrics['despesas'], 2, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Lucro liquido</span>
                            <span style="font-weight: 700; color: {{ $metrics['lucro_liquido'] >= 0 ? 'var(--success)' : 'var(--danger)' }};">R$ {{ number_format($metrics['lucro_liquido'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    @php $ev = $metrics['evolucao_percentual']; @endphp
                    <div class="metric-card highlight" style="margin-top: 16px;">
                        <p class="metric-label">Evolucao vs Mes Anterior</p>
                        <p class="metric-value large {{ $ev > 0 ? 'success' : ($ev < 0 ? 'danger' : '') }}">{{ $ev > 0 ? '+' : '' }}{{ number_format($ev, 1) }}%</p>
                    </div>
                    <div class="insight-box" style="margin-top: 16px;">
                        <p class="insight-text" style="font-size: 14px;">{{ $insights['insight_lucro'] }}</p>
                    </div>
                </div>
            </div>

            <div class="slide-footer">
                <span>{{ $barbeariaNome }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('atendimentos', $sections))
        @php $slides[] = 'atendimentos'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Operacao</p>
                    <h2 class="slide-title">Atendimentos</h2>
                    <p class="slide-subtitle">Volume e ticket medio</p>
                </div>
                <span class="category-badge">Operacao</span>
            </div>

            <div class="card-grid card-grid-2" style="margin-top: 36px;">
                <div class="metric-card highlight">
                    <p class="metric-label">Total de Atendimentos</p>
                    <p class="metric-value huge gold">{{ $metrics['quantidade_atendimentos'] }}</p>
                    <p class="metric-subtitle">Mes anterior: {{ $metrics['quantidade_atendimentos_anterior'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Ticket Medio</p>
                    <p class="metric-value large">R$ {{ number_format($metrics['ticket_medio'], 2, ',', '.') }}</p>
                    <p class="metric-subtitle">Mes anterior: R$ {{ number_format($metrics['ticket_medio_anterior'], 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="insight-box">
                <p class="insight-text">{{ $insights['insight_desempenho'] }}</p>
            </div>

            <div class="slide-footer">
                <span>{{ $barbeariaNome }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('metas', $sections))
        @php $slides[] = 'metas'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Performance</p>
                    <h2 class="slide-title">Metas do Mes</h2>
                    <p class="slide-subtitle">Acompanhamento de objetivos</p>
                </div>
                <span class="category-badge">Performance</span>
            </div>

            <div class="card-grid card-grid-4" style="margin-top: 28px;">
                <div class="metric-card">
                    <p class="metric-label">Total</p>
                    <p class="metric-value">{{ $metrics['metas']['total'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Concluidas</p>
                    <p class="metric-value success">{{ $metrics['metas']['concluidas'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Em Andamento</p>
                    <p class="metric-value" style="color: var(--warning);">{{ $metrics['metas']['em_andamento'] }}</p>
                </div>
                <div class="metric-card highlight">
                    <p class="metric-label">Taxa</p>
                    <p class="metric-value gold">{{ $metrics['metas']['taxa_conclusao'] }}%</p>
                </div>
            </div>

            @if(!empty($metrics['metas']['lista']))
            <div style="margin-top: 20px;">
                @foreach(array_slice($metrics['metas']['lista'], 0, 4) as $meta)
                <div class="meta-card">
                    <div class="meta-header">
                        <span class="meta-name">{{ $meta['nome'] }}</span>
                        <span class="meta-status {{ $meta['status'] }}">{{ $meta['status'] === 'concluida' ? 'Concluida' : ($meta['status'] === 'em_andamento' ? 'Em andamento' : 'Pendente') }}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill {{ $meta['percent'] >= 100 ? 'success' : ($meta['percent'] >= 50 ? 'gold' : 'danger') }}" style="width: {{ min(100, $meta['percent']) }}%"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: var(--text-muted);">
                        <span>{{ $meta['percent'] }}%</span>
                        <span>@if($meta['tipo'] === 'novos_clientes'){{ (int)$meta['valor_atual'] }}/{{ (int)$meta['valor_meta'] }}@else R$ {{ number_format($meta['valor_atual'], 0, ',', '.') }} / R$ {{ number_format($meta['valor_meta'], 0, ',', '.') }}@endif</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="insight-box">
                <p class="insight-text" style="font-size: 14px;">{{ $insights['insight_metas'] }}</p>
            </div>

            <div class="slide-footer">
                <span>{{ $barbeariaNome }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('equipe', $sections))
        @php $slides[] = 'equipe'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Time</p>
                    <h2 class="slide-title">Ranking da Equipe</h2>
                    <p class="slide-subtitle">Desempenho dos profissionais</p>
                </div>
                <span class="category-badge">Time</span>
            </div>

            <div class="split-layout">
                <div>
                    @forelse($metrics['ranking_barbeiros'] as $index => $barbeiro)
                    <div class="ranking-item">
                        <div class="ranking-position">{{ $index + 1 }}o</div>
                        <div class="ranking-info">
                            <p class="ranking-name">{{ $barbeiro['nome'] }}</p>
                            <p class="ranking-stats">{{ $barbeiro['atendimentos'] }} atendimentos</p>
                        </div>
                        <div class="ranking-value">R$ {{ number_format($barbeiro['faturamento'], 0, ',', '.') }}</div>
                    </div>
                    @empty
                    <div class="insight-box"><p class="insight-text">Sem dados de barbeiros no periodo.</p></div>
                    @endforelse
                </div>

                <div>
                    @if(!empty($metrics['barbeiro_destaque']['nome']) && $metrics['barbeiro_destaque']['nome'] !== 'Sem dados no periodo')
                    <div class="metric-card highlight">
                        <p class="metric-label">Destaque</p>
                        <p class="metric-value" style="font-size: 24px;">{{ $metrics['barbeiro_destaque']['nome'] }}</p>
                        <p class="metric-value gold" style="margin-top: 8px;">R$ {{ number_format($metrics['barbeiro_destaque']['faturamento'], 2, ',', '.') }}</p>
                    </div>
                    @endif
                    <div class="insight-box" style="margin-top: 16px;">
                        <p class="insight-text" style="font-size: 14px;">{{ $insights['insight_equipe'] }}</p>
                    </div>
                </div>
            </div>

            <div class="slide-footer">
                <span>{{ $barbeariaNome }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('servicos', $sections))
        @php $slides[] = 'servicos'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Produtos</p>
                    <h2 class="slide-title">Servicos Mais Vendidos</h2>
                    <p class="slide-subtitle">Ranking por demanda e valor</p>
                </div>
                <span class="category-badge">Produtos</span>
            </div>

            <div class="split-layout">
                <div>
                    @if(!empty($metrics['servico_mais_vendido']['nome']) && $metrics['servico_mais_vendido']['nome'] !== 'Sem dados no periodo')
                    <div class="metric-card highlight">
                        <p class="metric-label">Campeao</p>
                        <p class="metric-value" style="font-size: 24px;">{{ $metrics['servico_mais_vendido']['nome'] }}</p>
                        <p class="metric-subtitle">{{ $metrics['servico_mais_vendido']['quantidade'] }} vendas | R$ {{ number_format($metrics['servico_mais_vendido']['valor_total'], 2, ',', '.') }}</p>
                    </div>
                    @endif

                    <table class="data-table" style="margin-top: 20px;">
                        <thead>
                            <tr><th>Servico</th><th style="text-align: center;">Qtd</th><th style="text-align: right;">Valor</th></tr>
                        </thead>
                        <tbody>
                            @forelse(array_slice($metrics['lista_servicos'], 0, 6) as $servico)
                            <tr>
                                <td>{{ $servico['nome'] }}</td>
                                <td style="text-align: center;">{{ $servico['quantidade'] }}</td>
                                <td style="text-align: right; color: var(--gold-light); font-weight: 600;">R$ {{ number_format($servico['valor_total'], 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">Sem servicos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div>
                    <div class="metric-card">
                        <p class="metric-label">Total</p>
                        <p class="metric-value">{{ count($metrics['lista_servicos']) }}</p>
                        <p class="metric-subtitle">tipos vendidos</p>
                    </div>
                </div>
            </div>

            <div class="slide-footer">
                <span>{{ $barbeariaNome }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('clientes', $sections))
        @php $slides[] = 'clientes'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Clientes</p>
                    <h2 class="slide-title">Base de Clientes</h2>
                    <p class="slide-subtitle">Captacao e atividade</p>
                </div>
                <span class="category-badge">Clientes</span>
            </div>

            <div class="card-grid card-grid-3" style="margin-top: 36px;">
                <div class="metric-card highlight">
                    <p class="metric-label">Novos Clientes</p>
                    <p class="metric-value huge success">{{ $metrics['novos_clientes'] }}</p>
                    <p class="metric-subtitle">Anterior: {{ $metrics['novos_clientes_anterior'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Clientes Ativos</p>
                    <p class="metric-value large">{{ $metrics['clientes_ativos'] }}</p>
                    <p class="metric-subtitle">no periodo</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Crescimento</p>
                    @php $crescClientes = $metrics['novos_clientes_anterior'] > 0 ? round((($metrics['novos_clientes'] - $metrics['novos_clientes_anterior']) / $metrics['novos_clientes_anterior']) * 100, 1) : ($metrics['novos_clientes'] > 0 ? 100 : 0); @endphp
                    <p class="metric-value large {{ $crescClientes >= 0 ? 'success' : 'danger' }}">{{ $crescClientes >= 0 ? '+' : '' }}{{ $crescClientes }}%</p>
                </div>
            </div>

            <div class="insight-box">
                <p class="insight-text">{{ $insights['insight_clientes'] }}</p>
            </div>

            <div class="slide-footer">
                <span>{{ $barbeariaNome }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('operacao', $sections))
        @php $slides[] = 'operacao'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Operacao</p>
                    <h2 class="slide-title">Picos de Atendimento</h2>
                    <p class="slide-subtitle">Dias e horarios mais movimentados</p>
                </div>
                <span class="category-badge">Gestao</span>
            </div>

            <div class="card-grid card-grid-2" style="margin-top: 36px;">
                <div class="metric-card highlight">
                    <p class="metric-label">Dia de Pico</p>
                    <p class="metric-value large gold">{{ $metrics['dia_pico']['nome'] }}</p>
                    <p class="metric-subtitle">{{ $metrics['dia_pico']['total'] }} atendimentos</p>
                </div>
                <div class="metric-card">
                    <p class="metric-label">Horario de Pico</p>
                    <p class="metric-value large">{{ $metrics['hora_pico']['hora'] }}</p>
                    <p class="metric-subtitle">{{ $metrics['hora_pico']['total'] }} atendimentos</p>
                </div>
            </div>

            <div class="insight-box">
                <p class="insight-text">{{ $insights['insight_operacao'] }}</p>
            </div>

            <div class="slide-footer">
                <span>{{ $barbeariaNome }}</span>
                <span></span>
            </div>
        </section>
        @endif

        @if(in_array('encerramento', $sections))
        @php $slides[] = 'encerramento'; @endphp
        <section class="slide" data-slide="{{ count($slides) - 1 }}">
            <div class="hero-section" style="min-height: 520px;">
                <p class="hero-brand">Encerramento</p>
                @php $ev = $metrics['evolucao_percentual']; @endphp
                <p class="hero-value" style="font-size: 72px; color: {{ $ev > 0 ? 'var(--success)' : ($ev < 0 ? 'var(--danger)' : 'var(--gold-light)') }};">{{ $ev > 0 ? '+' : '' }}{{ number_format($ev, 1) }}%</p>
                <p class="slide-subtitle" style="font-size: 18px; margin-bottom: 32px;">{{ $insights['comentario_evolucao'] }}</p>
                <div class="insight-box" style="max-width: 700px;">
                    <p class="insight-text" style="font-size: 20px; text-align: center; line-height: 1.6;">"{{ $insights['mensagem_motivacional'] }}"</p>
                </div>
                <p style="margin-top: 36px; font-size: 16px; color: var(--text-muted);">Obrigado, equipe <strong style="color: var(--gold-light);">{{ $barbeariaNome }}</strong>!</p>
            </div>
            <div class="slide-footer">
                <span>{{ $metrics['periodo']['mes_ano'] }}</span>
                <span></span>
            </div>
        </section>
        @endif
    </div>

    <div class="fullscreen-nav" id="fullscreenNav">
        <div class="fullscreen-nav-inner">
            <button class="nav-btn" id="prevBtn" onclick="prevSlide()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <div class="slide-indicator" id="slideIndicator"></div>
            <span class="slide-counter" id="slideCounter">1 / 1</span>
            <button class="nav-btn" id="nextBtn" onclick="nextSlide()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        let totalSlides = 0;
        let slides = [];

        document.addEventListener('DOMContentLoaded', function() {
            slides = document.querySelectorAll('.slide');
            totalSlides = slides.length;
            buildIndicator();
        });

        function buildIndicator() {
            const indicator = document.getElementById('slideIndicator');
            indicator.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('div');
                dot.className = 'slide-dot' + (i === 0 ? ' active' : '');
                dot.onclick = () => goToSlide(i);
                indicator.appendChild(dot);
            }
            updateCounter();
        }

        function updateCounter() {
            document.getElementById('slideCounter').textContent = (currentSlide + 1) + ' / ' + totalSlides;
            document.getElementById('prevBtn').disabled = currentSlide === 0;
            document.getElementById('nextBtn').disabled = currentSlide === totalSlides - 1;

            const dots = document.querySelectorAll('.slide-dot');
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentSlide);
            });
        }

        function goToSlide(index) {
            if (index < 0 || index >= totalSlides) return;

            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });

            currentSlide = index;
            updateCounter();
        }

        function nextSlide() {
            if (currentSlide < totalSlides - 1) {
                goToSlide(currentSlide + 1);
            }
        }

        function prevSlide() {
            if (currentSlide > 0) {
                goToSlide(currentSlide - 1);
            }
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    document.body.classList.add('fullscreen-mode');
                    goToSlide(0);
                });
            } else {
                document.exitFullscreen().then(() => {
                    document.body.classList.remove('fullscreen-mode');
                });
            }
        }

        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                document.body.classList.remove('fullscreen-mode');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (document.body.classList.contains('fullscreen-mode')) {
                if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    nextSlide();
                } else if (e.key === 'ArrowLeft' || e.key === 'Backspace') {
                    e.preventDefault();
                    prevSlide();
                } else if (e.key === 'Escape') {
                    toggleFullscreen();
                }
            } else if (e.key === 'f' || e.key === 'F') {
                toggleFullscreen();
            }
        });

        function downloadPDF() {
            const btn = document.getElementById('downloadBtn');
            btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" opacity="0.3"></circle></svg> Abrindo...';
            btn.disabled = true;

            // Capturar os parâmetros atuais da URL
            const urlParams = new URLSearchParams(window.location.search);
            const pdfUrl = document.location.pathname.replace('/apresentacao/mensal', '/apresentacao/mensal/pdf') + '?' + urlParams.toString();

            // Abrir PDF em nova aba
            window.open(pdfUrl, '_blank');

            setTimeout(() => {
                btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Baixar PDF';
                btn.disabled = false;
            }, 500);
        }
    </script>
</body>
</html>
