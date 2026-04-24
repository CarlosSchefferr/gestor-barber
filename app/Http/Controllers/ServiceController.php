<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','owner']);
    }

    public function index()
    {
        $search = request('search');
        $status = request('status', '');
        $sort = request('sort', 'name');

        $query = Service::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('active', true);
        } elseif ($status === 'inactive') {
            $query->where('active', false);
        }

        if ($sort === 'price') {
            $query->orderBy('price');
        } elseif ($sort === 'commission') {
            $query->orderBy('commission');
        } else {
            $query->orderBy('name');
        }

        $services = $query->with('comboServices')->paginate(20)->withQueryString();

        $totalServicos = Service::count();
        $servicosAtivos = Service::where('active', true)->count();
        $servicosInativos = Service::where('active', false)->count();
        $ticketMedio = (float) (Service::avg('price') ?? 0);

        return view('services.index', compact(
            'services',
            'totalServicos',
            'servicosAtivos',
            'servicosInativos',
            'ticketMedio'
        ));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:service,combo',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required',
            'price' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'return_alert_days' => 'nullable|integer|min:0',
            'observations' => 'nullable|string',
            'combo_services' => 'nullable|array',
            'combo_services.*' => 'exists:services,id',
        ]);

        if (!isset($data['commission']) || $data['commission'] === null || $data['commission'] === '') {
            $data['commission'] = 0;
        }

        // Convert duration HH:MM to minutes
        if (!empty($data['duration'])) {
            $parts = explode(':', $data['duration']);
            if (count($parts) >= 2) {
                $data['duration'] = (int)$parts[0] * 60 + (int)$parts[1];
            } else {
                $data['duration'] = (int)$data['duration'];
            }
        }

        $service = Service::create([
            'type' => $data['type'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'duration' => $data['duration'] ?? 0,
            'price' => $data['price'],
            'commission' => $data['commission'],
            'return_alert_days' => $data['return_alert_days'] ?? null,
            'observations' => $data['observations'] ?? null,
        ]);

        if ($data['type'] === 'combo' && !empty($data['combo_services'])) {
            $service->comboServices()->sync($data['combo_services']);
        }

        return redirect()->route('admin.services.index')->with('success', 'Serviço criado com sucesso.');
    }

    /**
     * Store service via AJAX (inline) — only for owners (middleware applies)
     */
    public function storeInline(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
        ]);

        if (!isset($data['commission']) || $data['commission'] === null || $data['commission'] === '') {
            $data['commission'] = 0;
        }

        $service = Service::create($data);

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'price' => $service->price,
            'commission' => $service->commission,
        ]);
    }

    public function show(Service $service)
    {
        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'type' => 'required|in:service,combo',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required',
            'price' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'return_alert_days' => 'nullable|integer|min:0',
            'observations' => 'nullable|string',
            'combo_services' => 'nullable|array',
            'combo_services.*' => 'exists:services,id',
        ]);

        if (!isset($data['commission']) || $data['commission'] === null || $data['commission'] === '') {
            $data['commission'] = 0;
        }

        // Convert duration HH:MM to minutes
        if (!empty($data['duration'])) {
            $parts = explode(':', $data['duration']);
            if (count($parts) >= 2) {
                $data['duration'] = (int)$parts[0] * 60 + (int)$parts[1];
            } else {
                $data['duration'] = (int)$data['duration'];
            }
        }

        $service->update([
            'type' => $data['type'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'duration' => $data['duration'] ?? 0,
            'price' => $data['price'],
            'commission' => $data['commission'],
            'return_alert_days' => $data['return_alert_days'] ?? null,
            'observations' => $data['observations'] ?? null,
        ]);

        if ($data['type'] === 'combo') {
            $service->comboServices()->sync($data['combo_services'] ?? []);
        } else {
            $service->comboServices()->detach();
        }

        return redirect()->route('admin.services.index')->with('success', 'Serviço atualizado.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Serviço removido.');
    }
}
