<?php

namespace App\Services;

use App\Models\WebPush;
use Minishlink\WebPush\WebPush as WebPushClient;
use Minishlink\WebPush\Subscription;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Utility class
 */
class Appy
{

    function __construct()
    {
    }

    public function sendNotification($user_id, $title, $body)
    {
        if (empty($user_id) || empty($title) || empty($body)) {
            return false;
        }

        $tokens = WebPush::where('user_id', $user_id)->select('id', 'token')->get();
        if (empty($tokens)) {
            return false;
        }

        $publicKey = config('app.vapid_public');
        $privateKey = config('app.vapid_private');
        $image = '';

        $auth = [
            'VAPID' => [
                'subject' => 'mailto:no-reply@sublimesms.com.au',
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ];

        $payload = [
            'title' => $title,
            'body' => $body,
        ];
        if (!empty($image)) {
            $payload['image'] = $image;
        }
        $payload = json_encode($payload);

        $defaultOptions = [
            'TTL' => 604800, // defaults to 4 weeks
            'urgency' => 'high', // required (very-low, low, normal, or high)
            'batchSize' => 1000, // defaults to 1000
        ];

        $notifications = [];
        if (!empty($tokens)) {
            /** @disregard */
            $webPush = new WebPushClient($auth);
            $webPush->setDefaultOptions($defaultOptions);
            foreach ($tokens as $token) {
                if (!empty($token['token'])) {
                    /** @disregard */
                    $subscription = Subscription::create(json_decode($token['token'], true));
                    $notifications[] = [
                        'subscription' => $subscription,
                        'payload' => $payload,
                    ];
                }
            }
            foreach ($notifications as $notification) {
                $webPush->queueNotification(
                    $notification['subscription'],
                    $notification['payload'],
                );
            }
            $errors = [];
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if ($report->isSuccess()) {
                    Log::info('Notification sent: ' . $endpoint);
                } else {
                    $errors[] = $report->getReason();
                    Log::error('Notification failed: ' . $report->getReason());
                }
            }
            if (!empty($errors)) {
                error_log(json_encode($errors));
            }
            if (empty($errors)) {
                return true;
            }
        }
    }

    public function getIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'unknown';
        return $ipaddress;
    }

    public function getDeviceInfo()
    {
        $devices = [
            'mobile' => [
                'iPhone' => 'iOS',
                'Android' => 'Android',
                'Windows Phone' => 'Windows Phone',
                'BlackBerry' => 'BlackBerry',
                'Mobile' => 'unknown',
            ],
            'desktop' => [
                'Windows' => 'Windows',
                'Macintosh' => 'macOS',
                'Linux' => 'Linux',
                'X11' => 'Linux', /* X11 typically refers to Linux */
            ],
            'tablet' => [
                'iPad' => 'iOS',
                'Android' => 'Android',
                'Tablet' => 'Unknown',
                'Kindle' => 'Kindle',
            ],
        ];

        $deviceType = 'unknown';
        $operatingSystem = 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        foreach ($devices as $type => $osKeywords) {
            foreach ($osKeywords as $keyword => $os) {
                if (stripos($userAgent, $keyword) !== false) {
                    $deviceType = $type;
                    $operatingSystem = $os;
                    /* Break out of both loops once a match is found */
                    break 2;
                }
            }
        }
        return ['device' => $deviceType, 'os' => $operatingSystem];
    }

    public function getDevice()
    {
        $deviceInfo = $this->getDeviceInfo();
        if (!empty($deviceInfo['device'])) {
            return $deviceInfo['device'];
        }
        return 'unknown';
    }

    public function getOs()
    {
        $deviceInfo = $this->getDeviceInfo();
        if (!empty($deviceInfo['os'])) {
            return $deviceInfo['os'];
        }
        return 'unknown';
    }

    public function getBrowser()
    {
        $browserNames = [
            'Firefox' => 'Firefox',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
            'Opera' => 'Opera',
            'Edge' => 'Microsoft Edge',
            'MSIE' => 'Internet Explorer',
            // Add more browser names as needed
        ];
        $browser = 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        foreach ($browserNames as $browserString => $browserLabel) {
            if (strpos($userAgent, $browserString) !== false) {
                $browser = $browserLabel;
                break;
            }
        }
        return $browser;
    }
}
