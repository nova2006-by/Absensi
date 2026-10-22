<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';

    public const STATUS_HADIR = 'hadir';
    public const STATUS_TERLAMBAT = 'terlambat';
    public const STATUS_IZIN = 'izin';
    public const STATUS_SAKIT = 'sakit';
    public const STATUS_ALFA = 'alfa';

    public const SEMUA_STATUS = [
        self::STATUS_HADIR => 'Hadir',
        self::STATUS_TERLAMBAT => 'Terlambat',
        self::STATUS_IZIN => 'Izin',
        self::STATUS_SAKIT => 'Sakit',
        self::STATUS_ALFA => 'Alfa',
    ];

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'status',
        'jam_masuk',
        'jam_pulang',
        'keterangan',
        'ditandai_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ditandaiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditandai_oleh');
    }

    public function statusLabel(): string
    {
        return self::SEMUA_STATUS[$this->status] ?? $this->status;
    }

    public function isHadir(): bool
    {
        return in_array($this->status, [self::STATUS_HADIR, self::STATUS_TERLAMBAT], true);
    }
}
