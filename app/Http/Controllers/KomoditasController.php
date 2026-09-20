<?php

namespace App\Http\Controllers;

use App\Models\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KomoditasController extends Controller
{
    public function index()
    {
        // Hanya Admin yang boleh akses ini nanti (bisa ditambahkan middleware)
        if(Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mengakses Master Data.');
        }

        $komoditas = Komoditas::orderBy('nama_komoditas')->paginate(10);
        return view('master.komoditas.index', compact('komoditas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_komoditas' => 'required|string|max:255|unique:komoditas',
        ]);

        try {
            Komoditas::create([
                'nama_komoditas' => $request->nama_komoditas
            ]);

            return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Komoditas $komodita)
    {
        $request->validate([
            'nama_komoditas' => 'required|string|max:255|unique:komoditas,nama_komoditas,' . $komodita->id,
        ]);

        try {
            $komodita->update([
                'nama_komoditas' => $request->nama_komoditas
            ]);

            return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Komoditas $komodita)
    {
        try {
            $komodita->delete();
            return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
