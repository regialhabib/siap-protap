<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use Illuminate\Support\Facades\DB;

class PengamatanController extends Controller
{
    public function create()
    {
        // Hanya POPT yang boleh menginput data
        if (Auth::user()->role !== 'popt') {
            abort(403, 'Akses ditolak. Hanya Petugas POPT yang berhak mengisi form pengamatan.');
        }

        // Ambil master data untuk dropdown
        $komoditas = Komoditas::orderBy('nama_komoditas')->get();
        $opts = Opt::orderBy('nama_opt')->get();

        return view('pengamatan.create', compact('komoditas', 'opts'));
    }

    public function store(Request $request)
    {
        // Validasi input tunggal
        $request->validate([
            'tanggal_pengamatan' => 'required|date',
            'komoditas_id' => 'required|exists:komoditas,id',
            'opt_id' => 'required|exists:opts,id',
            'luas_komoditi_ha' => 'required|numeric|min:0',
            'serangan_ringan' => 'nullable|numeric|min:0',
            'serangan_sedang' => 'nullable|numeric|min:0',
            'serangan_berat' => 'nullable|numeric|min:0',
            'kendali_apbd_kab' => 'nullable|numeric|min:0',
            'kendali_apbd_prov' => 'nullable|numeric|min:0',
            'kendali_apbn' => 'nullable|numeric|min:0',
            'kendali_masyarakat' => 'nullable|numeric|min:0',
            'kondisi_serangan' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        
        if (!$user->uppt_id) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Akun Anda belum memiliki penempatan wilayah (UPPT). Hubungi Admin.');
        }

        try {
            // Kalkulasi otomatis jumlah serangan di backend
            $ringan = $request->serangan_ringan ?? 0;
            $sedang = $request->serangan_sedang ?? 0;
            $berat = $request->serangan_berat ?? 0;
            $jumlah_serangan = $ringan + $sedang + $berat;

            Pengamatan::create([
                'user_id' => $user->id,
                'uppt_id' => $user->uppt_id,
                'tanggal_pengamatan' => $request->tanggal_pengamatan,
                'komoditas_id' => $request->komoditas_id,
                'opt_id' => $request->opt_id,
                'luas_komoditi_ha' => $request->luas_komoditi_ha,
                'serangan_ringan' => $ringan,
                'serangan_sedang' => $sedang,
                'serangan_berat' => $berat,
                'serangan_jumlah' => $jumlah_serangan,
                'kendali_apbd_kab' => $request->kendali_apbd_kab ?? 0,
                'kendali_apbd_prov' => $request->kendali_apbd_prov ?? 0,
                'kendali_apbn' => $request->kendali_apbn ?? 0,
                'kendali_masyarakat' => $request->kendali_masyarakat ?? 0,
                'kondisi_serangan' => $request->kondisi_serangan,
            ]);

            return redirect()->route('dashboard')->with('success', 'Data pengamatan lapangan berhasil dikirim dan disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit(Pengamatan $pengamatan)
    {
        $user = Auth::user();
        if ($user->role === 'popt' && $pengamatan->uppt_id !== $user->uppt_id) {
            abort(403, 'Anda tidak diizinkan mengubah data wilayah lain.');
        }

        $komoditas = Komoditas::orderBy('nama_komoditas')->get();
        $opts = Opt::orderBy('nama_opt')->get();

        return view('pengamatan.edit', compact('pengamatan', 'komoditas', 'opts'));
    }

    public function update(Request $request, Pengamatan $pengamatan)
    {
        $user = Auth::user();
        if ($user->role === 'popt' && $pengamatan->uppt_id !== $user->uppt_id) {
            abort(403, 'Anda tidak diizinkan mengubah data wilayah lain.');
        }

        $request->validate([
            'tanggal_pengamatan' => 'required|date',
            'komoditas_id' => 'required|exists:komoditas,id',
            'opt_id' => 'required|exists:opts,id',
            'luas_komoditi_ha' => 'required|numeric|min:0',
            'serangan_ringan' => 'nullable|numeric|min:0',
            'serangan_sedang' => 'nullable|numeric|min:0',
            'serangan_berat' => 'nullable|numeric|min:0',
            'kendali_apbd_kab' => 'nullable|numeric|min:0',
            'kendali_apbd_prov' => 'nullable|numeric|min:0',
            'kendali_apbn' => 'nullable|numeric|min:0',
            'kendali_masyarakat' => 'nullable|numeric|min:0',
            'kondisi_serangan' => 'nullable|string|max:255',
        ]);

        try {
            $ringan = $request->serangan_ringan ?? 0;
            $sedang = $request->serangan_sedang ?? 0;
            $berat = $request->serangan_berat ?? 0;
            $jumlah_serangan = $ringan + $sedang + $berat;

            $pengamatan->update([
                'tanggal_pengamatan' => $request->tanggal_pengamatan,
                'komoditas_id' => $request->komoditas_id,
                'opt_id' => $request->opt_id,
                'luas_komoditi_ha' => $request->luas_komoditi_ha,
                'serangan_ringan' => $ringan,
                'serangan_sedang' => $sedang,
                'serangan_berat' => $berat,
                'serangan_jumlah' => $jumlah_serangan,
                'kendali_apbd_kab' => $request->kendali_apbd_kab ?? 0,
                'kendali_apbd_prov' => $request->kendali_apbd_prov ?? 0,
                'kendali_apbn' => $request->kendali_apbn ?? 0,
                'kendali_masyarakat' => $request->kendali_masyarakat ?? 0,
                'kondisi_serangan' => $request->kondisi_serangan,
            ]);

            return redirect()->route('dashboard')->with('success', 'Data pengamatan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy(Pengamatan $pengamatan)
    {
        $user = Auth::user();
        if ($user->role === 'popt' && $pengamatan->uppt_id !== $user->uppt_id) {
            abort(403, 'Anda tidak diizinkan menghapus data wilayah lain.');
        }

        try {
            $pengamatan->delete();
            return redirect()->route('dashboard')->with('success', 'Data pengamatan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
