<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSwap extends Model
{
    protected $fillable = [
        'requester_id',
        'target_user_id',
        'shift_id',
        'target_shift_id',
        'status',
        'note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class, 'shift_id');
    }

    public function targetShift(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class, 'target_shift_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
