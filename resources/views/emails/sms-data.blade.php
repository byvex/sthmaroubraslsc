<div>
    @php
    $tz_utc = new \DateTimeZone('UTC');
    $tz = new \DateTimeZone('Australia/Sydney');

    $deliver_at = $sms->delivered_at;
    if(!empty($deliver_at)) {
        try {
            $deliver_at_obj = new \DateTime($deliver_at, $tz_utc);
            $deliver_at_obj->setTimezone($tz);
            $deliver_at = $deliver_at_obj->format('d/m/Y h:i A');
        } catch (\Exception $e) {
            $deliver_at = $deliver_at;
        }
    }

    $send_at = $sms->send_at;
    if(!empty($send_at)) {
        try {
            $send_at_obj = new \DateTime($send_at, $tz_utc);
            $send_at_obj->setTimezone($tz);
            $send_at = $send_at_obj->format('d/m/Y h:i A');
        } catch (\Exception $e) {
            $send_at = $send_at;
        }
    }
    @endphp

    <div>Message {{ $sms->folder == 'outbox' ? 'sent' : 'received' }}</div>
    <div><strong>ID :</strong> {{ $sms->id }}</div>
    <div><strong>Folder :</strong> {{ $sms->folder }}</div>
    <div><strong>Delivered at :</strong> {{ $deliver_at }}</div>
    <div><strong>To :</strong> {{ $sms->to }}</div>
    <div><strong>Group :</strong> {{ $sms->recipient }}</div>
    <div><strong>Name :</strong> {{ $sms->name }}</div>
    <div><strong>Country :</strong> {{ $sms->countrycode }}</div>
    <div><strong>Company :</strong> {{ !empty($data['company']) ? $data['company'] : '' }}</div>
    <div><strong>From :</strong> {{ $sms->from }}</div>
    <div><strong>Status :</strong> {{ $sms->status }}</div>
    <div><strong>Message :</strong> {{ $sms->message }}</div>
    <div><strong>Part :</strong> {{ $sms->part }}</div>
    <div><strong>User Id :</strong> {{ $sms->user_id }}</div>
    <div><strong>User Email :</strong> {{ $sms->sender?->email }}</div>
    <div><strong>User Name :</strong> {{ $sms->sender?->name }}</div>
    <div><strong>Send at :</strong> {{ $send_at }}</div>

</div>
