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

        $pengamatans = $query->paginate(10);

        // Simple statistics
        $stats = [
            'total_pengamatan' => $pengamatans->total(),
            'total_luas_serangan' => $query->sum('serangan_jumlah'),
            'komoditas_terdampak' => $query->distinct('komoditas_id')->count('komoditas_id'),
        ];

        return view('dashboard', compact('pengamatans', 'stats'));
    }
}
