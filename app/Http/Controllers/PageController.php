<?php

namespace App\Http\Controllers;

use App\Services\SMSApi;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use App\Models\Sms;
use App\Models\SenderNumber;
use Exception;

class PageController extends Controller
{
    protected $smsApi;

    function __construct(SMSApi $smsApi)
    {
        $this->smsApi = $smsApi;
    }

    public function dashboard(Request $req)
    {
        // dd($this->smsApi->get_balance());

        $current_user = $req->user();
        $profiles = $current_user->getProfiles();
        $sender_numbers_unique = [];

        $profileIds = array_keys($profiles);
        if( $current_user->isUser() || $current_user->isAdmin() ) {
            $senderParents = $current_user->parents->pluck('id');
            if(!empty($senderParents)) {
                $profileIds = array_merge($profileIds, $senderParents->toArray());
            }
        }
        $senderUsers = User::whereIn('id', $profileIds)->with('sender')->get(['id', 'sender_number']);
        $sender_numbers = [];
        if (!empty($senderUsers)) {
            foreach ($senderUsers as $senderUser) {
                if (!empty($senderUser->sender)) {
                    $sender_numbers[] = [
                        'id' => $senderUser->sender->id,
                        'phone' => $senderUser->sender->phone,
                    ];
                } else {
                }
            }
        }

        $uniquePhones = [];
        foreach ($sender_numbers as $item) {
            if (!in_array($item['phone'], $uniquePhones)) {
                $uniquePhones[] = $item['phone'];
                $sender_numbers_unique[] = $item;
            }
        }

            // dd($profiles, $profileIds, $senderUsers, $sender_numbers, $sender_numbers_unique);
            // $_res = $this->smsApi->get_numbers();
            // $_data = $this->smsApi->get_number([
            //     'number'=> '61480008600',
            // ]);
            // dd($_res, $_data, route('api.sms.callback.dlr'), route('api.sms.callback.reply'));

        $phoneNumbers = $sender_numbers_unique;

        if (!empty($req->dev)) {
            dd($sender_numbers, $uniquePhones, $phoneNumbers, $sender_numbers_unique);
        }

        return view('dashboard', [
            'profiles' => $profiles,
            'phoneNumbers' => $phoneNumbers,
            'current_user' => $current_user,
            'countries' => config('countries'),
        ]);
    }
    
    public function globalRelayTest()
    {
        try {
            $id = '3548';
            $sms = Sms::where('id', $id)->first();
            $contact = Contact::where('phone', 'like', '%' . $sms->to . '%')->first();

            $data = globalRelay()->send([
                'sms' => $sms,
                'contact' => $contact,
            ]);
            $message = $data['message'] ?? 'No response, check browser console for more'; 
            return resJson([
                'message' => $message,
                'console'=> true,
                'data'=> $data,
            ]);
        } catch (Exception $e) {
            return resJson($e->getMessage(), 500);
        }
    }
}
