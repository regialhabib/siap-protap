<?php

namespace App\Http\Controllers;

use App\Models\Opt;
use Illuminate\Http\Request;

class OptController extends Controller
{
    public function index()
    {
        $query = Opt::query();

        $opts = $query->latest()->get();

        return view('master.opt.index', compact('opts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_opt' => 'required|string|max:255|unique:opts',
        ]);

        try {
            Opt::create([
                'nama_opt' => $request->nama_opt,
            ]);

            return redirect()->route('opt.index')->with('success', 'OPT berhasil ditambahkan!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function update(Request $request, Opt $opt)
    {
        $request->validate([
            'nama_opt' => 'required|string|max:255|unique:opts,nama_opt,'.$opt->id,
        ]);

        try {
            $opt->update([
                'nama_opt' => $request->nama_opt,
            ]);

            return redirect()->route('opt.index')->with('success', 'OPT berhasil diperbarui!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
        }
    }

    public function destroy(Opt $opt)
    {
        try {
            $opt->delete();

            return redirect()->route('opt.index')->with('success', 'OPT berhasil dihapus!');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.');
        }
    }
}
