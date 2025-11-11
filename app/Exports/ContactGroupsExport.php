<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
// use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Database\Query\Builder;

class ContactGroupsExport implements FromQuery, WithMapping, WithHeadings
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
            'user_id',
            'profile_id',
            'name',
            'username',
            'status',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->user_id,
            $item->profile_id,
            $item->name,
            $item->author->username,
            strtolower($item->status),
            $item->created_at,
            $item->updated_at,
            $item->deleted_at,
        ];
    }
}
