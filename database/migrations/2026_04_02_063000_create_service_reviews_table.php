<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('whatsapp_message_id')->nullable()->constrained('whatsapp_messages')->nullOnDelete();
            $table->string('token')->unique();
            $table->string('phone', 20)->nullable();
            $table->date('batch_date');
            $table->timestamp('registration_at')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->enum('whatsapp_status', ['pending', 'sent', 'failed', 'skipped'])->default('pending');
            $table->text('whatsapp_error_message')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['batch_date', 'user_id'], 'service_reviews_batch_user_unique');
            $table->index(['whatsapp_status', 'batch_date'], 'service_reviews_status_batch_idx');
            $table->index(['rating', 'submitted_at'], 'service_reviews_rating_submitted_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['registered_at', 'phone'], 'users_registered_phone_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_registered_phone_idx');
        });

        Schema::dropIfExists('service_reviews');
    }
};