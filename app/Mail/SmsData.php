<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Sms;
use App\Models\Setting;

class SmsData extends Mailable
{
    use Queueable, SerializesModels;

    public Sms $sms;
    public $data = [];

    /**
     * Create a new message instance.
     */
    public function __construct(Sms $sms, $data = [])
    {
        $this->sms = $sms;
        $this->data = $data;

        $settings = Setting::where('key', 'sms-relay-settings')->first();
        $settings = !empty($settings['value']) ? @json_decode($settings['value'], true) : [];
        $emails = (!empty($settings['emails']) && is_array($settings['emails'])) ? $settings['emails'] : [];

        if(!empty($emails)) {
            $this->to($emails);
        } else {
            $this->to(['simon@sublimex.com.au']);
        }
        // $this->bcc(['info@byvex.com']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sms Delivery Callback',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sms-data',
            with: [
                'sms' => $this->sms,
                'data' => $this->data,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
