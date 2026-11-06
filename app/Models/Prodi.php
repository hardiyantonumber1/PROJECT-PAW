<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prodi extends Model
{
    protected $table = 'prodi';

    protected $fillable = ['kode', 'nama', 'jenjang', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'prodi_id');
    }

    public function aspirasi(): HasMany
    {
        return $this->hasMany(Aspirasi::class, 'prodi_id');
    }
}
