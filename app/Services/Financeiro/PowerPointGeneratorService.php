<?php

namespace App\Services\Financeiro;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Slide\Background\Color as BackgroundColor;
use PhpOffice\PhpPresentation\Slide\SlideSizePreset;

class PowerPointGeneratorService
{
    private PhpPresentation $presentation;
    private string $barbeariaNome;
    private array $metrics;
    private array $insights;

    // Cores do tema
    private const COLOR_BG = '0A0A0A';
    private const COLOR_SURFACE = '1A1A1A';
    private const COLOR_GOLD = 'D4A24A';
    private const COLOR_GOLD_LIGHT = 'F0CF8D';
    private const COLOR_WHITE = 'FFFFFF';
    private const COLOR_MUTED = 'A1A1A1';
    private const COLOR_SUCCESS = '22C55E';
    private const COLOR_DANGER = 'EF4444';

    public function generate(string $barbeariaNome, array $metrics, array $insights): string
    {
        $this->barbeariaNome = $barbeariaNome;
        $this->metrics = $metrics;
        $this->insights = $insights;

        $this->presentation = new PhpPresentation();
        $this->presentation->getDocumentProperties()
            ->setCreator($barbeariaNome)
            ->setTitle('Relatorio Mensal - ' . $metrics['periodo']['mes_ano'])
            ->setSubject('Apresentacao de Resultados')
            ->setDescription('Relatorio mensal de desempenho da ' . $barbeariaNome);

        // Configurar apresentação em paisagem (landscape)
        $this->presentation->getSlideSize()->setDocumentLayout(
            \PhpOffice\PhpPresentation\Slide\SlideSizePreset::LAYOUT_WIDESCREEN_16_9
        );

        // Remove o slide inicial vazio
        $this->presentation->removeSlideByIndex(0);

        // Criar os slides
        $this->createCoverSlide();
        $this->createSummarySlide();
        $this->createRevenueSlide();
        $this->createAppointmentsSlide();
        $this->createGoalsSlide();
        $this->createTeamSlide();
        $this->createServicesSlide();
        $this->createOperationsSlide();
        $this->createClosingSlide();

        // Salvar o arquivo
        $tempPath = storage_path('app/temp');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $fileName = 'apresentacao-' . now()->format('Y-m-d-His') . '.pptx';
        $filePath = $tempPath . DIRECTORY_SEPARATOR . $fileName;

        $writer = IOFactory::createWriter($this->presentation, 'PowerPoint2007');
        $writer->save($filePath);

        return $filePath;
    }

    private function createCoverSlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        // Kicker
        $this->addText($slide, 'RELATORIO MENSAL', 50, 180, 900, 40, 14, self::COLOR_GOLD, true, Alignment::HORIZONTAL_CENTER);

        // Titulo
        $this->addText($slide, $this->barbeariaNome, 50, 230, 900, 80, 44, self::COLOR_WHITE, true, Alignment::HORIZONTAL_CENTER);

        // Periodo
        $this->addText($slide, $this->metrics['periodo']['mes_ano'], 50, 310, 900, 40, 20, self::COLOR_MUTED, false, Alignment::HORIZONTAL_CENTER);

        // Valor em destaque
        $faturamento = 'R$ ' . number_format($this->metrics['faturamento_total'], 2, ',', '.');
        $this->addText($slide, $faturamento, 50, 380, 900, 80, 56, self::COLOR_GOLD_LIGHT, true, Alignment::HORIZONTAL_CENTER);

        // Insight
        $this->addText($slide, $this->insights['frase_faturamento'], 100, 480, 800, 60, 16, self::COLOR_MUTED, false, Alignment::HORIZONTAL_CENTER);

        // Rodape
        $this->addText($slide, 'Gerado em ' . now()->format('d/m/Y H:i'), 50, 680, 900, 30, 10, self::COLOR_MUTED, false, Alignment::HORIZONTAL_CENTER);
    }

    private function createSummarySlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        $this->addSlideHeader($slide, 'SLIDE 2', 'Resumo Executivo', 'Principais indicadores do periodo');

        // Cards em grid
        $cardWidth = 210;
        $cardHeight = 100;
        $startX = 50;
        $startY = 150;
        $gap = 20;

        // Linha 1
        $this->addMetricCard($slide, $startX, $startY, $cardWidth, $cardHeight, 'FATURAMENTO', 'R$ ' . number_format($this->metrics['faturamento_total'], 0, ',', '.'), self::COLOR_GOLD_LIGHT);
        $this->addMetricCard($slide, $startX + $cardWidth + $gap, $startY, $cardWidth, $cardHeight, 'ATENDIMENTOS', (string) $this->metrics['quantidade_atendimentos'], self::COLOR_WHITE);
        $this->addMetricCard($slide, $startX + ($cardWidth + $gap) * 2, $startY, $cardWidth, $cardHeight, 'TICKET MEDIO', 'R$ ' . number_format($this->metrics['ticket_medio'], 0, ',', '.'), self::COLOR_WHITE);
        $this->addMetricCard($slide, $startX + ($cardWidth + $gap) * 3, $startY, $cardWidth, $cardHeight, 'LUCRO', 'R$ ' . number_format($this->metrics['lucro_liquido'], 0, ',', '.'), $this->metrics['lucro_liquido'] >= 0 ? self::COLOR_SUCCESS : self::COLOR_DANGER);

        // Linha 2
        $startY2 = $startY + $cardHeight + $gap;
        $this->addMetricCard($slide, $startX, $startY2, $cardWidth, $cardHeight, 'NOVOS CLIENTES', (string) $this->metrics['novos_clientes'], self::COLOR_SUCCESS);
        $this->addMetricCard($slide, $startX + $cardWidth + $gap, $startY2, $cardWidth, $cardHeight, 'CLIENTES ATIVOS', (string) $this->metrics['clientes_ativos'], self::COLOR_WHITE);
        $this->addMetricCard($slide, $startX + ($cardWidth + $gap) * 2, $startY2, $cardWidth, $cardHeight, 'METAS', $this->metrics['metas']['concluidas'] . '/' . $this->metrics['metas']['total'], self::COLOR_WHITE);

        $ev = $this->metrics['evolucao_percentual'];
        $evText = ($ev >= 0 ? '+' : '') . number_format($ev, 1) . '%';
        $this->addMetricCard($slide, $startX + ($cardWidth + $gap) * 3, $startY2, $cardWidth, $cardHeight, 'EVOLUCAO', $evText, $ev >= 0 ? self::COLOR_SUCCESS : self::COLOR_DANGER);
    }

    private function createRevenueSlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        $this->addSlideHeader($slide, 'SLIDE 3', 'Faturamento', 'Analise financeira do mes');
        $this->addBadge($slide, 820, 50, 'FINANCEIRO');

        // Valor principal
        $this->addText($slide, 'RECEITA TOTAL', 50, 150, 400, 30, 12, self::COLOR_MUTED, true);
        $faturamento = 'R$ ' . number_format($this->metrics['faturamento_total'], 2, ',', '.');
        $this->addText($slide, $faturamento, 50, 180, 500, 70, 48, self::COLOR_GOLD_LIGHT, true);

        // Detalhes financeiros
        $detailsY = 280;
        $this->addText($slide, 'Mes anterior: R$ ' . number_format($this->metrics['faturamento_mes_anterior'], 2, ',', '.'), 50, $detailsY, 400, 30, 14, self::COLOR_MUTED);
        $this->addText($slide, 'Despesas: R$ ' . number_format($this->metrics['despesas'], 2, ',', '.'), 50, $detailsY + 35, 400, 30, 14, self::COLOR_DANGER);
        $this->addText($slide, 'Lucro: R$ ' . number_format($this->metrics['lucro_liquido'], 2, ',', '.'), 50, $detailsY + 70, 400, 30, 14, $this->metrics['lucro_liquido'] >= 0 ? self::COLOR_SUCCESS : self::COLOR_DANGER, true);

        // Card de evolucao
        $ev = $this->metrics['evolucao_percentual'];
        $evText = ($ev >= 0 ? '+' : '') . number_format($ev, 1) . '%';
        $this->addMetricCard($slide, 550, 150, 380, 120, 'EVOLUCAO VS MES ANTERIOR', $evText, $ev >= 0 ? self::COLOR_SUCCESS : self::COLOR_DANGER);

        // Insight
        $this->addInsightBox($slide, 550, 300, 380, 100, $this->insights['insight_lucro']);
    }

    private function createAppointmentsSlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        $this->addSlideHeader($slide, 'SLIDE 4', 'Atendimentos', 'Volume e ticket medio');
        $this->addBadge($slide, 820, 50, 'OPERACAO');

        // Cards
        $this->addMetricCard($slide, 50, 160, 420, 150, 'TOTAL DE ATENDIMENTOS', (string) $this->metrics['quantidade_atendimentos'], self::COLOR_GOLD_LIGHT, 'Mes anterior: ' . $this->metrics['quantidade_atendimentos_anterior']);

        $this->addMetricCard($slide, 500, 160, 420, 150, 'TICKET MEDIO', 'R$ ' . number_format($this->metrics['ticket_medio'], 2, ',', '.'), self::COLOR_WHITE, 'Mes anterior: R$ ' . number_format($this->metrics['ticket_medio_anterior'], 2, ',', '.'));

        // Insight
        $this->addInsightBox($slide, 50, 350, 870, 100, $this->insights['insight_desempenho']);
    }

    private function createGoalsSlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        $this->addSlideHeader($slide, 'SLIDE 5', 'Metas do Mes', 'Acompanhamento de objetivos');
        $this->addBadge($slide, 820, 50, 'PERFORMANCE');

        // Cards de resumo
        $cardWidth = 210;
        $startX = 50;
        $startY = 150;
        $gap = 20;

        $this->addMetricCard($slide, $startX, $startY, $cardWidth, 80, 'TOTAL', (string) $this->metrics['metas']['total'], self::COLOR_WHITE);
        $this->addMetricCard($slide, $startX + $cardWidth + $gap, $startY, $cardWidth, 80, 'CONCLUIDAS', (string) $this->metrics['metas']['concluidas'], self::COLOR_SUCCESS);
        $this->addMetricCard($slide, $startX + ($cardWidth + $gap) * 2, $startY, $cardWidth, 80, 'EM ANDAMENTO', (string) $this->metrics['metas']['em_andamento'], self::COLOR_GOLD);
        $this->addMetricCard($slide, $startX + ($cardWidth + $gap) * 3, $startY, $cardWidth, 80, 'TAXA', $this->metrics['metas']['taxa_conclusao'] . '%', self::COLOR_GOLD_LIGHT);

        // Lista de metas
        $metaY = 260;
        $metas = array_slice($this->metrics['metas']['lista'], 0, 4);
        foreach ($metas as $meta) {
            $statusColor = $meta['status'] === 'concluida' ? self::COLOR_SUCCESS : ($meta['status'] === 'em_andamento' ? self::COLOR_GOLD : self::COLOR_DANGER);
            $this->addText($slide, $meta['nome'] . ' - ' . $meta['percent'] . '%', 50, $metaY, 700, 30, 14, $statusColor);
            $metaY += 35;
        }

        if (empty($metas)) {
            $this->addText($slide, 'Nenhuma meta cadastrada para o periodo', 50, $metaY, 700, 30, 14, self::COLOR_MUTED);
        }

        // Insight
        $this->addInsightBox($slide, 50, 450, 870, 80, $this->insights['insight_metas']);
    }

    private function createTeamSlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        $this->addSlideHeader($slide, 'SLIDE 6', 'Ranking da Equipe', 'Desempenho dos profissionais');
        $this->addBadge($slide, 820, 50, 'TIME');

        // Ranking
        $rankY = 160;
        $ranking = $this->metrics['ranking_barbeiros'];

        if (!empty($ranking)) {
            foreach ($ranking as $index => $barbeiro) {
                $position = ($index + 1) . 'o';
                $isFirst = $index === 0;
                $color = $isFirst ? self::COLOR_GOLD_LIGHT : self::COLOR_WHITE;

                $this->addText($slide, $position, 50, $rankY, 50, 35, 18, $color, true);
                $this->addText($slide, $barbeiro['nome'], 110, $rankY, 300, 35, 16, $color, $isFirst);
                $this->addText($slide, $barbeiro['atendimentos'] . ' atendimentos', 110, $rankY + 20, 300, 20, 11, self::COLOR_MUTED);
                $this->addText($slide, 'R$ ' . number_format($barbeiro['faturamento'], 0, ',', '.'), 750, $rankY, 170, 35, 18, self::COLOR_GOLD_LIGHT, true, Alignment::HORIZONTAL_RIGHT);

                $rankY += 60;
            }

            // Destaque
            if (!empty($this->metrics['barbeiro_destaque']['nome']) && $this->metrics['barbeiro_destaque']['nome'] !== 'Sem dados no periodo') {
                $this->addMetricCard($slide, 550, 400, 370, 100, 'DESTAQUE DO MES', $this->metrics['barbeiro_destaque']['nome'], self::COLOR_GOLD_LIGHT, 'R$ ' . number_format($this->metrics['barbeiro_destaque']['faturamento'], 2, ',', '.'));
            }
        } else {
            $this->addText($slide, 'Sem dados de atendimentos por barbeiro no periodo', 50, $rankY, 700, 30, 14, self::COLOR_MUTED);
        }

        // Insight
        $this->addInsightBox($slide, 50, 530, 450, 80, $this->insights['insight_equipe']);
    }

    private function createServicesSlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        $this->addSlideHeader($slide, 'SLIDE 7', 'Servicos Mais Vendidos', 'Ranking de servicos por demanda');
        $this->addBadge($slide, 820, 50, 'PRODUTOS');

        // Campeao
        if (!empty($this->metrics['servico_mais_vendido']['nome']) && $this->metrics['servico_mais_vendido']['nome'] !== 'Sem dados no periodo') {
            $this->addMetricCard($slide, 50, 150, 400, 100, 'CAMPEAO DE VENDAS', $this->metrics['servico_mais_vendido']['nome'], self::COLOR_GOLD_LIGHT, $this->metrics['servico_mais_vendido']['quantidade'] . ' vendas | R$ ' . number_format($this->metrics['servico_mais_vendido']['valor_total'], 2, ',', '.'));
        }

        // Tabela de servicos
        $tableY = 280;
        $this->addText($slide, 'SERVICO', 50, $tableY, 400, 25, 11, self::COLOR_GOLD, true);
        $this->addText($slide, 'QTD', 500, $tableY, 100, 25, 11, self::COLOR_GOLD, true, Alignment::HORIZONTAL_CENTER);
        $this->addText($slide, 'VALOR', 650, $tableY, 200, 25, 11, self::COLOR_GOLD, true, Alignment::HORIZONTAL_RIGHT);

        $tableY += 30;
        $servicos = array_slice($this->metrics['lista_servicos'], 0, 6);
        foreach ($servicos as $servico) {
            $this->addText($slide, $servico['nome'], 50, $tableY, 400, 25, 13, self::COLOR_WHITE);
            $this->addText($slide, (string) $servico['quantidade'], 500, $tableY, 100, 25, 13, self::COLOR_WHITE, false, Alignment::HORIZONTAL_CENTER);
            $this->addText($slide, 'R$ ' . number_format($servico['valor_total'], 2, ',', '.'), 650, $tableY, 200, 25, 13, self::COLOR_GOLD_LIGHT, false, Alignment::HORIZONTAL_RIGHT);
            $tableY += 30;
        }

        if (empty($servicos)) {
            $this->addText($slide, 'Sem servicos no periodo', 50, $tableY, 400, 25, 13, self::COLOR_MUTED);
        }

        // Total
        $this->addMetricCard($slide, 550, 150, 350, 80, 'TOTAL DE SERVICOS', count($this->metrics['lista_servicos']) . ' tipos', self::COLOR_WHITE);
    }

    private function createOperationsSlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        $this->addSlideHeader($slide, 'SLIDE 8', 'Operacao e Clientes', 'Insights operacionais e base de clientes');
        $this->addBadge($slide, 820, 50, 'GESTAO');

        // Cards
        $cardWidth = 210;
        $startX = 50;
        $startY = 160;
        $gap = 20;

        $this->addMetricCard($slide, $startX, $startY, $cardWidth, 90, 'DIA DE PICO', $this->metrics['dia_pico']['nome'], self::COLOR_GOLD_LIGHT, $this->metrics['dia_pico']['total'] . ' atendimentos');
        $this->addMetricCard($slide, $startX + $cardWidth + $gap, $startY, $cardWidth, 90, 'HORARIO DE PICO', $this->metrics['hora_pico']['hora'], self::COLOR_WHITE, $this->metrics['hora_pico']['total'] . ' atendimentos');
        $this->addMetricCard($slide, $startX + ($cardWidth + $gap) * 2, $startY, $cardWidth, 90, 'NOVOS CLIENTES', (string) $this->metrics['novos_clientes'], self::COLOR_SUCCESS, 'Anterior: ' . $this->metrics['novos_clientes_anterior']);
        $this->addMetricCard($slide, $startX + ($cardWidth + $gap) * 3, $startY, $cardWidth, 90, 'CLIENTES ATIVOS', (string) $this->metrics['clientes_ativos'], self::COLOR_WHITE, 'no periodo');

        // Insights
        $this->addInsightBox($slide, 50, 290, 420, 100, $this->insights['insight_operacao']);
        $this->addInsightBox($slide, 500, 290, 420, 100, $this->insights['insight_clientes']);
    }

    private function createClosingSlide(): void
    {
        $slide = $this->presentation->createSlide();
        $this->setSlideBackground($slide, self::COLOR_BG);

        // Kicker
        $this->addText($slide, 'ENCERRAMENTO', 50, 150, 900, 40, 14, self::COLOR_GOLD, true, Alignment::HORIZONTAL_CENTER);

        // Evolucao
        $ev = $this->metrics['evolucao_percentual'];
        $evText = ($ev >= 0 ? '+' : '') . number_format($ev, 1) . '%';
        $evColor = $ev >= 0 ? self::COLOR_SUCCESS : self::COLOR_DANGER;
        $this->addText($slide, $evText, 50, 200, 900, 100, 72, $evColor, true, Alignment::HORIZONTAL_CENTER);

        // Comentario
        $this->addText($slide, $this->insights['comentario_evolucao'], 100, 310, 800, 50, 16, self::COLOR_MUTED, false, Alignment::HORIZONTAL_CENTER);

        // Mensagem motivacional
        $this->addInsightBox($slide, 100, 400, 800, 100, '"' . $this->insights['mensagem_motivacional'] . '"');

        // Agradecimento
        $this->addText($slide, 'Obrigado, equipe ' . $this->barbeariaNome . '!', 50, 530, 900, 40, 18, self::COLOR_MUTED, false, Alignment::HORIZONTAL_CENTER);

        // Rodape
        $this->addText($slide, $this->metrics['periodo']['mes_ano'], 50, 680, 450, 30, 10, self::COLOR_MUTED);
        $this->addText($slide, 'Slide 9 de 9', 500, 680, 450, 30, 10, self::COLOR_MUTED, false, Alignment::HORIZONTAL_RIGHT);
    }

    private function setSlideBackground(Slide $slide, string $color): void
    {
        $backgroundColor = new BackgroundColor();
        $backgroundColor->setColor(new Color('FF' . $color));
        $slide->setBackground($backgroundColor);
    }

    private function addText(Slide $slide, string $text, int $x, int $y, int $width, int $height, int $fontSize, string $color, bool $bold = false, string $align = Alignment::HORIZONTAL_LEFT): RichText
    {
        $shape = $slide->createRichTextShape();
        $shape->setOffsetX($x);
        $shape->setOffsetY($y);
        $shape->setWidth($width);
        $shape->setHeight($height);

        $textRun = $shape->createTextRun($text);
        $textRun->getFont()
            ->setName('Arial')
            ->setSize($fontSize)
            ->setColor(new Color('FF' . $color))
            ->setBold($bold);

        $shape->getActiveParagraph()->getAlignment()->setHorizontal($align);

        return $shape;
    }

    private function addSlideHeader(Slide $slide, string $number, string $title, string $subtitle): void
    {
        $this->addText($slide, $number, 50, 30, 200, 25, 12, self::COLOR_GOLD, true);
        $this->addText($slide, $title, 50, 55, 700, 50, 36, self::COLOR_WHITE, true);
        $this->addText($slide, $subtitle, 50, 105, 700, 30, 14, self::COLOR_MUTED);
    }

    private function addBadge(Slide $slide, int $x, int $y, string $text): void
    {
        $shape = $slide->createRichTextShape();
        $shape->setOffsetX($x);
        $shape->setOffsetY($y);
        $shape->setWidth(120);
        $shape->setHeight(30);
        $shape->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('33' . self::COLOR_GOLD));
        $shape->getBorder()->setLineStyle(Border::LINE_SINGLE)->setColor(new Color('55' . self::COLOR_GOLD));

        $textRun = $shape->createTextRun($text);
        $textRun->getFont()
            ->setName('Arial')
            ->setSize(9)
            ->setColor(new Color('FF' . self::COLOR_GOLD_LIGHT))
            ->setBold(true);

        $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function addMetricCard(Slide $slide, int $x, int $y, int $width, int $height, string $label, string $value, string $valueColor, ?string $subtitle = null): void
    {
        // Fundo do card
        $shape = $slide->createRichTextShape();
        $shape->setOffsetX($x);
        $shape->setOffsetY($y);
        $shape->setWidth($width);
        $shape->setHeight($height);
        $shape->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FF' . self::COLOR_SURFACE));
        $shape->getBorder()->setLineStyle(Border::LINE_SINGLE)->setColor(new Color('33FFFFFF'));

        // Label
        $this->addText($slide, $label, $x + 15, $y + 12, $width - 30, 20, 10, self::COLOR_MUTED, true);

        // Valor
        $valueSize = strlen($value) > 12 ? 20 : 28;
        $this->addText($slide, $value, $x + 15, $y + 35, $width - 30, 40, $valueSize, $valueColor, true);

        // Subtitulo opcional
        if ($subtitle) {
            $this->addText($slide, $subtitle, $x + 15, $y + $height - 25, $width - 30, 20, 10, self::COLOR_MUTED);
        }
    }

    private function addInsightBox(Slide $slide, int $x, int $y, int $width, int $height, string $text): void
    {
        // Fundo do insight
        $shape = $slide->createRichTextShape();
        $shape->setOffsetX($x);
        $shape->setOffsetY($y);
        $shape->setWidth($width);
        $shape->setHeight($height);
        $shape->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('15' . self::COLOR_GOLD));
        $shape->getBorder()->setLineStyle(Border::LINE_SINGLE)->setColor(new Color('33' . self::COLOR_GOLD));

        // Texto
        $this->addText($slide, $text, $x + 15, $y + 15, $width - 30, $height - 30, 13, self::COLOR_WHITE);
    }
}
