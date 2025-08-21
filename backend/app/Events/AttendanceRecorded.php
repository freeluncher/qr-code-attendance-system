<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $attendance;
    public $type;

    /**
     * Create a new event instance.
     */
    public function __construct(Attendance $attendance, string $type)
    {
        $this->attendance = $attendance;
        $this->type = $type; // 'clock_in', 'clock_out', 'late_attendance', 'no_show'
    }
}
