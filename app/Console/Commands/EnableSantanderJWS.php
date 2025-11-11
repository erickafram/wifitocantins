<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnableSantanderJWS extends Command
{
    protected $signature = 'santander:enable-jws';
    protected $description = 'Habilita JWS (JSON Web Signature) para Santander PIX';

    public function handle()
    {
        $this->info('🔐 Habilitando JWS para Santander PIX...');
        
        // Verificar se o arquivo .env existe
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            $this->error('Arquivo .env não encontrado!');
            return 1;
        }
        
        // Ler conteúdo do .env
        $envContent = file_get_contents($envPath);
        
        // Verificar se já existe a variável SANTANDER_USE_JWS
        if (strpos($envContent, 'SANTANDER_USE_JWS') !== false) {
            // Atualizar valor existente
            $envContent = preg_replace(
                '/SANTANDER_USE_JWS=.*$/m',
                'SANTANDER_USE_JWS=true',
                $envContent
            );
            $this->info('✅ Variável SANTANDER_USE_JWS atualizada para true');
        } else {
            // Adicionar nova variável após as outras configurações do Santander
            $envContent = preg_replace(
                '/(SANTANDER_CERTIFICATE_PASSWORD=.*$)/m',
                "$1\nSANTANDER_USE_JWS=true",
                $envContent
            );
            $this->info('✅ Variável SANTANDER_USE_JWS adicionada ao .env');
        }
        
        // Salvar arquivo .env
        file_put_contents($envPath, $envContent);
        
        // Limpar cache de configuração
        $this->call('config:clear');
        
        $this->newLine();
        $this->info('✨ JWS habilitado com sucesso!');
        $this->info('');
        $this->info('📋 Próximos passos:');
        $this->info('   1. Verifique se o certificado contém chave privada: php artisan santander:test');
        $this->info('   2. Teste a geração de QR Code PIX');
        
        return 0;
    }
}

