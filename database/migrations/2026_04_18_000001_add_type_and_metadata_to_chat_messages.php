<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // type: 'text' (default), 'probe_request' (cartão com link), 'probe_result' (resultado)
            $table->string('type', 30)->default('text')->after('message');
            // metadata: token do probe, resultados, etc.
            $table->json('metadata')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['type', 'metadata']);
        });
    }
};
