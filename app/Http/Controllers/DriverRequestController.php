<?php

namespace App\Http\Controllers;

use App\Models\DriverRequest;
use Illuminate\Http\Request;

class DriverRequestController extends Controller
{
    public function create()
    {
        return view('driver-request.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'document' => 'required|string|max:30',
            'bus_number' => 'required|string|max:20',
            'observation' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Informe seu nome completo.',
            'phone.required' => 'Informe seu telefone.',
            'document.required' => 'Informe seu CPF.',
            'bus_number.required' => 'Informe o numero do onibus.',
        ]);

        $validated['phone'] = preg_replace('/\D/', '', $validated['phone']);
        $validated['name'] = mb_strtoupper($validated['name']);
        $validated['bus_number'] = mb_strtoupper($validated['bus_number']);
        if (!empty($validated['observation'])) {
            $validated['observation'] = mb_strtoupper($validated['observation']);
        }

        // Verifica se ja tem pedido pendente com mesmo telefone
        $existing = DriverRequest::where('phone', $validated['phone'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('warning', 'Voce ja tem um cadastro pendente de aprovacao. Aguarde o administrador ativar seu voucher.');
        }

        DriverRequest::create($validated);

        return redirect()->route('driver-request.success');
    }

    public function success()
    {
        return view('driver-request.success');
    }
}
