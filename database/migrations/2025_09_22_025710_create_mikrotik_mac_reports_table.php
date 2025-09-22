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
        Schema::create('mikrotik_mac_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 15)->index();
            $table->string('mac_address', 17)->index();
            $table->string('transaction_id')->nullable();
            $table->string('mikrotik_ip', 15)->nullable();
            $table->timestamp('reported_at');
            $table->timestamps();
            
            // Índices únicos para evitar duplicatas
            $table->unique(['ip_address', 'mac_address']);
            
            // TTL - dados expiram após 1 hora
            $table->index('reported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_mac_reports');
    }
};
