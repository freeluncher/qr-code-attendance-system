<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceAudit extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_id',
        'action',
        'description',
    ];

    // Relation
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
