<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RouterOS\Client;
use RouterOS\Query;

class MikrotikRemoteController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function connect()
    {
        try {
            $config = [
                'host' => env('MIKROTIK_HOST', '10.5.50.1'),
                'user' => env('MIKROTIK_USER', 'admin'),
                'pass' => env('MIKROTIK_PASSWORD', ''),
                'port' => (int) env('MIKROTIK_PORT', 8728),
            ];

            $this->client = new Client($config);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function index()
    {
        return view('admin.mikrotik.remote');
    }

    public function getStatus()
    {
        if (!$this->connect()) {
            return response()->json(['error' => 'Não foi possível conectar ao Mikrotik'], 500);
        }

        try {
            // Informações do sistema
            $query = new Query('/system/resource/print');
            $system = $this->client->query($query)->read();

            // Usuários ativos
            $query = new Query('/ip/hotspot/active/print');
            $activeUsers = $this->client->query($query)->read();

            // Bindings (usuários pagos)
            $query = new Query('/ip/hotspot/ip-binding/print');
            $bindings = $this->client->query($query)->read();

            // DHCP Leases
            $query = new Query('/ip/dhcp-server/lease/print');
            $leases = $this->client->query($query)->read();

            return response()->json([
                'system' => $system,
                'activeUsers' => count($activeUsers),
                'paidUsers' => count($bindings),
                'totalDevices' => count($leases),
                'details' => [
                    'active' => $activeUsers,
                    'bindings' => $bindings,
                    'leases' => $leases,
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

        if (!$this->connect()) {
            return response()->json(['error' => 'Não foi possível conectar ao Mikrotik'], 500);
        }

        try {
            $command = $request->input('command');
            
            // Segurança: apenas comandos de leitura e alguns específicos
            $allowedCommands = [
                '/system/resource/print',
                '/ip/hotspot/active/print',
                '/ip/hotspot/ip-binding/print',
                '/ip/dhcp-server/lease/print',
                '/log/print',
                '/interface/print',
                '/ip/address/print',
                '/system/script/run',
            ];

            $isAllowed = false;
            foreach ($allowedCommands as $allowed) {
                if (str_starts_with($command, $allowed)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                return response()->json(['error' => 'Comando não permitido'], 403);
            }

            $query = new Query($command);
            $response = $this->client->query($query)->read();

            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncNow()
    {
        if (!$this->connect()) {
            return response()->json(['error' => 'Não foi possível conectar ao Mikrotik'], 500);
        }

        try {
            // Executar script de sincronização
            $query = (new Query('/system/script/run'))
                ->equal('number', 'syncPagos');
            
            $this->client->query($query)->read();

            // Aguardar 2 segundos
            sleep(2);

            // Buscar logs
            $query = (new Query('/log/print'))
                ->where('message', '~SYNC');
            
            $logs = $this->client->query($query)->read();

            return response()->json([
                'success' => true,
                'message' => 'Sincronização executada',
                'logs' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function liberateMac(Request $request)
    {
        $request->validate([
            'mac' => 'required|string',
        ]);

        if (!$this->connect()) {
            return response()->json(['error' => 'Não foi possível conectar ao Mikrotik'], 500);
        }

        try {
            $mac = strtoupper($request->input('mac'));

            // Adicionar binding
            $query = (new Query('/ip/hotspot/ip-binding/add'))
                ->equal('mac-address', $mac)
                ->equal('type', 'bypassed')
                ->equal('comment', 'PAGO-MANUAL');
            
            $this->client->query($query)->read();

            return response()->json([
                'success' => true,
                'message' => "MAC {$mac} liberado com sucesso"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function blockMac(Request $request)
    {
        $request->validate([
            'mac' => 'required|string',
        ]);

        if (!$this->connect()) {
            return response()->json(['error' => 'Não foi possível conectar ao Mikrotik'], 500);
        }

        try {
            $mac = strtoupper($request->input('mac'));

            // Buscar binding
            $query = (new Query('/ip/hotspot/ip-binding/print'))
                ->where('mac-address', $mac);
            
            $bindings = $this->client->query($query)->read();

            if (empty($bindings)) {
                return response()->json(['error' => 'MAC não encontrado'], 404);
            }

            // Remover binding
            $query = (new Query('/ip/hotspot/ip-binding/remove'))
                ->equal('.id', $bindings[0]['.id']);
            
            $this->client->query($query)->read();

            return response()->json([
                'success' => true,
                'message' => "MAC {$mac} bloqueado com sucesso"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLogs()
    {
        if (!$this->connect()) {
            return response()->json(['error' => 'Não foi possível conectar ao Mikrotik'], 500);
        }

        try {
            $query = (new Query('/log/print'));
            $logs = $this->client->query($query)->read();

            // Pegar últimos 50 logs
            $logs = array_slice($logs, -50);

            return response()->json([
                'success' => true,
                'logs' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
