<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['status', 'expires_at'], 'users_status_expires_idx');
            $table->index(['ip_address', 'connected_at'], 'users_ip_connected_idx');
            $table->index(['created_at', 'status'], 'users_created_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'payments_status_created_idx');
            $table->index(['user_id', 'status', 'created_at'], 'payments_user_status_created_idx');
            $table->index('transaction_id', 'payments_transaction_id_idx');
            $table->index('gateway_payment_id', 'payments_gateway_payment_id_idx');
        });

        Schema::table('wifi_sessions', function (Blueprint $table) {
            $table->index(['session_status', 'started_at'], 'wifi_sessions_status_started_idx');
            $table->index(['user_id', 'started_at'], 'wifi_sessions_user_started_idx');
        });

        Schema::table('mikrotik_mac_reports', function (Blueprint $table) {
            $table->index(['ip_address', 'reported_at'], 'mikrotik_reports_ip_reported_idx');
            $table->index(['mac_address', 'reported_at'], 'mikrotik_reports_mac_reported_idx');
            $table->index(['mikrotik_id', 'reported_at'], 'mikrotik_reports_mid_reported_idx');
        });
    }

    public function down(): void
    {
        Schema::table('mikrotik_mac_reports', function (Blueprint $table) {
            $table->dropIndex('mikrotik_reports_ip_reported_idx');
            $table->dropIndex('mikrotik_reports_mac_reported_idx');
            $table->dropIndex('mikrotik_reports_mid_reported_idx');
        });

        Schema::table('wifi_sessions', function (Blueprint $table) {
            $table->dropIndex('wifi_sessions_status_started_idx');
            $table->dropIndex('wifi_sessions_user_started_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_created_idx');
            $table->dropIndex('payments_user_status_created_idx');
            $table->dropIndex('payments_transaction_id_idx');
            $table->dropIndex('payments_gateway_payment_id_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_expires_idx');
            $table->dropIndex('users_ip_connected_idx');
            $table->dropIndex('users_created_status_idx');
        });
    }
};