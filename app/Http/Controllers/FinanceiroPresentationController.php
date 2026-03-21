<?php

namespace App\Http\Controllers;

use App\Services\Financeiro\MonthlyPresentationDataService;
use App\Services\Financeiro\MonthlyPresentationInsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class FinanceiroPresentationController extends Controller
{
    private const ALL_SECTIONS = [
        'capa',
        'resumo',
        'faturamento',
        'atendimentos',
        'metas',
        'equipe',
        'servicos',
        'clientes',
        'operacao',
        'encerramento',
    ];

    public function __construct(
        private MonthlyPresentationDataService $dataService,
        private MonthlyPresentationInsightService $insightService
    ) {
    }

    public function preview(Request $request)
    {
        [$referenceDate, $barbeariaNome] = $this->resolveInput($request);
        $sections = $this->resolveSections($request);

        $payload = $this->buildPayload($referenceDate, $barbeariaNome, $sections);

        return view('financeiro.presentation.monthly', $payload);
    }

    public function downloadPDF(Request $request)
    {
        [$referenceDate, $barbeariaNome] = $this->resolveInput($request);
        $sections = $this->resolveSections($request);

        $payload = $this->buildPayload($referenceDate, $barbeariaNome, $sections);

        // Renderizar HTML otimizado para PDF
        $html = View::make('financeiro.presentation.pdf', $payload)->render();

        // Converter para PDF em paisagem
        $pdf = Browsershot::html($html)
            ->landscape()
            ->margins(0, 0, 0, 0)
            ->format('A4')
            ->pdf();

        $fileName = 'apresentacao-' . $barbeariaNome . '-' . $referenceDate->format('Y-m-d') . '.pdf';

        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    private function buildPayload(Carbon $referenceDate, string $barbeariaNome, array $sections): array
    {
        $metrics = $this->dataService->build($referenceDate);
        $insights = $this->insightService->generate($metrics);

        return [
            'barbeariaNome' => $barbeariaNome,
            'metrics' => $metrics,
            'insights' => $insights,
            'sections' => $sections,
            'allSections' => self::ALL_SECTIONS,
            'generatedAt' => now(),
        ];
    }

    private function resolveInput(Request $request): array
    {
        $validated = $request->validate([
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'ano' => ['nullable', 'integer', 'between:2000,2100'],
            'barbearia_nome' => ['nullable', 'string', 'max:120'],
        ]);

        $year = $validated['ano'] ?? now()->year;
        $month = $validated['mes'] ?? now()->month;

        $referenceDate = Carbon::create($year, $month, 1)->startOfMonth();
        $barbeariaNome = $validated['barbearia_nome'] ?? (string) config('app.name', 'Barbearia Premium');

        return [$referenceDate, $barbeariaNome];
    }

    private function resolveSections(Request $request): array
    {
        $sectionsParam = $request->input('sections');

        if (empty($sectionsParam)) {
            return self::ALL_SECTIONS;
        }

        $requested = array_map('trim', explode(',', $sectionsParam));
        $valid = array_intersect($requested, self::ALL_SECTIONS);

        return !empty($valid) ? array_values($valid) : self::ALL_SECTIONS;
    }
}
