<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengamatan;
use App\Models\Uppt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

// Pustaka Export
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    private function getFilters(Request $request): array
    {
        $uppts = Uppt::orderBy('nama_uppt', 'asc')->get();
        return [
            'jenis' => $request->input('jenis', 'bulanan'),
            'tahun' => $request->input('tahun', date('Y')),
            'bulan' => $request->input('bulan', date('m')),
            'triwulan' => $request->input('triwulan', '1'),
            'uppts' => $uppts,
            'uppt_id' => $request->input('uppt_id', $uppts->first()?->id ?? ''),
        ];
    }

    private function getData(array $filters)
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Akses ditolak.');

        $query = Pengamatan::with(['komoditas', 'opt', 'uppt', 'user']);

        // Filter Tahun
        $query->whereYear('tanggal_pengamatan', $filters['tahun']);

        // Filter Jenis
        if ($filters['jenis'] == 'bulanan') {
            $query->whereMonth('tanggal_pengamatan', $filters['bulan']);
        } elseif ($filters['jenis'] == 'triwulan') {
            $startMonth = ($filters['triwulan'] - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereMonth('tanggal_pengamatan', '>=', $startMonth)
                  ->whereMonth('tanggal_pengamatan', '<=', $endMonth);
        }

        // Filter UPPT
        if (!empty($filters['uppt_id'])) {
            $query->where('uppt_id', $filters['uppt_id']);
        }

        return $query->orderBy('tanggal_pengamatan', 'asc')->get();
    }

    public function index(Request $request)
    {
        $filters = $this->getFilters($request);
        $data = $this->getData($filters);

        return view('laporan.index', array_merge(['data' => $data], $filters));
    }

    private function getFilename(array $filters, $ext)
    {
        $periode = $filters['tahun'];
        if ($filters['jenis'] == 'bulanan') {
            $namaBulan = \Carbon\Carbon::create()->month((int)$filters['bulan'])->translatedFormat('F');
            $periode = "{$namaBulan}_{$filters['tahun']}";
        } elseif ($filters['jenis'] == 'triwulan') {
            $periode = "Triwulan_{$filters['triwulan']}_{$filters['tahun']}";
        }
        return "Laporan_{$periode}.{$ext}";
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->getFilters($request);
        $data = $this->getData($filters);

        $filename = $this->getFilename($filters, 'xlsx');
        return Excel::download(new LaporanExport(
            $data, 
            $filters['jenis'], 
            $filters['tahun'], 
            $filters['bulan'], 
            $filters['triwulan'], 
            $filters['uppt_id']
        ), $filename);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->getFilters($request);
        $data = $this->getData($filters);

        $pdf = Pdf::loadView('laporan.pdf', array_merge(['data' => $data], $filters))
                  ->setPaper('a4', 'landscape');
        
        $filename = $this->getFilename($filters, 'pdf');
        return $pdf->stream($filename);
    }
}
