<?php

namespace App\Http\Requests;

use App\Models\Uppt;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admin');
    }

    /**
     * Kecamatan dikirim sebagai daftar {id?, nama_kecamatan}; id hanya boleh milik UPPT yang diedit.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $uppt = $this->route('uppt');

        return [
            'nama_uppt' => ['required', 'string', 'max:255'],
            'kabupaten_id' => ['required', 'exists:kabupatens,id'],
            'kecamatans' => ['nullable', 'array', 'max:50'],
            'kecamatans.*' => ['array'],
            'kecamatans.*.id' => ['nullable', 'integer', Rule::exists('kecamatans', 'id')->where('uppt_id', $uppt instanceof Uppt ? $uppt->id : null)],
            'kecamatans.*.nama_kecamatan' => ['nullable', 'string', 'max:255', 'distinct:ignore_case'],
        ];
    }
}
