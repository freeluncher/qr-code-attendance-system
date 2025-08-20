<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'location_id',
        'shift_id',
        'qr_code_id',
        'scanned_at',
        'check_out_time',
        'status',
        'late_category',
        'photo_url',
        'latitude',
        'longitude',
        'check_out_latitude',
        'check_out_longitude',
        'distance',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // Relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function qrCode()
    {
        return $this->belongsTo(QRCode::class);
    }
}
