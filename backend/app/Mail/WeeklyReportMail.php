<?php

namespace App\Mail;

use App\Models\WeeklyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class WeeklyReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public WeeklyReport $report;

    /**
     * Create a new message instance.
     */
    public function __construct(WeeklyReport $report)
    {
        $this->report = $report;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Weekly Attendance Report - ' . $this->report->location->name . ' (' . $this->report->date_range . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-report',
            with: [
                'report' => $this->report,
                'reportData' => $this->report->report_data,
                'location' => $this->report->location,
                'weekPeriod' => $this->report->date_range,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->report->file_path && Storage::exists($this->report->file_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->report->file_path)
                ->as(basename($this->report->file_path))
                ->withMime('text/csv');
        }

        return $attachments;
    }
}
