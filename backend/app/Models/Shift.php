<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    // Relation
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class);
    }
}
