<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->json('embedding_absensi')->nullable()->after('liveness_challenge');
            $table->json('embedding_masuk')->nullable()->after('embedding_absensi');
            $table->json('embedding_keluar')->nullable()->after('embedding_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn([
                'embedding_absensi',
                'embedding_masuk',
                'embedding_keluar',
            ]);
        });
    }
};
