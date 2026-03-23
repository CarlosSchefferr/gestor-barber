<?php

namespace App\Http\Controllers;

use App\Models\AgendaConfig;
use App\Models\AgendaImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AgendaConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'owner']);
    }

    /**
     * Display the agenda configuration page
     */
    public function index(Request $request): View
    {
        $agendaConfig = AgendaConfig::where('user_id', $request->user()->id)->first();

        if (!$agendaConfig) {
            $agendaConfig = new AgendaConfig([
                'user_id' => $request->user()->id,
                'nome_barbearia' => $request->user()->name,
                'public_token' => (string) Str::uuid(),
                'horario_inicio' => '08:00',
                'horario_fim' => '18:00',
                'intervalo_slots' => 30,
                'dias_atendimento' => ['segunda', 'terca', 'quarta', 'quinta', 'sexta'],
                'ativa' => true,
            ]);
            $agendaConfig->save();
        }

        return view('agenda-config.index', [
            'agendaConfig' => $agendaConfig,
            'diasSemana' => [
                'segunda' => 'Segunda-feira',
                'terca' => 'Terça-feira',
                'quarta' => 'Quarta-feira',
                'quinta' => 'Quinta-feira',
                'sexta' => 'Sexta-feira',
                'sabado' => 'Sábado',
                'domingo' => 'Domingo',
            ],
        ]);
    }

    /**
     * Update agenda configuration
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nome_barbearia' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'horario_inicio' => 'required|string|regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
            'horario_fim' => 'required|string|regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
            'intervalo_slots' => 'required|integer|min:15|max:120',
            'dias_atendimento' => 'nullable|array',
            'dias_atendimento.*' => 'string|in:segunda,terca,quarta,quinta,sexta,sabado,domingo',
            'ativa' => 'boolean',
        ]);

        // Validar que pelo menos um dia foi selecionado
        $dias = collect($validated['dias_atendimento'] ?? [])->filter()->all();
        if (empty($dias)) {
            return redirect()->route('agenda.config.index')
                ->withErrors(['dias_atendimento' => 'Selecione pelo menos um dia de atendimento.']);
        }

        $validated['dias_atendimento'] = $dias;

        $agendaConfig = AgendaConfig::where('user_id', $request->user()->id)->first();

        if (!$agendaConfig) {
            $agendaConfig = new AgendaConfig([
                'user_id' => $request->user()->id,
                'public_token' => (string) Str::uuid(),
            ]);
        }

        $agendaConfig->fill($validated);
        $agendaConfig->save();

        return redirect()->route('agenda.config.index')
            ->with('success', 'Configurações da agenda atualizadas com sucesso!');
    }

    /**
     * Upload images for the carousel
     */
    public function uploadImages(Request $request): RedirectResponse
    {
        $request->validate([
            'imagens.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $agendaConfig = AgendaConfig::where('user_id', $request->user()->id)->firstOrFail();

        if ($request->hasFile('imagens')) {
            $ordem = $agendaConfig->imagens()->max('ordem') ?? 0;

            foreach ($request->file('imagens') as $image) {
                $path = $image->store('agenda-imagens', 'public');

                AgendaImagem::create([
                    'agenda_config_id' => $agendaConfig->id,
                    'caminho_imagem' => $path,
                    'ordem' => ++$ordem,
                ]);
            }
        }

        return redirect()->route('agenda.config.index')
            ->with('success', 'Imagens adicionadas com sucesso!');
    }

    /**
     * Delete an image from carousel
     */
    public function deleteImage(AgendaImagem $imagem): RedirectResponse
    {
        $userId = auth()->id();

        if ($imagem->agendaConfig->user_id !== $userId) {
            abort(403);
        }

        Storage::disk('public')->delete($imagem->caminho_imagem);
        $imagem->delete();

        return redirect()->route('agenda.config.index')
            ->with('success', 'Imagem removida com sucesso!');
    }

    /**
     * Reorder images
     */
    public function reorderImages(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ordem' => 'required|array',
            'ordem.*' => 'integer|min:0',
        ]);

        $agendaConfig = AgendaConfig::where('user_id', $request->user()->id)->firstOrFail();

        foreach ($validated['ordem'] as $imagemId => $ordem) {
            AgendaImagem::where('id', $imagemId)
                ->where('agenda_config_id', $agendaConfig->id)
                ->update(['ordem' => $ordem]);
        }

        return redirect()->route('agenda.config.index')
            ->with('success', 'Ordem das imagens atualizada!');
    }
}
