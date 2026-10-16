<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengamatanRequest;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use App\Services\PengamatanHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PengamatanController extends Controller
{
    public function create()
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

        $summary = $history->previousSummary([
            ...$validated,
            'uppt_id' => $upptId,
        ], $pengamatanId);

        return response()->json(['summary' => $summary]);
    }

    public function store(PengamatanRequest $request)
    {
        $user = Auth::user();

        if (! $user->uppt_id) {
            return back()->withInput()->with('error', 'Gagal: Akun Anda belum memiliki penempatan wilayah (UPPT). Hubungi Admin.');
        }

        $validated = $request->validated();
        $ringan = (float) ($validated['serangan_ringan'] ?? 0);
        $berat = (float) ($validated['serangan_berat'] ?? 0);

        Pengamatan::create([
            ...$validated,
            'user_id' => $user->id,
            'uppt_id' => $user->uppt_id,
            'serangan_ringan' => $ringan,
            'serangan_berat' => $berat,
            'serangan_jumlah' => $ringan + $berat,
            'kendali_apbd_kab' => $validated['kendali_apbd_kab'] ?? 0,
            'kendali_apbd_prov' => $validated['kendali_apbd_prov'] ?? 0,
            'kendali_apbn' => $validated['kendali_apbn'] ?? 0,
            'kendali_masyarakat' => $validated['kendali_masyarakat'] ?? 0,
        ]);

        return redirect()->route('dashboard')->with('success', 'Data pengamatan lapangan berhasil dikirim dan disimpan!');
    }

    public function edit(Pengamatan $pengamatan)
    {
        Gate::authorize('update', $pengamatan);

        $komoditas = Komoditas::orderBy('nama_komoditas')->get();
        $opts = Opt::with('jenisOpts')->orderBy('nama_opt')->get();

        $user = Auth::user();
        $user->loadMissing('uppt.kecamatans');
        $kecamatans = $user->uppt ? $user->uppt->kecamatans : collect();

        $history = app(PengamatanHistoryService::class);
        $historySummary = $history->previousSummary($pengamatan->toArray(), $pengamatan->id);

        return view('pengamatan.edit', compact('pengamatan', 'komoditas', 'opts', 'kecamatans', 'historySummary'));
    }

    public function update(PengamatanRequest $request, Pengamatan $pengamatan)
    {
        Gate::authorize('update', $pengamatan);

        $validated = $request->validated();
        $ringan = (float) ($validated['serangan_ringan'] ?? 0);
        $berat = (float) ($validated['serangan_berat'] ?? 0);

        $pengamatan->update([
            ...$validated,
            'serangan_ringan' => $ringan,
            'serangan_berat' => $berat,
            'serangan_jumlah' => $ringan + $berat,
            'kendali_apbd_kab' => $validated['kendali_apbd_kab'] ?? 0,
            'kendali_apbd_prov' => $validated['kendali_apbd_prov'] ?? 0,
            'kendali_apbn' => $validated['kendali_apbn'] ?? 0,
            'kendali_masyarakat' => $validated['kendali_masyarakat'] ?? 0,
        ]);

        return redirect()->route('dashboard')->with('success', 'Data pengamatan berhasil diperbarui!');
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
