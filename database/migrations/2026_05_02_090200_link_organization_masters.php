<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('units', 'department_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->foreignId('department_id')->nullable()->after('id')->constrained('departments')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('employee_details', 'department_id')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->foreignId('department_id')->nullable()->after('unit_id')->constrained('departments')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('employee_details', 'position_id')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->foreignId('position_id')->nullable()->after('department_id')->constrained('positions')->nullOnDelete();
            });
        }

        $departmentIds = [];
        $details = DB::table('employee_details')
            ->select('id', 'unit_id', 'departemen', 'jabatan')
            ->orderBy('id')
            ->get();

        foreach ($details as $detail) {
            $departmentName = trim((string) $detail->departemen);
            if ($departmentName === '') {
                continue;
            }

            if (!isset($departmentIds[$departmentName])) {
                $existingDepartmentId = DB::table('departments')->where('nama_departemen', $departmentName)->value('id');
                $departmentIds[$departmentName] = $existingDepartmentId ?: DB::table('departments')->insertGetId([
                    'nama_departemen' => $departmentName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $departmentId = $departmentIds[$departmentName];

            if ($detail->unit_id) {
                $unitDepartmentId = DB::table('units')->where('id', $detail->unit_id)->value('department_id');
                if (!$unitDepartmentId) {
                    DB::table('units')->where('id', $detail->unit_id)->update(['department_id' => $departmentId]);
                }
            }

            $positionId = null;
            $positionName = trim((string) $detail->jabatan);
            if ($positionName !== '') {
                $positionId = DB::table('positions')
                    ->where('department_id', $departmentId)
                    ->where('nama_jabatan', $positionName)
                    ->value('id');

                if (!$positionId) {
                    $positionId = DB::table('positions')->insertGetId([
                        'department_id' => $departmentId,
                        'nama_jabatan' => $positionName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('employee_details')->where('id', $detail->id)->update([
                'department_id' => $departmentId,
                'position_id' => $positionId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            if (Schema::hasColumn('employee_details', 'position_id')) {
                $table->dropConstrainedForeignId('position_id');
            }
            if (Schema::hasColumn('employee_details', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }
        });

        if (Schema::hasColumn('units', 'department_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropConstrainedForeignId('department_id');
            });
        }
    }
};
