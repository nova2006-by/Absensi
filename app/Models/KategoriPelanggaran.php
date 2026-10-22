<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPelanggaran extends Model
{
    protected $table = 'kategori_pelanggaran';

    protected $fillable = [
        'nama',
        'poin',
    ];

    protected $casts = [
        'poin' => 'integer',
    ];

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class);
    }
}
