<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'kelas_id',
        'nis',
        'nisn',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function pengajuanAbsensi(): HasMany
    {
        return $this->hasMany(AbsensiPengajuan::class);
    }

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class);
    }

    public function totalPoin(): int
    {
        return (int) $this->pelanggaran()->sum('poin');
    }

    public function absensiHariIni(): ?Absensi
    {
        return $this->absensi()->whereDate('tanggal', today())->first();
    }
}
