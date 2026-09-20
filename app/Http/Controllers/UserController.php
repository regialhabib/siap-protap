<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Uppt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        if(Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mengakses Manajemen Pengguna.');
        }

        // Ambil data user (selain admin utama) beserta relasi UPPT
        $users = User::with('uppt')->where('id', '!=', Auth::id())->orderBy('name')->paginate(10);
        
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
            'uppt_id.required_if' => 'Wilayah UPPT wajib dipilih untuk petugas POPT.'
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'uppt_id' => $request->role === 'admin' ? null : $request->uppt_id,
            ]);

            return redirect()->route('pengguna.index')->with('success', 'Akun petugas berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, User $pengguna)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $pengguna->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,popt'],
            'uppt_id' => ['nullable', 'required_if:role,popt', 'exists:uppts,id'],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'uppt_id' => $request->role === 'admin' ? null : $request->uppt_id,
            ];

            // Update password hanya jika diisi (tidak kosong)
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $pengguna->update($data);

            return redirect()->route('pengguna.index')->with('success', 'Akun petugas berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
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
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
