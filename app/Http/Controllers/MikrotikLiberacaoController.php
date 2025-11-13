<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Payment;
use App\Models\MikrotikMacReport;
use Carbon\Carbon;

class MikrotikLiberacaoController extends Controller
{
    /**
     * Libera acesso imediatamente após pagamento confirmado
     */
    public function liberarAcessoImediato($userId)
    {
        try {
            $user = User::find($userId);
            
            if (!$user || !$user->mac_address) {
                Log::error('Usuário ou MAC não encontrado para liberação', [
                    'user_id' => $userId
                ]);
                return false;
            }

            Log::info('🚀 LIBERAÇÃO IMEDIATA INICIADA', [
                'user_id' => $user->id,
                'mac_address' => $user->mac_address,
                'ip_address' => $user->ip_address,
                'expires_at' => $user->expires_at
            ]);

            // Buscar TODOS os MACs associados ao IP do usuário
            $allMacs = $this->getAllUserMacs($user);
            
            Log::info('📋 MACs encontrados para liberar', [
                'primary_mac' => $user->mac_address,
                'all_macs' => $allMacs,
                'total_macs' => count($allMacs)
            ]);

            // Criar comando para executar no MikroTik via API
            $mikrotikCommands = $this->generateMikrotikCommands($user, $allMacs);
            
            // Executar comandos no MikroTik (se API habilitada)
            if (config('wifi.mikrotik_api_enabled')) {
                $this->executeMikrotikCommands($mikrotikCommands);
            }

            // Salvar comandos para o MikroTik buscar via polling
            $this->savePendingCommands($user, $mikrotikCommands);

            Log::info('✅ LIBERAÇÃO IMEDIATA CONCLUÍDA', [
                'user_id' => $user->id,
                'commands_count' => count($mikrotikCommands)
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('❌ ERRO NA LIBERAÇÃO IMEDIATA', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Busca todos os MACs associados ao usuário
     */
    private function getAllUserMacs($user)
    {
        $macs = collect();
        
        // MAC principal
        if ($user->mac_address) {
            $macs->push(strtolower($user->mac_address));
            $macs->push(strtoupper($user->mac_address));
        }

        // MACs do mesmo IP reportados pelo MikroTik
        if ($user->ip_address) {
            $reportedMacs = MikrotikMacReport::where('ip_address', $user->ip_address)
                ->where('reported_at', '>', now()->subHours(24))
                ->pluck('mac_address');
            
            foreach ($reportedMacs as $mac) {
                $macs->push(strtolower($mac));
                $macs->push(strtoupper($mac));
            }
        }

        return $macs->unique()->filter()->values()->toArray();
    }

    /**
     * Gera comandos para o MikroTik executar
     */
    private function generateMikrotikCommands($user, $allMacs)
    {
        $commands = [];
        
        foreach ($allMacs as $mac) {
            // Remover usuário existente
            $commands[] = [
                'type' => 'remove_user',
                'path' => '/ip/hotspot/user',
                'action' => 'remove',
                'where' => ['name' => $mac]
            ];

            // Criar novo usuário
            $commands[] = [
                'type' => 'add_user',
                'path' => '/ip/hotspot/user',
                'action' => 'add',
                'params' => [
                    'name' => $mac,
                    'mac-address' => $mac,
                    'profile' => 'default',
                    'comment' => 'Liberado automaticamente - ' . now()->format('d/m/Y H:i')
                ]
            ];

            // Criar IP binding se tiver IP
            if ($user->ip_address) {
                // Remover binding existente
                $commands[] = [
                    'type' => 'remove_binding',
                    'path' => '/ip/hotspot/ip-binding',
                    'action' => 'remove',
                    'where' => ['mac-address' => $mac]
                ];

                // Criar novo binding
                $commands[] = [
                    'type' => 'add_binding',
                    'path' => '/ip/hotspot/ip-binding',
                    'action' => 'add',
                    'params' => [
                        'mac-address' => $mac,
                        'address' => $user->ip_address,
                        'type' => 'bypassed',
                        'comment' => 'Pago - ' . $user->name . ' - Expira: ' . $user->expires_at->format('d/m/Y H:i')
                    ]
                ];
            }
        }

        // Comando para remover usuário da sessão ativa (força reconexão)
        foreach ($allMacs as $mac) {
            $commands[] = [
                'type' => 'remove_active',
                'path' => '/ip/hotspot/active',
                'action' => 'remove',
                'where' => ['mac-address' => $mac]
            ];
        }

        return $commands;
    }

    /**
     * Salva comandos pendentes para o MikroTik buscar
     */
    private function savePendingCommands($user, $commands)
    {
        // Salvar em cache ou banco para o MikroTik buscar
        cache()->put(
            'mikrotik_commands_' . $user->mac_address, 
            $commands, 
            now()->addMinutes(10)
        );
        
        Log::info('💾 Comandos salvos para polling', [
            'mac' => $user->mac_address,
            'commands_count' => count($commands)
        ]);
    }

    /**
     * Endpoint melhorado para MikroTik buscar usuários pendentes
     */
    public function getPendingUsersV2(Request $request)
    {
        try {
            // Validar token
            $token = $this->extractToken($request);
            if (!$this->validateToken($token)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Buscar usuários ativos que devem ter acesso
            $activeUsers = $this->getActiveUsers();
            
            // Formatar resposta simplificada para MikroTik
            $response = $this->formatMikrotikResponse($activeUsers);
            
            Log::info('📤 Resposta V2 para MikroTik', [
                'users_count' => count($activeUsers),
                'response' => $response
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Erro no endpoint V2', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Busca usuários ativos com acesso válido
     */
    private function getActiveUsers()
    {
        return User::where('status', 'connected')
            ->whereNotNull('mac_address')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->get();
    }

    /**
     * Formata resposta otimizada para MikroTik processar
     */
    private function formatMikrotikResponse($users)
    {
        $allowList = [];
        $ipBindings = [];
        
        foreach ($users as $user) {
            // Adicionar MAC principal
            $allowList[] = strtolower($user->mac_address);
            $allowList[] = strtoupper($user->mac_address);
            
            // Adicionar binding IP->MAC
            if ($user->ip_address) {
                $ipBindings[] = [
                    'ip' => $user->ip_address,
                    'mac' => $user->mac_address,
                    'comment' => 'User: ' . $user->name . ' - Until: ' . $user->expires_at->format('d/m H:i')
                ];
            }
            
            // Adicionar MACs relacionados
            $relatedMacs = MikrotikMacReport::where('ip_address', $user->ip_address)
                ->where('reported_at', '>', now()->subHours(6))
                ->pluck('mac_address');
            
            foreach ($relatedMacs as $mac) {
                $allowList[] = strtolower($mac);
                $allowList[] = strtoupper($mac);
            }
        }
        
        $allowList = array_unique($allowList);
        
        return [
            'success' => true,
            'timestamp' => now()->toISOString(),
            'allow_users' => array_values($allowList),
            'ip_bindings' => $ipBindings,
            'total_allowed' => count($allowList),
            'message' => 'Users to allow access'
        ];
    }

    /**
     * Endpoint para buscar comandos pendentes para um MAC específico
     */
    public function getCommandsForMac(Request $request)
    {
        $mac = $request->input('mac_address');
        
        if (!$mac) {
            return response()->json(['error' => 'MAC address required'], 400);
        }

        $commands = cache()->get('mikrotik_commands_' . $mac, []);
        
        if (!empty($commands)) {
            // Limpar comandos após envio
            cache()->forget('mikrotik_commands_' . $mac);
            
            Log::info('📨 Comandos enviados para MikroTik', [
                'mac' => $mac,
                'commands_count' => count($commands)
            ]);
        }

        return response()->json([
            'success' => true,
            'mac_address' => $mac,
            'commands' => $commands,
            'has_commands' => !empty($commands)
        ]);
    }

    /**
     * Extrai token da requisição
     */
    private function extractToken(Request $request)
    {
        return $request->bearerToken() ?? 
               $request->get('token') ?? 
               str_replace('Bearer ', '', $request->header('Authorization', ''));
    }

    /**
     * Valida token
     */
    private function validateToken($token)
    {
        $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
        return $token === $expectedToken;
    }

    /**
     * Libera acesso para voucher de motorista
     */
    public function liberarAcesso($macAddress, $ipAddress, $hours = 24)
    {
        try {
            Log::info('🎫 LIBERAÇÃO DE ACESSO VOUCHER INICIADA', [
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'hours' => $hours
            ]);

            // Criar usuário temporário para usar a lógica existente
            $tempUser = new User([
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'expires_at' => now()->addHours($hours),
                'status' => 'active'
            ]);

            // Buscar todos os MACs associados
            $allMacs = $this->getAllUserMacs($tempUser);
            
            Log::info('📋 MACs encontrados para voucher', [
                'primary_mac' => $macAddress,
                'all_macs' => $allMacs,
                'total_macs' => count($allMacs)
            ]);

            // Gerar comandos para o MikroTik
            $mikrotikCommands = $this->generateMikrotikCommands($tempUser, $allMacs);
            
            // Executar comandos no MikroTik (se API habilitada)
            if (config('wifi.mikrotik_api_enabled')) {
                $this->executeMikrotikCommands($mikrotikCommands);
            }

            // Salvar comandos para o MikroTik buscar via polling
            $this->savePendingCommands($tempUser, $mikrotikCommands);

            Log::info('✅ LIBERAÇÃO DE ACESSO VOUCHER CONCLUÍDA', [
                'mac_address' => $macAddress,
                'commands_count' => count($mikrotikCommands)
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('❌ ERRO NA LIBERAÇÃO DE ACESSO VOUCHER', [
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Executa comandos diretamente no MikroTik via API (se habilitado)
     */
    private function executeMikrotikCommands($commands)
    {
        // Implementar se API REST do MikroTik estiver habilitada
        if (!config('wifi.mikrotik_api_enabled')) {
            return;
        }

        try {
            $mikrotikHost = config('wifi.mikrotik_host');
            $mikrotikUser = config('wifi.mikrotik_user');
            $mikrotikPass = config('wifi.mikrotik_password');
            
            foreach ($commands as $command) {
                // Executar via API REST do MikroTik
                // Implementação específica depende da versão do RouterOS
                Log::info('Executando comando no MikroTik', [
                    'type' => $command['type'],
                    'path' => $command['path'] ?? null
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao executar comandos no MikroTik', [
                'error' => $e->getMessage()
            ]);
        }
    }
}