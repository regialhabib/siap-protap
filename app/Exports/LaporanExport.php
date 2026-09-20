<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanExport implements FromView, WithColumnWidths, WithEvents
{
    protected $data;
    protected $jenis;
    protected $tahun;
    protected $bulan;
    protected $triwulan;
    protected $uppt_id;

    public function __construct($data, $jenis, $tahun, $bulan, $triwulan, $uppt_id)
    {
        $this->data = $data;
        $this->jenis = $jenis;
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->triwulan = $triwulan;
        $this->uppt_id = $uppt_id;
    }

    public function view(): View
    {
        return view('laporan.excel', [
            'data' => $this->data,
            'jenis' => $this->jenis,
            'tahun' => $this->tahun,
            'bulan' => $this->bulan,
            'triwulan' => $this->triwulan,
            'uppt_id' => $this->uppt_id
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 13.0,
            'B' => 6.33,
            'C' => 16.21,
            'D' => 14.10,
            'E' => 12.21,
            'F' => 9.88,
            'G' => 13.0,
            'H' => 13.0,
            'I' => 13.0,
            'J' => 13.0,
            'K' => 14.21,
            'L' => 13.77,
            'M' => 13.0,
            'N' => 13.0,
            'O' => 13.0,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Formatting tambahan yang tidak bisa di-handle HTML
                // Pastikan gridlines tidak hilang sepenuhnya jika mau, atau biarkan default
            },
        ];
    }
}
