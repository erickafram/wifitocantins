<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MikrotikRemoteController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    public function index()
    {
        return view('admin.mikrotik.remote');
    }

    /**
     * Retorna dados dos usuários com MAC - fonte de verdade para o MikroTik
     * O MikroTik consulta /api/mikrotik/check-paid-users-lite a cada 15s
     * Então o que está aqui = o que o MikroTik vai executar
     */
    public function getStatus()
    {
        try {
            // Usuários liberados (MAC vai como L: para o MikroTik)
            $liberados = User::whereIn('status', ['connected', 'active', 'temp_bypass'])
                ->where('expires_at', '>', now())
                ->whereNotNull('mac_address')
                ->where('mac_address', '!=', '')
                ->orderBy('expires_at', 'desc')
                ->get(['id', 'name', 'phone', 'mac_address', 'ip_address', 'status', 'connected_at', 'expires_at', 'device_name']);

            // Usuários expirados recentes (MAC vai como R: para o MikroTik)
            $expirados = User::where('status', 'expired')
                ->whereNotNull('mac_address')
                ->where('mac_address', '!=', '')
                ->where('expires_at', '>', now()->subHours(24))
                ->where('expires_at', '<', now())
                ->orderBy('expires_at', 'desc')
                ->get(['id', 'name', 'phone', 'mac_address', 'ip_address', 'status', 'connected_at', 'expires_at', 'device_name']);

            // Todos os usuários com MAC (histórico)
            $todos = User::whereNotNull('mac_address')
                ->where('mac_address', '!=', '')
                ->orderBy('updated_at', 'desc')
                ->limit(100)
                ->get(['id', 'name', 'phone', 'mac_address', 'ip_address', 'status', 'connected_at', 'expires_at', 'device_name', 'updated_at']);

            return response()->json([
                'success' => true,
                'liberados' => $liberados,
                'expirados' => $expirados,
                'todos' => $todos,
                'stats' => [
                    'total_liberados' => $liberados->count(),
                    'total_expirados' => $expirados->count(),
                    'total_registrados' => $todos->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('MikrotikRemote getStatus error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Liberar MAC - Adiciona na lista que o MikroTik consulta
     * Seta status=connected e expires_at para +24h
     */
    public function liberateMac(Request $request)
    {
        $request->validate([
            'mac' => 'required|string',
            'phone' => 'nullable|string',
            'hours' => 'nullable|integer|min:1|max:720',
        ]);

        try {
            $mac = strtoupper(trim($request->input('mac')));
            $phone = $request->input('phone', '');
            $hours = $request->input('hours', 24);

            // Verificar se já existe um usuário com esse MAC
            $user = User::where('mac_address', $mac)->first();

            if ($user) {
                // Atualizar usuário existente
                $user->update([
                    'status' => 'connected',
                    'connected_at' => now(),
                    'expires_at' => now()->addHours($hours),
                    'phone' => $phone ?: $user->phone,
                ]);
                $message = "MAC {$mac} RE-liberado por {$hours}h";
            } else {
                // Criar novo usuário
                $user = User::create([
                    'name' => 'Manual - ' . $mac,
                    'mac_address' => $mac,
                    'phone' => $phone,
                    'status' => 'connected',
                    'connected_at' => now(),
                    'expires_at' => now()->addHours($hours),
                    'ip_address' => '',
                    'password' => bcrypt('manual-' . $mac),
                ]);
                $message = "MAC {$mac} liberado por {$hours}h (novo usuário)";
            }

            Log::info("🟢 Admin: $message", ['mac' => $mac, 'phone' => $phone, 'hours' => $hours]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('MikrotikRemote liberateMac error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bloquear MAC - Remove da lista que o MikroTik consulta
     * Seta status=expired e expires_at para agora
     */
    public function blockMac(Request $request)
    {
        $request->validate([
            'mac' => 'required|string',
        ]);

        try {
            $mac = strtoupper(trim($request->input('mac')));

            $user = User::where('mac_address', $mac)->first();

            if (!$user) {
                return response()->json(['error' => 'MAC não encontrado no sistema'], 404);
            }

            $user->update([
                'status' => 'expired',
                'expires_at' => now()->subMinute(), // Expirou há 1 min
                'connected_at' => null,
            ]);

            Log::info("🔴 Admin: MAC {$mac} bloqueado", ['user_id' => $user->id, 'phone' => $user->phone]);

            return response()->json([
                'success' => true,
                'message' => "MAC {$mac} bloqueado. O MikroTik vai removê-lo na próxima sincronização (~15s).",
            ]);
        } catch (\Exception $e) {
            Log::error('MikrotikRemote blockMac error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sincronizar agora - Mostra o que o MikroTik vai receber
     * (preview do check-paid-users-lite)
     */
    public function syncNow()
    {
        try {
            // Simular resposta do check-paid-users-lite
            $activeMacs = User::whereIn('status', ['connected', 'active', 'temp_bypass'])
                ->where('expires_at', '>', now())
                ->whereNotNull('mac_address')
                ->where('mac_address', '!=', '')
                ->pluck('mac_address')
                ->map(fn($mac) => strtoupper(trim($mac)))
                ->unique()
                ->values()
                ->toArray();

            $expiredMacs = User::where('status', 'expired')
                ->whereNotNull('mac_address')
                ->where('mac_address', '!=', '')
                ->whereNotIn('mac_address', $activeMacs)
                ->where('expires_at', '>', now()->subHours(24))
                ->where('expires_at', '<', now())
                ->pluck('mac_address')
                ->map(fn($mac) => strtoupper(trim($mac)))
                ->unique()
                ->values()
                ->toArray();

            $output = "OK\n";
            foreach ($activeMacs as $mac) {
                $output .= "L:$mac\n";
            }
            foreach ($expiredMacs as $mac) {
                $output .= "R:$mac\n";
            }
            $output .= "END";

            return response()->json([
                'success' => true,
                'message' => 'Preview da resposta da API para o MikroTik',
                'api_response' => $output,
                'stats' => [
                    'liberar' => count($activeMacs),
                    'remover' => count($expiredMacs),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna logs do sistema relacionados ao MikroTik
     */
    public function getLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (!file_exists($logFile)) {
                return response()->json(['success' => true, 'logs' => []]);
            }

            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            $lines = array_slice($lines, -200); // Últimas 200 linhas

            // Filtrar apenas logs do MikroTik
            $mikrotikLogs = array_filter($lines, function ($line) {
                return stripos($line, 'mikrotik') !== false 
                    || stripos($line, 'sync') !== false
                    || stripos($line, 'PAGO') !== false
                    || stripos($line, 'liberar') !== false
                    || stripos($line, 'remover') !== false
                    || stripos($line, 'MAC') !== false;
            });

            return response()->json([
                'success' => true,
                'logs' => array_values(array_slice($mikrotikLogs, -50)),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna logs de bypass temporário (aprovados e negados)
     */
    public function getBypassLogs(Request $request)
    {
        try {
            $query = \App\Models\TempBypassLog::orderBy('created_at', 'desc');

            // Filtros opcionais
            if ($request->filled('mac')) {
                $query->where('mac_address', 'like', '%' . $request->mac . '%');
            }
            if ($request->filled('phone')) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            }
            if ($request->filled('denied_only')) {
                $query->where('was_denied', true);
            }

            $logs = $query->limit(100)->get();

            // Estatísticas
            $today = now()->startOfDay();
            $stats = [
                'total_hoje' => \App\Models\TempBypassLog::where('created_at', '>=', $today)->count(),
                'aprovados_hoje' => \App\Models\TempBypassLog::where('created_at', '>=', $today)->where('was_denied', false)->count(),
                'negados_hoje' => \App\Models\TempBypassLog::where('created_at', '>=', $today)->where('was_denied', true)->count(),
                'total_geral' => \App\Models\TempBypassLog::count(),
            ];

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Resetar contador de bypass para um MAC/telefone
     * Limpa o cache de anti-abuso para que o usuário possa usar mais bypasses
     */
    public function resetBypass(Request $request)
    {
        $request->validate([
            'mac' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        try {
            $mac = $request->input('mac');
            $phone = $request->input('phone');
            $resetted = [];

            if ($mac) {
                $macKey = 'bypass_mac_' . strtoupper(trim($mac));
                \Illuminate\Support\Facades\Cache::forget($macKey);
                $resetted[] = "MAC: {$mac}";
            }

            if ($phone) {
                $phoneKey = 'bypass_phone_' . $phone;
                \Illuminate\Support\Facades\Cache::forget($phoneKey);
                $resetted[] = "Phone: {$phone}";
            }

            if (empty($resetted)) {
                return response()->json(['error' => 'Informe MAC ou telefone para resetar'], 422);
            }

            $desc = implode(', ', $resetted);
            Log::info("🔄 Admin: Bypass resetado para {$desc}");

            return response()->json([
                'success' => true,
                'message' => "Bypass resetado para {$desc}. O usuário pode usar mais 2 liberações.",
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao resetar bypass: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Editar tempo de expiração de um usuário
     */
    public function editExpiration(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'hours' => 'required|integer|min:1|max:720',
        ]);

        try {
            $user = User::findOrFail($request->input('user_id'));
            
            $user->update([
                'expires_at' => now()->addHours($request->input('hours')),
                'status' => 'connected',
                'connected_at' => $user->connected_at ?? now(),
            ]);

            Log::info("✏️ Admin: Expiração de {$user->mac_address} alterada para +{$request->input('hours')}h");

            return response()->json([
                'success' => true,
                'message' => "Expiração atualizada para +{$request->input('hours')}h",
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
