<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
// use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Database\Query\Builder;

class SmsExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    protected $items;

    public function __construct(Builder $items)
    {
        $this->items = $items;
    }

    public function query()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'id',
            'folder',
            'delivered_at',
            'to',
            'group',
            'name',
            'country',
            'from',
            'status',
            'message',
            'part',
            'user_id',
            'user_email',
            'user_name',
            'send_at',
        ];
    }

    public function map($item): array
    {
        $tz_utc = new \DateTimeZone('UTC');
        $tz = new \DateTimeZone('Australia/Sydney');

        $deliver_at = $item->delivered_at;
        if(!empty($deliver_at)) {
            try {
                $deliver_at_obj = new \DateTime($deliver_at, $tz_utc);
                $deliver_at_obj->setTimezone($tz);
                $deliver_at = $deliver_at_obj->format('d/m/Y h:i A');
            } catch (\Exception $e) {
                $deliver_at = $deliver_at;
            }
        }

        $send_at = $item->send_at;
        if(!empty($send_at)) {
            try {
                $send_at_obj = new \DateTime($send_at, $tz_utc);
                $send_at_obj->setTimezone($tz);
                $send_at = $send_at_obj->format('d/m/Y h:i A');
            } catch (\Exception $e) {
                $send_at = $send_at;
            }
        }


        return [
            $item->id,
            $item->folder,
            $deliver_at,
            $item->to,
            $item->recipient,
            $item->name,
            $item->countrycode,
            $item->from,
            strtolower($item->status),
            $item->message,
            $item->part,
            $item->user_id,
            $item->sender?->email,
            $item->sender?->name,
            $send_at,
        ];
    }
}
