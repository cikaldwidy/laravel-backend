<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'nama_departemen',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function employeeDetails(): HasMany
    {
        return $this->hasMany(EmployeeDetail::class);
    }
}
