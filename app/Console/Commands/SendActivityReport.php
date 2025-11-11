<?php

namespace App\Console\Commands;

use Exception;
use Carbon\Carbon;
use App\Models\Sms;
use App\Models\Setting;
use App\Exports\SmsExport;
use Illuminate\Console\Command;
use App\Mail\SmsActivityReport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class SendActivityReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-activity-report {type=unknown} {file=both}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send activity report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $settings = Setting::where('key', 'activity-report-settings')->first();
        $settings = !empty($settings['value']) ? @json_decode($settings['value'], true) : [];

        $emails = (!empty($settings['emails']) && is_array($settings['emails'])) ? $settings['emails'] : [];
        $times = (!empty($settings['times']) && is_array($settings['times'])) ? $settings['times'] : [];

        $type = $this->argument('type');
        $file = $this->argument('file');

        if($type == 'recent-7-days') {
            if($file == 'eml') {
                $this->sendCsv('recent-7-days', $emails, $file);
            } else {
                $this->sendCsv('recent-7-days', $emails);
            }
        } else {
            if (in_array('daily', $times)) {
                // Send the CSV file daily
                $this->sendCsv('daily', $emails);
            }

            if (in_array('weekly', $times)) {
                // Send the CSV file weekly (e.g., every Monday, or Friday)
                if ($now->isMonday()) {
                    $this->sendCsv('weekly', $emails);
                }
            }

            if (in_array('monthly', $times)) {
                // Send the CSV file monthly (e.g., on the 1st of every month)
                if ($now->day == 1) {
                    $this->sendCsv('monthly', $emails);
                }
            }
        }
    }

    public function sendCsv($frequency = 'daily', $emails = [], $file = 'both')
    {
        try {
            $csvFilePath = '';
            $emlFilePath = '';
            if($file == 'eml') {
                $emlFilePath = $this->generateEml($frequency);
            } else {
                $emlFilePath = $this->generateEml($frequency);
                $csvFilePath = $this->generateCsv($frequency);
            }
            Mail::to($emails)->send(new SmsActivityReport($csvFilePath, $frequency, $emlFilePath));
            Log::info('SMS Excel Report ' . $frequency . ' mail sent');
        } catch (Exception $e) {
            Log::info('SMS Excel Report ' . $frequency . ': ' . $e->getMessage());
        }
    }

    public function generateCsv($frequency = 'daily')
    {
        $this->info($frequency);
        $query = Sms::query()->select([
            'id',
            'message',
            'to',
            'countrycode',
            'from',
            'from_name',
            'name',
            'user_id',
            'recipient',
            'send_at',
            'folder',
            'cost',
            'part',
            'delivered_at',
            'status'
        ])->with('sender:id,email,name');

        if ($frequency == 'monthly') {
            // Get the start of the last month
            $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
            // Get the end of the last month
            $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
            // take between the dates of month
            $query = $query->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
        } elseif ($frequency == 'weekly') {
            // Get the start of the current week (Monday)
            $startOfWeek = Carbon::now()->startOfWeek()->subWeek();
            // Get the end of the last week (Sunday)
            $endOfWeek = Carbon::now()->startOfWeek()->subDay();
            // take between the dates of week
            $query = $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        } elseif($frequency == 'recent-7-days') {
            $query = $query->whereDate('created_at', '>=', Carbon::now()->subDays(7));
        } else {
            $yesterday = Carbon::yesterday();
            $query = $query->whereDate('created_at', $yesterday);
        }
        $query = $query->orderBy('id', 'DESC');

        $filename = 'activity-' . $frequency . '-' . date('Y-m-d-H-i-s') . '.xlsx';
        $filePath = 'cache/' . $filename;
        // return (new SmsExport($query))->download($filename);
        Excel::store(new SmsExport($query), $filePath, 'local');
        return $filePath;
    }

    public function generateEml($frequency = 'daily')
    {
        $this->info($frequency);
        $query = Sms::query()->select([
            'id',
            'message',
            'to',
            'countrycode',
            'from',
            'from_name',
            'name',
            'user_id',
            'recipient',
            'send_at',
            'folder',
            'cost',
            'part',
            'delivered_at',
            'status'
        ])->with('sender:id,email,name');

        if ($frequency == 'monthly') {
            // Get the start of the last month
            $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
            // Get the end of the last month
            $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
            // take between the dates of month
            $query = $query->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
        } elseif ($frequency == 'weekly') {
            // Get the start of the current week (Monday)
            $startOfWeek = Carbon::now()->startOfWeek()->subWeek();
            // Get the end of the last week (Sunday)
            $endOfWeek = Carbon::now()->startOfWeek()->subDay();
            // take between the dates of week
            $query = $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        } elseif($frequency == 'recent-7-days') {
            $query = $query->whereDate('created_at', '>=', Carbon::now()->subDays(7));
        } else {
            $yesterday = Carbon::yesterday();
            $query = $query->whereDate('created_at', $yesterday);
        }
        $query = $query->orderBy('id', 'DESC');

        $items = $query->get();
        $emailBody = View::make('emails.sms-activity-report-eml', ['items'=> $items])->render();
        $headers = "From: no-reply@sublimesms.com.au\r\n";
        $headers .= "To: simon@sublimex.com.au\r\n";
        $headers .= "Subject: Exported Data\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $emlContent = $headers . "\r\n" . $emailBody;
        $filebasename = 'activity-' . $frequency . '-eml-' . date('Y-m-d-H-i-s');
        $filename = $filebasename . '.eml';
        $filenameZip = $filebasename . '.zip';
        $filePath = storage_path('app/cache/' . $filename);
        file_put_contents($filePath, $emlContent);

        $zipPath = storage_path('app/cache/'. $filenameZip);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filePath, $filebasename . '.eml');
            $zip->close();
        }
        return 'cache/' . $filenameZip;
    }

}
