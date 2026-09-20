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
        
        $query = Pengamatan::query()
            ->join('komoditas', 'pengamatans.komoditas_id', '=', 'komoditas.id')
            ->join('opts', 'pengamatans.opt_id', '=', 'opts.id')
            ->join('uppts', 'pengamatans.uppt_id', '=', 'uppts.id')
            ->select('pengamatans.*', 'komoditas.nama_komoditas', 'opts.nama_opt', 'uppts.nama_uppt')
            ->orderBy('tanggal_pengamatan', 'desc');

        if ($user->role === 'popt') {
            $query->where('pengamatans.uppt_id', $user->uppt_id);
        }

        // Clone query for stats calculation before applying pagination (which modifies limit/offset)
        $baseQuery = clone $query;
        $pengamatans = $query->paginate(10);

        // Simple statistics
        $stats = [
            'total_pengamatan' => $pengamatans->total(),
            'total_luas_serangan' => $baseQuery->sum('serangan_jumlah'),
            'komoditas_terdampak' => $baseQuery->distinct('pengamatans.komoditas_id')->count('pengamatans.komoditas_id'),
        ];

        return view('dashboard', compact('pengamatans', 'stats'));
    }
}
