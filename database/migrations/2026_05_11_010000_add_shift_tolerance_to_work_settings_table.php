<?php

use App\Models\WorkSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('work_settings', 'checkin_early_minutes')) {
                $table->unsignedSmallInteger('checkin_early_minutes')
                    ->default(WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES)
                    ->after('radius_meters');
            }

            if (!Schema::hasColumn('work_settings', 'checkout_late_minutes')) {
                $table->unsignedSmallInteger('checkout_late_minutes')
                    ->default(WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES)
                    ->after('checkin_early_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_settings', function (Blueprint $table) {
            if (Schema::hasColumn('work_settings', 'checkout_late_minutes')) {
                $table->dropColumn('checkout_late_minutes');
            }

            if (Schema::hasColumn('work_settings', 'checkin_early_minutes')) {
                $table->dropColumn('checkin_early_minutes');
            }
        });
    }
};
