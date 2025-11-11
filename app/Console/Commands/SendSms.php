<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Sms;
use App\Models\SmsJob;
use App\Services\SMSApi;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSms extends Command
{
    protected $smsApi;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-sms {--show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Scheduled SMS';

    /**
     * Execute the console command.
     */
    public function handle(SMSApi $smsApi)
    {
        $show_msg = $this->option('show');
        try {
            $dlr_callback = route('api.sms.callback.dlr');
            $smses = Sms::where('local_status', 'PENDING')->take(250)->get();

            foreach ($smses as $sms) {
                // $is_scheduled if false then keep send_at empty
                $senderNumber = $sms->from;
                if($sms->countrycode == 'SG' && $sms->from == 61480088898) {
                    $senderNumber = 6583184436;
                }
                if($sms->countrycode == 'HK' && $sms->from == 61480088898) {
                    $senderNumber = 'BGC Future';
                }

                $api_res = $smsApi->send_sms([
                    'to' => $sms->to,
                    'message' => $sms->message,
                    'countrycode' => $sms->countrycode,
                    'send_at' => !empty($sms->send_at) ? $sms->send_at : '',
                    'dlr_callback' => $dlr_callback,
                    'from' => $senderNumber,
                ]);
                if (empty($sms->send_at)) {
                    $sms->send_at = date('Y-m-d H:i:s');
                }
                $sms->local_status = 'SENT';
                $sms->sms_id = !empty($api_res['message_id']) ? $api_res['message_id'] : '';
                $sms->part = !empty($api_res['sms']) ? $api_res['sms'] : '';
                $sms->cost = isset($api_res['cost']) ? $api_res['cost'] : '';
                $sms->save();
                Log::info(json_encode($api_res));
            }


            // if ($show_msg) {
            //     $this->info('DELIVERY CALLBACK: ' . $dlr_callback);
            // }
            // Log::info('DELIVERY CALLBACK: ' . $dlr_callback);

            /*
            $job = SmsJob::whereIn('status', ['PENDING', 'TESTING'])->first();
            if (!empty($job)) {

                $message = $job->message;
                $is_scheduled = $job->scheduled;
                $is_teting = ($job->status != 'PENDING') ? true : false;
                $send_at = $job->send_at;
                $user_id = $job->user_id;

                $job->status = 'WORKING';
                $job->save();

                $msg = 'SMS JOB: started ' . $job->id . ' ' . $job->name;
                if ($show_msg) {
                    $this->info($msg);
                } else {
                    Log::info($msg);
                }


                $numbers = $job->numbers;
                $list_uids = $job->list_uids;
                if (!empty($numbers)) {
                    foreach ($numbers as $number) {
                        // send sms to number changing placeholders
                        $cnt = Contact::where('id', $number)->first();
                        if (!empty($cnt) && !empty($cnt->phone)) {
                            $contact_firstname = $cnt->name;
                            $contact_lastname = $cnt->lastname;
                            $contact_phone = $cnt->phone;
                            $contact_company = $cnt->company;
                            $contact_country = $cnt->country;
                            $to = $contact_phone;

                            $formatted_msg = $message;
                            $formatted_msg = str_replace('[Firstname]', $contact_firstname, $formatted_msg);
                            $formatted_msg = str_replace('[Lastname]', $contact_lastname, $formatted_msg);
                            $formatted_msg = str_replace('[Mobile]', $contact_phone, $formatted_msg);
                            $formatted_msg = str_replace('[Company]', $contact_company, $formatted_msg);

                            $recipient_name = $contact_firstname . ' ' . $contact_lastname;
                            $recipient_group_name = '';

                            if (!empty($formatted_msg)) {
                                $this->info($formatted_msg);
                                // send to api
                                if ($is_teting) {
                                    $api_res = [];
                                } else {
                                    // $is_scheduled if false then keep send_at empty
                                    $api_res = $smsApi->send_sms([
                                        'to' => $to,
                                        'message' => $formatted_msg,
                                        'countrycode' => $contact_country,
                                        'send_at' => !empty($is_scheduled) ? $send_at : '',
                                        'dlr_callback' => $dlr_callback,
                                    ]);
                                }

                                $sms = Sms::create([
                                    'sms_job_id' => $job->id,
                                    'sms_id' => !empty($api_res['message_id']) ? $api_res['message_id'] : '',
                                    'message' => $formatted_msg,
                                    'to' => $to,
                                    'name' => $recipient_name,
                                    'recipient' => $recipient_group_name,
                                    'list_id' => 0,
                                    'user_id' => $user_id,
                                    'countrycode' => $contact_country,
                                    'from' => '',
                                    'send_at' => $send_at,
                                    'dlr_callback' => $dlr_callback,
                                    'cost' => isset($api_res['cost']) ? $api_res['cost'] : '',
                                    'folder' => 'outbox',
                                    'status' => 'sent',
                                    'local_status' => 'sent',
                                ]);
                                if ($show_msg) {
                                    $this->info(json_encode($api_res));
                                } else {
                                    Log::info(json_encode($api_res));
                                }
                            }
                        }
                    }
                }

                if (!empty($list_uids)) {
                    foreach ($list_uids as $list_id) {
                        $list = ContactGroup::where('id', $list_id)->with('contacts')->first(['id', 'name']);
                        if (!empty($list)) {
                            $contacts = $list->contacts;
                            if (!empty($contacts)) {
                                foreach ($contacts as $contact) {
                                    $contact_firstname = $contact->name;
                                    $contact_lastname = $contact->lastname;
                                    $contact_phone = $contact->phone;
                                    $contact_company = $contact->company;
                                    $contact_country = $contact->country;
                                    $to = $contact_phone;

                                    $formatted_msg = $message;
                                    $formatted_msg = str_replace('[Firstname]', $contact_firstname, $formatted_msg);
                                    $formatted_msg = str_replace('[Lastname]', $contact_lastname, $formatted_msg);
                                    $formatted_msg = str_replace('[Mobile]', $contact_phone, $formatted_msg);
                                    $formatted_msg = str_replace('[Company]', $contact_company, $formatted_msg);

                                    $recipient_name = $contact_firstname . ' ' . $contact_lastname;
                                    $recipient_group_name = $list->name;

                                    if (!empty($formatted_msg)) {
                                        $this->info($formatted_msg);
                                        // send to api
                                        if ($is_teting) {
                                            $api_res = [];
                                        } else {
                                            // $is_scheduled if false then keep send_at empty
                                            $api_res = $smsApi->send_sms([
                                                'to' => $to,
                                                'message' => $formatted_msg,
                                                'countrycode' => $contact_country,
                                                'send_at' => !empty($is_scheduled) ? $send_at : '',
                                                'dlr_callback' => $dlr_callback,
                                            ]);
                                        }

                                        $sms = Sms::create([
                                            'sms_job_id' => $job->id,
                                            'sms_id' => !empty($api_res['message_id']) ? $api_res['message_id'] : '',
                                            'message' => $formatted_msg,
                                            'to' => $to,
                                            'name' => $recipient_name,
                                            'recipient' => $recipient_group_name,
                                            'list_id' => $list_id,
                                            'user_id' => $user_id,
                                            'countrycode' => $contact_country,
                                            'from' => '',
                                            'send_at' => $send_at,
                                            'dlr_callback' => $dlr_callback,
                                            'cost' => isset($api_res['cost']) ? $api_res['cost'] : '',
                                            'folder' => 'outbox',
                                            'status' => 'sent',
                                            'local_status' => 'sent',
                                        ]);
                                        if ($show_msg) {
                                            $this->info(json_encode($api_res));
                                        } else {
                                            Log::error(json_encode($api_res));
                                        }
                                    }
                                }
                            } else {
                                $msg = 'SMS JOB: Group has no contacts ' . $list->id . ' ' . $list->name;
                                if ($show_msg) {
                                    $this->error($msg);
                                } else {
                                    Log::error($msg);
                                }
                            }
                        } else {
                            $msg = 'SMS JOB: Group not found ' . $list_id;
                            if ($show_msg) {
                                $this->error($msg);
                            } else {
                                Log::error($msg);
                            }
                        }
                    }
                    $job->status = 'COMPLETE';
                    $job->save();
                } else {
                    $job->status = 'COMPLETE';
                    $job->save();
                    $msg = 'SMS JOB: Groups not added, Marked Complete ' . $job->id . ' ' . $job->name;
                    if ($show_msg) {
                        $this->error($msg);
                    } else {
                        Log::error($msg);
                    }
                }
            }

            // $this->info(json_encode($job));
            // $balance = $smsApi->get_balance();
            // Log::info('BALANCE', $balance);
            */
        } catch (Exception $e) {
            $msg = 'ERROR ' . $e->getMessage();
            $this->error($msg);
            Log::error($msg);
        }
    }
}
