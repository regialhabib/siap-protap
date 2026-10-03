<?php

namespace App\Http\Controllers;

use App\Models\Uppt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        // Ambil data user (selain admin utama) beserta relasi UPPT
        $query = User::with('uppt')->where('id', '!=', Auth::id());

        $users = $query->latest()->get();

        // Ambil data UPPT untuk form pilihan wilayah penugasan
        $uppts = Uppt::orderBy('nama_uppt')->get();

        return view('master.user.index', compact('users', 'uppts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,popt'],
            'uppt_id' => ['nullable', 'required_if:role,popt', 'exists:uppts,id'],
        ], [
            'uppt_id.required_if' => 'Wilayah UPPT wajib dipilih untuk petugas POPT.',
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'uppt_id' => $request->role === 'admin' ? null : $request->uppt_id,
                'status' => 'aktif',
            ]);

            return redirect()->route('pengguna.index')->with('success', 'Akun petugas berhasil dibuat!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function update(Request $request, User $pengguna)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$pengguna->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,popt'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'uppt_id' => ['nullable', 'required_if:role,popt', 'exists:uppts,id'],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
                'uppt_id' => $request->role === 'admin' ? null : $request->uppt_id,
            ];

            // Update password hanya jika diisi (tidak kosong)
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $pengguna->update($data);

            return redirect()->route('pengguna.index')->with('success', 'Akun petugas berhasil diperbarui!');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
        }
    }

    public function destroy(User $pengguna)
    {
        // Jangan izinkan menghapus diri sendiri
        if ($pengguna->id === Auth::id()) {
            return redirect()->route('pengguna.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            $pengguna->delete();

            return redirect()->route('pengguna.index')->with('success', 'Akun petugas berhasil dihapus!');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.');
        }
    }

    public function updateStatus(Request $request, User $pengguna)
    {
        $request->validate([
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        try {
            $pengguna->update(['status' => $request->status]);

            return back()->with('success', 'Status akun berhasil diperbarui menjadi '.ucfirst($request->status).'!');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Terjadi kesalahan saat memperbarui status. Silakan coba lagi.');
        }
    }
}
