<?php

namespace App\Console\Commands;

use App\Services\SantanderPixService;
use Illuminate\Console\Command;

class SantanderPixSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'santander:pix 
                            {action : Ação a executar: test, webhook-config, webhook-status, webhook-delete}
                            {--url= : URL do webhook (para ação webhook-config)}
                            {--chave= : Chave PIX específica (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerenciar integração Santander PIX (teste de conexão e configuração de webhook)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $service = new SantanderPixService();

        $this->info("🏦 Santander PIX - {$action}");
        $this->newLine();

        switch ($action) {
            case 'test':
                $this->testConnection($service);
                break;

            case 'webhook-config':
                $this->configureWebhook($service);
                break;

            case 'webhook-status':
                $this->getWebhookStatus($service);
                break;

            case 'webhook-delete':
                $this->deleteWebhook($service);
                break;

            default:
                $this->error("❌ Ação inválida: {$action}");
                $this->info('Ações disponíveis: test, webhook-config, webhook-status, webhook-delete');
                return 1;
        }

        return 0;
    }

    /**
     * Testar conectividade com Santander
     */
    private function testConnection(SantanderPixService $service)
    {
        $this->info('🧪 Testando conexão com Santander PIX...');
        $this->newLine();

        $result = $service->testConnection();

        if ($result['success']) {
            $this->info('✅ Conexão estabelecida com sucesso!');
            $this->newLine();

            $this->table(
                ['Verificação', 'Status'],
                [
                    ['Ambiente', $result['checks']['environment']],
                    ['Base URL', $result['checks']['base_url']],
                    ['Client ID', $result['checks']['client_id'] ? '✅ Configurado' : '❌ Ausente'],
                    ['Client Secret', $result['checks']['client_secret'] ? '✅ Configurado' : '❌ Ausente'],
                    ['Chave PIX', $result['checks']['pix_key'] ? '✅ Configurado' : '❌ Ausente'],
                    ['Certificado', $result['checks']['certificate_exists'] ? '✅ Encontrado' : '❌ Não encontrado'],
                    ['Token OAuth', $result['checks']['token_obtained'] ? '✅ Obtido' : '❌ Falhou'],
                ]
            );
        } else {
            $this->error('❌ Erro na conexão:');
            $this->error($result['message']);
            
            if (isset($result['checks'])) {
                $this->newLine();
                $this->warn('Detalhes das verificações:');
                foreach ($result['checks'] as $check => $status) {
                    $icon = $status ? '✅' : '❌';
                    $this->line("  {$icon} {$check}: " . ($status ? 'OK' : 'FALHOU'));
                }
            }
        }
    }

    /**
     * Configurar webhook
     */
    private function configureWebhook(SantanderPixService $service)
    {
        $url = $this->option('url');
        $chave = $this->option('chave');

        if (!$url) {
            $url = $this->ask('📝 URL do webhook (ex: https://seudominio.com.br/api/payment/webhook/santander)');
        }

        if (!$url) {
            $this->error('❌ URL do webhook é obrigatória');
            return;
        }

        $this->info("🔔 Configurando webhook...");
        $this->info("URL: {$url}");
        if ($chave) {
            $this->info("Chave: {$chave}");
        }
        $this->newLine();

        $this->warn('⚠️  IMPORTANTE:');
        $this->warn('   1. Sua URL deve aceitar requisições GET (para validação)');
        $this->warn('   2. Sua URL deve aceitar requisições POST (para notificações)');
        $this->warn('   3. A URL deve estar categorizada na CISCO: https://www.talosintelligence.com/');
        $this->newLine();

        if (!$this->confirm('Deseja continuar?', true)) {
            $this->info('Operação cancelada');
            return;
        }

        $result = $service->configureWebhook($url, $chave);

        if ($result['success']) {
            $this->info('✅ Webhook configurado com sucesso!');
            $this->newLine();
            $this->info('Dados do webhook:');
            $this->line(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $this->error('❌ Erro ao configurar webhook:');
            $this->error($result['message']);
        }
    }

    /**
     * Consultar status do webhook
     */
    private function getWebhookStatus(SantanderPixService $service)
    {
        $chave = $this->option('chave');

        $this->info('🔍 Consultando webhook configurado...');
        if ($chave) {
            $this->info("Chave: {$chave}");
        }
        $this->newLine();

        $result = $service->getWebhookConfig($chave);

        if ($result['success']) {
            $this->info('✅ Webhook encontrado!');
            $this->newLine();
            $this->line(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $this->error('❌ Erro ao consultar webhook:');
            $this->error($result['message']);
        }
    }

    /**
     * Deletar webhook
     */
    private function deleteWebhook(SantanderPixService $service)
    {
        $chave = $this->option('chave');

        $this->warn('⚠️  Você está prestes a DELETAR o webhook configurado!');
        if ($chave) {
            $this->warn("Chave: {$chave}");
        }
        $this->newLine();

        if (!$this->confirm('Tem certeza que deseja continuar?', false)) {
            $this->info('Operação cancelada');
            return;
        }

        $this->info('🗑️  Deletando webhook...');

        $result = $service->deleteWebhook($chave);

        if ($result['success']) {
            $this->info('✅ Webhook deletado com sucesso!');
        } else {
            $this->error('❌ Erro ao deletar webhook:');
            $this->error($result['message']);
        }
    }
} 