<!DOCTYPE html>
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
            width: 100%;
            height: 100%;
        }

        @page {
            size: A4 landscape;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg) !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        .slide {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            page-break-inside: avoid;
            padding: 48px 56px;
            background: var(--surface);
            border: none;
            margin: 0;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .slide:last-child {
            page-break-after: auto;
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

        .slide-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
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

        .hero-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex: 1;
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

        .split-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            margin-top: 28px;
        }

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

        .slide-content {
            flex: 1;
            overflow: hidden;
        }
    </style>
</head>
<body>
    @if(in_array('capa', $sections))
    <section class="slide">
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
    <section class="slide">
        <div class="slide-content">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Visao Geral</p>
                    <h2 class="slide-title">Resumo Executivo</h2>
                    <p class="slide-subtitle">Principais indicadores do periodo</p>
                </div>
                <span class="category-badge">Resumo</span>
            </div>

            <div class="card-grid card-grid-4" style="margin-top: 24px;">
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

            <div class="card-grid card-grid-4" style="margin-top: 12px;">
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
        </div>
        <div class="slide-footer">
            <span>{{ $barbeariaNome }}</span>
            <span></span>
        </div>
    </section>
    @endif

    @if(in_array('faturamento', $sections))
    <section class="slide">
        <div class="slide-content">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Financeiro</p>
                    <h2 class="slide-title">Faturamento</h2>
                    <p class="slide-subtitle">Analise financeira detalhada</p>
                </div>
                <span class="category-badge">Financeiro</span>
            </div>

            <div class="split-layout" style="margin-top: 20px;">
                <div>
                    <p class="metric-label" style="margin-top: 16px;">Receita Total</p>
                    <p class="metric-value huge gold">R$ {{ number_format($metrics['faturamento_total'], 2, ',', '.') }}</p>

                    <div style="margin-top: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--border);">
                            <span style="color: var(--text-muted);">Mes anterior</span>
                            <span style="font-weight: 600;">R$ {{ number_format($metrics['faturamento_mes_anterior'], 2, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--border);">
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
        </div>
        <div class="slide-footer">
            <span>{{ $barbeariaNome }}</span>
            <span></span>
        </div>
    </section>
    @endif

    @if(in_array('atendimentos', $sections))
    <section class="slide">
        <div class="slide-content">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Operacao</p>
                    <h2 class="slide-title">Atendimentos</h2>
                    <p class="slide-subtitle">Volume e ticket medio</p>
                </div>
                <span class="category-badge">Operacao</span>
            </div>

            <div class="card-grid card-grid-2" style="margin-top: 28px;">
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

            <div class="insight-box" style="margin-top: 20px;">
                <p class="insight-text">{{ $insights['insight_desempenho'] }}</p>
            </div>
        </div>
        <div class="slide-footer">
            <span>{{ $barbeariaNome }}</span>
            <span></span>
        </div>
    </section>
    @endif

    @if(in_array('metas', $sections))
    <section class="slide">
        <div class="slide-content">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Performance</p>
                    <h2 class="slide-title">Metas do Mes</h2>
                    <p class="slide-subtitle">Acompanhamento de objetivos</p>
                </div>
                <span class="category-badge">Performance</span>
            </div>

            <div class="card-grid card-grid-4" style="margin-top: 20px;">
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
            <div style="margin-top: 12px; max-height: 180px; overflow: hidden;">
                @foreach(array_slice($metrics['metas']['lista'], 0, 3) as $meta)
                <div class="meta-card">
                    <div class="meta-header">
                        <span class="meta-name">{{ $meta['nome'] }}</span>
                        <span class="meta-status {{ $meta['status'] }}">{{ $meta['status'] === 'concluida' ? 'Concluida' : ($meta['status'] === 'em_andamento' ? 'Em andamento' : 'Pendente') }}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill {{ $meta['percent'] >= 100 ? 'success' : ($meta['percent'] >= 50 ? 'gold' : 'danger') }}" style="width: {{ min(100, $meta['percent']) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        <div class="slide-footer">
            <span>{{ $barbeariaNome }}</span>
            <span></span>
        </div>
    </section>
    @endif

    @if(in_array('equipe', $sections))
    <section class="slide">
        <div class="slide-content">
            <div class="slide-header">
                <div>
                    <p class="slide-number">Time</p>
                    <h2 class="slide-title">Ranking da Equipe</h2>
                    <p class="slide-subtitle">Desempenho dos profissionais</p>
                </div>
                <span class="category-badge">Time</span>
            </div>

            <div class="split-layout" style="margin-top: 20px;">
                <div style="max-height: 300px; overflow: hidden;">
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
        </div>
        <div class="slide-footer">
            <span>{{ $barbeariaNome }}</span>
            <span></span>
        </div>
    </section>
    @endif

    @if(in_array('encerramento', $sections))
    <section class="slide">
        <div class="hero-section">
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
</body>
</html>
