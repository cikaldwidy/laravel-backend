<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    protected $fillable = [
        'judul',
        'isi',
        'tanggal_mulai',
        'tanggal_berakhir',
        'target_type',
        'unit_id',
        'is_published',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
        'is_published' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function dismissedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_dismissals')->withTimestamps();
    }
}
