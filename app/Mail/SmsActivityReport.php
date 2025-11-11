<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class SmsActivityReport extends Mailable
{
    use Queueable, SerializesModels;

    public $csvFilePath;
    public $frequency;
    public $emlFilePath;

    /**
     * Create a new message instance.
     */
    public function __construct($csvFilePath, $frequency, $emlFilePath = '')
    {
        if (!empty($csvFilePath)) {
            $this->csvFilePath = storage_path('app/' . $csvFilePath);
        } else {
            $this->csvFilePath = '';
        }
        if(!empty($emlFilePath)) {
            $this->emlFilePath = storage_path('app/'. $emlFilePath);
        }
        $this->frequency = $frequency;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $tz = new \DateTimeZone('Australia/Sydney');
        $tz_utc = new \DateTimeZone('UTC');
        $date = new \DateTime(date('Y-m-d H:i:s'), $tz_utc);
        $date->setTimezone($tz);
        $dateString = $date->format('j F Y, h:i:s a e');
        return new Envelope(
            subject: 'SMS activity '. $this->frequency .' report '. $dateString,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sms-activity-report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $files = [];
        if (!empty($this->csvFilePath)) {
            $files[] = Attachment::fromPath($this->csvFilePath);
        }
        if(!empty($this->emlFilePath)) {
            $files[] = Attachment::fromPath($this->emlFilePath);
        }
        return $files;
    }
}
