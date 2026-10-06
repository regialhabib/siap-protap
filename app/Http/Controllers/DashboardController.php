<?php

namespace App\Http\Controllers;

use App\Models\Pengamatan;
use App\Models\Uppt;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();

        $currentMonth = $request->input('bulan', now()->month);
        $currentYear = $request->input('tahun', now()->year);

        $statsQuery = Pengamatan::whereMonth('tanggal_pengamatan', $currentMonth)
            ->whereYear('tanggal_pengamatan', $currentYear);

        if ($user->role === 'popt') {
            $statsQuery->where('uppt_id', $user->uppt_id);
        }

        $stats = [
            'total_pengamatan' => $statsQuery->count(),
            'total_luas_serangan' => $statsQuery->sum('serangan_jumlah'),
            'komoditas_terdampak' => $statsQuery->distinct('komoditas_id')->count('komoditas_id'),
        ];

        // Table Query
        $tableQuery = Pengamatan::with(['komoditas', 'opt', 'uppt', 'kecamatan'])
            ->latest('tanggal_pengamatan');

        if ($user->role === 'popt') {
            $tableQuery->where('uppt_id', $user->uppt_id);
        } else {
            $tableQuery->whereMonth('tanggal_pengamatan', $currentMonth)
                       ->whereYear('tanggal_pengamatan', $currentYear);
        }

        $pengamatans = $tableQuery->paginate(5)->withQueryString();

        // Hitung Kepatuhan UPPT (Bulan Ini)
        $allUppt = Uppt::orderBy('nama_uppt', 'asc')->get();
        $allUpptCount = $allUppt->count();
        
        $upptIdsWithPengamatan = Pengamatan::whereMonth('tanggal_pengamatan', $currentMonth)
            ->whereYear('tanggal_pengamatan', $currentYear)
            ->distinct('uppt_id')
            ->pluck('uppt_id')
            ->toArray();

        $upptSudah = $allUppt->whereIn('id', $upptIdsWithPengamatan)->values();
        $upptBelum = $allUppt->whereNotIn('id', $upptIdsWithPengamatan)->values();

        $totalSudah = $upptSudah->count();
        $totalBelum = $upptBelum->count();

        $persentaseSudah = $allUpptCount > 0 ? round(($totalSudah / $allUpptCount) * 100, 1) : 0;
        $persentaseBelum = $allUpptCount > 0 ? round(($totalBelum / $allUpptCount) * 100, 1) : 0;

        $chartData = [
            'sudah' => $upptSudah,
            'belum' => $upptBelum,
            'total_sudah' => $totalSudah,
            'total_belum' => $totalBelum,
            'persentase_sudah' => $persentaseSudah,
            'persentase_belum' => $persentaseBelum,
            'total' => $allUpptCount,
            'bulan_ini' => Carbon::createFromDate($currentYear, $currentMonth, 1)->isoFormat('MMMM Y')
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'stats' => $stats,
                'chartData' => $chartData,
                'table_html' => view('partials.dashboard-table', compact('pengamatans'))->render(),
            ]);
        }

        return view('dashboard', compact('stats', 'chartData', 'pengamatans'));
    }
}
