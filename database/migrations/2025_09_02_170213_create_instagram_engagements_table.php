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
        Schema::create('instagram_engagements', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address', 17)->index();
            $table->string('ip_address', 15);
            $table->integer('time_spent_seconds'); // Tempo gasto no Instagram
            $table->integer('verification_score'); // Pontuação das perguntas (0-3)
            $table->boolean('claimed_successfully')->default(false);
            $table->timestamp('instagram_visit_start');
            $table->timestamp('returned_at')->nullable();
            $table->json('answers')->nullable(); // Respostas das perguntas
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_engagements');
    }
};
