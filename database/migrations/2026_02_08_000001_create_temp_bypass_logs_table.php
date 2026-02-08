<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temp_bypass_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('mac_address', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->integer('bypass_number')->default(1)->comment('Qual bypass nessa hora (1 ou 2)');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('was_denied')->default(false)->comment('True se foi negado por anti-abuso');
            $table->string('deny_reason')->nullable()->comment('Motivo da negação');
            $table->timestamps();

            $table->index('user_id');
            $table->index('mac_address');
            $table->index('phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temp_bypass_logs');
    }
};
