<?php
// =====================================================
// 🚀 INTEGRAÇÃO WIREGUARD + LARAVEL
// Comunicação direta MikroTik → Laravel via tunnel
// =====================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WireguardMikrotikController extends Controller
{
    /**
     * Endpoint para MikroTik via WireGuard
     * URL: http://10.0.0.2/api/mikrotik/wireguard/sync
     */
    public function wireguardSync(Request $request)
    {
        // Verificar se a requisição vem do tunnel WireGuard
        $clientIP = $request->ip();
        
        if ($clientIP !== '10.0.0.1') {
            Log::warning('Tentativa de acesso não autorizada via WireGuard', ['ip' => $clientIP]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        Log::info('🔒 WIREGUARD-SYNC: Requisição recebida via tunnel seguro', ['ip' => $clientIP]);
        
        // Buscar usuários pagos para liberação
        $paidUsers = \App\Models\User::where('status', 'paid')
            ->where('paid_until', '>', now())
            ->with('payments')
            ->get();
        
        $usersToAllow = [];
        
        foreach ($paidUsers as $user) {
            $usersToAllow[] = [
                'mac' => $user->mac_address,
                'ip' => $user->ip_address,
                'paid_until' => $user->paid_until->toISOString(),
                'amount' => $user->payments->last()->amount ?? 0
            ];
        }
        
        Log::info('WIREGUARD-SYNC: Enviando usuários para liberação', [
            'count' => count($usersToAllow),
            'users' => array_column($usersToAllow, 'mac')
        ]);
        
        return response()->json([
            'success' => true,
            'action' => 'allow_users',
            'timestamp' => now()->toISOString(),
            'users' => $usersToAllow,
            'total_users' => count($usersToAllow)
        ]);
    }
    
    /**
     * Comando direto para MikroTik via WireGuard
     * Libera usuário instantaneamente após pagamento
     */
    public function liberateUserInstant(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string',
            'user_id' => 'required|integer'
        ]);
        
        $user = \App\Models\User::find($request->user_id);
        
        if (!$user || $user->status !== 'paid') {
            return response()->json(['error' => 'User not found or not paid'], 404);
        }
        
        // Enviar comando direto para MikroTik via WireGuard
        $mikrotikCommand = $this->sendMikrotikCommand([
            'action' => 'liberate_user',
            'mac' => $user->mac_address,
            'ip' => $user->ip_address
        ]);
        
        Log::info('WIREGUARD: Comando de liberação enviado', [
            'user_id' => $user->id,
            'mac' => $user->mac_address,
            'result' => $mikrotikCommand
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'User liberated instantly via WireGuard',
            'user' => $user->mac_address
        ]);
    }
    
    /**
     * Enviar comando direto para MikroTik via tunnel WireGuard
     */
    private function sendMikrotikCommand($data)
    {
        try {
            // Usar IP do tunnel para comunicação direta
            $mikrotikUrl = 'http://10.0.0.1/api/command';
            
            $response = Http::timeout(5)->post($mikrotikUrl, $data);
            
            return $response->json();
            
        } catch (\Exception $e) {
            Log::error('Erro ao enviar comando para MikroTik via WireGuard', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return ['error' => $e->getMessage()];
        }
    }
}

// =====================================================
// ADICIONAR ROTA NO routes/api.php
// =====================================================
/*
Route::prefix('mikrotik/wireguard')->group(function () {
    Route::get('/sync', [WireguardMikrotikController::class, 'wireguardSync']);
    Route::post('/liberate', [WireguardMikrotikController::class, 'liberateUserInstant']);
});
*/
