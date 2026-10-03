<?php

namespace App\Policies;

use App\Models\Pengamatan;
use App\Models\User;

class PengamatanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pengamatan $pengamatan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'popt';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pengamatan $pengamatan): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'popt' && $user->uppt_id === $pengamatan->uppt_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pengamatan $pengamatan): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'popt' && $user->uppt_id === $pengamatan->uppt_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pengamatan $pengamatan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pengamatan $pengamatan): bool
    {
        return false;
    }
}
