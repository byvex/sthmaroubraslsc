<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebPush extends Model
{
    use HasFactory;

    protected $table = 'web_push_devices';

    protected $fillable = [
        'user_id', 'user_email', 'token', 'device', 'os', 'browser', 'disabled', 'expired'
    ];

}
