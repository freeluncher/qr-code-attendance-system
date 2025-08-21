<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'location_id',
        'active_days',
        'capacity',
        'status',
        'description',
    ];

    protected $casts = [
        'active_days' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Relations
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class);
    }

    // Scopes
    public function scopeForLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId)->orWhereNull('location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where(function($q) use ($dayOfWeek) {
            $q->whereNull('active_days')
              ->orWhereJsonContains('active_days', $dayOfWeek);
        });
    }

    // Helpers
    public function isAvailableOnDay($dayOfWeek)
    {
        if (empty($this->active_days)) {
            return true; // Available all days if not specified
        }
        return in_array($dayOfWeek, $this->active_days);
    }

    public function getFormattedTimeAttribute()
    {
        $startTime = $this->start_time->format('H:i');
        $endTime = $this->end_time->format('H:i');

        if ($this->isOvernightShift()) {
            return $startTime . ' - ' . $endTime . ' (+1 day)';
        }

        return $startTime . ' - ' . $endTime;
    }

    public function isOvernightShift()
    {
        return $this->start_time->format('H:i') > $this->end_time->format('H:i');
    }

    public function getDurationInHours()
    {
        $start = $this->start_time;
        $end = $this->end_time;

        if ($this->isOvernightShift()) {
            // Add 24 hours for overnight shift calculation
            $end = $end->addDay();
        }

        return $start->diffInHours($end);
    }
}
