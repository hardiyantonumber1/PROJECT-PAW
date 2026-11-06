<?php

namespace App\Policies;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\Pengguna;

/**
 * Aturan akses per baris aspirasi (RB-01, RB-03).
 * Mahasiswa: hanya miliknya & hanya saat status Diajukan.
 * Petugas: hanya aspirasi pada unitnya.
 */
class AspirasiPolicy
{
    /** Admin boleh melihat semua; petugas unit terkait; mahasiswa pemilik. */
    public function view(Pengguna $user, Aspirasi $aspirasi): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPetugas()) {
            return $user->unit_id === $aspirasi->unit_id;
        }

        return $aspirasi->pengguna_id === $user->id;
    }

    /** RB-03: pengajuan hanya boleh diubah selama status Diajukan. */
    public function update(Pengguna $user, Aspirasi $aspirasi): bool
    {
        return $aspirasi->pengguna_id === $user->id
            && $aspirasi->status === StatusAspirasi::Diajukan;
    }

    public function delete(Pengguna $user, Aspirasi $aspirasi): bool
    {
        return $this->update($user, $aspirasi);
    }

    /** RB-01: hanya petugas unit terkait yang boleh mengubah status. */
    public function tindakLanjut(Pengguna $user, Aspirasi $aspirasi): bool
    {
        return $user->isPetugas()
            && $user->unit_id === $aspirasi->unit_id
            && $aspirasi->status->berjalan();
    }
}
