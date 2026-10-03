<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Uppt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpptController extends Controller
{
    public function index()
    {
        // Mengambil data UPPT beserta relasi ke Kabupaten dan Kecamatans
        $query = Uppt::with(['kabupaten', 'kecamatans']);

        $uppts = $query->latest()->get();

        // Mengambil daftar Kabupaten untuk opsi dropdown di Modal
        $kabupatens = Kabupaten::orderBy('nama_kabupaten')->get();

        return view('master.uppt.index', compact('uppts', 'kabupatens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_uppt' => 'required|string|max:255',
            'kabupaten_id' => 'required|exists:kabupatens,id',
        ]);

        try {
            $uppt = Uppt::create([
                'nama_uppt' => $request->nama_uppt,
                'kabupaten_id' => $request->kabupaten_id,
            ]);

            if ($request->has('kecamatans')) {
                foreach (array_filter($request->kecamatans) as $nama_kecamatan) {
                    $uppt->kecamatans()->create(['nama_kecamatan' => $nama_kecamatan]);
                }
            }

            return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil ditambahkan!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function update(Request $request, Uppt $uppt)
    {
        $request->validate([
            'nama_uppt' => 'required|string|max:255',
            'kabupaten_id' => 'required|exists:kabupatens,id',
        ]);

        try {
            $uppt->update([
                'nama_uppt' => $request->nama_uppt,
                'kabupaten_id' => $request->kabupaten_id,
            ]);

            $uppt->kecamatans()->delete();
            if ($request->has('kecamatans')) {
                foreach (array_filter($request->kecamatans) as $nama_kecamatan) {
                    $uppt->kecamatans()->create(['nama_kecamatan' => $nama_kecamatan]);
                }
            }

            return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil diperbarui!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
        }
    }

    public function destroy(Uppt $uppt)
    {
        try {
            $uppt->delete();

            return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil dihapus!');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.');
        }
    }

    public function getKecamatans(Uppt $uppt): JsonResponse
    {
        $kecamatans = $uppt->kecamatans()->orderBy('nama_kecamatan')->get();

        return response()->json($kecamatans);
    }
}
