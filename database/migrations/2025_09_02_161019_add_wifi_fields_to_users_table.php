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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mac_address', 17)->nullable()->unique()->index();
            $table->string('ip_address', 15)->nullable();
            $table->string('device_name')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('data_used')->default(0); // em MB
            $table->string('status')->default('offline'); // offline, connected, expired
            
            // Tornar campos originais opcionais para usuários WiFi
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mac_address',
                'ip_address', 
                'device_name',
                'connected_at',
                'expires_at',
                'data_used',
                'status'
            ]);
            
            // Restaurar campos obrigatórios
            $table->string('name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
