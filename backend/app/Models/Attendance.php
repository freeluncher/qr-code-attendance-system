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
        'face_photo_url',
        'face_landmarks',
        'face_descriptor',
        'face_quality_status',
        'face_validation_message',
        'latitude',
        'longitude',
        'check_out_latitude',
        'check_out_longitude',
        'distance',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'check_out_time' => 'datetime',
        'face_landmarks' => 'array',
        'face_descriptor' => 'array',
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
