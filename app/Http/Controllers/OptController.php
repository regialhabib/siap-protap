<?php

namespace App\Http\Controllers;

use App\Models\Opt;
use Illuminate\Http\Request;

class OptController extends Controller
{
    public function index()
    {
        if(auth()->user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mengakses Master Data.');
        }

        $opts = Opt::orderBy('nama_opt')->paginate(10);
        return view('master.opt.index', compact('opts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_opt' => 'required|string|max:255|unique:opts',
        ]);

        try {
            Opt::create([
                'nama_opt' => $request->nama_opt
            ]);

            return redirect()->route('opt.index')->with('success', 'OPT berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Opt $opt)
    {
        $request->validate([
            'nama_opt' => 'required|string|max:255|unique:opts,nama_opt,' . $opt->id,
        ]);

        try {
            $opt->update([
                'nama_opt' => $request->nama_opt
            ]);

            return redirect()->route('opt.index')->with('success', 'OPT berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Opt $opt)
    {
        try {
            $opt->delete();
            return redirect()->route('opt.index')->with('success', 'OPT berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
