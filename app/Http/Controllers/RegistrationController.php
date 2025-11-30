<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\HotspotIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    /**
     * Register a new user for WiFi access
     */
    public function register(Request $request)
    {
        try {
            // Validar os dados
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'required|string|max:20',
            ], [
                'name.required' => 'Nome completo é obrigatório',
                'email.required' => 'E-mail é obrigatório',
                'email.email' => 'E-mail deve ter um formato válido',
                'email.unique' => 'Este e-mail já está cadastrado',
                'phone.required' => 'Telefone é obrigatório',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Criar o usuário
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make('default_password_'.time()), // Password temporário
                'registered_at' => now(),
                'status' => 'pending', // Status inicial
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário cadastrado com sucesso!',
                'user_id' => $user->id,
                'redirect_to_payment' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if email already exists
     */
    public function checkEmail(Request $request)
    {
        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }

    /**
     * Check if user exists by email or phone and return user data
     */
    public function checkUser(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $user = null;

        // Buscar por email ou telefone
        if ($request->email) {
            $user = User::where('email', $request->email)->first();
        } elseif ($request->phone) {
            // Limpar formatação do telefone para busca
            $cleanPhone = preg_replace('/[^\d]/', '', $request->phone);
            $user = User::where('phone', 'LIKE', '%'.$cleanPhone.'%')->first();
        }

        if ($user) {
            return response()->json([
                'exists' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'mac_address' => $user->mac_address,  // ✅ Adicionar MAC
                    'ip_address' => $user->ip_address,    // ✅ Adicionar IP
                ],
            ]);
        }

        return response()->json([
            'exists' => false,
        ]);
    }

    private function resolveClientIp(Request $request)
    {
        $candidates = [
            $request->input('ip_address'),
            $request->input('client_ip'),
            $request->header('X-Client-IP'),
            $request->header('X-Forwarded-For'),
            $request->header('X-Real-IP'),
        ];

        foreach ($candidates as $value) {
            if (! $value) {
                continue;
            }

            $ip = trim(explode(',', $value)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return $request->ip();
    }

    private function shouldReplaceMac(?string $currentMac, string $newMac): bool
    {
        if (! $currentMac) {
            return true;
        }

        $isCurrentMock = stripos($currentMac, '02:') === 0;
        $isNewMock = stripos($newMac, '02:') === 0;

        if ($isCurrentMock && ! $isNewMock) {
            return true;
        }

        if (! $isCurrentMock && ! $isNewMock) {
            return $currentMac !== $newMac;
        }

        return false;
    }

    /**
     * Register or update existing user for payment (SIMPLIFICADO - apenas telefone)
     */
    public function registerForPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id', // ID do usuário existente (opcional)
                'phone' => 'required|string|min:10|max:20',
                'mac_address' => 'nullable|string|max:17',
                'ip_address' => 'nullable|ip',
            ], [
                'phone.required' => 'Telefone é obrigatório',
                'phone.min' => 'Telefone deve ter pelo menos 10 dígitos',
                'mac_address.string' => 'MAC address deve ser uma string válida',
                'ip_address.ip' => 'IP inválido',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // 🎯 PROCESSAR MAC E IP ADDRESS
            // PRIORIZAR IP/MAC do request body (enviado pelo JavaScript) em vez do IP público
            $ipAddress = $request->input('ip_address');
            $macAddress = $request->input('mac_address');
            
            \Log::info('📋 DADOS RECEBIDOS DO FRONTEND', [
                'ip_enviado' => $ipAddress,
                'mac_enviado' => $macAddress,
                'ip_http_header' => $request->header('X-Real-IP'),
            ]);
            
            // Se não veio do frontend, tentar detectar
            if (!$ipAddress) {
            $ipAddress = HotspotIdentity::resolveClientIp($request);
                \Log::warning('⚠️ IP não enviado pelo frontend, usando fallback', ['ip_fallback' => $ipAddress]);
            }
            
            if (!$macAddress) {
                $macAddress = HotspotIdentity::resolveRealMac(null, $ipAddress);
                \Log::warning('⚠️ MAC não enviado pelo frontend, usando fallback', ['mac_fallback' => $macAddress]);
            }

            if (! $macAddress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível identificar o dispositivo. Reconecte ao Wi-Fi e tente novamente.',
                ], 422);
            }

            // Limpar telefone (apenas números)
            $cleanPhone = preg_replace('/[^\d]/', '', $request->phone);
            
            // 🔧 FIX: Primeiro verificar se já existe usuário com este MAC (prioridade máxima)
            $existingUserByMac = $macAddress ? User::where('mac_address', $macAddress)->first() : null;
            
            if ($existingUserByMac) {
                // Dispositivo já foi usado antes - atualizar telefone e IP
                $existingUserByMac->update([
                    'phone' => $cleanPhone,
                    'ip_address' => $ipAddress,
                    'registered_at' => now(),
                    'status' => 'pending',
                ]);

                \Log::info('🔄 Reutilizando usuário existente pelo MAC', [
                    'user_id' => $existingUserByMac->id,
                    'mac_address' => $macAddress,
                    'phone' => $cleanPhone,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Dispositivo reconhecido!',
                    'user_id' => $existingUserByMac->id,
                    'existing_user' => true,
                    'redirect_to_payment' => true,
                ]);
            }
            
            // Se tem user_id e MAC não existe em outro usuário, usar usuário existente
            if ($request->user_id) {
                $user = User::find($request->user_id);

                if (! $user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuário não encontrado',
                    ], 404);
                }

                // Atualizar dados (seguro pois já verificamos que MAC não existe em outro usuário)
                $updateData = [
                    'phone' => $cleanPhone,
                ];

                if (HotspotIdentity::shouldReplaceMac($user->mac_address, $macAddress)) {
                    $updateData['mac_address'] = $macAddress;
                }
                if ($ipAddress && $user->ip_address !== $ipAddress) {
                    $updateData['ip_address'] = $ipAddress;
                }

                $user->update($updateData);

                return response()->json([
                    'success' => true,
                    'message' => 'Dados atualizados com sucesso!',
                    'user_id' => $user->id,
                    'existing_user' => true,
                    'redirect_to_payment' => true,
                ]);
            }

            // Verificar se já existe usuário com este telefone
            $existingUserByPhone = User::where('phone', $cleanPhone)->first();

            if ($existingUserByPhone) {
                // Usuário já existe com este telefone - atualizar MAC/IP (seguro pois já verificamos MAC)
                $updateData = ['phone' => $cleanPhone];
                
                if (HotspotIdentity::shouldReplaceMac($existingUserByPhone->mac_address, $macAddress)) {
                    $updateData['mac_address'] = $macAddress;
                }
                if ($ipAddress) {
                    $updateData['ip_address'] = $ipAddress;
                }
                
                $existingUserByPhone->update($updateData);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Bem-vindo de volta!',
                    'user_id' => $existingUserByPhone->id,
                    'existing_user' => true,
                    'redirect_to_payment' => true,
                ]);
            }

            // Criar novo usuário (apenas com telefone, MAC e IP)
            $userData = [
                'phone' => $cleanPhone,
                'registered_at' => now(),
                'status' => 'pending',
            ];

            if ($macAddress) {
                $userData['mac_address'] = $macAddress;
            }
            if ($ipAddress) {
                $userData['ip_address'] = $ipAddress;
            }

            $user = User::create($userData);

            \Log::info('📱 NOVO USUÁRIO SIMPLIFICADO', [
                'user_id' => $user->id,
                'phone' => $cleanPhone,
                'mac' => $macAddress,
                'ip' => $ipAddress,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cadastro realizado!',
                'user_id' => $user->id,
                'existing_user' => false,
                'redirect_to_payment' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar se usuário existe pelo MAC address
     */
    public function checkMacAddress(string $mac)
    {
        try {
            // Normalizar MAC (uppercase e remover espaços)
            $mac = strtoupper(trim($mac));
            
            // Buscar usuário pelo MAC
            $user = User::where('mac_address', $mac)->first();
            
            if ($user) {
                return response()->json([
                    'exists' => true,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ]);
            }
            
            return response()->json([
                'exists' => false,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao verificar MAC:', ['error' => $e->getMessage()]);
            return response()->json([
                'exists' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
