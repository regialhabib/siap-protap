<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admin');
    }

    /**
     * Password wajib saat membuat akun, opsional saat edit. Status hanya diubah lewat form edit.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->route('pengguna') instanceof User;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->route('pengguna'))],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'status' => Rule::when($isUpdate, ['required', Rule::enum(UserStatus::class)], ['exclude']),
            'uppt_id' => ['nullable', 'required_if:role,'.UserRole::Popt->value, 'exists:uppts,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'uppt_id.required_if' => 'Wilayah UPPT wajib dipilih untuk petugas POPT.',
        ];
    }

    /**
     * Data akun siap simpan: admin tidak terikat UPPT, akun baru langsung aktif, password hanya jika diisi.
     *
     * @return array<string, mixed>
     */
    public function userAttributes(): array
    {
        $validated = $this->validated();
        $role = UserRole::from($validated['role']);

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $role,
            'status' => $validated['status'] ?? UserStatus::Aktif->value,
            'uppt_id' => $role === UserRole::Admin ? null : ($validated['uppt_id'] ?? null),
        ];

        if (filled($validated['password'] ?? null)) {
            $attributes['password'] = $validated['password'];
        }

        return $attributes;
    }
}
