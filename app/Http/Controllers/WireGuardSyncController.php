<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Payment;

class WireGuardSyncController extends Controller
{
    /**
     * Receber MACs reais via WireGuard
     */
    public function receiveRealMacs(Request $request)
    {
        Log::info('🔒 WG-SYNC: Recebendo MACs via WireGuard', [
            'ip' => $request->ip(),
            'data' => $request->all()
        ]);
        
        $macs = $request->input('macs', []);
        $macsParaLiberar = [];
        
        foreach ($macs as $macInfo) {
            $mac = $macInfo['mac'] ?? null;
            $ip = $macInfo['ip'] ?? null;
            
            if (!$mac || !$ip) continue;
            
            // Buscar/criar usuário
            $user = User::where('mac_address', $mac)->first();
            if (!$user) {
                $user = User::create([
                    'mac_address' => $mac,
                    'ip_address' => $ip,
                    'status' => 'connected'
                ]);
                Log::info("🔒 WG-SYNC: Usuário criado via WireGuard", ['mac' => $mac, 'ip' => $ip]);
            } else {
                $user->update(['ip_address' => $ip, 'status' => 'connected']);
            }
            
            // Verificar se usuário pagou
            $payment = Payment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subHours(24))
                ->first();
                
            if ($payment) {
                $macsParaLiberar[] = $mac;
                Log::info("🔒 WG-SYNC: MAC para liberação via WireGuard", ['mac' => $mac, 'payment_id' => $payment->id]);
            }
        }
        
        $response = [
            'status' => 'success',
            'received_macs' => count($macs),
            'liberate' => $macsParaLiberar,
            'timestamp' => now()->toISOString()
        ];
        
        Log::info('🔒 WG-SYNC: Resposta enviada via WireGuard', $response);
        
        return response()->json($response);
    }
    
    /**
     * Notificação de novo cliente
     */
    public function newClient(Request $request)
    {
        $mac = $request->input('mac');
        $ip = $request->input('ip');
        
        Log::info('🔒 WG-SYNC: Novo cliente via WireGuard', [
            'mac' => $mac,
            'ip' => $ip,
            'timestamp' => $request->input('timestamp')
        ]);
        
        // Buscar/criar usuário
        $user = User::where('mac_address', $mac)->first();
        if (!$user) {
            $user = User::create([
                'mac_address' => $mac,
                'ip_address' => $ip,
                'status' => 'connected'
            ]);
        }
        
        return response()->json([
            'status' => 'registered',
            'user_id' => $user->id,
            'mac' => $mac
        ]);
    }
    
    /**
     * Heartbeat para manter tunnel ativo
     */
    public function heartbeat(Request $request)
    {
        Log::info('🔒 WG-SYNC: Heartbeat recebido via WireGuard', [
            'status' => $request->input('status'),
            'timestamp' => $request->input('timestamp')
        ]);
        
        return response()->json([
            'status' => 'alive',
            'server_time' => now()->toISOString(),
            'tunnel' => 'active'
        ]);
    }
}
