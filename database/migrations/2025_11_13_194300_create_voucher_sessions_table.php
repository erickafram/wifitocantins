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
        Schema::create('voucher_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id');
            $table->unsignedBigInteger('user_id');
            $table->string('mac_address', 17);
            $table->string('ip_address', 15);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('hours_granted')->default(0);
            $table->integer('minutes_used')->default(0);
            $table->enum('status', ['active', 'expired', 'disconnected'])->default('active');
            $table->text('mikrotik_response')->nullable();
            $table->timestamps();

            $table->index(['voucher_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('mac_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_sessions');
    }
};
