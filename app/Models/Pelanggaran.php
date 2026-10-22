<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelanggaran extends Model
{
    protected $table = 'pelanggaran';

    protected $fillable = [
        'siswa_id',
        'kategori_pelanggaran_id',
        'poin',
        'tanggal',
        'keterangan',
        'gambar',
        'ditambahkan_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'poin' => 'integer',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_pelanggaran_id');
    }

    public function ditambahkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditambahkan_oleh');
    }
}
