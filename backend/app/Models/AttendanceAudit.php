<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceAudit extends Model
{
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
