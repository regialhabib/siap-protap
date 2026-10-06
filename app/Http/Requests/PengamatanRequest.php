<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'luas_komoditi_ha' => 'required|numeric|min:0',
            'serangan_ringan' => 'nullable|numeric|min:0',
            'serangan_sedang' => 'nullable|numeric|min:0',
            'serangan_berat' => 'nullable|numeric|min:0',
            'kendali_apbd_kab' => 'nullable|numeric|min:0',
            'kendali_apbd_prov' => 'nullable|numeric|min:0',
            'kendali_apbn' => 'nullable|numeric|min:0',
            'kendali_masyarakat' => 'nullable|numeric|min:0',
            'kondisi_status' => 'nullable|string|in:Bertambah,Tetap,Berkurang',
            'kondisi_luas' => 'nullable|numeric|min:0',
        ];

        $user = Auth::user();
        if ($user && $user->uppt_id && $user->uppt->kecamatans()->count() > 0) {
            $rules['kecamatan_id'] = 'required|exists:kecamatans,id';
        } else {
            $rules['kecamatan_id'] = 'nullable';
        }

        return $rules;
    }
}
