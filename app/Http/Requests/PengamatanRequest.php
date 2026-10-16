<?php

namespace App\Http\Requests;

use App\Models\JenisOpt;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PengamatanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi sudah dilakukan di Controller, jadi ini bisa di-set true
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'tanggal_pengamatan' => 'required|date',
            'komoditas_id' => 'required|exists:komoditas,id',
            'opt_id' => 'required|exists:opts,id',
            'jenis_opt_id' => [
                Rule::requiredIf(fn () => $this->filled('opt_id') && JenisOpt::where('opt_id', $this->input('opt_id'))->exists()),
                'nullable',
                Rule::exists('jenis_opts', 'id')->where('opt_id', $this->input('opt_id')),
            ],
            'luas_komoditi_ha' => 'required|numeric|min:0',
            'serangan_ringan' => 'nullable|numeric|min:0',
            'serangan_berat' => 'nullable|numeric|min:0',
            'kendali_apbd_kab' => 'nullable|numeric|min:0',
            'kendali_apbd_prov' => 'nullable|numeric|min:0',
            'kendali_apbn' => 'nullable|numeric|min:0',
            'kendali_masyarakat' => 'nullable|numeric|min:0',
            'cara_pengendalian' => ['nullable', 'string', 'max:2000'],
        ];

        $user = Auth::user();
        if ($user && $user->uppt_id && $user->uppt->kecamatans()->count() > 0) {
            $rules['kecamatan_id'] = ['required', Rule::exists('kecamatans', 'id')->where('uppt_id', $user->uppt_id)];
        } else {
            $rules['kecamatan_id'] = 'nullable';
        }

        return $rules;
    }
}
