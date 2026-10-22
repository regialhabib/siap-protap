<?php

namespace App\Http\Controllers;

use App\Http\Requests\KomoditasRequest;
use App\Models\Komoditas;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KomoditasController extends Controller
{
    public function index(): View
    {
        $komoditas = Komoditas::latest()->get();

        return view('master.komoditas.index', compact('komoditas'));
    }

    public function store(KomoditasRequest $request): RedirectResponse
    {
        Komoditas::create($request->validated());

        return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil ditambahkan!');
    }

    public function update(KomoditasRequest $request, Komoditas $komoditas): RedirectResponse
    {
        $komoditas->update($request->validated());

        return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil diperbarui!');
    }

    public function destroy(Komoditas $komoditas): RedirectResponse
    {
        $komoditas->delete();

        return redirect()->route('komoditas.index')->with('success', 'Komoditas berhasil dihapus!');
    }
}
