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
            // Adicionar intervalo entre ativações (em horas)
            $table->decimal('activation_interval_hours', 5, 2)->default(24)->after('daily_hours');
            
            // Mudar daily_hours para decimal para suportar minutos (ex: 2.5 = 2h30min)
            $table->decimal('daily_hours', 5, 2)->default(24)->change();
            $table->decimal('daily_hours_used', 5, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('activation_interval_hours');
            
            // Reverter para integer
            $table->integer('daily_hours')->default(24)->change();
            $table->integer('daily_hours_used')->default(0)->change();
        });
    }
};
