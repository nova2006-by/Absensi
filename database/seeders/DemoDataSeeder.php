<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\KategoriPelanggaran;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            throw new RuntimeException('Seeder data demo hanya boleh dijalankan di lingkungan nonproduksi.');
        }

        $password = 'UjiCoba#2026';

        $admin = User::firstOrCreate(
            ['email' => 'demo.admin@sekolah.test'],
            ['name' => 'Admin Uji Coba', 'password' => $password, 'role' => User::ROLE_ADMIN],
        );

        $guruList = collect(range(1, 5))->map(fn (int $nomor) => User::firstOrCreate(
            ['email' => sprintf('demo.guru%02d@sekolah.test', $nomor)],
            ['name' => sprintf('Guru Contoh %02d', $nomor), 'password' => $password, 'role' => User::ROLE_GURU],
        ));

        $bkList = collect(range(1, 4))->map(fn (int $nomor) => User::firstOrCreate(
            ['email' => sprintf('demo.bk%02d@sekolah.test', $nomor)],
            ['name' => sprintf('BK Contoh %02d', $nomor), 'password' => $password, 'role' => User::ROLE_BK],
        ));

        $kelasList = collect(range(1, 4))->map(fn (int $nomor) => Kelas::firstOrCreate(
            ['nama' => sprintf('DEMO X RPL %d', $nomor)],
            [
                'tingkat' => 'X',
                'wali_kelas_id' => $guruList[($nomor - 1) % $guruList->count()]->id,
            ],
        ));

        $siswaList = collect(range(1, 40))->map(function (int $nomor) use ($password, $kelasList): Siswa {
            $user = User::firstOrCreate(
                ['email' => sprintf('demo.siswa%03d@sekolah.test', $nomor)],
                [
                    'name' => sprintf('Siswa Contoh %03d', $nomor),
                    'password' => $password,
                    'role' => User::ROLE_SISWA,
                ],
            );

            return Siswa::firstOrCreate(
                ['nis' => sprintf('DEMO2026-%03d', $nomor)],
                [
                    'user_id' => $user->id,
                    'kelas_id' => $kelasList[intdiv($nomor - 1, 10)]->id,
                    'nisn' => sprintf('DEMO-NISN-%03d', $nomor),
                ],
            );
        });

        $kategoriList = collect([
            ['nama' => 'DEMO Terlambat masuk sekolah', 'poin' => 5],
            ['nama' => 'DEMO Tidak memakai seragam lengkap', 'poin' => 10],
            ['nama' => 'DEMO Tidak mengikuti pelajaran', 'poin' => 20],
            ['nama' => 'DEMO Membawa HP tanpa izin', 'poin' => 15],
        ])->map(fn (array $kategori) => KategoriPelanggaran::firstOrCreate(
            ['nama' => $kategori['nama']],
            ['poin' => $kategori['poin']],
        ));

        $statusContoh = [
            Absensi::STATUS_HADIR,
            Absensi::STATUS_HADIR,
            Absensi::STATUS_TERLAMBAT,
            Absensi::STATUS_IZIN,
            Absensi::STATUS_SAKIT,
            Absensi::STATUS_ALFA,
        ];

        foreach ($siswaList as $indeks => $siswa) {
            $this->simpanAbsensi($siswa, today(), $statusContoh[$indeks % count($statusContoh)], $guruList[$indeks % $guruList->count()]);
        }

        for ($bulanKe = 5; $bulanKe >= 0; $bulanKe--) {
            $tanggal = now()->startOfMonth()->subMonths($bulanKe)->addDays(4);

            foreach ($siswaList as $indeks => $siswa) {
                $status = $statusContoh[($indeks + $bulanKe) % count($statusContoh)];

                $this->simpanAbsensi($siswa, $tanggal, $status, $guruList[$indeks % $guruList->count()]);
            }

            for ($urutan = 0; $urutan < 4; $urutan++) {
                $indeksSiswa = ($bulanKe * 4 + $urutan) % $siswaList->count();
                $tanggalPelanggaran = $tanggal->copy()->addDays($urutan + 5)->toDateString();
                $kategori = $kategoriList[($bulanKe + $urutan) % $kategoriList->count()];

                Pelanggaran::firstOrCreate(
                    [
                        'siswa_id' => $siswaList[$indeksSiswa]->id,
                        'kategori_pelanggaran_id' => $kategori->id,
                        'tanggal' => $tanggalPelanggaran,
                        'keterangan' => sprintf('Data uji coba DEMO %02d-%d', $bulanKe, $urutan + 1),
                    ],
                    [
                        'poin' => $kategori->poin,
                        'ditambahkan_oleh' => $bkList[($bulanKe + $urutan) % $bkList->count()]->id,
                    ],
                );
            }
        }

        $this->command?->info('50 akun demo siap: 1 admin, 5 guru, 4 BK, dan 40 siswa.');
        $this->command?->info('Login semua akun demo menggunakan password: '.$password);
        $this->command?->info('Email admin: '.$admin->email);
        $this->command?->info('Data absensi dan pelanggaran demo dibuat untuk mengisi dashboard.');
    }

    private function simpanAbsensi(Siswa $siswa, Carbon $tanggal, string $status, User $guru): void
    {
        $hadir = in_array($status, [Absensi::STATUS_HADIR, Absensi::STATUS_TERLAMBAT], true);

        Absensi::updateOrCreate(
            ['siswa_id' => $siswa->id, 'tanggal' => $tanggal->toDateString()],
            [
                'status' => $status,
                'jam_masuk' => $hadir ? ($status === Absensi::STATUS_TERLAMBAT ? '07:45:00' : '07:10:00') : null,
                'jam_pulang' => $hadir && $tanggal->isToday() ? null : ($hadir ? '14:45:00' : null),
                'ditandai_oleh' => $guru->id,
            ],
        );
    }
}
