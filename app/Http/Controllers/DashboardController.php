<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengamatan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $query = Pengamatan::with(['komoditas', 'opt', 'uppt'])
            ->orderBy('tanggal_pengamatan', 'desc');

        if ($user->role === 'popt') {
            $query->where('uppt_id', $user->uppt_id);
        }

        // Clone query for stats calculation before applying pagination (which modifies limit/offset)
        $baseQuery = clone $query;
        $pengamatans = $query->paginate(10);

        // Simple statistics
        $stats = [
            'total_pengamatan' => $pengamatans->total(),
            'total_luas_serangan' => $baseQuery->sum('serangan_jumlah'),
            'komoditas_terdampak' => $baseQuery->distinct('komoditas_id')->count('komoditas_id'),
        ];

        return view('dashboard', compact('pengamatans', 'stats'));
    }
}
