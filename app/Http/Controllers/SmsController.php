<?php

namespace App\Http\Controllers;

use App\Exports\SmsExport;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Sms;
use App\Models\User;
use App\Models\SmsJob;
use App\Models\Template;
use App\Services\SMSApi;
use Exception;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\SenderNumber;
use App\Imports\SmsImport;
use App\Mail\SmsData;
use App\Services\Appy;
use DateTimeZone;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

class SmsController extends Controller
{
    protected $appy;
    protected $smsApi;

    function __construct(Appy $appy, SmsApi $smsApi)
    {
        $this->appy = $appy;
        $this->smsApi = $smsApi;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $current_user = $req->user();
        $profile_id = $current_user->getActiveProfile();
        $profile_ids = $current_user->allProfileIds();
        $is_export = $req->export;

        if ($req->ajax() || !empty($is_export)) {
            $keyword = $req->keyword;
            $keywordRecipient = $req->keywordRecipient;
            $filterStatus = $req->filterStatus;
            $filterFolder = $req->filterFolder;
            $filterStartDate = $req->filterStartDate;
            $filterEndDate = $req->filterEndDate;

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
            ])->with('sender:id,email,name,lastname');
            if (!$current_user->isSuperAdmin()) {
                $query = $query->whereIn('user_id', $profile_ids);
            }

            if (!empty($keyword)) {
                $query = $query->where('message', 'like', '%' . $keyword . '%');
            }
            if (!empty($filterStatus)) {
                $query = $query->where('status', $filterStatus);
            }
            if (!empty($filterFolder)) {
                $query = $query->where('folder', $filterFolder);
            }
            if (!empty($keywordRecipient)) {
                $query = $query->where(function ($q) use ($keywordRecipient) {
                    $q->where('name', 'like', '%' . $keywordRecipient . '%')
                        ->orWhere('recipient', 'like', '%' . $keywordRecipient . '%');
                });
            }
            if (!empty($filterStartDate)) {
                $query = $query->where('send_at', '>=', $filterStartDate);
            }
            if (!empty($filterEndDate)) {
                $query = $query->where('send_at', '<=', $filterEndDate);
            }
            $query = $query->orderBy('id', 'DESC');

            if ($is_export) {
                $query = $query->take(500);
                $filename = 'activity-' . date('Y-m-d-H-i-s') . '.xlsx';
                return (new SmsExport($query))->download($filename);
            }


            $data = $query->paginate(20);
            $items = [];
            $perPage = $data->perPage();
            $totalPages = $data->lastPage();
            $totalRows = $data->total();
            $page = $data->currentPage();
            if ($page > $totalPages) {
                $page = $totalPages;
            }
            if (!empty($data->items())) {
                foreach ($data->items() as $row) {
                    $from_name = $row->from_name;
                    $from_lastname = '';
                    if (!empty($row->sender)) {
                        if (trim($from_name) == '') {
                            $from_name = $row->sender->name;
                        }
                        $from_lastname = $row->sender->lastname;
                    }
                    $items[] = [
                        'id' => $row->id,
                        'message' => $row->message,
                        'to' => $row->to,
                        'from_number' => $row->from,
                        'from' => $row?->sender?->email,
                        'from_name' => $from_name,
                        'from_lastname' => $from_lastname,
                        'send_at' => $row?->send_at?->setTimezone('Australia/Sydney')->format('d/m/Y h:i A'),
                        'cost' => $row->cost,
                        'recipient_name' => $row->name,
                        'recipient' => !empty($row->recipient) ? $row->recipient : $row->name,
                        'status' => strtoupper($row->status),
                        'folder' => $row->folder,
                    ];
                }
            }
            return response()->json([
                'items' => $items,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalRows' => $totalRows,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        $input = $req->validate([
            'profile_id' => ['required', 'integer'],
            'template_id' => ['nullable', 'integer', Rule::exists(Template::class, 'id')],
            'send_at' => ['nullable', 'string'],
            'contact_id' => ['nullable'],
            'contact_id.*' => ['nullable', Rule::exists(Contact::class, 'id')],
            'contact_group_uid' => ['nullable'],
            'contact_group_uid.*' => ['nullable', Rule::exists(ContactGroup::class, 'id')],
            'message' => ['required', 'string'],
            'from' => ['required', Rule::exists(SenderNumber::class, 'phone')],
            'isTesting' => ['nullable'],
            'isQuick' => ['nullable'],
            'sending' => ['required', 'in:adhoc,contact'],
            'adhoc_numbers' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
        ], []);

        $input['title'] = 'No title';
        $isTesting = !empty($input['isTesting']) ? true : false;
        $isQuick = (!empty($input['isQuick']) && $input['isQuick'] == 'YES');

        $is_adhoc = $input['sending'] == 'adhoc';

        if (!$is_adhoc && empty($input['contact_group_uid']) && empty($input['contact_id'])) {
            return response()->json(['message' => 'Please select recipient'], 422);
        }
        if ($is_adhoc && empty($input['adhoc_numbers'])) {
            return response()->json(['message' => 'Please enter adhoc number'], 422);
        }

        if($is_adhoc && empty($input['country'])) {
            return response()->json(['message' => 'Please select country for adhoc number'], 422);
        }

        $adhoc_numbers = [];
        if ($is_adhoc) {
            if (!empty($input['adhoc_numbers'])) {
                $adhoc_numbers_temp = explode(',', $input['adhoc_numbers']);
                if (!empty($adhoc_numbers_temp) && is_array($adhoc_numbers_temp)) {
                    foreach ($adhoc_numbers_temp as $_adhoc_num) {
                        $_adhoc_num = trim($_adhoc_num); // trim extra space
                        $_adhoc_num = str_replace('+', '', $_adhoc_num); // remove + sign of country code
                        $_adhoc_num = intval($_adhoc_num); // remove letters to get only number
                        if (!empty($_adhoc_num)) {
                            $adhoc_numbers[] = $_adhoc_num;
                        }
                    }
                }
            }
            if (empty($adhoc_numbers)) {
                return response()->json(['message' => 'Please enter valid adhoc number'], 422);
            }
        }

        $tzUtc = new \DateTimeZone('UTC');
        $tz = new \DateTimeZone('Australia/Sydney');
        $now = new DateTime('now', $tzUtc);
        $now->setTimezone($tz);

        $send_at = $now->format('Y-m-d H:i:s');
        $list_uids = [];
        $numbers = [];
        $scheduled = false;
        try {

            $quick_job_id = null;
            $quick_sms_ids = [];

            if (!empty($input['send_at'])) {
                $send_at_obj = new DateTime($input['send_at']);
                $send_at = $send_at_obj->format('Y-m-d H:i:s');
                $now = new DateTime($send_at, $tzUtc);
                $now->setTimezone($tz);
                $send_at = $now->format('Y-m-d H:i:s');
                $scheduled = true;
            }

            if (!$is_adhoc) {
                if (!empty($input['contact_group_uid'])) {
                    $list_uids = $input['contact_group_uid'];
                }

                if (!empty($input['contact_id'])) {
                    $numbers = $input['contact_id'];
                }

                $job = SmsJob::create([
                    'name' => $input['title'],
                    'user_id' => $input['profile_id'],
                    'send_at' => $send_at,
                    'template_id' => $input['template_id'],
                    'list_uids' => $list_uids,
                    'numbers' => $numbers,
                    'from' => $input['from'],
                    'message' => $input['message'],
                    'scheduled' => $scheduled,
                    'status' => $isTesting ? 'TESTING' : 'PENDING',
                ]);
                $quick_job_id = $job->id;
            }

            // generate all sms here
            $dlr_callback = route('api.sms.callback.dlr');
            $message = $input['message'];
            $is_scheduled = $scheduled;
            $is_teting = $isTesting;
            $send_at = $send_at;
            $user_id = $input['profile_id'];

            if (!$is_adhoc) {
                // handle normal contact group sending logic
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
                                // send to api
                                if ($is_teting) {
                                    $api_res = [];
                                } else {
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
                                    'from' => $input['from'],
                                    'send_at' => !empty($is_scheduled) ? $send_at : NULL,
                                    'dlr_callback' => $dlr_callback,
                                    'cost' => isset($api_res['cost']) ? $api_res['cost'] : '',
                                    'folder' => 'outbox',
                                    'status' => 'pending',
                                    'local_status' => !empty($is_teting) ? 'SENT' : 'PENDING',
                                ]);
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
                                        // send to api
                                        if ($is_teting) {
                                            $api_res = [];
                                        } else {
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
                                            'from' => $input['from'],
                                            'send_at' => !empty($is_scheduled) ? $send_at : NULL,
                                            'dlr_callback' => $dlr_callback,
                                            'cost' => isset($api_res['cost']) ? $api_res['cost'] : '',
                                            'folder' => 'outbox',
                                            'status' => 'pending',
                                            'local_status' => !empty($is_teting) ? 'SENT' : 'PENDING',
                                        ]);
                                    }
                                }
                            } else {
                                $msg = 'SMS JOB: Group has no contacts ' . $list->id . ' ' . $list->name;
                            }
                        } else {
                            $msg = 'SMS JOB: Group not found ' . $list_id;
                        }
                    }
                    $job->status = 'COMPLETE';
                    $job->save();
                } else {
                    $job->status = 'COMPLETE';
                    $job->save();
                    $msg = 'SMS JOB: Groups not added, Marked Complete ' . $job->id . ' ' . $job->name;
                }
            } else {
                // handle adhoc numbers
                foreach ($adhoc_numbers as $adhoc_number) {
                    $sms = Sms::create([
                        'sms_job_id' => 0,
                        'sms_id' => '',
                        'message' => $message,
                        'to' => $adhoc_number,
                        'name' => $adhoc_number,
                        'recipient' => '',
                        'list_id' => 0,
                        'user_id' => $user_id,
                        'countrycode' => $input['country'],
                        'from' => $input['from'],
                        'send_at' => !empty($is_scheduled) ? $send_at : NULL,
                        'dlr_callback' => $dlr_callback,
                        'cost' => '',
                        'folder' => 'outbox',
                        'status' => 'pending',
                        'local_status' => !empty($is_teting) ? 'SENT' : 'PENDING',
                    ]);
                    $quick_sms_ids[] = $sms->id;
                }
            }


            if (!empty($send_at) && !empty($send_at_obj)) {
                if($isQuick) {
                    if(!empty($quick_job_id)) {
                        $smses = Sms::where('local_status', 'PENDING')->where('sms_job_id', $quick_job_id)->take(250)->get();
                        if(!empty($smses) && count($smses)) {
                            foreach($smses as $sms) {
                                $this->send_quick_sms($sms);
                            }
                        }
                    }
                    if(!empty($quick_sms_ids)) {
                        foreach ($quick_sms_ids as $quick_sms_id) {
                            $sms = Sms::where('local_status', 'PENDING')->where('id', $quick_sms_id)->first();
                            if(!empty($sms)) {
                                $this->send_quick_sms($sms);
                            }
                        }
                    }
                }

                $now = new DateTime($send_at_obj->format('Y-m-d H:i:s'), $tzUtc);
                $now->setTimezone($tz);
                return response()->json([
                    'scheduled' => true,
                    'message' => 'Horray! Your SMS has been scheduled for',
                    'message2' => $now->format('D M j g:i A Y'),
                ]);
            } else {
                if($isQuick) {
                    if(!empty($quick_job_id)) {
                        $smses = Sms::where('local_status', 'PENDING')->where('sms_job_id', $quick_job_id)->take(250)->get();
                        if(!empty($smses) && count($smses)) {
                            foreach($smses as $sms) {
                                $this->send_quick_sms($sms);
                            }
                        }
                    }
                    if(!empty($quick_sms_ids)) {
                        foreach ($quick_sms_ids as $quick_sms_id) {
                            $sms = Sms::where('local_status', 'PENDING')->where('id', $quick_sms_id)->first();
                            if(!empty($sms)) {
                                $this->send_quick_sms($sms);
                            }
                        }
                    }
                }


                $now = new DateTime('now', $tzUtc);
                $now->setTimezone($tz);
                return response()->json([
                    'scheduled' => false,
                    'message' => 'Your Message has been Sent',
                    'message2' => $now->format('D M j g:i A Y'),
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function send_quick_sms($sms = null)
    {
        try {
            $dlr_callback = route('api.sms.callback.dlr');
            // $is_scheduled if false then keep send_at empty
            $senderNumber = $sms->from;
            if($sms->countrycode == 'SG' && $sms->from == 61480088898) {
                $senderNumber = 6583184436;
            }
            if($sms->countrycode == 'HK' && $sms->from == 61480088898) {
                $senderNumber = 'BGC Future';
            }

            $api_res = $this->smsApi->send_sms([
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
        } catch ( Exception $e ) {
            info($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sms $sms)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sms $sms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sms $sms)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sms $sms)
    {
        //
    }

    public function send_report(Request $request)
    {
        Artisan::call('send-activity-report', [
            'type'=> 'recent-7-days',
        ]);
        return response()->json([
            'success'=> true,
            'message'=> 'Email triggered, please check inbox'
        ]);
    }

    public function send_report_eml(Request $request)
    {
        Artisan::call('send-activity-report', [
            'type'=> 'recent-7-days',
            'file'=> 'eml',
        ]);
        return response()->json([
            'success'=> true,
            'message'=> 'Email triggered, please check inbox'
        ]);
    }

    public function dlr_callback(Request $req)
    {
        $sms_id           = $req->message_id;
        $sms_delivered_at = $req->datetime;
        $sms_status       = $req->status;
        $sms_user_id      = $req->user_id;

        $inputs = $req->all();

        try {
            $sms = Sms::where('sms_id', $sms_id)->with('sender')->first();
            if (!empty($sms)) {
                $sms->delivered_at = $sms_delivered_at;
                $sms->status = $sms_status;
                $sms->sender_id = $sms_user_id;
                $sms->save();

                $contact = Contact::where('phone', 'like', '%' . $sms->to . '%')->first();
                $data = [];
                if(!empty($contact)) {
                    $data['company'] = $contact->company;
                }
                Mail::send(new SmsData($sms, $data));
                // send to global relay here
                $data = globalRelay()->send([ 'sms' => $sms, 'contact' => $contact ]);
            }
            Log::info(json_encode($inputs));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function reply_callback(Request $req)
    {
        try {
            $inputs = $req->all();
            Log::info('Replay Callback: ');
            Log::info(json_encode($inputs));
            $x = $inputs;
            if (!empty($x['message_id']) && !empty($x['mobile']) && !empty($x['longcode'])) {
                $user_id = 1;
                $sender_number = SenderNumber::where('phone', $x['longcode'])->select('id')->first();
                $children_ids = [];
                $admin_name = '';
                $user = null;
                if (!empty($sender_number)) {
                    $user = User::where('sender_number', $sender_number->id)->where('role', 'ADMIN')->select('id', 'name', 'lastname', 'company')->first();
                    if (!empty($user)) {
                        $user_id = $user->id;
                        $admin_name = $user->name . ' ' . $user->lastname;
                        if (trim($admin_name)) {
                            $admin_name = $user->company;
                        }
                        $childrens = $user->children();
                        if (!empty($childrens)) {
                            $children_ids = $childrens->pluck('id');
                        }
                    }
                }

                $sender = null;
                $message = !empty($x['response']) ? $x['response'] : 'no text';
                $sender_mobile = !empty($x['mobile']) ? $x['mobile'] : 'NA';
                $admin_mobile = !empty($x['longcode']) ? $x['longcode'] : '';
                if (empty($admin_name)) {
                    $admin_name = $admin_mobile;
                }
                $sender_name = $sender_mobile;
                if (!empty($sender_mobile)) {
                    $sender = Contact::where('phone', 'like', '%' . $sender_mobile . '%')->first();
                    if (!empty($sender)) {
                        $sender_name = $sender->name . ' ' . $sender->lastname;
                    }
                }

                $sms = Sms::create([
                    'sms_job_id' => 0,
                    'sms_id' => !empty($x['message_id']) ? $x['message_id'] : '',
                    'message' => $message,
                    'to' => $admin_mobile,
                    'name' => $admin_name,
                    'recipient' => $admin_name,
                    'list_id' => !empty($x['list_id']) ? $x['list_id'] : '',
                    'user_id' => $user_id,
                    'countrycode' => '',
                    'from' => $sender_mobile,
                    'from_name' => $sender_name,
                    'send_at' => !empty($x['datetime_entry']) ? $x['datetime_entry'] : NULL,
                    'dlr_callback' => '',
                    'cost' => isset($api_res['cost']) ? $api_res['cost'] : '',
                    'folder' => 'inbox',
                    'status' => 'received',
                    'local_status' => 'RECEIVED',
                ]);

                if (!empty($children_ids)) {
                    foreach ($children_ids as $child_id) {
                        $this->appy->sendNotification($child_id, 'SMS from: ' . $sender_mobile, $message);
                    }
                }

                Mail::send(new SmsData($sms));
                $data = globalRelay()->send([ 'sms' => $sms, 'contact'=> $user, 'sender' => $sender, ]);
                return response()->json([$sms, $children_ids]);
            }
            return response()->json(['message' => 'message id and mobile number are missing'], 422);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
