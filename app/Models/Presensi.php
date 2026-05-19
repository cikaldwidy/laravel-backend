<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'foto',
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
        'embedding_absensi',
        'embedding_masuk',
        'embedding_keluar',
        'status',
        'status_pulang',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i:s',
        'jam_keluar' => 'datetime:H:i:s',
        'latitude_masuk' => 'float',
        'longitude_masuk' => 'float',
        'latitude_keluar' => 'float',
        'longitude_keluar' => 'float',
        'jarak_masuk' => 'float',
        'jarak_keluar' => 'float',
        'face_distance_masuk' => 'float',
        'face_distance_keluar' => 'float',
        'liveness_challenge' => 'array',
        'embedding_absensi' => 'array',
        'embedding_masuk' => 'array',
        'embedding_keluar' => 'array',
    ];
    public function user() {
        return $this->belongsTo(\App\Models\User::class);
    }
    
}
