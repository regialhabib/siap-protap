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
    private function getData(Request $request)
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Akses ditolak.');

        $jenis = $request->input('jenis', 'bulanan');
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('m'));
        $uppts = Uppt::orderBy('nama_uppt', 'asc')->get();
        $uppt_id = $request->input('uppt_id', $uppts->isNotEmpty() ? $uppts->first()->id : '');

        $query = Pengamatan::with(['komoditas', 'opt', 'uppt', 'user']);

        // Filter Tahun
        $query->whereYear('tanggal_pengamatan', $tahun);

        // Filter Jenis
        if ($jenis == 'bulanan') {
            $query->whereMonth('tanggal_pengamatan', $bulan);
        } elseif ($jenis == 'triwulan') {
            $startMonth = ($triwulan - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereMonth('tanggal_pengamatan', '>=', $startMonth)
                  ->whereMonth('tanggal_pengamatan', '<=', $endMonth);
        }

        // Filter UPPT
        if (!empty($uppt_id)) {
            $query->where('uppt_id', $uppt_id);
        }

        return $query->orderBy('tanggal_pengamatan', 'asc')->get();
    }

    public function index(Request $request)
    {
        $data = $this->getData($request);
        $uppts = Uppt::orderBy('nama_uppt', 'asc')->get();
        
        $jenis = $request->input('jenis', 'bulanan');
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('m'));
        $triwulan = $request->input('triwulan', '1');
        $uppt_id = $request->input('uppt_id', '');

        return view('laporan.index', compact('data', 'jenis', 'tahun', 'bulan', 'triwulan', 'uppt_id', 'uppts'));
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getData($request);
        $jenis = $request->input('jenis', 'bulanan');
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('m'));
        $triwulan = $request->input('triwulan', '1');
        $uppt_id = $request->input('uppt_id', '');

        $filename = "Laporan_Pengamatan_{$jenis}_{$tahun}.xlsx";
        return Excel::download(new LaporanExport($data, $jenis, $tahun, $bulan, $triwulan, $uppt_id), $filename);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getData($request);
        $jenis = $request->input('jenis', 'bulanan');
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('m'));
        $triwulan = $request->input('triwulan', '1');
        $uppt_id = $request->input('uppt_id', '');

        $pdf = Pdf::loadView('laporan.pdf', compact('data', 'jenis', 'tahun', 'bulan', 'triwulan', 'uppt_id'))
                  ->setPaper('a4', 'landscape');
        
        $filename = "Laporan_Pengamatan_{$jenis}_{$tahun}.pdf";
        return $pdf->stream($filename);
    }
}
