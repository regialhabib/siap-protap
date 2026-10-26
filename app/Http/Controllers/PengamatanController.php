<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengamatanRequest;
use App\Models\Kecamatan;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use App\Services\PengamatanHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengamatanController extends Controller
{
    public function create(): View
    {
        // Otorisasi melalui Policy
        Gate::authorize('create', Pengamatan::class);

        // Ambil master data untuk dropdown
        $komoditas = Komoditas::orderBy('nama_komoditas')->get();
        $opts = Opt::with('jenisOpts')->orderBy('nama_opt')->get();

        $user = Auth::user();
        $user->loadMissing('uppt.kecamatans');
        $kecamatans = $user->uppt ? $user->uppt->kecamatans : collect();

        return view('pengamatan.create', compact('komoditas', 'opts', 'kecamatans'));
    }

    public function history(Request $request, PengamatanHistoryService $history): JsonResponse
    {
        $user = Auth::user();
        $pengamatanId = $request->integer('pengamatan_id') ?: null;

        if ($pengamatanId !== null) {
            $current = Pengamatan::findOrFail($pengamatanId);
            Gate::authorize('update', $current);
            $upptId = $current->uppt_id;
        } else {
            Gate::authorize('create', Pengamatan::class);
            abort_if(! $user->uppt_id, 403);
            $upptId = $user->uppt_id;
        }

        $validated = $request->validate([
            'tanggal_pengamatan' => ['required', 'date'],
            'komoditas_id' => ['required', 'exists:komoditas,id'],
            'opt_id' => ['required', 'exists:opts,id'],
            'jenis_opt_id' => ['nullable', Rule::exists('jenis_opts', 'id')->where('opt_id', $request->input('opt_id'))],
            'kecamatan_id' => ['nullable', Rule::exists('kecamatans', 'id')->where('uppt_id', $upptId)],
        ]);

        $attributes = [...$validated, 'uppt_id' => $upptId];
        $context = $history->chainContext($attributes, $pengamatanId);

        return response()->json([
            'summary' => $history->previousSummary($attributes, $pengamatanId),
            'terakhir' => $context['terakhir']?->tanggal_pengamatan->translatedFormat('d F Y'),
            'bulan_sama' => $context['bulan_sama']->map(fn (Pengamatan $pengamatan): array => [
                'tanggal' => $pengamatan->tanggal_pengamatan->translatedFormat('d F Y'),
                'edit_url' => $user->can('update', $pengamatan) ? route('pengamatan.edit', $pengamatan) : null,
            ])->values(),
        ]);
    }

    public function store(PengamatanRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->uppt_id) {
            return back()->withInput()->with('error', 'Gagal: Akun Anda belum memiliki penempatan wilayah (UPPT). Hubungi Admin.');
        }

        Pengamatan::create([
            ...$request->observationAttributes(),
            'user_id' => $user->id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Data pengamatan lapangan berhasil dikirim dan disimpan!');
    }

    public function edit(Pengamatan $pengamatan, PengamatanHistoryService $history): View
    {
        Gate::authorize('update', $pengamatan);

        $pengamatan->loadMissing('uppt');
        $komoditas = Komoditas::orderBy('nama_komoditas')->get();
        $opts = Opt::with('jenisOpts')->orderBy('nama_opt')->get();
        $kecamatans = Kecamatan::where('uppt_id', $pengamatan->uppt_id)->orderBy('nama_kecamatan')->get();
        $historySummary = $history->previousSummary([
            ...$pengamatan->only(['uppt_id', 'kecamatan_id', 'komoditas_id', 'opt_id', 'jenis_opt_id']),
            'tanggal_pengamatan' => $pengamatan->tanggal_pengamatan->toDateString(),
        ], $pengamatan->id);

        return view('pengamatan.edit', compact('pengamatan', 'komoditas', 'opts', 'kecamatans', 'historySummary'));
    }

    public function update(PengamatanRequest $request, Pengamatan $pengamatan): RedirectResponse
    {
        Gate::authorize('update', $pengamatan);

        $pengamatan->update($request->observationAttributes());

        return redirect()->route('dashboard')->with('success', 'Data pengamatan berhasil diperbarui!');
    }

    public function destroy(Pengamatan $pengamatan, PengamatanHistoryService $history): RedirectResponse
    {
        Gate::authorize('delete', $pengamatan);

        $violation = $history->firstInvalidCondition($pengamatan, null);
        if ($violation !== null) {
            $tanggal = $violation['tanggal']->translatedFormat('d F Y');

            return back()->with('error', $violation['masalah'] === 'minus'
                ? "Data tidak dapat dihapus karena kondisi serangan pada pengamatan tanggal {$tanggal} akan menjadi minus. Ubah pengendalian pada tanggal tersebut terlebih dahulu."
                : "Data tidak dapat dihapus karena kondisi serangan pada pengamatan tanggal {$tanggal} akan melebihi luas komoditinya. Ubah data pada tanggal tersebut terlebih dahulu.");
        }

        $pengamatan->delete();

        return redirect()->route('dashboard')->with('success', 'Data pengamatan berhasil dihapus!');
    }
}
