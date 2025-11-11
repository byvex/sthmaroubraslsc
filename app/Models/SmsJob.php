<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsJob extends Model
{
    use HasFactory;

    protected $table = 'sms_jobs';

    protected $fillable = [
        'id',
        'name',
        'profile_id',
        'user_id',
        'send_at',
        'template_id',
        'list_uids',
        'numbers',
        'message',
        'scheduled',
        'meta',
        'status',

        'user_id',
        'from',
        'validity',
        'replies_to_email',
        'tracked_link_url',
        'link_hits_callback',
        'dlr_callback',
        'reply_callback',
    ];

    protected $casts = [
        'send_at' => 'datetime',
        'list_uids' => 'array',
        'numbers' => 'array',
        'scheduled' => 'boolean',
    ];
}
