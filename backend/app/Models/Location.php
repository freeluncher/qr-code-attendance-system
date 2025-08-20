<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'address',
    ];

    // Relation
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function qrCode()
    {
        return $this->hasMany(QrCode::class);
    }
}
