<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiPengajuan extends Model
{
    public const JENIS_MASUK = 'masuk';

    public const JENIS_PULANG = 'pulang';

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'absensi_pengajuan';

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'jenis',
        'diminta_pada',
        'status',
        'ditinjau_oleh',
        'ditinjau_pada',
        'catatan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'diminta_pada' => 'datetime',
            'ditinjau_pada' => 'datetime',
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
}
