<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->string('last_public_ip', 45)->nullable()->after('is_active');
            $table->timestamp('last_sync_at')->nullable()->after('last_public_ip');
            $table->string('last_city', 100)->nullable()->after('last_sync_at');
            $table->string('last_state', 50)->nullable()->after('last_city');
            $table->decimal('last_lat', 10, 7)->nullable()->after('last_state');
            $table->decimal('last_lng', 10, 7)->nullable()->after('last_lat');
        });
    }

    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropColumn(['last_public_ip', 'last_sync_at', 'last_city', 'last_state', 'last_lat', 'last_lng']);
        });
    }
};
