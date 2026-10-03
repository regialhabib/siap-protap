<?php

namespace App\Http\Controllers;

use App\Models\Komoditas;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    public function index()
    {
        $query = Komoditas::query();

        $komoditas = $query->latest()->get();

        return view('master.komoditas.index', compact('komoditas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_komoditas' => 'required|string|max:255|unique:komoditas',
        ]);

        try {
            Komoditas::create([
                'nama_komoditas' => $request->nama_komoditas,
            ]);

            return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil ditambahkan!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function update(Request $request, Komoditas $komodita)
    {
        $request->validate([
            'nama_komoditas' => 'required|string|max:255|unique:komoditas,nama_komoditas,'.$komodita->id,
        ]);

        try {
            $komodita->update([
                'nama_komoditas' => $request->nama_komoditas,
            ]);

            return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil diperbarui!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
        }
    }

    public function destroy(Komoditas $komodita)
    {
        try {
            $komodita->delete();

            return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil dihapus!');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.');
        }
    }
}
