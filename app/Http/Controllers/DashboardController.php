<?php

namespace App\Http\Controllers;

use App\Models\Pengamatan;
use App\Models\Uppt;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $query = Pengamatan::with(['komoditas', 'opt', 'uppt', 'kecamatan'])
            ->latest('tanggal_pengamatan');

        if ($user->role === 'popt') {
            $query->where('uppt_id', $user->uppt_id);
        }

        $pengamatans = $query->paginate(5);

        // Clone query for stats calculation
        $baseQuery = clone $query;
        $baseQuery->whereMonth('tanggal_pengamatan', $currentMonth)
            ->whereYear('tanggal_pengamatan', $currentYear);

        // Simple statistics
        $stats = [
            'total_pengamatan' => $baseQuery->count(),
            'total_luas_serangan' => $baseQuery->sum('serangan_jumlah'),
            'komoditas_terdampak' => $baseQuery->distinct('komoditas_id')->count('komoditas_id'),
        ];

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
            'bulan_ini' => now()->isoFormat('MMMM Y')
        ];

        return view('dashboard', compact('stats', 'chartData', 'pengamatans'));
    }
}
