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
            'B' => 6.5,
            'C' => 25.0,  // Wilayah Pengamatan/Kecamatan
            'D' => 18.0,  // Jenis Komoditas
            'E' => 16.0,  // Luas Komoditi
            'F' => 15.0,  // Jenis OPT
            'G' => 13.0,
            'H' => 13.0,
            'I' => 13.0,
            'J' => 13.0,
            'K' => 16.0,  // APBD Kab
            'L' => 16.0,  // APBD Prov
            'M' => 15.0,  // Masyarakat
            'N' => 13.0,  // APBN
            'O' => 20.0,  // Kondisi Serangan
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
