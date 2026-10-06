<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengamatanRequest;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PengamatanController extends Controller
{
    public function create()
    {
        // Otorisasi melalui Policy
        Gate::authorize('create', Pengamatan::class);

        // Ambil master data untuk dropdown
        $komoditas = Komoditas::orderBy('nama_komoditas')->get();
        $opts = Opt::orderBy('nama_opt')->get();

        $user = Auth::user();
        $user->loadMissing('uppt.kecamatans');
        $kecamatans = $user->uppt ? $user->uppt->kecamatans : collect();

        return view('pengamatan.create', compact('komoditas', 'opts', 'kecamatans'));
    }

    public function store(PengamatanRequest $request)
    {

        $user = Auth::user();

        if (! $user->uppt_id) {
            return back()->withInput()->with('error', 'Gagal: Akun Anda belum memiliki penempatan wilayah (UPPT). Hubungi Admin.');
        }

        try {
            // Kalkulasi otomatis jumlah serangan di backend
            $ringan = $request->serangan_ringan ?? 0;
            $sedang = $request->serangan_sedang ?? 0;
            $berat = $request->serangan_berat ?? 0;
            $jumlah_serangan = $ringan + $sedang + $berat;

            $kondisi_serangan = null;
            if ($request->kondisi_status) {
                if ($request->kondisi_status === 'Tetap') {
                    $kondisi_serangan = 'Tetap';
                } else {
                    $luas = $request->kondisi_luas ?? 0;
                    $kondisi_serangan = $request->kondisi_status . ' ' . $luas . ' Ha';
                }
            }

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
                'kecamatan_id' => $request->kecamatan_id,
                'kendali_apbd_kab' => $request->kendali_apbd_kab ?? 0,
                'kendali_apbd_prov' => $request->kendali_apbd_prov ?? 0,
                'kendali_apbn' => $request->kendali_apbn ?? 0,
                'kendali_masyarakat' => $request->kendali_masyarakat ?? 0,
                'kondisi_serangan' => $kondisi_serangan,
            ]);

            return redirect()->route('dashboard')->with('success', 'Data pengamatan lapangan berhasil dikirim dan disimpan!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function edit(Pengamatan $pengamatan)
    {
        Gate::authorize('update', $pengamatan);

        $komoditas = Komoditas::orderBy('nama_komoditas')->get();
        $opts = Opt::orderBy('nama_opt')->get();

        $user = Auth::user();
        $user->loadMissing('uppt.kecamatans');
        $kecamatans = $user->uppt ? $user->uppt->kecamatans : collect();

        return view('pengamatan.edit', compact('pengamatan', 'komoditas', 'opts', 'kecamatans'));
    }

    public function update(PengamatanRequest $request, Pengamatan $pengamatan)
    {
        Gate::authorize('update', $pengamatan);

        try {
            $ringan = $request->serangan_ringan ?? 0;
            $sedang = $request->serangan_sedang ?? 0;
            $berat = $request->serangan_berat ?? 0;
            $jumlah_serangan = $ringan + $sedang + $berat;

            $kondisi_serangan = null;
            if ($request->kondisi_status) {
                if ($request->kondisi_status === 'Tetap') {
                    $kondisi_serangan = 'Tetap';
                } else {
                    $luas = $request->kondisi_luas ?? 0;
                    $kondisi_serangan = $request->kondisi_status . ' ' . $luas . ' Ha';
                }
            }

            $pengamatan->update([
                'tanggal_pengamatan' => $request->tanggal_pengamatan,
                'komoditas_id' => $request->komoditas_id,
                'opt_id' => $request->opt_id,
                'luas_komoditi_ha' => $request->luas_komoditi_ha,
                'serangan_ringan' => $ringan,
                'serangan_sedang' => $sedang,
                'serangan_berat' => $berat,
                'serangan_jumlah' => $jumlah_serangan,
                'kecamatan_id' => $request->kecamatan_id,
                'kendali_apbd_kab' => $request->kendali_apbd_kab ?? 0,
                'kendali_apbd_prov' => $request->kendali_apbd_prov ?? 0,
                'kendali_apbn' => $request->kendali_apbn ?? 0,
                'kendali_masyarakat' => $request->kendali_masyarakat ?? 0,
                'kondisi_serangan' => $kondisi_serangan,
            ]);

            return redirect()->route('dashboard')->with('success', 'Data pengamatan berhasil diperbarui!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memperbarui data. Silakan coba lagi.');
        }
    }

    public function destroy(Pengamatan $pengamatan)
    {
        Gate::authorize('delete', $pengamatan);

        try {
            $pengamatan->delete();

            return redirect()->route('dashboard')->with('success', 'Data pengamatan berhasil dihapus!');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }
    }
}
