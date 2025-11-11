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
        Schema::table('vouchers', function (Blueprint $table) {
            // Remove campos antigos de desconto
            $table->dropColumn(['discount', 'discount_percent', 'max_uses', 'used_count']);
            
            // Adiciona novos campos para vouchers de motoristas
            $table->string('driver_name')->after('code'); // Nome do motorista
            $table->string('driver_document')->nullable()->after('driver_name'); // CPF/CNH
            $table->integer('daily_hours')->default(24)->after('driver_document'); // Horas diárias permitidas
            $table->integer('daily_hours_used')->default(0)->after('daily_hours'); // Horas usadas hoje
            $table->date('last_used_date')->nullable()->after('daily_hours_used'); // Última data de uso
            $table->timestamp('activated_at')->nullable()->after('expires_at'); // Data de ativação
            $table->enum('voucher_type', ['unlimited', 'limited'])->default('limited')->after('is_active'); // Tipo de voucher
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Restaura campos antigos
            $table->decimal('discount', 8, 2)->nullable();
            $table->integer('discount_percent')->nullable();
            $table->integer('max_uses')->default(1);
            $table->integer('used_count')->default(0);
            
            // Remove novos campos
            $table->dropColumn([
                'driver_name',
                'driver_document',
                'daily_hours',
                'daily_hours_used',
                'last_used_date',
                'activated_at',
                'voucher_type'
            ]);
        });
    }
};
