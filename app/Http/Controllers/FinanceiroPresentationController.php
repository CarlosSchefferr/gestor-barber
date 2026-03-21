<?php

namespace App\Http\Controllers;

use App\Services\Financeiro\MonthlyPresentationDataService;
use App\Services\Financeiro\MonthlyPresentationInsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class FinanceiroPresentationController extends Controller
{
    public function __construct(
        private MonthlyPresentationDataService $dataService,
        private MonthlyPresentationInsightService $insightService
    ) {
    }

    public function preview(Request $request)
    {
        [$referenceDate, $barbeariaNome] = $this->resolveInput($request);

        $payload = $this->buildPayload($referenceDate, $barbeariaNome);

        return view('financeiro.presentation.monthly', $payload);
    }

    public function downloadPdf(Request $request)
    {
        [$referenceDate, $barbeariaNome] = $this->resolveInput($request);

        $payload = $this->buildPayload($referenceDate, $barbeariaNome);
        $html = view('financeiro.presentation.monthly', $payload)->render();

        $historyDir = storage_path('app/presentations/' . $referenceDate->format('Y/m'));
        File::ensureDirectoryExists($historyDir);

        $safeBarbershop = Str::slug($barbeariaNome ?: 'barbearia');
        $fileName = sprintf(
            'apresentacao-mensal-%s-%s.pdf',
            $safeBarbershop,
            $referenceDate->format('Y-m')
        );

        $pdfPath = $historyDir . DIRECTORY_SEPARATOR . $fileName;

        $browserClass = 'Spatie\\Browsershot\\Browsershot';
        if (!class_exists($browserClass)) {
            throw new RuntimeException('Pacote spatie/browsershot nao instalado. Execute: composer install.');
        }

        $browsershot = $browserClass::html($html)
            ->showBackground()
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->waitUntilNetworkIdle()
            ->timeout((int) config('services.browsershot.timeout', 90));

        if (config('services.browsershot.no_sandbox', false)) {
            $browsershot->noSandbox();
        }

        if ($nodeBinary = config('services.browsershot.node_binary')) {
            $browsershot->setNodeBinary((string) $nodeBinary);
        }

        if ($npmBinary = config('services.browsershot.npm_binary')) {
            $browsershot->setNpmBinary((string) $npmBinary);
        }

        if ($chromePath = config('services.browsershot.chrome_path')) {
            $browsershot->setChromePath((string) $chromePath);
        }

        $browsershot->savePdf($pdfPath);

        return response()->download($pdfPath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function buildPayload(Carbon $referenceDate, string $barbeariaNome): array
    {
        $metrics = $this->dataService->build($referenceDate);
        $insights = $this->insightService->generate($metrics);

        return [
            'barbeariaNome' => $barbeariaNome,
            'metrics' => $metrics,
            'insights' => $insights,
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
}
