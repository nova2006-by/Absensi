<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PengajuanIzin extends Model
{
    public const JENIS_IZIN = 'izin';

    public const JENIS_SAKIT = 'sakit';

    public const SEMUA_JENIS = [
        self::JENIS_IZIN => 'Izin',
        self::JENIS_SAKIT => 'Sakit',
    ];

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    public const SEMUA_STATUS = [
        self::STATUS_MENUNGGU => 'Menunggu',
        self::STATUS_DISETUJUI => 'Disetujui',
        self::STATUS_DITOLAK => 'Ditolak',
    ];

    /** Batas durasi pengajuan agar tidak menutup seluruh semester. */
    public const MAKS_HARI = 30;

    protected $table = 'pengajuan_izin';

    protected $fillable = [
        'siswa_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'bukti',
        'status',
        'ditinjau_oleh',
        'ditinjau_at',
        'catatan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'ditinjau_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ditinjauOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }

    public function jenisLabel(): string
    {
        return self::SEMUA_JENIS[$this->jenis] ?? $this->jenis;
    }

    public function statusLabel(): string
    {
        return self::SEMUA_STATUS[$this->status] ?? $this->status;
    }

    public function isMenunggu(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    public function tanggalAkhir(): Carbon
    {
        return $this->tanggal_selesai ?? $this->tanggal_mulai;
    }

    /**
     * Daftar tanggal (Y-m-d) yang dicakup pengajuan.
     *
     * @return list<string>
     */
    public function rentangTanggal(): array
    {
        $mulai = $this->tanggal_mulai->copy()->startOfDay();
        $akhir = $this->tanggalAkhir()->copy()->startOfDay();

        $tanggal = [];

        for ($t = $mulai->copy(); $t->lte($akhir); $t->addDay()) {
            $tanggal[] = $t->toDateString();
        }

        return $tanggal;
    }
}
