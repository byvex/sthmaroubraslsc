<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactGroupController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SenderNumberController;
use App\Http\Controllers\WebPushController;
use Illuminate\Support\Facades\Route;


// Route::get('/welcome', function () {
    // return view('welcome');
// });

Route::post('/web-push/subscribe', [WebPushController::class, 'subscribe']);

Route::middleware('guest')->group(function () {
    Route::get('/login/redirect/google', [
        SocialLoginController::class, 'googleRedirect'
    ])->name('login.google');
    Route::get('/login/callback/google', [
        SocialLoginController::class, 'googleCallback'
    ]);
});

Route::middleware([
    'auth:sanctum', config('jetstream.auth_session'), 'verified',
])->group(function () {

    Route::get('/', function () {
        return redirect('/dashboard');
        // return view('pages.index');
    })->name('home');

    Route::get('/dashboard', [
        PageController::class, 'dashboard'
    ])->name('dashboard');

    Route::resource('contact-groups', ContactGroupController::class, [
        'name' => 'contact-groups'
    ]);
    Route::post('contact-groups/delete', [
        ContactGroupController::class, 'delete'
    ])->name('contact-groups.delete');
    Route::get('contact-groups/export/download', [
        ContactGroupController::class, 'exportDownload'
    ])->name('contact-groups.export.download');


    Route::resource('contacts', ContactController::class, [
        'name' => 'contacts'
    ]);
    Route::post('contacts/delete', [
        ContactController::class, 'delete'
    ])->name('contacts.delete');
    Route::get('contacts/export/download', [
        ContactController::class, 'exportDownload'
    ])->name('contacts.export.download');
    Route::post('contacts/import/upload', [
        ContactController::class, 'importUpload'
    ])->name('contacts.import.upload');


    Route::resource('templates', TemplateController::class, [
        'name' => 'templates'
    ]);
    Route::post('templates/delete', [
        TemplateController::class, 'delete'
    ])->name('templates.delete');

    Route::resource('dashboard/users', UserController::class, [
        'name' => 'users',
    ]);

    Route::post('/user/profile/update', [
        UserController::class, 'setProfile'
    ])->name('users.profile.update');

    Route::resource('sms', SmsController::class, [
        'name' => 'sms'
    ]);

    Route::resource('dashboard/sender-numbers', SenderNumberController::class, [
        'name' => 'sender-numbers'
    ]);


    Route::post('dashboard/activity-report-settings', [
        UserController::class, 'activityReportSettings'
    ])->name('activity-report-settings');

    Route::post('dashboard/sms-relay-settings', [
        UserController::class, 'smsRelaySettings'
    ])->name('sms-relay-settings');
    
    Route::post('dashboard/global-relay-settings', [
        UserController::class, 'globalRelaySettings'
    ])->name('global-relay-settings');
    Route::post('/api/v1/global-relay-test', [
        PageController::class, 'globalRelayTest'
    ])->name('global-relay-settings.test');

    Route::post('dashboard/send-sms-report', [
        SmsController::class, 'send_report',
    ])->name('sms.send-report');

    Route::post('dashboard/send-sms-report-eml', [
        SmsController::class, 'send_report_eml',
    ])->name('sms.send-report-eml');

});

Route::match(['get', 'post'], 'dashboard/mimic-login', [UserController::class, 'mimic_login'])->name('mimic-login');

// Route::impersonate();

