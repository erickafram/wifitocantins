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
     * Register or update existing user for payment
     */
    public function registerForPayment(Request $request)
    {
        try {
            // Validação diferente para usuário existente (login) vs novo usuário (cadastro)
            $rules = [
                'user_id' => 'nullable|exists:users,id',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'mac_address' => 'nullable|string|max:17',
                'ip_address' => 'nullable|ip',
                'password' => 'required|string|min:6',
            ];

            // Se NÃO tem user_id, é novo usuário - exigir name e password_confirmation
            if (!$request->has('user_id') || !$request->user_id) {
                $rules['name'] = 'required|string|max:255';
                $rules['password_confirmation'] = 'required|string|min:6|same:password';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // 🎯 PROCESSAR MAC ADDRESS
            $ipAddress = $ipAddress ?? HotspotIdentity::resolveClientIp($request);
            $macAddress = $macAddress ?? HotspotIdentity::resolveRealMac($request->input('mac_address'), $ipAddress);

            if (! $macAddress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível identificar o dispositivo. Reconecte ao Wi-Fi e tente novamente.',
                ], 422);
            }

            // Se tem user_id, é um usuário existente tentando fazer login
            if ($request->user_id) {
                $user = User::find($request->user_id);

                if (! $user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuário não encontrado',
                    ], 404);
                }

                // Validar senha do usuário existente
                if (!$request->filled('password') || !Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Senha incorreta.',
                        'errors' => ['password' => ['Senha incorreta.']],
                    ], 422);
                }

                // Atualizar apenas MAC e IP se necessário (não atualiza nome/email/senha)
                $updateData = [];

                if (HotspotIdentity::shouldReplaceMac($user->mac_address, $macAddress)) {
                    $updateData['mac_address'] = $macAddress;
                }
                if ($ipAddress && $user->ip_address !== $ipAddress) {
                    $updateData['ip_address'] = $ipAddress;
                }

                if (!empty($updateData)) {
                    $user->update($updateData);
                }

                // Fazer login automático do usuário
                auth()->login($user);

                return response()->json([
                    'success' => true,
                    'message' => 'Login realizado com sucesso!',
                    'user_id' => $user->id,
                    'existing_user' => true,
                    'redirect_to_dashboard' => true,
                ]);
            }

            // Verificar se já existe usuário com este email, telefone OU MAC address
            $existingUser = User::where('email', $request->email)
                ->orWhere('phone', $request->phone)
                ->orWhere('mac_address', $macAddress)
                ->first();

            if ($existingUser) {
                // Usuário já existe - atualizar dados
                $updates = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password ?? 'default_password_'.time()),
                ];
                
                // Atualizar MAC e IP
                if (HotspotIdentity::shouldReplaceMac($existingUser->mac_address, $macAddress)) {
                    $updates['mac_address'] = $macAddress;
                }
                if ($ipAddress && $existingUser->ip_address !== $ipAddress) {
                    $updates['ip_address'] = $ipAddress;
                }

                $existingUser->update($updates);
                
                // Fazer login automático
                auth()->login($existingUser);

                return response()->json([
                    'success' => true,
                    'message' => 'Cadastro atualizado com sucesso!',
                    'user_id' => $existingUser->id,
                    'existing_user' => false,
                    'redirect_to_dashboard' => true,
                ]);
            }

            // Criar novo usuário (só chega aqui se realmente não existir)
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password ?? 'default_password_'.time()),
                'registered_at' => now(),
                'status' => 'pending',
            ];

            // Adicionar MAC e IP
            if ($macAddress) {
                $userData['mac_address'] = $macAddress;
            }
            if ($ipAddress) {
                $userData['ip_address'] = $ipAddress;
            }

            $user = User::create($userData);
            
            // Fazer login automático
            auth()->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Usuário cadastrado com sucesso!',
                'user_id' => $user->id,
                'existing_user' => false,
                'redirect_to_dashboard' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: '.$e->getMessage(),
            ], 500);
        }
    }
}
