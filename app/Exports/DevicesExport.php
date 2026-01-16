<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DevicesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected Collection $devices;

    public function __construct(Collection $devices)
    {
        $this->devices = $devices;
    }

    /**
     * Data rows
     */
    public function collection(): Collection
    {
        return $this->devices->map(function ($device) {
            return [
                'Model'   => $device->model,
                'IMEI 1'  => $device->imei_1,
                'IMEI 2'  => $device->imei_2,
                'Dealer'  => $device->allocation?->dealer?->name ?? '',
                'Status'  => ucfirst($device->status),
            ];
        });
    }

    /**
     * Excel headings
     */
    public function headings(): array
    {
        return [
            'Model',
            'IMEI 1',
            'IMEI 2',
            'Dealer',
            'Status',
        ];
    }
}
