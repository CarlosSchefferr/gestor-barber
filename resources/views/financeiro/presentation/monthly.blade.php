<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apresentacao Mensal - {{ $barbeariaNome }}</title>
    <style>
        :root {
            --bg: #111111;
            --surface: #1c1c1c;
            --surface-soft: #262626;
            --gold: #d4a24a;
            --gold-soft: #f0cf8d;
            --text: #f8f5ef;
            --muted: #c8bba5;
            --ok: #53c48d;
            --warn: #f6b757;
            --bad: #ff6b6b;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: #0d0d0d;
            color: var(--text);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .deck {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 18px 0;
        }

        .slide {
            min-height: 1020px;
            margin: 0 auto 18px;
            padding: 48px;
            background:
                radial-gradient(circle at 0% 0%, rgba(212,162,74,0.20), transparent 44%),
                radial-gradient(circle at 100% 100%, rgba(212,162,74,0.16), transparent 35%),
                linear-gradient(145deg, var(--bg), #161616 60%, #1f1a13);
            border: 1px solid rgba(212, 162, 74, 0.28);
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            page-break-after: always;
        }

        .slide:last-child { page-break-after: auto; }

        .frame {
            position: absolute;
            inset: 16px;
            border: 1px solid rgba(212, 162, 74, 0.16);
            border-radius: 16px;
            pointer-events: none;
        }

        .header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 28px;
        }

        .kicker {
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold-soft);
            margin: 0 0 8px;
        }

        .title {
            margin: 0;
            font-size: 46px;
            line-height: 1.1;
            letter-spacing: 0.2px;
        }

        .subtitle {
            margin: 8px 0 0;
            font-size: 20px;
            color: var(--muted);
        }

        .badge {
            background: rgba(212, 162, 74, 0.14);
            border: 1px solid rgba(212, 162, 74, 0.35);
            color: var(--gold-soft);
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .hero-value {
            font-size: 72px;
            line-height: 1;
            font-weight: 700;
            margin: 10px 0 16px;
            color: var(--gold-soft);
        }

        .message {
            font-size: 28px;
            line-height: 1.35;
            max-width: 760px;
            margin: 0;
            color: var(--text);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 12px;
        }

        .card {
            background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 16px;
            padding: 22px;
        }

        .label {
            margin: 0 0 8px;
            color: var(--muted);
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 12px;
        }

        .value {
            margin: 0;
            font-size: 38px;
            font-weight: 700;
            color: var(--text);
        }

        .muted {
            color: var(--muted);
            font-size: 17px;
            line-height: 1.5;
            margin-top: 14px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 16px;
        }

        .table th,
        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            text-align: left;
        }

        .table th {
            color: var(--gold-soft);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .value-positive { color: var(--ok); }
        .value-negative { color: var(--bad); }
        .value-neutral { color: var(--warn); }

        .footer-note {
            position: absolute;
            left: 48px;
            bottom: 34px;
            color: var(--muted);
            font-size: 12px;
        }

        @media print {
            body {
                background: #fff;
            }
            .deck {
                max-width: none;
                padding: 0;
            }
            .slide {
                margin: 0;
                border-radius: 0;
                border: none;
                min-height: 1000px;
            }
        }
    </style>
</head>
<body>
    <div class="deck">
        <section class="slide">
            <div class="frame"></div>
            <p class="kicker">Relatorio Mensal</p>
            <h1 class="title">{{ $barbeariaNome }}</h1>
            <p class="subtitle">{{ $metrics['periodo']['mes_ano'] }}</p>
            <p class="message" style="margin-top: 80px;">{{ $insights['frase_faturamento'] }}</p>
            <p class="footer-note">Gerado em {{ $generatedAt->format('d/m/Y H:i') }}</p>
        </section>

        <section class="slide">
            <div class="frame"></div>
            <div class="header">
                <div>
                    <p class="kicker">Slide 2</p>
                    <h2 class="title">Faturamento</h2>
                </div>
                <span class="badge">Financeiro</span>
            </div>
            <p class="hero-value">R$ {{ number_format($metrics['faturamento_total'], 2, ',', '.') }}</p>
            <p class="message">{{ $insights['frase_faturamento'] }}</p>
            <p class="muted">Comparativo mes anterior: R$ {{ number_format($metrics['faturamento_mes_anterior'], 2, ',', '.') }}</p>
        </section>

        <section class="slide">
            <div class="frame"></div>
            <div class="header">
                <div>
                    <p class="kicker">Slide 3</p>
                    <h2 class="title">Desempenho</h2>
                </div>
                <span class="badge">Operacao</span>
            </div>

            <div class="grid-2">
                <div class="card">
                    <p class="label">Atendimentos</p>
                    <p class="value">{{ $metrics['quantidade_atendimentos'] }}</p>
                </div>
                <div class="card">
                    <p class="label">Ticket Medio</p>
                    <p class="value">R$ {{ number_format($metrics['ticket_medio'], 2, ',', '.') }}</p>
                </div>
            </div>

            <p class="message" style="margin-top: 34px;">{{ $insights['insight_desempenho'] }}</p>
        </section>

        <section class="slide">
            <div class="frame"></div>
            <div class="header">
                <div>
                    <p class="kicker">Slide 4</p>
                    <h2 class="title">Destaques</h2>
                </div>
                <span class="badge">Performance</span>
            </div>

            <div class="grid-2">
                <div class="card">
                    <p class="label">Servico Mais Vendido</p>
                    <p class="value">{{ $metrics['servico_mais_vendido']['nome'] }}</p>
                    <p class="muted">{{ $metrics['servico_mais_vendido']['quantidade'] }} atendimentos | R$ {{ number_format($metrics['servico_mais_vendido']['valor_total'], 2, ',', '.') }}</p>
                </div>
                <div class="card">
                    <p class="label">Barbeiro Destaque</p>
                    <p class="value">{{ $metrics['barbeiro_destaque']['nome'] }}</p>
                    <p class="muted">Faturamento: R$ {{ number_format($metrics['barbeiro_destaque']['faturamento'], 2, ',', '.') }}</p>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Servico</th>
                        <th>Qtd</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_slice($metrics['lista_servicos'], 0, 6) as $servico)
                        <tr>
                            <td>{{ $servico['nome'] }}</td>
                            <td>{{ $servico['quantidade'] }}</td>
                            <td>R$ {{ number_format($servico['valor_total'], 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Sem servicos com faturamento no periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="slide">
            <div class="frame"></div>
            <div class="header">
                <div>
                    <p class="kicker">Slide 5</p>
                    <h2 class="title">Evolucao</h2>
                </div>
                <span class="badge">Comparativo</span>
            </div>

            @php
                $ev = $metrics['evolucao_percentual'];
                $evClass = $ev > 0 ? 'value-positive' : ($ev < 0 ? 'value-negative' : 'value-neutral');
                $evPrefix = $ev > 0 ? '+' : '';
            @endphp

            <p class="hero-value {{ $evClass }}">{{ $evPrefix }}{{ number_format($ev, 2, ',', '.') }}%</p>
            <p class="message">{{ $insights['comentario_evolucao'] }}</p>
            <p class="muted">Base comparativa: {{ $metrics['periodo']['mes_ano_anterior'] }}</p>
        </section>

        <section class="slide">
            <div class="frame"></div>
            <div class="header">
                <div>
                    <p class="kicker">Slide 6</p>
                    <h2 class="title">Encerramento</h2>
                </div>
                <span class="badge">Time</span>
            </div>

            <p class="message" style="margin-top: 100px; font-size: 36px; line-height: 1.3;">{{ $insights['mensagem_motivacional'] }}</p>
            <p class="muted" style="margin-top: 60px;">Obrigado, equipe {{ $barbeariaNome }}. Vamos para um novo mes com foco em excelencia, recorrencia e experiencia do cliente.</p>
        </section>
    </div>
</body>
</html>
