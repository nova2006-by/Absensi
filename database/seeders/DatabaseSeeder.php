<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\KategoriPelanggaran;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /** Password default semua akun siap pakai. Ganti setelah login pertama kali. */
    private const PASSWORD = 'SmkNuBandar#2026';

    /**
     * Domain email sekolah belum aktif, sementara semua akun memakai
     * format Gmail (@gmail.com) agar bisa langsung dipakai.
     */
    private const DOMAIN = 'gmail.com';

    public function run(): void
    {
        if (config('app.env') === 'production') {
            throw new RuntimeException('Seeder data bawaan hanya boleh dijalankan di lingkungan nonproduksi.');
        }

        $password = Hash::make(self::PASSWORD);

        $admin = User::create([
            'name' => 'Administrator SMK NU Bandar',
            'email' => 'admin.smknubandar@'.self::DOMAIN,
            'password' => $password,
            'role' => 'admin',
            'telepon' => '081234567890',
        ]);

        $guru = User::create([
            'name' => 'Pak Budi Santoso',
            'email' => 'guru.smknubandar@'.self::DOMAIN,
            'password' => $password,
            'role' => 'guru',
            'telepon' => '081234567891',
        ]);

        $guru2 = User::create([
            'name' => 'Ibu Siti Aminah',
            'email' => 'guru2.smknubandar@'.self::DOMAIN,
            'password' => $password,
            'role' => 'guru',
            'telepon' => '081234567892',
        ]);

        $bk = User::create([
            'name' => 'Bu Rina Wulandari',
            'email' => 'bk.smknubandar@'.self::DOMAIN,
            'password' => $password,
            'role' => 'bk',
            'telepon' => '081234567893',
        ]);

        $kelasX1 = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'wali_kelas_id' => $guru->id]);
        $kelasX2 = Kelas::create(['nama' => 'X RPL 2', 'tingkat' => 'X', 'wali_kelas_id' => $guru2->id]);
        $kelasXI1 = Kelas::create(['nama' => 'XI RPL 1', 'tingkat' => 'XI', 'wali_kelas_id' => $guru->id]);

        $namaSiswa = [
            ['Ahmad Fauzi', $kelasX1], ['Budi Hartono', $kelasX1], ['Citra Dewi', $kelasX1],
            ['Dwi Prasetyo', $kelasX1], ['Eka Wulandari', $kelasX2], ['Fajar Nurhidayat', $kelasX2],
            ['Gita Puspita', $kelasX2], ['Hendra Gunawan', $kelasX2], ['Indah Permata', $kelasXI1],
            ['Joko Susilo', $kelasXI1], ['Kartika Sari', $kelasXI1], ['Lukman Hakim', $kelasXI1],
        ];

        $siswaList = collect();

        foreach ($namaSiswa as $i => [$nama, $kelas]) {
            $nomor = $i + 1;
            $user = User::create([
                'name' => $nama,
                'email' => sprintf('siswa%02d.smknubandar@', $nomor).self::DOMAIN,
                'password' => $password,
                'role' => 'siswa',
            ]);

            $siswaList->push(Siswa::create([
                'user_id' => $user->id,
                'kelas_id' => $kelas->id,
                'nis' => sprintf('2026%03d', $nomor),
                'nisn' => sprintf('009%05d', $nomor),
            ]));
        }

        $kategori = [
            ['nama' => 'Terlambat masuk sekolah', 'poin' => 5],
            ['nama' => 'Tidak memakai seragam lengkap', 'poin' => 10],
            ['nama' => 'Membolos / tidak mengikuti pelajaran', 'poin' => 20],
            ['nama' => 'Membawa HP tanpa izin', 'poin' => 15],
            ['nama' => 'Merokok di lingkungan sekolah', 'poin' => 30],
            ['nama' => 'Berkelahi', 'poin' => 50],
            ['nama' => 'Tidak mengerjakan tugas', 'poin' => 5],
        ];

        $kategoriModels = collect($kategori)->map(fn ($k) => KategoriPelanggaran::create($k));

        // Absensi 10 hari kerja terakhir agar rekap & dashboard terisi.
        for ($hari = 9; $hari >= 0; $hari--) {
            $tanggal = now()->subDays($hari);

            if ($tanggal->isWeekend()) {
                continue;
            }

            foreach ($siswaList as $idx => $siswa) {
                $acak = ($idx * 7 + $hari * 3) % 10;
                $status = match (true) {
                    $acak === 0 => Absensi::STATUS_ALFA,
                    $acak === 1 => Absensi::STATUS_IZIN,
                    $acak === 2 => Absensi::STATUS_TERLAMBAT,
                    $acak === 3 => Absensi::STATUS_SAKIT,
                    default => Absensi::STATUS_HADIR,
                };

                $hadir = in_array($status, [Absensi::STATUS_HADIR, Absensi::STATUS_TERLAMBAT], true);

                Absensi::create([
                    'siswa_id' => $siswa->id,
                    'tanggal' => $tanggal->toDateString(),
                    'status' => $status,
                    'jam_masuk' => $hadir ? ($status === Absensi::STATUS_TERLAMBAT ? '07:45:00' : '07:10:00') : null,
                    'jam_pulang' => $hadir && $hari === 0 ? null : ($hadir ? '14:45:00' : null),
                    'ditandai_oleh' => $guru->id,
                ]);
            }
        }

        // Beberapa pelanggaran contoh agar modul poin terlihat berisi.
        Pelanggaran::create([
            'siswa_id' => $siswaList[1]->id,
            'kategori_pelanggaran_id' => $kategoriModels[0]->id,
            'poin' => 5,
            'tanggal' => now()->subDays(3)->toDateString(),
            'keterangan' => 'Terlambat 15 menit',
            'ditambahkan_oleh' => $bk->id,
        ]);

        Pelanggaran::create([
            'siswa_id' => $siswaList[1]->id,
            'kategori_pelanggaran_id' => $kategoriModels[2]->id,
            'poin' => 20,
            'tanggal' => now()->subDays(1)->toDateString(),
            'keterangan' => 'Membolos jam pelajaran ke-3',
            'ditambahkan_oleh' => $bk->id,
        ]);

        Pelanggaran::create([
            'siswa_id' => $siswaList[3]->id,
            'kategori_pelanggaran_id' => $kategoriModels[3]->id,
            'poin' => 15,
            'tanggal' => now()->subDays(2)->toDateString(),
            'keterangan' => 'Main HP di kelas',
            'ditambahkan_oleh' => $bk->id,
        ]);

        // Contoh pengajuan izin/sakit agar modul baru ikut terisi.
        PengajuanIzin::create([
            'siswa_id' => $siswaList[0]->id,
            'jenis' => PengajuanIzin::JENIS_SAKIT,
            'tanggal_mulai' => today(),
            'alasan' => 'Demam, sedang istirahat di rumah sesuai anjuran dokter.',
            'status' => PengajuanIzin::STATUS_MENUNGGU,
        ]);

        PengajuanIzin::create([
            'siswa_id' => $siswaList[5]->id,
            'jenis' => PengajuanIzin::JENIS_IZIN,
            'tanggal_mulai' => now()->subDays(2),
            'alasan' => 'Mengurus administrasi keluarga.',
            'status' => PengajuanIzin::STATUS_DISETUJUI,
            'ditinjau_oleh' => $bk->id,
            'ditinjau_at' => now()->subDays(2),
        ]);

        // Tandai akun pendidik & admin sudah pernah login agar dasbor kontrol menampilkan angka.
        User::whereIn('id', [$admin->id, $guru->id, $guru2->id, $bk->id])->update(['last_login_at' => now()]);
        User::where('role', User::ROLE_SISWA)->update(['last_login_at' => now()->subHours(3)]);

        $this->command?->info('Seeder selesai. Login memakai domain @'.self::DOMAIN.' — password: '.self::PASSWORD);
    }
}
