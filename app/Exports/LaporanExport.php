<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class LaporanExport implements FromView, WithColumnWidths, WithEvents
{
    /**
     * @param  Collection<int, object>  $data
     * @param  'bulanan'|'triwulan'|'tahunan'|'riwayat'  $jenis
     * @param  array<string, mixed>  $viewData
     */
    public function __construct(
        protected Collection $data,
        protected string $jenis,
        protected array $viewData = [],
    ) {}

    public function view(): View
    {
        return view(match ($this->jenis) {
            'tahunan' => 'laporan.excel-tahunan',
            'triwulan' => 'laporan.excel-triwulan',
            'riwayat' => 'laporan.excel-riwayat',
            default => 'laporan.excel',
        }, [
            ...$this->viewData,
            'data' => $this->data,
            'jenis' => $this->jenis,
        ]);
    }

    public function columnWidths(): array
    {
        if ($this->jenis === 'riwayat') {
            return [
                'A' => 18.0, 'B' => 14.0, 'C' => 10.0, 'D' => 10.0, 'E' => 10.0, 'F' => 10.0, 'G' => 10.0,
                'H' => 10.0, 'I' => 10.0, 'J' => 10.0, 'K' => 14.0, 'L' => 22.0,
            ];
        }

        if ($this->jenis === 'tahunan') {
            return [
                'A' => 3.0, 'B' => 6.5, 'C' => 25.0, 'D' => 22.0, 'E' => 16.0,
                'F' => 28.0, 'G' => 19.0, 'H' => 12.0, 'I' => 12.0, 'J' => 12.0,
                'K' => 13.0, 'L' => 13.0, 'M' => 15.0, 'N' => 12.0, 'O' => 13.0,
                'P' => 60.0, 'Q' => 18.0, 'R' => 3.0,
            ];
        }

        return [
            'A' => 13.0,
            'B' => 6.5,
            'C' => 25.0,  // Wilayah Pengamatan/Kecamatan atau Kabupaten/Kota
            'D' => 18.0,  // Jenis Komoditas
            'E' => 16.0,  // Luas Komoditi
            'F' => 15.0,  // Jenis OPT
            'G' => 13.0,
            'H' => 13.0,
            'I' => 13.0,
            'J' => 13.0,
            'K' => 16.0,  // APBD I
            'L' => 16.0,  // APBD II
            'M' => 15.0,  // Masyarakat
            'N' => 13.0,  // APBN
            'O' => 28.0,  // Kondisi Serangan
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
