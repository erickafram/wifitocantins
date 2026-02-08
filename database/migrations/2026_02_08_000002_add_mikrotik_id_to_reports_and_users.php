<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar mikrotik_id aos reports (identifica qual ônibus reportou o MAC)
        Schema::table('mikrotik_mac_reports', function (Blueprint $table) {
            $table->string('mikrotik_id', 30)->nullable()->after('mikrotik_ip')->index();
            $table->timestamp('last_seen')->nullable()->after('reported_at');
        });

        // Adicionar last_mikrotik_id aos users (em qual ônibus o usuário está)
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_mikrotik_id', 30)->nullable()->after('device_name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('mikrotik_mac_reports', function (Blueprint $table) {
            $table->dropColumn(['mikrotik_id', 'last_seen']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_mikrotik_id');
        });
    }
};
