<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    protected $fillable = [
        'location_id',
        'week_start_date',
        'week_end_date',
        'report_data',
        'file_path',
        'email_sent_at',
        'created_by'
    ];

    protected $casts = [
        'week_start_date' => 'datetime',
        'week_end_date' => 'datetime',
        'report_data' => 'array',
        'email_sent_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the week identifier (YYYY-WW format)
     */
    public function getWeekIdentifierAttribute(): string
    {
        return $this->week_start_date->format('Y-\WW');
    }

    /**
     * Check if report is for current week
     */
    public function isCurrentWeek(): bool
    {
        $now = now();
        return $this->week_start_date->isSameWeek($now);
    }

    /**
     * Get formatted date range
     */
    public function getDateRangeAttribute(): string
    {
        return $this->week_start_date->format('d M Y') . ' - ' . $this->week_end_date->format('d M Y');
    }
}
