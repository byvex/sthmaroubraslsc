<?php

namespace App\Traits;

// use Hidehalo\Nanoid\Client;

// trait UuidTrait
// {
//     protected static function boot()
//     {
//         parent::boot();
//         static::creating(function ($model) {
//             if (empty($model->{$model->getKeyName()})) {
//                 $client = new Client();
//                 // $model->{$model->getKeyName()} = Str::uuid()->toString();
//                 $nano_id_characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
//                 $model->{$model->getKeyName()} = $client->formattedId($nano_id_characters, 36);
//             }
//         });
//     }

//     public function getIncrementing()
//     {
//         return false;
//     }

//     public function getKeyType()
//     {
//         return 'string';
//     }
// }
