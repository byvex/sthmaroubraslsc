<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GlobalRelay
{
    private static $instance;
    
    private $mailer;
    private $from;
    private $to;
    private $header;
    private $rcptTo;
    private $subjectPrefix;

    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct()
    {
        $settings = Setting::where('key', 'global-relay-settings')->first();
        $settings = !empty($settings['value'])
            ? @json_decode($settings['value'], true) : [];
        $data = [
            'host' => $settings['host'] ?? '',
            'port' => $settings['port'] ?? '',
            'user' => $settings['user'] ?? '',
            'pass' => $settings['pass'] ?? '',
            'rcpt_to' => $settings['rcpt_to'] ?? '',
            'header_name' => $settings['header_name'] ?? '',
            'header_value' => $settings['header_value'] ?? '',
            'from' => $settings['from'] ?? '',
            'to' => $settings['to'] ?? '',
            'tls' => !empty($settings['tls']),
            'message_id' => $settings['message_id'] ?? '',
            'subject_prefix' => $settings['subject_prefix'] ?? '',
        ];

        $username = rawurlencode($data['user']);
        $password = rawurlencode($data['pass']);
        $host = $data['host'];
        $port = intval($data['port']);
        $this->subjectPrefix = $data['subject_prefix'];
        $this->rcptTo = $data['rcpt_to'];
        $this->from = $data['from'];
        $this->to = $data['to'];
        $this->header = [
            'name' => $data['header_name'],
            'value' => $data['header_value'],
        ];
        
        $smtp = 'smtp://%s:%s@%s:%d';
        if (!$data['tls']) {
            $smtp .= '?verify_peer=0&verify_peer_name=0&allow_self_signed=1';
        }

        // disable TLS verification
        $dsn = sprintf($smtp, $username, $password, $host, $port);
        $transport = Transport::fromDsn($dsn);

        $this->mailer = new Mailer($transport);
    }

    public function send(array $data)
    {
        Log::info('Global Relay send data:', $data);
        $sms = $data['sms'] ?? null; // collection object
        $contact = $data['contact'] ?? null; // collection object
        $sender = $data['sender'] ?? null; // collection object
        
        if (empty($sms)) {
            return ['message'=> 'SMS not found'];
        }
        if (empty($sender)) {
            $sender = $sms->sender;
        }
        
        $id = $sms->id;
        $folder = $sms->folder;
        $direction = $folder == 'outbox' ? 'sent' : 'received';
    
        $to = $sms->to;
        $from = $sms->from;

        $to = preg_replace('/\D/', '', $to);
        $to_email = "$to@unknown.com";
        $to_company = '';
        $to_recipient = $sms->recipient;

        $from = preg_replace('/\D/', '', $from);
        $from_email = "$from@sublimex.com.au";
        $from_name = '';

        $subject = '2 users, 1 message';

        $tz = new \DateTimeZone('Australia/Sydney');
        $tz_utc = new \DateTimeZone('UTC');
        
        if (!empty($contact)) {
            if (!empty($contact->email)) {
                $to_email = $contact->email;
            }
            if (!empty($contact->company)) {
                $to_company = $contact->company;
            }
        }
        if (!empty($sender)) {
            if (!empty($sender->email)) {
                $from_email = $sender->email;
            }
            if (!empty($sender->name)) {
                $from_name = $sender->name;
            }
        }

        $send_at = null;
        $send_at_string = '';
        if ( !empty($sms->send_at) ) {
            $send_at = $sms->send_at->setTimezone($tz);
            $send_at_string = $send_at->format('d/m/Y h:i A');
        }

        $delivered_at = null;
        $delivered_at_string = '';
        if ( !empty($sms->delivered_at) ) {
            if (is_string($sms->delivered_at)) {
                $deliver_at_obj = new \DateTime($sms->delivered_at, $tz_utc);
                $delivered_at = $deliver_at_obj->setTimezone($tz);
                $delivered_at_string = $delivered_at->format('d/m/Y h:i A');
            }
        }

        $html = 'Message ' . $direction . PHP_EOL;
        $html .= 'ID : ' . $id . PHP_EOL;
        $html .= 'Folder : ' . $folder . PHP_EOL;
        $html .= 'Delivered at : ' . $delivered_at_string . PHP_EOL;
        $html .= 'To : ' . $sms->to . PHP_EOL;
        $html .= 'To email : ' . $to_email . PHP_EOL;
        $html .= 'Name : ' . $sms->name . PHP_EOL;
        $html .= 'Country : ' . $sms->countrycode . PHP_EOL;
        $html .= 'Company : ' . $to_company . PHP_EOL;
        $html .= 'From : ' . $sms->from . PHP_EOL;
        $html .= 'Group : ' . $to_recipient . PHP_EOL;
        $html .= 'Status : ' . $sms->status . PHP_EOL;
        $html .= 'Message : ' . $sms->message . PHP_EOL;
        $html .= 'Part : ' . $sms->part . PHP_EOL;
        $html .= 'User Id : ' . $sms->user_id . PHP_EOL;
        $html .= 'User Email : ' . $from_email . PHP_EOL;
        $html .= 'User Name : ' . $from_name . PHP_EOL;
        $html .= 'Send at : ' . $send_at_string . PHP_EOL;

        $rcptTo = $this->rcptTo;
        $subjectPrefix = $this->subjectPrefix;
        $subject = trim("$subjectPrefix $subject");

        $email = (new Email())->from($from_email)->to($to_email)->subject($subject)->html($html);

        if (empty($id)) {
            $id = Str::uuid()->toString();
        }
        $messageId = "$id@sublimex.com.au";

        if (!empty($send_at)) {
            $email->getHeaders()->addDateHeader('Date', $send_at);
        }
        $email->getHeaders()->addTextHeader('Rcpt To', $rcptTo);
        $email->getHeaders()->addTextHeader('Mail From', 'bgcg_sms@sublimex.com.au');
        $email->getHeaders()->addTextHeader('Group', $to_recipient);
        $email->getHeaders()->addIdHeader('Message-ID', $messageId);

        // Add GlobalRelay required headers
        $header = $this->header;
        if (!empty($header['name'])) {
            $email->getHeaders()->addTextHeader(
                $header['name'], $header['value']
            );
        }
        
        // save .eml
        $emlContent = $email->toString();
        $todayFolder = date('Y/m');
        $filename = 'email-' . $id . '.eml';
        $filepath = "eml-files/$todayFolder/$filename";
        Storage::disk('public')->put($filepath, $emlContent);

        // send
        $rawMessage = new RawMessage($emlContent);
        $envelope = new Envelope(new Address($from_email), [new Address($rcptTo)]);
        $response = $this->mailer->send($rawMessage, $envelope);
        // Storage::disk('public')->delete($filepath); // uncomment after test

        return $response;
    }
}
