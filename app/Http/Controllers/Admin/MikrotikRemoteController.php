<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MikrotikCommand;
use App\Models\Device;

class MikrotikRemoteController extends Controller
{

    public function index()
    {
        return view('admin.mikrotik.remote');
    }

    public function getStatus()
    {
        try {
            // Estatísticas do banco de dados
            $activeUsers = User::where('status', 'connected')
                ->where('expires_at', '>', now())
                ->count();

            $paidUsers = User::where('status', 'connected')
                ->where('expires_at', '>', now())
                ->count();

            $totalDevices = User::whereNotNull('mac_address')->count();

            // Comandos pendentes
            $pendingCommands = MikrotikCommand::pending()->count();

            // Últimos usuários ativos
            $recentUsers = User::where('status', 'connected')
                ->where('expires_at', '>', now())
                ->orderBy('connected_at', 'desc')
                ->limit(20)
                ->get();

            // Usuários pagos (com bypass)
            $paidUsersList = User::where('status', 'connected')
                ->where('expires_at', '>', now())
                ->get();

            // Todos os dispositivos
            $allDevices = User::whereNotNull('mac_address')
                ->orderBy('connected_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'activeUsers' => $activeUsers,
                'paidUsers' => $paidUsers,
                'totalDevices' => $totalDevices,
                'pendingCommands' => $pendingCommands,
                'details' => [
                    'active' => $recentUsers->map(function($user) {
                        return [
                            'mac-address' => $user->mac_address,
                            'address' => $user->ip_address,
                            'uptime' => $user->connected_at ? $user->connected_at->diffForHumans() : '-',
                            'expires' => $user->expires_at ? $user->expires_at->format('d/m/Y H:i') : '-',
                        ];
                    }),
                    'bindings' => $paidUsersList->map(function($user) {
                        return [
                            'mac-address' => $user->mac_address,
                            'type' => 'bypassed',
                            'comment' => 'PAGO-AUTO',
                            'expires' => $user->expires_at ? $user->expires_at->format('d/m/Y H:i') : '-',
                        ];
                    }),
                    'leases' => $allDevices->map(function($user) {
                        return [
                            'mac-address' => $user->mac_address,
                            'address' => $user->ip_address,
                            'host-name' => $user->device_name ?? $user->name ?? '-',
                            'status' => $user->status,
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function executeCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        // Listar comandos pendentes
        $pendingCommands = MikrotikCommand::pending()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pendingCommands
        ]);
    }

    public function syncNow()
    {
        try {
            // Forçar sincronização criando um comando especial
            MikrotikCommand::create([
                'command_type' => 'sync',
                'mac_address' => '00:00:00:00:00:00',
                'status' => 'pending',
            ]);

            // Buscar últimos logs de sincronização
            $recentCommands = MikrotikCommand::orderBy('executed_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Comando de sincronização enviado. O Mikrotik executará em até 15 segundos.',
                'logs' => $recentCommands
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function liberateMac(Request $request)
    {
        $request->validate([
            'mac' => 'required|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
        ]);

        try {
            $mac = strtoupper(str_replace('-', ':', $request->input('mac')));

            // Criar comando para liberar
            MikrotikCommand::create([
                'command_type' => 'liberate',
                'mac_address' => $mac,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Comando enviado! O MAC {$mac} será liberado em até 15 segundos pelo Mikrotik."
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function blockMac(Request $request)
    {
        $request->validate([
            'mac' => 'required|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
        ]);

        try {
            $mac = strtoupper(str_replace('-', ':', $request->input('mac')));

            // Criar comando para bloquear
            MikrotikCommand::create([
                'command_type' => 'block',
                'mac_address' => $mac,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Comando enviado! O MAC {$mac} será bloqueado em até 15 segundos pelo Mikrotik."
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLogs()
    {
        try {
            // Buscar últimos comandos executados
            $logs = MikrotikCommand::orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function($cmd) {
                    return [
                        'time' => $cmd->created_at->format('H:i:s'),
                        'message' => "[{$cmd->status}] {$cmd->command_type}: {$cmd->mac_address}" . 
                                   ($cmd->response ? " - {$cmd->response}" : ''),
                    ];
                });

            return response()->json([
                'success' => true,
                'logs' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
