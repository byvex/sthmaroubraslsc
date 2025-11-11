<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebPush;
use App\Services\Appy;
use Exception;

class WebPushController extends Controller
{
    protected $appy;

    function __construct(Appy $appy)
    {
        $this->appy = $appy;
    }

    public function subscribe(Request $req)
    {

        $user = $req->user();
        if (empty($user)) {
            return response()->json(['message' => 'Please login to subscribe notifications'], 401);
        }

        $token = $req->token;
        if (empty($token)) {
            return response()->json(['message' => 'Token is required'], 422);
        }

        try {
            $user_id = $user->id;
            $user_email = $user->email;
            $device = $this->appy->getDevice();
            $os = $this->appy->getOs();
            $browser = $this->appy->getBrowser();

            $oldToken = WebPush::where('user_id', $user_id)->where('device', $device)->where('os', $os)->select('id', 'token')->first();
            if (!empty($oldToken)) {
                if ($oldToken['token'] == $token) {
                    return response()->json([
                        'message' => 'Already saved web push token',
                    ]);
                } else {
                    $oldToken->update(['token' => $token]);
                    $this->appy->sendNotification($user_id, 'Subscribed to activity', 'You will get notified when there is an incoming sms');
                    return response()->json([
                        'message' => 'Web push token updated',
                    ]);
                }
            } else {
                $newToken = WebPush::create([
                    'user_id' => $user_id,
                    'user_email' => $user_email,
                    'token' => $token,
                    'device' => $device,
                    'os' => $os,
                    'browser' => $browser,
                ]);
                $this->appy->sendNotification($user_id, 'Subscribed to activity', 'You will get notified when there is an incoming sms');
                return response()->json([
                    'message' => 'Web push token created',
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
