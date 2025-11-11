<?php

// use Illuminate\Foundation\Inspiring;
// use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('send-sms')->everyThirtySeconds()->withoutOverlapping(1);
Schedule::command('send-activity-report')->timezone('Australia/Sydney')->dailyAt('23:59');


// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

// add this command to cron jobs
// cd /home/sublimesms && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1

