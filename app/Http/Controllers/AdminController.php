<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Payment;
use App\Models\Voucher;
use App\Models\Device;
use App\Models\Session;
use App\Models\SystemSetting;

class AdminController extends Controller
{
    /**
     * Dashboard principal
     */
    public function dashboard()
    {
        $stats = $this->getDashboardStats();
        $connected_users = $this->getConnectedUsers();
        $revenue_chart = $this->getRevenueChartData();
        $connections_chart = $this->getConnectionsChartData();

        return view('admin.dashboard', compact(
            'stats',
            'connected_users', 
            'revenue_chart',
            'connections_chart'
        ));
    }

    /**
     * Obtém estatísticas do dashboard
     */
    private function getDashboardStats()
    {
        return [
            'connected_users' => User::where('status', 'connected')->count(),
            'daily_revenue' => Payment::where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'total_devices' => Device::count(),
            'active_vouchers' => Voucher::where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->count()
        ];
    }

    /**
     * Obtém usuários conectados
     */
    private function getConnectedUsers()
    {
        return User::where('status', 'connected')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->orderBy('connected_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Dados para gráfico de receita
     */
    private function getRevenueChartData()
    {
        $days = collect();
        $revenues = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days->push($date->format('d/m'));
            
            $revenue = Payment::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('amount');
            
            $revenues->push((float) $revenue);
        }

        return [
            'labels' => $days->toArray(),
            'data' => $revenues->toArray()
        ];
    }

    /**
     * Dados para gráfico de conexões por hora
     */
    private function getConnectionsChartData()
    {
        $hours = collect();
        $connections = collect();

        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('H:00');
            $hours->push($hour);
            
            $count = Session::where('started_at', '>=', now()->subHours($i+1))
                ->where('started_at', '<', now()->subHours($i))
                ->count();
            
            $connections->push($count);
        }

        return [
            'labels' => $hours->slice(-12)->values()->toArray(), // Últimas 12 horas
            'data' => $connections->slice(-12)->values()->toArray()
        ];
    }

    /**
     * Relatório de receitas
     */
    public function revenueReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $payments = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $totalRevenue = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $paymentMethods = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('method')
            ->select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->get();

        return view('admin.revenue-report', compact(
            'payments',
            'totalRevenue',
            'paymentMethods',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Gerenciar vouchers
     */
    public function vouchers()
    {
        $vouchers = Voucher::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.vouchers', compact('vouchers'));
    }

    /**
     * Criar voucher
     */
    public function createVoucher(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
            'prefix' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
            'max_uses' => 'required|integer|min:1'
        ]);

        try {
            $vouchers = [];
            $prefix = $request->prefix ?? 'WIFI';

            for ($i = 0; $i < $request->quantity; $i++) {
                $code = $this->generateVoucherCode($prefix);
                
                $voucher = Voucher::create([
                    'code' => $code,
                    'description' => $request->description ?? 'Voucher gerado pelo admin',
                    'discount' => null,
                    'discount_percent' => 100, // Acesso gratuito
                    'expires_at' => $request->expires_at,
                    'max_uses' => $request->max_uses,
                    'used_count' => 0,
                    'is_active' => true
                ]);

                $vouchers[] = $voucher;
            }

            return redirect()->back()->with('success', "Criados {$request->quantity} vouchers com sucesso!");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao criar vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Desativar voucher
     */
    public function deactivateVoucher($id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Voucher desativado com sucesso!');
    }

    /**
     * Dispositivos conectados
     */
    public function devices()
    {
        $devices = Device::with(['user' => function($query) {
            $query->where('status', 'connected');
        }])
        ->orderBy('last_seen', 'desc')
        ->paginate(30);

        return view('admin.devices', compact('devices'));
    }

    /**
     * Logs de conexão
     */
    public function connectionLogs()
    {
        $sessions = Session::with(['user', 'payment'])
            ->orderBy('started_at', 'desc')
            ->paginate(50);

        return view('admin.connection-logs', compact('sessions'));
    }

    /**
     * Configurações do sistema
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * API para obter estatísticas em tempo real
     */
    public function apiStats()
    {
        return response()->json([
            'connected_users' => User::where('status', 'connected')->count(),
            'daily_revenue' => Payment::where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'total_devices' => Device::count(),
            'active_sessions' => Session::where('session_status', 'active')->count()
        ]);
    }

    /**
     * Exportar relatório
     */
    public function exportReport(Request $request)
    {
        $type = $request->get('type', 'payments');
        $format = $request->get('format', 'csv');
        
        // Implementar exportação (CSV, Excel, PDF)
        
        return response()->json(['message' => 'Export em desenvolvimento']);
    }

    /**
     * Gera código único para voucher
     */
    private function generateVoucherCode($prefix = 'WIFI')
    {
        do {
            $code = $prefix . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }

    /**
     * Gerenciar usuários
     */
    public function users()
    {
        $users = User::with('payments')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Estatísticas para a página
        $stats = [
            'total_users' => User::count(),
            'connected_users' => User::where('status', 'connected')->count(),
            'today_registrations' => User::whereDate('created_at', today())->count(),
            'users_with_payments' => User::whereHas('payments', function($q) { 
                $q->where('status', 'completed'); 
            })->count()
        ];
        
        return view('admin.users', compact('users', 'stats'));
    }

    /**
     * Obter detalhes de um usuário
     */
    public function getUserDetails($id)
    {
        $user = User::with(['payments', 'sessions'])->findOrFail($id);
        
        return response()->json($user);
    }

    /**
     * Desconectar usuário
     */
    public function disconnectUser($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Atualizar status do usuário
            $user->update([
                'status' => 'offline',
                'expires_at' => null,
                'connected_at' => null
            ]);

            // Finalizar sessões ativas
            $user->sessions()->where('session_status', 'active')->update([
                'session_status' => 'ended',
                'ended_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário desconectado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desconectar usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir usuário
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Verificar se é um administrador
            if ($user->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir usuários administradores!'
                ], 403);
            }

            // Finalizar sessões ativas antes de excluir
            $user->sessions()->where('session_status', 'active')->update([
                'session_status' => 'ended',
                'ended_at' => now()
            ]);

            // Excluir o usuário (soft delete se estiver configurado)
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    public function apiSettings()
    {
        $currentGateway = SystemSetting::getValue('pix_gateway', 'santander');

        $gateways = [
            'santander' => 'Santander (Recomendado)',
            'woovi' => 'Woovi',
        ];

        return view('admin.api-settings', compact('currentGateway', 'gateways'));
    }

    public function updateGateway(Request $request)
    {
        $request->validate([
            'pix_gateway' => 'required|in:santander,woovi',
        ]);

        SystemSetting::setValue('pix_gateway', $request->pix_gateway);

        return redirect()->route('admin.api')->with('success', 'Gateway PIX atualizado com sucesso!');
    }
}
