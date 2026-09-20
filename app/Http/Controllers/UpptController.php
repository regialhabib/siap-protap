<?php

namespace App\Http\Controllers;

use App\Models\Uppt;
use App\Models\Kabupaten;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class UpptController extends Controller
{
    public function index()
    {
        if(Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mengakses Master Data.');
        }

        // Mengambil data UPPT beserta relasi ke Kabupaten
        $uppts = Uppt::with('kabupaten')->orderBy('nama_uppt')->paginate(10);
        
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
            Uppt::create([
                'nama_uppt' => $request->nama_uppt,
                'kabupaten_id' => $request->kabupaten_id
            ]);

            return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
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
                'kabupaten_id' => $request->kabupaten_id
            ]);

            return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Uppt $uppt)
    {
        try {
            $uppt->delete();
            return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
