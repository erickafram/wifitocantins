<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\User;
use App\Models\Session;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Filtros padrão
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $paymentStatus = $request->get('payment_status', 'all');
        $userStatus = $request->get('user_status', 'all');
        
        // Estatísticas gerais
        $stats = $this->getGeneralStats($startDate, $endDate, $paymentStatus, $userStatus);
        
        // Dados dos pagamentos
        $payments = $this->getPaymentsData($startDate, $endDate, $paymentStatus);
        
        // Dados dos usuários
        $users = $this->getUsersData($startDate, $endDate, $userStatus);
        
        // Dados para gráficos
        $charts = $this->getChartsData($startDate, $endDate);
        
        return view('admin.reports', compact(
            'stats', 
            'payments', 
            'users', 
            'charts',
            'startDate',
            'endDate',
            'paymentStatus',
            'userStatus'
        ));
    }
    
    private function getGeneralStats($startDate, $endDate, $paymentStatus, $userStatus)
    {
        // Receita total - apenas pagamentos completados
        $totalRevenue = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');
        
        // Total de pagamentos - respeitando filtro
        $query = Payment::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($paymentStatus !== 'all') {
            $query->where('status', $paymentStatus);
        }
        
        $totalPayments = $query->count();
        
        // Pagamentos por status
        $paymentsByStatus = Payment::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        
        // Usuários conectados no período
        $userQuery = User::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($userStatus !== 'all') {
            $userQuery->where('status', $userStatus);
        }
        
        $totalUsers = $userQuery->count();
        $connectedUsers = User::where('status', 'connected')->count();
        
        // Sessões ativas
        $activeSessions = DB::table('wifi_sessions')
            ->whereBetween('started_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('session_status', 'active')
            ->count();
        
        // Pagamentos pendentes
        $pendingPayments = Payment::where('status', 'pending')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');
        
        return [
            'total_revenue' => $totalRevenue,
            'pending_payments' => $pendingPayments,
            'total_payments' => $totalPayments,
            'total_users' => $totalUsers,
            'connected_users' => $connectedUsers,
            'active_sessions' => $activeSessions,
            'payments_by_status' => $paymentsByStatus,
            'avg_payment' => $totalPayments > 0 ? $totalRevenue / $totalPayments : 0,
        ];
    }
    
    private function getPaymentsData($startDate, $endDate, $paymentStatus)
    {
        $query = Payment::with(['user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($paymentStatus !== 'all') {
            $query->where('status', $paymentStatus);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->paginate(10);
    }
    
    private function getUsersData($startDate, $endDate, $userStatus)
    {
        $query = User::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($userStatus !== 'all') {
            $query->where('status', $userStatus);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->paginate(50);
    }
    
    private function getChartsData($startDate, $endDate)
    {
        // Receita por dia
        $revenueByDay = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Pagamentos por status
        $paymentsByStatus = Payment::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        // Usuários por dia
        $usersByDay = User::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Conexões por hora (últimas 24h)
        $connectionsByHour = User::where('connected_at', '>=', Carbon::now()->subDay())
            ->select(
                DB::raw('HOUR(connected_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        return [
            'revenue_by_day' => $revenueByDay,
            'payments_by_status' => $paymentsByStatus,
            'users_by_day' => $usersByDay,
            'connections_by_hour' => $connectionsByHour,
        ];
    }
    
    public function export(Request $request)
    {
        $type = $request->get('type', 'payments');
        $format = $request->get('format', 'csv');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        if ($type === 'payments') {
            return $this->exportPayments($startDate, $endDate, $format);
        } elseif ($type === 'users') {
            return $this->exportUsers($startDate, $endDate, $format);
        }
        
        return back()->with('error', 'Tipo de exportação inválido');
    }
    
    private function exportPayments($startDate, $endDate, $format)
    {
        $payments = Payment::with(['user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $filename = 'pagamentos_' . $startDate . '_' . $endDate . '.' . $format;
        
        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($payments) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Usuario', 'Email', 'Valor', 'Tipo', 'Status', 'Data Pagamento', 'Data Criacao']);
                
                foreach ($payments as $payment) {
                    fputcsv($file, [
                        $payment->id,
                        $payment->user->name ?? 'N/A',
                        $payment->user->email ?? 'N/A',
                        'R$ ' . number_format($payment->amount, 2, ',', '.'),
                        ucfirst($payment->payment_type),
                        ucfirst($payment->status),
                        $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : 'N/A',
                        $payment->created_at->format('d/m/Y H:i:s'),
                    ]);
                }
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        return back()->with('error', 'Formato de exportação não suportado');
    }
    
    private function exportUsers($startDate, $endDate, $format)
    {
        $users = User::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $filename = 'usuarios_' . $startDate . '_' . $endDate . '.' . $format;
        
        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($users) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Nome', 'Email', 'Telefone', 'MAC Address', 'IP Address', 'Status', 'Conectado em', 'Expira em', 'Data Cadastro']);
                
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->name ?? 'N/A',
                        $user->email ?? 'N/A',
                        $user->phone ?? 'N/A',
                        $user->mac_address ?? 'N/A',
                        $user->ip_address ?? 'N/A',
                        ucfirst($user->status),
                        $user->connected_at ? $user->connected_at->format('d/m/Y H:i:s') : 'N/A',
                        $user->expires_at ? $user->expires_at->format('d/m/Y H:i:s') : 'N/A',
                        $user->created_at->format('d/m/Y H:i:s'),
                    ]);
                }
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        return back()->with('error', 'Formato de exportação não suportado');
    }
}

