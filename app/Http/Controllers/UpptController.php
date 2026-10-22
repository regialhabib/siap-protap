<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpptRequest;
use App\Models\Kabupaten;
use App\Models\Uppt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UpptController extends Controller
{
    public function index(): View
    {
        $uppts = Uppt::with(['kabupaten', 'kecamatans'])->latest()->get();
        $kabupatens = Kabupaten::orderBy('nama_kabupaten')->get();

        return view('master.uppt.index', compact('uppts', 'kabupatens'));
    }

    public function store(UpptRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $uppt = Uppt::create($request->safe()->only(['nama_uppt', 'kabupaten_id']));
            $this->syncKecamatans($uppt, $request->validated('kecamatans', []));
        });

        return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil ditambahkan!');
    }

    public function update(UpptRequest $request, Uppt $uppt): RedirectResponse
    {
        DB::transaction(function () use ($request, $uppt): void {
            $uppt->update($request->safe()->only(['nama_uppt', 'kabupaten_id']));
            $this->syncKecamatans($uppt, $request->validated('kecamatans', []));
        });

        return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil diperbarui!');
    }

    public function destroy(Uppt $uppt): RedirectResponse
    {
        $uppt->delete();

        return redirect()->route('uppt.index')->with('success', 'Data UPPT berhasil dihapus!');
    }

    public function getKecamatans(Uppt $uppt): JsonResponse
    {
        $kecamatans = $uppt->kecamatans()->orderBy('nama_kecamatan')->get();

        return response()->json($kecamatans);
    }

    /**
     * Sinkron per ID agar kecamatan yang dipertahankan tidak dihapus beserta pengamatannya.
     *
     * @param  array<int, array{id?: int|string|null, nama_kecamatan?: string|null}>  $kecamatans
     */
    private function syncKecamatans(Uppt $uppt, array $kecamatans): void
    {
        $retainedKecamatanIds = [];
        foreach ($kecamatans as $kecamatanData) {
            $namaKecamatan = trim($kecamatanData['nama_kecamatan'] ?? '');
            if ($namaKecamatan === '') {
                continue;
            }

            if (! empty($kecamatanData['id'])) {
                $uppt->kecamatans()->whereKey($kecamatanData['id'])->update(['nama_kecamatan' => $namaKecamatan]);
                $retainedKecamatanIds[] = (int) $kecamatanData['id'];
            } else {
                $retainedKecamatanIds[] = $uppt->kecamatans()->create(['nama_kecamatan' => $namaKecamatan])->id;
            }
        }

        $uppt->kecamatans()->whereNotIn('id', $retainedKecamatanIds)->delete();
    }
}
