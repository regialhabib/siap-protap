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
        
        $query = Pengamatan::with(['komoditas', 'opt', 'uppt', 'kecamatan'])
            ->orderBy('tanggal_pengamatan', 'desc');

        if ($user->role === 'popt') {
            $query->where('uppt_id', $user->uppt_id);
        }

        if ($search = request('search')) {
            $query->where(function($q) use ($search) {
                $q->whereHas('komoditas', function($q2) use ($search) {
                    $q2->where('nama_komoditas', 'like', "%{$search}%");
                })->orWhereHas('opt', function($q2) use ($search) {
                    $q2->where('nama_opt', 'like', "%{$search}%");
                })->orWhereHas('uppt', function($q2) use ($search) {
                    $q2->where('nama_uppt', 'like', "%{$search}%");
                })->orWhere('kondisi_serangan', 'like', "%{$search}%");
            });
        }

        // Clone query for stats calculation before applying pagination (which modifies limit/offset)
        $baseQuery = clone $query;
        $pengamatans = $query->paginate(10)->withQueryString();

        // Simple statistics
        $stats = [
            'total_pengamatan' => $pengamatans->total(),
            'total_luas_serangan' => $baseQuery->sum('serangan_jumlah'),
            'komoditas_terdampak' => $baseQuery->distinct('komoditas_id')->count('komoditas_id'),
        ];

        return view('dashboard', compact('pengamatans', 'stats'));
    }
}
