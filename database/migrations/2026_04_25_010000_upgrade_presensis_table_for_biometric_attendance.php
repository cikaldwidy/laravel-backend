<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->string('foto_masuk')->nullable()->after('foto');
            $table->string('foto_keluar')->nullable()->after('foto_masuk');
            $table->decimal('latitude_masuk', 10, 7)->nullable()->after('foto_keluar');
            $table->decimal('longitude_masuk', 10, 7)->nullable()->after('latitude_masuk');
            $table->decimal('latitude_keluar', 10, 7)->nullable()->after('longitude_masuk');
            $table->decimal('longitude_keluar', 10, 7)->nullable()->after('latitude_keluar');
            $table->decimal('jarak_masuk', 10, 2)->nullable()->after('longitude_keluar');
            $table->decimal('jarak_keluar', 10, 2)->nullable()->after('jarak_masuk');
            $table->decimal('face_distance_masuk', 8, 6)->nullable()->after('jarak_keluar');
            $table->decimal('face_distance_keluar', 8, 6)->nullable()->after('face_distance_masuk');
            $table->json('liveness_challenge')->nullable()->after('face_distance_keluar');
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn([
                'foto_masuk',
                'foto_keluar',
                'latitude_masuk',
                'longitude_masuk',
                'latitude_keluar',
                'longitude_keluar',
                'jarak_masuk',
                'jarak_keluar',
                'face_distance_masuk',
                'face_distance_keluar',
                'liveness_challenge',
            ]);
        });
    }
};
