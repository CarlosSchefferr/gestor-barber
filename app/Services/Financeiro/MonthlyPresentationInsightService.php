<?php

namespace App\Services\Financeiro;

use Illuminate\Support\Facades\Http;

class MonthlyPresentationInsightService
{
    public function generate(array $metrics): array
    {
        $prompts = $this->buildPrompts($metrics);

        return [
            'prompts' => $prompts,
            'frase_faturamento' => $this->generateLine(
                $prompts['frase_faturamento'],
                $this->fallbackRevenueHighlight($metrics)
            ),
            'insight_desempenho' => $this->generateLine(
                $prompts['insight_desempenho'],
                $this->fallbackPerformanceInsight($metrics)
            ),
            'comentario_evolucao' => $this->generateLine(
                $prompts['comentario_evolucao'],
                $this->fallbackGrowthComment($metrics)
            ),
            'mensagem_motivacional' => $this->generateLine(
                $prompts['mensagem_motivacional'],
                $this->fallbackMotivationalMessage($metrics)
            ),
            'insight_metas' => $this->fallbackMetasInsight($metrics),
            'insight_clientes' => $this->fallbackClientesInsight($metrics),
            'insight_equipe' => $this->fallbackEquipeInsight($metrics),
            'insight_operacao' => $this->fallbackOperacaoInsight($metrics),
            'insight_lucro' => $this->fallbackLucroInsight($metrics),
        ];
    }

    public function buildPrompts(array $metrics): array
    {
        $context = json_encode([
            'faturamento_total' => $metrics['faturamento_total'] ?? 0,
            'faturamento_mes_anterior' => $metrics['faturamento_mes_anterior'] ?? 0,
            'quantidade_atendimentos' => $metrics['quantidade_atendimentos'] ?? 0,
            'ticket_medio' => $metrics['ticket_medio'] ?? 0,
            'servico_mais_vendido' => $metrics['servico_mais_vendido']['nome'] ?? null,
            'barbeiro_destaque' => $metrics['barbeiro_destaque']['nome'] ?? null,
            'evolucao_percentual' => $metrics['evolucao_percentual'] ?? 0,
            'periodo' => $metrics['periodo']['mes_ano'] ?? null,
        ], JSON_UNESCAPED_UNICODE);

        $baseRules = 'Escreva em portugues do Brasil, tom profissional, inspirador, curto, com no maximo 18 palavras, sem emojis e sem hashtags.';

        return [
            'frase_faturamento' => "Contexto: {$context}. {$baseRules} Gere uma frase de destaque sobre faturamento mensal.",
            'insight_desempenho' => "Contexto: {$context}. {$baseRules} Gere um insight sobre desempenho mensal considerando atendimentos e ticket medio.",
            'comentario_evolucao' => "Contexto: {$context}. {$baseRules} Gere comentario sobre crescimento ou queda em relacao ao mes anterior.",
            'mensagem_motivacional' => "Contexto: {$context}. {$baseRules} Gere uma mensagem final motivacional para a equipe da barbearia.",
        ];
    }

    private function generateLine(string $prompt, string $fallback): string
    {
        $generated = $this->callOpenAi($prompt);

        if (!$generated) {
            return $fallback;
        }

        $line = trim(preg_replace('/\s+/', ' ', $generated) ?? '');
        if ($line === '') {
            return $fallback;
        }

        return mb_substr($line, 0, 180);
    }

    private function callOpenAi(string $prompt): ?string
    {
        $apiKey = config('services.ai.openai_api_key');
        if (!$apiKey) {
            return null;
        }

        $endpoint = rtrim((string) config('services.ai.openai_endpoint', 'https://api.openai.com/v1'), '/');
        $model = (string) config('services.ai.openai_model', 'gpt-4o-mini');

        $response = Http::timeout(25)
            ->withToken($apiKey)
            ->post("{$endpoint}/chat/completions", [
                'model' => $model,
                'temperature' => 0.6,
                'max_tokens' => 80,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Voce e um consultor financeiro de barbearias e escreve frases curtas para apresentacoes executivas.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json('choices.0.message.content');
    }

    private function fallbackRevenueHighlight(array $metrics): string
    {
        $faturamento = $this->money($metrics['faturamento_total'] ?? 0);

        return "Fechamos o periodo com faturamento de {$faturamento}, reforcando a forca do nosso atendimento premium.";
    }

    private function fallbackPerformanceInsight(array $metrics): string
    {
        $atendimentos = (int) ($metrics['quantidade_atendimentos'] ?? 0);
        $ticket = $this->money($metrics['ticket_medio'] ?? 0);

        return "Com {$atendimentos} atendimentos e ticket medio de {$ticket}, mantivemos consistencia e valor por cliente.";
    }

    private function fallbackGrowthComment(array $metrics): string
    {
        $evolucao = (float) ($metrics['evolucao_percentual'] ?? 0.0);

        if ($evolucao > 0) {
            return 'Crescemos em relacao ao mes anterior, sinalizando que nossas estrategias comerciais estao funcionando.';
        }

        if ($evolucao < 0) {
            return 'Houve recuo no comparativo mensal, abrindo oportunidade para ajustar agenda, mix e conversao.';
        }

        return 'Mantivemos estabilidade no comparativo mensal, com base consistente para buscar uma nova escalada.';
    }

    private function fallbackMotivationalMessage(array $metrics): string
    {
        $barbeiro = $metrics['barbeiro_destaque']['nome'] ?? 'a equipe';

        return "Parabens ao time e destaque para {$barbeiro}; vamos elevar ainda mais o padrao no proximo mes.";
    }

    private function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    private function fallbackMetasInsight(array $metrics): string
    {
        $metas = $metrics['metas'] ?? [];
        $total = $metas['total'] ?? 0;
        $concluidas = $metas['concluidas'] ?? 0;
        $taxa = $metas['taxa_conclusao'] ?? 0;

        if ($total === 0) {
            return 'Nenhuma meta definida para o periodo. Considere estabelecer objetivos claros para o proximo mes.';
        }

        if ($taxa >= 80) {
            return "Excelente! {$concluidas} de {$total} metas alcancadas ({$taxa}%). O time esta superando expectativas.";
        }

        if ($taxa >= 50) {
            return "Bom progresso! {$concluidas} de {$total} metas concluidas. Vamos manter o ritmo para fechar todas.";
        }

        return "Atencao: apenas {$concluidas} de {$total} metas finalizadas. Hora de revisar estrategias e acelerar.";
    }

    private function fallbackClientesInsight(array $metrics): string
    {
        $novos = $metrics['novos_clientes'] ?? 0;
        $novosAnterior = $metrics['novos_clientes_anterior'] ?? 0;
        $ativos = $metrics['clientes_ativos'] ?? 0;

        if ($novos > $novosAnterior && $novosAnterior > 0) {
            $crescimento = round((($novos - $novosAnterior) / $novosAnterior) * 100);
            return "{$novos} novos clientes neste mes, {$crescimento}% a mais que o anterior. A captacao esta funcionando!";
        }

        if ($novos > 0) {
            return "{$novos} novos clientes conquistados e {$ativos} clientes ativos no periodo.";
        }

        return "Nenhum novo cliente registrado. Invista em acoes de marketing e indicacoes.";
    }

    private function fallbackEquipeInsight(array $metrics): string
    {
        $ranking = $metrics['ranking_barbeiros'] ?? [];
        $destaque = $metrics['barbeiro_destaque'] ?? [];

        if (empty($ranking)) {
            return 'Sem dados de desempenho da equipe no periodo.';
        }

        $totalBarbeiros = count($ranking);
        $nomeDestaque = $destaque['nome'] ?? 'o destaque do mes';
        $faturamentoDestaque = $this->money($destaque['faturamento'] ?? 0);

        if ($totalBarbeiros === 1) {
            return "Parabens {$nomeDestaque}! Faturamento individual de {$faturamentoDestaque}.";
        }

        return "{$totalBarbeiros} profissionais em atividade. Destaque: {$nomeDestaque} com {$faturamentoDestaque}.";
    }

    private function fallbackOperacaoInsight(array $metrics): string
    {
        $diaPico = $metrics['dia_pico'] ?? [];
        $horaPico = $metrics['hora_pico'] ?? [];

        $dia = $diaPico['nome'] ?? 'Desconhecido';
        $totalDia = $diaPico['total'] ?? 0;
        $hora = $horaPico['hora'] ?? 'Desconhecido';

        if ($dia === 'Sem dados' || $totalDia === 0) {
            return 'Dados insuficientes para analise de picos operacionais.';
        }

        return "{$dia} e o dia mais movimentado ({$totalDia} atendimentos), com pico as {$hora}.";
    }

    private function fallbackLucroInsight(array $metrics): string
    {
        $faturamento = $metrics['faturamento_total'] ?? 0;
        $despesas = $metrics['despesas'] ?? 0;
        $lucro = $metrics['lucro_liquido'] ?? 0;

        if ($faturamento === 0) {
            return 'Sem faturamento registrado no periodo.';
        }

        $margem = $faturamento > 0 ? round(($lucro / $faturamento) * 100, 1) : 0;

        if ($lucro > 0) {
            return "Lucro liquido de {$this->money($lucro)} com margem de {$margem}%. Saude financeira positiva.";
        }

        return "Prejuizo de {$this->money(abs($lucro))}. Necessario revisar despesas ({$this->money($despesas)}) ou aumentar receita.";
    }
}
