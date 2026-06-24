<?php

namespace App\Http\Controllers;

use App\Models\AgendaConfig;
use App\Models\AgendaImagem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

        if (! $agendaConfig) {
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
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $agendaConfig = AgendaConfig::where('user_id', $request->user()->id)->first();

        if (! $agendaConfig) {
            $agendaConfig = new AgendaConfig([
                'user_id' => $request->user()->id,
                'public_token' => (string) Str::uuid(),
            ]);
            $agendaConfig->save();
        }

        $validated = $request->validate([
            'nome_barbearia' => 'required|string|max:255',
            'slug' => [
                'required', 'string', 'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('agenda_configs', 'slug')->ignore($agendaConfig->id),
            ],
            'descricao' => 'nullable|string',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'horario_inicio' => 'required|string|date_format:H:i',
            'horario_fim' => 'required|string|date_format:H:i',
            'intervalo_slots' => 'required|integer|min:15|max:120',
            'dias_atendimento' => 'nullable|array',
            'dias_atendimento.*' => 'string|in:segunda,terca,quarta,quinta,sexta,sabado,domingo',
            'ativa' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'remover_logo' => 'nullable|boolean',
        ], [
            'slug.regex' => 'O endereço do link deve conter apenas letras minúsculas, números e hífens.',
            'slug.unique' => 'Esse endereço já está em uso. Escolha outro.',
        ]);

        // Validar que pelo menos um dia foi selecionado
        $dias = collect($validated['dias_atendimento'] ?? [])->filter()->all();
        if (empty($dias)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Selecione pelo menos um dia de atendimento.',
                    'errors' => ['dias_atendimento' => ['Selecione pelo menos um dia de atendimento.']],
                ], 422);
            }

            return redirect()->route('agenda.config.index')
                ->withErrors(['dias_atendimento' => 'Selecione pelo menos um dia de atendimento.']);
        }

        $validated['dias_atendimento'] = $dias;

        // Logo: upload novo, remoção ou manutenção.
        if ($request->hasFile('logo')) {
            if ($agendaConfig->logo) {
                Storage::disk('public')->delete($agendaConfig->logo);
            }
            $validated['logo'] = $request->file('logo')->store('agenda-logos', 'public');
        } elseif ($request->boolean('remover_logo')) {
            if ($agendaConfig->logo) {
                Storage::disk('public')->delete($agendaConfig->logo);
            }
            $validated['logo'] = null;
        } else {
            unset($validated['logo']);
        }
        unset($validated['remover_logo']);

        $agendaConfig->fill($validated);
        $agendaConfig->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configurações da agenda atualizadas com sucesso!',
                'ativa' => (bool) $agendaConfig->ativa,
                'slug' => $agendaConfig->slug,
                'public_url' => $agendaConfig->getPublicUrl(),
                'logo_url' => $agendaConfig->getLogoUrl(),
            ]);
        }

        return redirect()->route('agenda.config.index')
            ->with('success', 'Configurações da agenda atualizadas com sucesso!');
    }

    /**
     * Verifica a disponibilidade de um slug para o link público.
     */
    public function checkSlug(Request $request): JsonResponse
    {
        $request->validate(['slug' => 'required|string|max:120']);

        $slug = Str::slug($request->input('slug'));
        $agendaConfig = AgendaConfig::where('user_id', $request->user()->id)->first();

        $emUso = AgendaConfig::where('slug', $slug)
            ->when($agendaConfig, fn ($q) => $q->where('id', '!=', $agendaConfig->id))
            ->exists();

        return response()->json([
            'slug' => $slug,
            'available' => $slug !== '' && ! $emUso,
            'url' => url('/t/'.$slug),
        ]);
    }

    /**
     * Upload images for the carousel
     */
    public function uploadImages(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'imagens.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $agendaConfig = AgendaConfig::where('user_id', $request->user()->id)->firstOrFail();

        $novas = [];

        if ($request->hasFile('imagens')) {
            $ordem = $agendaConfig->imagens()->max('ordem') ?? 0;

            foreach ($request->file('imagens') as $image) {
                $path = $image->store('agenda-imagens', 'public');

                $imagem = AgendaImagem::create([
                    'agenda_config_id' => $agendaConfig->id,
                    'caminho_imagem' => $path,
                    'ordem' => ++$ordem,
                ]);

                $novas[] = [
                    'id' => $imagem->id,
                    'url' => asset('storage/'.$imagem->caminho_imagem),
                ];
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($novas).' imagem(ns) adicionada(s) com sucesso!',
                'imagens' => $novas,
            ]);
        }

        return redirect()->route('agenda.config.index')
            ->with('success', 'Imagens adicionadas com sucesso!');
    }

    /**
     * Delete an image from carousel
     */
    public function deleteImage(AgendaImagem $imagem): RedirectResponse|JsonResponse
    {
        $userId = auth()->id();

        if ($imagem->agendaConfig->user_id !== $userId) {
            abort(403);
        }

        Storage::disk('public')->delete($imagem->caminho_imagem);
        $imagem->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Imagem removida com sucesso!',
            ]);
        }

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
