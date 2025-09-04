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
}
