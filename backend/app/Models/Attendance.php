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
        'status',
        'late_category',
        'photo_url',
        'latitude',
        'longitude',
        'distance',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
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
