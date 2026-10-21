<?php

namespace App\Http\Requests;

use App\Models\JenisOpt;
use App\Models\Kecamatan;
use App\Models\Pengamatan;
use App\Services\PengamatanHistoryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PengamatanRequest extends FormRequest
{
    /**
     * Cek hak akses sebelum validasi agar pesan validasi tidak membocorkan rantai milik UPPT lain.
     */
    public function authorize(): bool
    {
        $pengamatan = $this->route('pengamatan');

        return $pengamatan instanceof Pengamatan
            ? $this->user()->can('update', $pengamatan)
            : $this->user()->can('create', Pengamatan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $upptId = $this->upptId();

        return [
            'tanggal_pengamatan' => 'required|date',
            'komoditas_id' => 'required|exists:komoditas,id',
            'opt_id' => 'required|exists:opts,id',
            'jenis_opt_id' => [
                Rule::requiredIf(fn () => $this->filled('opt_id') && JenisOpt::where('opt_id', $this->input('opt_id'))->exists()),
                'nullable',
                Rule::exists('jenis_opts', 'id')->where('opt_id', $this->input('opt_id')),
            ],
            'kecamatan_id' => [
                Rule::requiredIf(fn () => $upptId !== null && Kecamatan::where('uppt_id', $upptId)->exists()),
                'nullable',
                Rule::exists('kecamatans', 'id')->where('uppt_id', $upptId),
            ],
            'luas_komoditi_ha' => 'required|numeric|decimal:0,2|min:0',
            'serangan_ringan' => 'nullable|numeric|decimal:0,2|min:0',
            'serangan_berat' => 'nullable|numeric|decimal:0,2|min:0',
            'kendali_apbd_kab' => 'nullable|numeric|decimal:0,2|min:0',
            'kendali_apbd_prov' => 'nullable|numeric|decimal:0,2|min:0',
            'kendali_apbn' => 'nullable|numeric|decimal:0,2|min:0',
            'kendali_masyarakat' => 'nullable|numeric|decimal:0,2|min:0',
            'cara_pengendalian' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Tolak perubahan yang membuat kondisi serangan rantai menjadi minus.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || $this->upptId() === null) {
                    return;
                }

                $violation = app(PengamatanHistoryService::class)->firstNegativeCondition(
                    $this->route('pengamatan'),
                    $this->observationAttributes(),
                );

                if ($violation === null) {
                    return;
                }

                $maksimal = PengamatanHistoryService::hektare($violation['maksimal']);
                $validator->errors()->add('pengendalian', $violation['is_candidate']
                    ? "Total pengendalian melebihi luas serangan yang ada. Maksimal {$maksimal} Ha (kondisi sebelumnya + tambah serangan)."
                    : 'Perubahan ini membuat kondisi serangan pada pengamatan tanggal '.$violation['tanggal']->translatedFormat('d F Y')." menjadi minus (pengendalian maksimal di tanggal tersebut {$maksimal} Ha).");
            },
        ];
    }

    /**
     * Data pengamatan siap simpan: serangan & pengendalian kosong menjadi 0, jumlah serangan dihitung.
     *
     * @return array<string, mixed>
     */
    public function observationAttributes(): array
    {
        $validated = $this->validated();
        $ringan = (float) ($validated['serangan_ringan'] ?? 0);
        $berat = (float) ($validated['serangan_berat'] ?? 0);

        return [
            ...$validated,
            'uppt_id' => $this->upptId(),
            'kecamatan_id' => $validated['kecamatan_id'] ?? null,
            'jenis_opt_id' => $validated['jenis_opt_id'] ?? null,
            'serangan_ringan' => $ringan,
            'serangan_berat' => $berat,
            'serangan_jumlah' => $ringan + $berat,
            'kendali_apbd_kab' => $validated['kendali_apbd_kab'] ?? 0,
            'kendali_apbd_prov' => $validated['kendali_apbd_prov'] ?? 0,
            'kendali_apbn' => $validated['kendali_apbn'] ?? 0,
            'kendali_masyarakat' => $validated['kendali_masyarakat'] ?? 0,
        ];
    }

    /**
     * UPPT pengamatan: milik pengamatan yang diedit, atau UPPT petugas untuk entri baru.
     */
    private function upptId(): ?int
    {
        $pengamatan = $this->route('pengamatan');

        return $pengamatan instanceof Pengamatan ? $pengamatan->uppt_id : $this->user()?->uppt_id;
    }
}
