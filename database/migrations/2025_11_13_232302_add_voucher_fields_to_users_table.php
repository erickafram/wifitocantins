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
            // Campos para vouchers de motoristas
            $table->unsignedBigInteger('voucher_id')->nullable()->after('role');
            $table->string('driver_phone', 20)->nullable()->after('voucher_id');
            $table->timestamp('voucher_activated_at')->nullable()->after('driver_phone');
            $table->timestamp('voucher_last_connection')->nullable()->after('voucher_activated_at');
            $table->integer('voucher_daily_minutes_used')->default(0)->after('voucher_last_connection');
            
            // Índices para performance
            $table->index('voucher_id');
            $table->index('driver_phone');
            
            // Foreign key para vouchers
            $table->foreign('voucher_id')
                  ->references('id')
                  ->on('vouchers')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropIndex(['voucher_id']);
            $table->dropIndex(['driver_phone']);
            $table->dropColumn([
                'voucher_id',
                'driver_phone',
                'voucher_activated_at',
                'voucher_last_connection',
                'voucher_daily_minutes_used'
            ]);
        });
    }
};
