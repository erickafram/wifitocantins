<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabela de mensagens do WhatsApp
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->string('phone', 20);
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('message_id')->nullable(); // ID da mensagem no WhatsApp
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('phone');
            $table->index('user_id');
        });

        // Tabela de configurações do WhatsApp
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Inserir configurações padrão
        DB::table('whatsapp_settings')->insert([
            [
                'key' => 'is_connected',
                'value' => 'false',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'connected_phone',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_send_enabled',
                'value' => 'true',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'pending_minutes',
                'value' => '15',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'message_template',
                'value' => "Olá! 👋\n\nVocê ainda não efetuou seu pagamento.\n\nPara navegar durante sua viagem, pague apenas *R$ 5,99* e tenha internet à vontade! 🚀\n\n📱 Acesse: http://10.5.50.1/login\n\nWiFi Tocantins - Internet na sua viagem!",
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'last_qr_code',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'connection_status',
                'value' => 'disconnected',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_settings');
    }
};
