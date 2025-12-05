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
        $services = Service::orderBy('name')->paginate(20);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Service::create($data);

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
        ]);

        $service = Service::create($data);

        return response()->json(['id' => $service->id, 'name' => $service->name]);
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $service->update($data);

        return redirect()->route('admin.services.index')->with('success', 'Serviço atualizado.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Serviço removido.');
    }
}
