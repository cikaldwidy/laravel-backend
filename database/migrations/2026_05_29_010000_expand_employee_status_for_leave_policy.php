<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_details') || !Schema::hasColumn('employee_details', 'status_kerja')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employee_details MODIFY status_kerja ENUM('tetap', 'kontrak', 'capeg', 'training', 'magang') NOT NULL");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('employee_details') || !Schema::hasColumn('employee_details', 'status_kerja')) {
            return;
        }

        DB::table('employee_details')
            ->whereIn('status_kerja', ['capeg', 'training'])
            ->update(['status_kerja' => 'magang']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employee_details MODIFY status_kerja ENUM('tetap', 'kontrak', 'magang') NOT NULL");
        }
    }
};
