<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
                    'errors' => $validator->errors()
                ], 422);
            }

            // Criar o usuário
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make('default_password_' . time()), // Password temporário
                'registered_at' => now(),
                'status' => 'pending' // Status inicial
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário cadastrado com sucesso!',
                'user_id' => $user->id,
                'redirect_to_payment' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
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
            'exists' => $exists
        ]);
    }

    /**
     * Check if user exists by email or phone and return user data
     */
    public function checkUser(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string'
        ]);

        $user = null;

        // Buscar por email ou telefone
        if ($request->email) {
            $user = User::where('email', $request->email)->first();
        } elseif ($request->phone) {
            // Limpar formatação do telefone para busca
            $cleanPhone = preg_replace('/[^\d]/', '', $request->phone);
            $user = User::where('phone', 'LIKE', '%' . $cleanPhone . '%')->first();
        }

        if ($user) {
            return response()->json([
                'exists' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone
                ]
            ]);
        }

        return response()->json([
            'exists' => false
        ]);
    }

    private function resolveClientIp(Request $request)
    {
        $candidates = [
            $request->input('ip_address'),
            $request->input('client_ip'),
            $request->header('X-Client-IP'),
            $request->header('X-Forwarded-For'),
            $request->header('X-Real-IP')
        ];

        foreach ($candidates as $value) {
            if (!$value) {
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
        if (!$currentMac) {
            return true;
        }

        $isCurrentMock = stripos($currentMac, '02:') === 0;
        $isNewMock = stripos($newMac, '02:') === 0;

        if ($isCurrentMock && !$isNewMock) {
            return true;
        }

        if (!$isCurrentMock && !$isNewMock) {
            return $currentMac !== $newMac;
        }

        return false;
    }

    /**
     * Register or update existing user for payment
     */
    public function registerForPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id', // ID do usuário existente (opcional)
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'mac_address' => 'nullable|string|max:17',
                'ip_address' => 'nullable|ip',
            ], [
                'name.required' => 'Nome completo é obrigatório',
                'email.required' => 'E-mail é obrigatório',
                'email.email' => 'E-mail deve ter um formato válido',
                'phone.required' => 'Telefone é obrigatório',
                'mac_address.string' => 'MAC address deve ser uma string válida',
                'ip_address.ip' => 'IP inválido',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 🎯 PROCESSAR MAC ADDRESS
            $macAddress = $request->mac_address ? strtoupper(str_replace('-', ':', $request->mac_address)) : null;
            $ipAddress = $this->resolveClientIp($request);

            // Se tem user_id, é um usuário existente
            if ($request->user_id) {
                $user = User::find($request->user_id);

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuário não encontrado'
                    ], 404);
                }

                // Atualizar dados se necessário
                $updateData = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ];

                // 🎯 ATUALIZAR MAC E IP SE FORNECIDOS
                if ($macAddress && ($this->shouldReplaceMac($user->mac_address, $macAddress))) {
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
                    'redirect_to_payment' => true
                ]);
            }

            // Verificar se já existe usuário com este email ou telefone
            $existingUser = User::where('email', $request->email)
                                ->orWhere('phone', $request->phone)
                                ->first();

            if ($existingUser) {
                $updates = [];
                if ($macAddress && $this->shouldReplaceMac($existingUser->mac_address, $macAddress)) {
                    $updates['mac_address'] = $macAddress;
                }
                if ($ipAddress && $existingUser->ip_address !== $ipAddress) {
                    $updates['ip_address'] = $ipAddress;
                }

                if (!empty($updates)) {
                    $existingUser->update($updates);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Usuário já existente atualizado.',
                    'user_id' => $existingUser->id,
                    'existing_user' => true,
                    'redirect_to_payment' => true
                ]);
            }

            // Criar novo usuário
            $userData = [
                'name' => $request->name ?? 'Visitante WiFi',
                'email' => $request->email ?? ('guest+' . uniqid() . '@wifitocantins.com.br'),
                'phone' => $request->phone ?? '0000000000',
                'password' => Hash::make('default_password_' . time()),
                'registered_at' => now(),
                'status' => 'pending'
            ];

            // 🎯 ADICIONAR MAC E IP SE FORNECIDOS
            if ($macAddress) {
                $userData['mac_address'] = $macAddress;
            }
            if ($ipAddress) {
                $userData['ip_address'] = $ipAddress;
            }

            $user = User::create($userData);

            return response()->json([
                'success' => true,
                'message' => 'Usuário cadastrado com sucesso!',
                'user_id' => $user->id,
                'existing_user' => false,
                'redirect_to_payment' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
