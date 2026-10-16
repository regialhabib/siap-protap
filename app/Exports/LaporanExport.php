<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class LaporanExport implements FromView, WithColumnWidths, WithEvents
{
    protected $data;

    protected $jenis;

    protected $tahun;

    protected $bulan;

    protected $bulanAwal;

    protected $bulanTengah;

    protected $bulanAkhir;

    protected $uppt_id;

    public function __construct($data, $jenis, $tahun, $bulan, $bulanAwal, $bulanTengah, $bulanAkhir, $upptId)
    {
        $this->data = $data;
        $this->jenis = $jenis;
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->bulanAwal = $bulanAwal;
        $this->bulanTengah = $bulanTengah;
        $this->bulanAkhir = $bulanAkhir;
        $this->uppt_id = $upptId;
    }

    public function view(): View
    {
        return view($this->jenis === 'tahunan' ? 'laporan.excel-tahunan' : 'laporan.excel', [
            'data' => $this->data,
            'jenis' => $this->jenis,
            'tahun' => $this->tahun,
            'bulan' => $this->bulan,
            'bulan_awal' => $this->bulanAwal,
            'bulan_tengah' => $this->bulanTengah,
            'bulan_akhir' => $this->bulanAkhir,
            'uppt_id' => $this->uppt_id,
        ]);
    }

    public function columnWidths(): array
    {
        if ($this->jenis === 'tahunan') {
            return [
                'A' => 3.0, 'B' => 6.5, 'C' => 25.0, 'D' => 22.0, 'E' => 16.0,
                'F' => 28.0, 'G' => 19.0, 'H' => 12.0, 'I' => 12.0, 'J' => 12.0,
                'K' => 13.0, 'L' => 13.0, 'M' => 15.0, 'N' => 12.0, 'O' => 13.0,
                'P' => 33.0, 'Q' => 18.0, 'R' => 3.0,
            ];
        }

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
            AfterSheet::class => function (AfterSheet $event) {
                if ($this->jenis !== 'tahunan') {
                    return;
                }

                $sheet = $event->sheet->getDelegate();
                $pageSetup = $sheet->getPageSetup();

                $sheet->setShowGridlines(false);
                $sheet->freezePane('B10');
                $pageSetup
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A3)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setRowsToRepeatAtTopByStartAndEnd(8, 9)
                    ->setHorizontalCentered(true)
                    ->setPrintArea("A1:R{$sheet->getHighestRow()}");

                $sheet->getPageMargins()
                    ->setLeft(0.25)
                    ->setRight(0.25)
                    ->setTop(0.5)
                    ->setBottom(0.5);
            },
        ];
    }
}
