<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_settings', function (Blueprint $table) {
            $table->decimal('office_latitude', 10, 7)->nullable()->after('batas_telat');
            $table->decimal('office_longitude', 10, 7)->nullable()->after('office_latitude');
            $table->unsignedInteger('radius_meters')->default(100)->after('office_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('work_settings', function (Blueprint $table) {
            $table->dropColumn([
                'office_latitude',
                'office_longitude',
                'radius_meters',
            ]);
        });
    }
};
