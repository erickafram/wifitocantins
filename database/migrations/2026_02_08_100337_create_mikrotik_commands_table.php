<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_commands', function (Blueprint $table) {
            $table->id();
            $table->string('command_type'); // 'liberate', 'block', 'disconnect'
            $table->string('mac_address', 17);
            $table->string('status')->default('pending'); // pending, executed, failed
            $table->text('response')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('mac_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_commands');
    }
};
