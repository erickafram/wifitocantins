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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address', 17)->unique()->index();
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, laptop, etc
            $table->string('user_agent')->nullable();
            $table->timestamp('first_seen');
            $table->timestamp('last_seen');
            $table->integer('total_connections')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
