<?php

namespace App\Http\Controllers;

use App\Models\JenisOpt;
use App\Models\Opt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OptController extends Controller
{
    public function index(): View
    {
        $query = Opt::query();

        $opts = $query->with('jenisOpts')->latest()->get();

        return view('master.opt.index', compact('opts'));
    }

    public function storeJenis(Request $request, Opt $opt): RedirectResponse
    {
        $validated = $request->validate([
            'nama_jenis_opt' => 'required|string|max:255|unique:jenis_opts,nama_jenis_opt,NULL,id,opt_id,'.$opt->id,
        ], [
            'nama_jenis_opt.unique' => 'Jenis turunan tersebut sudah terdaftar untuk OPT ini.',
        ]);
        $opt->jenisOpts()->create($validated);

        return redirect()->route('opt.index')->with('success', 'Jenis turunan OPT berhasil ditambahkan.');
    }

    public function updateJenis(Request $request, Opt $opt, JenisOpt $jenisOpt): RedirectResponse
    {
        abort_unless($jenisOpt->opt_id === $opt->id, 404);
        $validated = $request->validate([
            'nama_jenis_opt' => 'required|string|max:255|unique:jenis_opts,nama_jenis_opt,'.$jenisOpt->id.',id,opt_id,'.$opt->id,
        ], [
            'nama_jenis_opt.unique' => 'Jenis turunan tersebut sudah terdaftar untuk OPT ini.',
        ]);
        $jenisOpt->update($validated);

        return redirect()->route('opt.index')->with('success', 'Jenis turunan OPT berhasil diperbarui.');
    }

    public function destroyJenis(Opt $opt, JenisOpt $jenisOpt): RedirectResponse
    {
        abort_unless($jenisOpt->opt_id === $opt->id, 404);

        if ($jenisOpt->pengamatans()->exists()) {
            return redirect()->route('opt.index')->with('error', "Jenis OPT \"{$jenisOpt->nama_jenis_opt}\" tidak dapat dihapus karena masih dipakai di data pengamatan.");
        }

        $jenisOpt->delete();

        return redirect()->route('opt.index')->with('success', 'Jenis turunan OPT berhasil dihapus.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_opt' => 'required|string|max:255|unique:opts',
            'jenis_opts' => 'nullable|array|max:20',
            'jenis_opts.*' => 'nullable|string|max:255|distinct:ignore_case',
        ], [
            'jenis_opts.*.distinct' => 'Nama jenis turunan OPT tidak boleh duplikat.',
        ]);

        DB::transaction(function () use ($validated): void {
            $opt = Opt::create(['nama_opt' => $validated['nama_opt']]);
            $jenisOpts = array_values(array_filter(
                $validated['jenis_opts'] ?? [],
                fn (?string $namaJenisOpt): bool => filled($namaJenisOpt),
            ));

            foreach ($jenisOpts as $namaJenisOpt) {
                $opt->jenisOpts()->create(['nama_jenis_opt' => $namaJenisOpt]);
            }
        });

        return redirect()->route('opt.index')->with('success', 'OPT dan jenis turunannya berhasil ditambahkan!');
    }

    public function update(Request $request, Opt $opt): RedirectResponse
    {
        $validated = $request->validate([
            'nama_opt' => 'required|string|max:255|unique:opts,nama_opt,'.$opt->id,
            'jenis_opts_present' => 'sometimes|accepted',
            'jenis_opts' => 'nullable|array|max:20',
            'jenis_opts.*.id' => ['nullable', 'integer', Rule::exists('jenis_opts', 'id')->where('opt_id', $opt->id)],
            'jenis_opts.*.nama_jenis_opt' => 'nullable|string|max:255|distinct:ignore_case',
        ], [
            'jenis_opts.*.nama_jenis_opt.distinct' => 'Nama jenis turunan OPT tidak boleh duplikat.',
        ]);

        $usedJenisOpt = $this->usedJenisOptToDelete($opt, $validated);
        if ($usedJenisOpt !== null) {
            return back()->withInput()->with('error', "Jenis OPT \"{$usedJenisOpt->nama_jenis_opt}\" tidak dapat dihapus karena masih dipakai di data pengamatan.");
        }

        DB::transaction(function () use ($opt, $validated): void {
            $opt->update(['nama_opt' => $validated['nama_opt']]);

            if (! array_key_exists('jenis_opts_present', $validated)) {
                return;
            }

            $retainedJenisOptIds = [];
            foreach ($validated['jenis_opts'] ?? [] as $jenisOptData) {
                $namaJenisOpt = trim($jenisOptData['nama_jenis_opt'] ?? '');
                if ($namaJenisOpt === '') {
                    continue;
                }

                if (! empty($jenisOptData['id'])) {
                    $opt->jenisOpts()->whereKey($jenisOptData['id'])->update(['nama_jenis_opt' => $namaJenisOpt]);
                    $retainedJenisOptIds[] = (int) $jenisOptData['id'];
                } else {
                    $jenisOpt = $opt->jenisOpts()->create(['nama_jenis_opt' => $namaJenisOpt]);
                    $retainedJenisOptIds[] = $jenisOpt->id;
                }
            }

            $jenisOptsToDelete = $opt->jenisOpts();
            if ($retainedJenisOptIds !== []) {
                $jenisOptsToDelete->whereNotIn('id', $retainedJenisOptIds);
            }
            $jenisOptsToDelete->delete();
        });

        return redirect()->route('opt.index')->with('success', 'OPT dan jenis turunannya berhasil diperbarui!');
    }

    /**
     * Jenis OPT yang akan terhapus oleh form edit tetapi masih dipakai pengamatan.
     *
     * @param  array{jenis_opts_present?: mixed, jenis_opts?: array<int, array{id?: int|string|null, nama_jenis_opt?: string|null}>}  $validated
     */
    private function usedJenisOptToDelete(Opt $opt, array $validated): ?JenisOpt
    {
        if (! array_key_exists('jenis_opts_present', $validated)) {
            return null;
        }

        $retainedJenisOptIds = collect($validated['jenis_opts'] ?? [])
            ->filter(fn (array $jenisOptData): bool => filled($jenisOptData['id'] ?? null) && filled(trim($jenisOptData['nama_jenis_opt'] ?? '')))
            ->map(fn (array $jenisOptData): int => (int) $jenisOptData['id']);

        return $opt->jenisOpts()
            ->whereNotIn('id', $retainedJenisOptIds)
            ->whereHas('pengamatans')
            ->first();
    }

    public function destroy(Opt $opt): RedirectResponse
    {
        $opt->delete();

        return redirect()->route('opt.index')->with('success', 'OPT berhasil dihapus!');
    }
}
