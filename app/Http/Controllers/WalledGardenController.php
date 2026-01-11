<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalledGardenController extends Controller
{
    /**
     * Lista de domínios de bancos brasileiros para Walled Garden
     * Estes domínios são liberados para acesso sem autenticação no hotspot
     * para que os usuários possam pagar via PIX
     */
    private function getBankDomains(): array
    {
        return [
            // ========== BANCOS DIGITAIS ==========
            // Nubank (usa AWS)
            '*.nubank.com.br',
            '*.nubank.com',
            'nubank.com.br',
            'nubank.com',
            '*.nubankstatic.com',
            
            // PicPay
            '*.picpay.com',
            'picpay.com',
            
            // Inter
            '*.bancointer.com.br',
            '*.inter.co',
            'bancointer.com.br',
            
            // C6 Bank
            '*.c6bank.com.br',
            '*.c6bank.com',
            'c6bank.com.br',
            
            // Neon
            '*.neon.com.br',
            'neon.com.br',
            
            // Next (Bradesco)
            '*.next.me',
            'next.me',
            
            // Will Bank
            '*.willbank.com.br',
            'willbank.com.br',
            
            // Original
            '*.original.com.br',
            'original.com.br',
            
            // PagBank/PagSeguro
            '*.pagseguro.com.br',
            '*.pagseguro.uol.com.br',
            'pagseguro.com.br',
            
            // Mercado Pago
            '*.mercadopago.com.br',
            '*.mercadopago.com',
            'mercadopago.com.br',
            
            // ========== BANCOS TRADICIONAIS ==========
            // Caixa Econômica Federal
            '*.caixa.gov.br',
            'caixa.gov.br',
            '*.caixa.com.br',
            'internetbanking.caixa.gov.br',
            'acessoseguro.caixa.gov.br',
            
            // Banco do Brasil
            '*.bb.com.br',
            'bb.com.br',
            '*.bancodobrasil.com.br',
            
            // Itaú
            '*.itau.com.br',
            'itau.com.br',
            '*.itau-unibanco.com.br',
            
            // Bradesco
            '*.bradesco.com.br',
            'bradesco.com.br',
            '*.banco.bradesco',
            
            // Santander
            '*.santander.com.br',
            'santander.com.br',
            '*.santandernet.com.br',
            
            // BRB (Banco de Brasília)
            '*.brb.com.br',
            'brb.com.br',
            
            // Banco da Amazônia
            '*.bancoamazonia.com.br',
            'bancoamazonia.com.br',
            '*.basa.com.br',
            
            // Sicoob
            '*.sicoob.com.br',
            'sicoob.com.br',
            
            // Sicredi
            '*.sicredi.com.br',
            'sicredi.com.br',
            
            // Banrisul
            '*.banrisul.com.br',
            'banrisul.com.br',
            
            // ========== PIX / BANCO CENTRAL ==========
            '*.bcb.gov.br',
            'bcb.gov.br',
            
            // ========== GATEWAYS DE PAGAMENTO ==========
            // Woovi/OpenPix
            '*.woovi.com',
            'woovi.com',
            '*.openpix.com.br',
            'openpix.com.br',
            
            // ========== CDNs USADOS PELOS BANCOS ==========
            '*.cloudflare.com',
            '*.akamaized.net',
            '*.azioncdn.net',
            '*.azion.net',
            '*.fastly.net',
            
            // Google (usado por vários apps)
            '*.googleapis.com',
            '*.gstatic.com',
            '*.firebase.google.com',
            '*.firebaseio.com',
            
            // Apple (para Apple Pay)
            '*.apple.com',
            '*.icloud.com',
        ];
    }

    /**
     * Lista de ranges de IP dos bancos e CDNs
     * Formato CIDR para uso no MikroTik
     */
    private function getBankIpRanges(): array
    {
        return [
            // ========== AWS BRASIL (usado por Nubank, PicPay, etc) ==========
            ['range' => '18.228.0.0/14', 'comment' => 'AWS-BR-1'],
            ['range' => '18.230.0.0/16', 'comment' => 'AWS-BR-2'],
            ['range' => '18.231.0.0/16', 'comment' => 'AWS-BR-3'],
            ['range' => '52.67.0.0/16', 'comment' => 'AWS-BR-4'],
            ['range' => '54.207.0.0/16', 'comment' => 'AWS-BR-5'],
            ['range' => '54.232.0.0/14', 'comment' => 'AWS-BR-6'],
            ['range' => '177.71.128.0/17', 'comment' => 'AWS-BR-7'],
            ['range' => '15.228.0.0/15', 'comment' => 'AWS-BR-8'],
            ['range' => '3.0.0.0/8', 'comment' => 'AWS-Global'],
            
            // ========== CLOUDFLARE ==========
            ['range' => '104.16.0.0/12', 'comment' => 'Cloudflare-1'],
            ['range' => '172.64.0.0/13', 'comment' => 'Cloudflare-2'],
            ['range' => '131.0.72.0/22', 'comment' => 'Cloudflare-3'],
            ['range' => '141.101.64.0/18', 'comment' => 'Cloudflare-4'],
            ['range' => '162.158.0.0/15', 'comment' => 'Cloudflare-5'],
            ['range' => '188.114.96.0/20', 'comment' => 'Cloudflare-6'],
            ['range' => '190.93.240.0/20', 'comment' => 'Cloudflare-7'],
            ['range' => '197.234.240.0/22', 'comment' => 'Cloudflare-8'],
            ['range' => '198.41.128.0/17', 'comment' => 'Cloudflare-9'],
            
            // ========== AKAMAI (CDN de bancos) ==========
            ['range' => '23.0.0.0/12', 'comment' => 'Akamai-1'],
            ['range' => '104.64.0.0/10', 'comment' => 'Akamai-2'],
            ['range' => '184.24.0.0/13', 'comment' => 'Akamai-3'],
            ['range' => '184.50.0.0/15', 'comment' => 'Akamai-4'],
            
            // ========== AZURE BRASIL ==========
            ['range' => '191.232.0.0/13', 'comment' => 'Azure-BR-1'],
            ['range' => '191.234.0.0/15', 'comment' => 'Azure-BR-2'],
            ['range' => '20.195.0.0/16', 'comment' => 'Azure-BR-3'],
            ['range' => '20.197.0.0/16', 'comment' => 'Azure-BR-4'],
            ['range' => '20.201.0.0/16', 'comment' => 'Azure-BR-5'],
            ['range' => '20.206.0.0/16', 'comment' => 'Azure-BR-6'],
            
            // ========== GOOGLE CLOUD BRASIL ==========
            ['range' => '35.198.0.0/16', 'comment' => 'GCP-BR-1'],
            ['range' => '35.199.0.0/16', 'comment' => 'GCP-BR-2'],
            ['range' => '35.247.0.0/16', 'comment' => 'GCP-BR-3'],
            
            // ========== BANCOS ESPECÍFICOS ==========
            // Caixa Econômica Federal
            ['range' => '200.201.0.0/16', 'comment' => 'CEF-1'],
            ['range' => '200.201.160.0/20', 'comment' => 'CEF-2'],
            ['range' => '161.148.0.0/16', 'comment' => 'CEF-CDN'],
            
            // Banco do Brasil
            ['range' => '170.66.0.0/16', 'comment' => 'BB-1'],
            ['range' => '201.33.144.0/21', 'comment' => 'BB-2'],
            ['range' => '198.184.161.0/24', 'comment' => 'BB-3'],
            
            // Itaú
            ['range' => '200.196.144.0/20', 'comment' => 'Itau-1'],
            ['range' => '138.59.160.0/22', 'comment' => 'Itau-2'],
            ['range' => '200.186.244.0/24', 'comment' => 'Itau-3'],
            
            // Bradesco
            ['range' => '200.155.80.0/20', 'comment' => 'Bradesco-1'],
            ['range' => '177.92.208.0/20', 'comment' => 'Bradesco-2'],
            
            // Santander
            ['range' => '200.220.176.0/20', 'comment' => 'Santander-1'],
            ['range' => '200.232.64.0/19', 'comment' => 'Santander-2'],
            
            // BRB
            ['range' => '200.11.16.0/20', 'comment' => 'BRB'],
            
            // Banco da Amazônia
            ['range' => '45.5.204.0/22', 'comment' => 'BancoAmazonia'],
            
            // ========== AZION (CDN usado pela Caixa) ==========
            ['range' => '179.191.160.0/20', 'comment' => 'Azion-1'],
            ['range' => '179.191.176.0/20', 'comment' => 'Azion-2'],
            ['range' => '186.195.64.0/20', 'comment' => 'Azion-3'],
            
            // ========== FASTLY (CDN) ==========
            ['range' => '151.101.0.0/16', 'comment' => 'Fastly-1'],
            ['range' => '199.232.0.0/16', 'comment' => 'Fastly-2'],
            
            // ========== DNS ==========
            ['range' => '1.1.1.1/32', 'comment' => 'DNS-Cloudflare'],
            ['range' => '1.0.0.1/32', 'comment' => 'DNS-Cloudflare-2'],
            ['range' => '8.8.8.8/32', 'comment' => 'DNS-Google'],
            ['range' => '8.8.4.4/32', 'comment' => 'DNS-Google-2'],
        ];
    }

    /**
     * Endpoint para MikroTik buscar lista de domínios do Walled Garden
     * Formato: texto simples, um domínio por linha
     */
    public function getDomains(Request $request)
    {
        $token = $request->get('token');
        $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
        
        if ($token !== $expectedToken) {
            return response('ERROR:AUTH', 401)->header('Content-Type', 'text/plain');
        }

        $domains = $this->getBankDomains();
        $output = implode("\n", $domains);
        
        return response($output, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Cache-Control', 'max-age=3600'); // Cache 1 hora
    }

    /**
     * Endpoint para MikroTik buscar lista de IPs do Walled Garden
     * Formato: texto simples, CIDR|comentário por linha
     */
    public function getIpRanges(Request $request)
    {
        $token = $request->get('token');
        $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
        
        if ($token !== $expectedToken) {
            return response('ERROR:AUTH', 401)->header('Content-Type', 'text/plain');
        }

        $ranges = $this->getBankIpRanges();
        $lines = [];
        
        foreach ($ranges as $range) {
            $lines[] = $range['range'] . '|' . $range['comment'];
        }
        
        $output = implode("\n", $lines);
        
        return response($output, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Cache-Control', 'max-age=3600'); // Cache 1 hora
    }

    /**
     * Endpoint JSON com todas as informações do Walled Garden
     */
    public function getAll(Request $request)
    {
        $token = $request->get('token');
        $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
        
        if ($token !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'success' => true,
            'domains' => $this->getBankDomains(),
            'ip_ranges' => $this->getBankIpRanges(),
            'total_domains' => count($this->getBankDomains()),
            'total_ip_ranges' => count($this->getBankIpRanges()),
            'updated_at' => '2026-01-11',
            'note' => 'Domínios e IPs de bancos brasileiros para Walled Garden do Hotspot'
        ]);
    }

    /**
     * Gera script RouterOS para configurar Walled Garden
     * Pode ser executado diretamente no MikroTik
     */
    public function getRouterOSScript(Request $request)
    {
        $token = $request->get('token');
        $expectedToken = config('wifi.mikrotik_sync_token', 'mikrotik-sync-2024');
        
        if ($token !== $expectedToken) {
            return response('# ERROR: AUTH', 401)->header('Content-Type', 'text/plain');
        }

        $hotspotServer = $request->get('server', 'tocantins-hotspot');
        
        $script = "# ============================================================\n";
        $script .= "# WALLED GARDEN - BANCOS BRASILEIROS\n";
        $script .= "# Gerado automaticamente em " . now()->format('Y-m-d H:i:s') . "\n";
        $script .= "# ============================================================\n\n";
        
        // Remover regras antigas com tag específica
        $script .= "# Remover regras antigas\n";
        $script .= "/ip hotspot walled-garden remove [find comment~\"AUTO-BANK\"]\n";
        $script .= "/ip hotspot walled-garden ip remove [find comment~\"AUTO-BANK\"]\n\n";
        
        // Adicionar domínios
        $script .= "# Adicionar domínios de bancos\n";
        $script .= "/ip hotspot walled-garden\n";
        
        foreach ($this->getBankDomains() as $domain) {
            // Escapar caracteres especiais
            $escapedDomain = str_replace('*', '\\*', $domain);
            $script .= "add dst-host=\"{$domain}\" server={$hotspotServer} comment=\"AUTO-BANK\"\n";
        }
        
        $script .= "\n# Adicionar ranges de IP\n";
        $script .= "/ip hotspot walled-garden ip\n";
        
        foreach ($this->getBankIpRanges() as $range) {
            $script .= "add action=accept dst-address={$range['range']} server={$hotspotServer} comment=\"AUTO-BANK-{$range['comment']}\"\n";
        }
        
        $script .= "\n# ============================================================\n";
        $script .= "# FIM DO SCRIPT\n";
        $script .= "# ============================================================\n";
        
        return response($script, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="walled-garden-banks.rsc"');
    }
}
