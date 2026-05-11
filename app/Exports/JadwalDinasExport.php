<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JadwalDinasExport implements FromView, ShouldAutoSize
{
    public function __construct(private readonly array $data)
    {
    }

    public function view(): View
    {
        return view('admin.jadwal_dinas.export', $this->data);
    }
}
