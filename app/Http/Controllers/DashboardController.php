<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengamatan;
use App\Models\Uppt;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $query = Pengamatan::with(['komoditas', 'opt', 'uppt', 'kecamatan'])
            ->orderBy('tanggal_pengamatan', 'desc');

        if ($user->role === 'popt') {
            $query->where('uppt_id', $user->uppt_id);
        }

        // Clone query for stats calculation
        $baseQuery = clone $query;

        // Simple statistics
        $stats = [
            'total_pengamatan' => $baseQuery->count(),
            'total_luas_serangan' => $baseQuery->sum('serangan_jumlah'),
            'komoditas_terdampak' => $baseQuery->distinct('komoditas_id')->count('komoditas_id'),
        ];

        // Hitung Kepatuhan UPPT (Bulan Ini)
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

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
            'bulan_ini' => Carbon::now()->isoFormat('MMMM Y')
        ];

        return view('dashboard', compact('stats', 'chartData'));
    }
}
