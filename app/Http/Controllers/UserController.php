<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Requests\UserRequest;
use App\Models\Uppt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('uppt')->whereKeyNot(Auth::id())->latest()->get();
        $uppts = Uppt::orderBy('nama_uppt')->get();

        return view('master.user.index', compact('users', 'uppts'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->userAttributes());

        return redirect()->route('pengguna.index')->with('success', 'Akun petugas berhasil dibuat!');
    }

    public function update(UserRequest $request, User $pengguna): RedirectResponse
    {
        $pengguna->update($request->userAttributes());

        return redirect()->route('pengguna.index')->with('success', 'Akun petugas berhasil diperbarui!');
    }

    public function destroy(User $pengguna): RedirectResponse
    {
        if ($pengguna->is(Auth::user())) {
            return redirect()->route('pengguna.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $pengguna->delete();

        return redirect()->route('pengguna.index')->with('success', 'Akun petugas berhasil dihapus!');
    }

    public function updateStatus(Request $request, User $pengguna): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(UserStatus::class)],
        ]);

        $pengguna->update($validated);

        return back()->with('success', 'Status akun berhasil diperbarui menjadi '.ucfirst($validated['status']).'!');
    }
}
