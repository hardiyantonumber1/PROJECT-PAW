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
        // Peran pengawas (ULTKSP & Pimpinan) memantau seluruh aspirasi —
        // sama dengan lingkup daftar yang diberikan scopeTerlihatOleh().
        if ($user->isAdmin() || $user->isUltksp() || $user->isPimpinan()) {
            return true;
        }

        if ($user->isPetugas()) {
            return $user->unit_id !== null && $user->unit_id === $aspirasi->unit_id;
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
        if (! $aspirasi->status->berjalan()) {
            return false;
        }

        // Peran pengawas boleh mencatat tindak lanjut lintas unit (audit).
        if ($user->isAdminProdi() || $user->isSuperAdmin() || $user->isUltksp() || $user->isPimpinan()) {
            return true;
        }

        return $user->isPetugas()
            && $user->unit_id !== null
            && $user->unit_id === $aspirasi->unit_id;
    }

    /** Jejak audit boleh dibaca siapa pun yang boleh melihat aspirasinya. */
    public function lihatRiwayat(Pengguna $user, Aspirasi $aspirasi): bool
    {
        return $this->view($user, $aspirasi);
    }
}
