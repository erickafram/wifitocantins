<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('mikrotik_serial', 30)->unique()->comment('Serial number do MikroTik (mid)');
            $table->string('name', 100)->comment('Nome amigável: ex Ônibus 01 - Palmas/Araguaína');
            $table->string('plate', 15)->nullable()->comment('Placa do veículo');
            $table->string('route_description', 255)->nullable()->comment('Rota: ex Palmas → Araguaína');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
