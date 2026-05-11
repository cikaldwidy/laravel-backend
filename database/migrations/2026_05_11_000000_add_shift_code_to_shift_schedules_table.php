<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('shift_schedules', 'shift_code')) {
                $table->string('shift_code', 1)->nullable()->after('status')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shift_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('shift_schedules', 'shift_code')) {
                $table->dropColumn('shift_code');
            }
        });
    }
};
