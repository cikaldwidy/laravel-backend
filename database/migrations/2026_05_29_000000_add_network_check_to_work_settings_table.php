<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('work_settings', 'attendance_network_check_enabled')) {
                $table->boolean('attendance_network_check_enabled')
                    ->default(false)
                    ->after('checkout_late_minutes');
            }

            if (!Schema::hasColumn('work_settings', 'attendance_allowed_networks')) {
                $table->text('attendance_allowed_networks')
                    ->nullable()
                    ->after('attendance_network_check_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_settings', function (Blueprint $table) {
            if (Schema::hasColumn('work_settings', 'attendance_allowed_networks')) {
                $table->dropColumn('attendance_allowed_networks');
            }

            if (Schema::hasColumn('work_settings', 'attendance_network_check_enabled')) {
                $table->dropColumn('attendance_network_check_enabled');
            }
        });
    }
};
