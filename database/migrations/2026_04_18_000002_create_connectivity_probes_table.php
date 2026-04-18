<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('connectivity_probes', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique(); // random 32 chars
            $table->foreignId('conversation_id')->nullable()->constrained('chat_conversations')->nullOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // quem executou (se logado)

            // Contexto capturado no momento da criação
            $table->string('target_mac', 32)->nullable();
            $table->string('target_phone', 32)->nullable();

            // Status: pending (criado) / completed (resultados recebidos) / expired (>30min)
            $table->string('status', 20)->default('pending');

            // Resultados dos 5 testes (JSON)
            $table->json('results')->nullable();

            // Contexto do cliente no momento do teste
            $table->string('client_ip', 45)->nullable();
            $table->string('client_mac', 32)->nullable();
            $table->text('client_user_agent')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connectivity_probes');
    }
};
