<?php

namespace App\Enums;

enum ModelStatusEnum: string
{
    case DRAFT         = 'DRAFT';
    case PUBLISHED     = 'PUBLISHED';
    case UNSUBSCRIBED  = 'UNSUBSCRIBED';

    public static function toArray() : array {
        $arr = [];
        foreach(self::cases() as $case) {
            $arr[$case->value] = $case->name;
        }
        return $arr;
    }
}
