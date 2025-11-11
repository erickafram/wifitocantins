<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MikrotikWebhookService
{
    private $mikrotikIp;
    private $mikrotikPort;
    private $mikrotikUser;
    private $mikrotikPassword;
    private $timeout;

    public function __construct()
    {
        // Configurações do MikroTik - ajuste conforme necessário
        $this->mikrotikIp = env('MIKROTIK_IP', '10.10.10.1'); // IP do MikroTik na rede local
        $this->mikrotikPort = env('MIKROTIK_REST_PORT', '8728');
        $this->mikrotikUser = env('MIKROTIK_API_USER', 'api-user');
        $this->mikrotikPassword = env('MIKROTIK_API_PASSWORD', 'ApiTocantins2024!');
        $this->timeout = 10; // timeout em segundos
    }

    /**
     * Libera MAC address no MikroTik via REST API
     */
    public function liberarMacAddress($macAddress)
    {
        try {
            Log::info('🚀 WEBHOOK: Iniciando liberação via REST API', [
                'mac_address' => $macAddress,
                'mikrotik_ip' => $this->mikrotikIp
            ]);

            // MÉTODO 1: Tentar via REST API (RouterOS v7+)
            $result = $this->liberarViaRestApi($macAddress);
            
            if (!$result) {
                // MÉTODO 2: Fallback - Executar comando direto via SSH
                $result = $this->liberarViaSSH($macAddress);
            }

            if ($result) {
                Log::info('✅ WEBHOOK: MAC liberado com sucesso!', [
                    'mac_address' => $macAddress
                ]);
                return true;
            }

            Log::warning('⚠️ WEBHOOK: Falha ao liberar MAC', [
                'mac_address' => $macAddress
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('❌ WEBHOOK: Erro ao liberar MAC', [
                'mac_address' => $macAddress,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Método 1: REST API do RouterOS v7
     */
    private function liberarViaRestApi($macAddress)
    {
        try {
            $url = "https://{$this->mikrotikIp}/rest/system/script/run";
            
            $response = Http::withBasicAuth($this->mikrotikUser, $this->mikrotikPassword)
                ->withOptions([
                    'verify' => false, // Ignorar SSL self-signed
                    'timeout' => $this->timeout
                ])
                ->post($url, [
                    'numbers' => 'liberarUsuarioPago',
                    'arguments' => $macAddress
                ]);

            if ($response->successful()) {
                Log::info('✅ REST API: Comando executado', [
                    'response' => $response->body()
                ]);
                return true;
            }

            Log::warning('⚠️ REST API: Resposta não bem-sucedida', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('❌ REST API: Erro na comunicação', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Método 2: SSH direto (fallback)
     */
    private function liberarViaSSH($macAddress)
    {
        try {
            // Comando SSH para executar no MikroTik
            $command = sprintf(
                'sshpass -p "%s" ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 %s@%s "/system script run liberarUsuarioPago %s"',
                escapeshellarg($this->mikrotikPassword),
                escapeshellarg($this->mikrotikUser),
                escapeshellarg($this->mikrotikIp),
                escapeshellarg($macAddress)
            );

            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);

            if ($return_var === 0) {
                Log::info('✅ SSH: Comando executado com sucesso', [
                    'output' => implode("\n", $output)
                ]);
                return true;
            }

            Log::warning('⚠️ SSH: Comando falhou', [
                'return_code' => $return_var,
                'output' => implode("\n", $output)
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('❌ SSH: Erro ao executar comando', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Método 3: Chamada HTTP simples (se MikroTik tiver script web)
     */
    public function liberarViaHttpSimples($macAddress)
    {
        try {
            // URL customizada se você criar um endpoint HTTP no MikroTik
            $url = "http://{$this->mikrotikIp}/liberar.php";
            
            $response = Http::timeout($this->timeout)
                ->post($url, [
                    'mac_address' => $macAddress,
                    'token' => env('MIKROTIK_SYNC_TOKEN', 'mikrotik-sync-2024')
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('❌ HTTP Simples: Erro', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Testa conexão com MikroTik
     */
    public function testConnection()
    {
        try {
            $url = "https://{$this->mikrotikIp}/rest/system/identity";
            
            $response = Http::withBasicAuth($this->mikrotikUser, $this->mikrotikPassword)
                ->withOptions([
                    'verify' => false,
                    'timeout' => 5
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('✅ Conexão MikroTik OK', [
                    'identity' => $data['name'] ?? 'Unknown'
                ]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('❌ Teste de conexão falhou', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}