<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\AbsensiPengajuan;
use App\Models\KategoriPelanggaran;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\SchoolSetting;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbsensiSekolahTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(now()->setTime(7, 0));

        config([
            'absensi.lokasi_latitude' => -6.2,
            'absensi.lokasi_longitude' => 106.8,
            'absensi.lokasi_radius' => 100,
            'absensi.jam_masuk_mulai' => '06:30',
            'absensi.jam_masuk' => '07:30',
            'absensi.jam_masuk_batas' => '08:30',
            'absensi.jam_pulang' => '14:30',
            'absensi.jam_pulang_batas' => '17:00',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function buatSiswa(array $atribut = []): Siswa
    {
        $user = User::factory()->create(['role' => 'siswa']);

        return Siswa::create(array_merge([
            'user_id' => $user->id,
            'kelas_id' => Kelas::create(['nama' => 'X TEST 1', 'tingkat' => 'X'])->id,
            'nis' => '9001',
        ], $atribut));
    }

    public function test_login_dan_redirect_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'siswa', 'password' => bcrypt('rahasia123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('siswa.absen'));
    }

    public function test_halaman_login_tanpa_panel_akun_demo(): void
    {
        // Panel akun demo sudah dihapus sepenuhnya dari halaman login.
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Akun demo')
            ->assertDontSee('admin@sekolah.id')
            ->assertDontSee('siswa1@sekolah.id');
    }

    public function test_koordinat_browser_hanya_mengajukan_absensi_menunggu_guru(): void
    {
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('absensi', [
            'siswa_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
        ]);
        $this->assertTrue(AbsensiPengajuan::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today())
            ->where('jenis', AbsensiPengajuan::JENIS_MASUK)
            ->where('status', AbsensiPengajuan::STATUS_MENUNGGU)
            ->exists());
    }

    public function test_absen_masuk_ditolak_sebelum_jam_buka(): void
    {
        Carbon::setTestNow(today()->setTime(6, 29));
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->from(route('siswa.absen'))
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_absen_masuk_ditolak_setelah_batas_waktu(): void
    {
        Carbon::setTestNow(today()->setTime(8, 31));
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->from(route('siswa.absen'))
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_absen_masuk_pada_batas_akhir_menunggu_pengesahan_dan_dihitung_terlambat(): void
    {
        Carbon::setTestNow(today()->setTime(8, 30));
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertRedirect();

        $pengajuan = AbsensiPengajuan::where('siswa_id', $siswa->id)->firstOrFail();
        $this->assertDatabaseMissing('absensi', [
            'siswa_id' => $siswa->id,
            'tanggal' => today()->toDateString(),
        ]);

        $guru = User::factory()->create(['role' => 'guru']);
        Kelas::whereKey($siswa->kelas_id)->update(['wali_kelas_id' => $guru->id]);

        $this->actingAs($guru)
            ->post(route('guru.kehadiran.pengajuan.setujui', $pengajuan))
            ->assertSessionHas('success');

        $this->assertTrue(Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today())
            ->where('status', Absensi::STATUS_TERLAMBAT)
            ->exists());
    }

    public function test_siswa_hanya_mengajukan_absensi_dan_guru_wali_mengesahkan(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create(['role' => 'guru']);
        Kelas::whereKey($siswa->kelas_id)->update(['wali_kelas_id' => $guru->id]);

        $this->actingAs($siswa->user)
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('success');

        $this->actingAs($guru)
            ->get(route('guru.kehadiran', ['kelas' => $siswa->kelas_id]))
            ->assertOk()
            ->assertSee('Permintaan Absensi Menunggu Verifikasi')
            ->assertSee($siswa->user->name);

        $this->actingAs($siswa->user)
            ->get(route('siswa.absen'))
            ->assertOk()
            ->assertSee('Permintaan masuk menunggu verifikasi guru');

        $pengajuan = AbsensiPengajuan::firstOrFail();

        $this->actingAs($guru)
            ->post(route('guru.kehadiran.pengajuan.setujui', $pengajuan))
            ->assertSessionHas('success');

        $this->assertTrue(Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today())
            ->where('status', Absensi::STATUS_HADIR)
            ->where('ditandai_oleh', $guru->id)
            ->exists());
        $this->assertDatabaseHas('absensi_pengajuan', [
            'id' => $pengajuan->id,
            'status' => AbsensiPengajuan::STATUS_DISETUJUI,
            'ditinjau_oleh' => $guru->id,
        ]);
    }

    public function test_siswa_tidak_bisa_mengesahkan_pengajuan_absensinya_sendiri(): void
    {
        $siswa = $this->buatSiswa();
        $pengajuan = AbsensiPengajuan::create([
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'jenis' => AbsensiPengajuan::JENIS_MASUK,
            'diminta_pada' => now(),
        ]);

        $this->actingAs($siswa->user)
            ->post(route('guru.kehadiran.pengajuan.setujui', $pengajuan))
            ->assertForbidden();

        $this->assertDatabaseMissing('absensi', ['siswa_id' => $siswa->id]);
        $this->assertSame(AbsensiPengajuan::STATUS_MENUNGGU, $pengajuan->fresh()->status);
    }

    public function test_siswa_tidak_bisa_mengirim_pengajuan_masuk_ganda(): void
    {
        $siswa = $this->buatSiswa();

        foreach (range(1, 2) as $percobaan) {
            $respons = $this->actingAs($siswa->user)
                ->from(route('siswa.absen'))
                ->post(route('siswa.absen.masuk'), [
                    'latitude' => -6.2,
                    'longitude' => 106.8,
                    'accuracy' => 10,
                ]);

            if ($percobaan === 2) {
                $respons->assertSessionHas('error');
            }
        }

        $this->assertSame(1, AbsensiPengajuan::where('siswa_id', $siswa->id)
            ->where('jenis', AbsensiPengajuan::JENIS_MASUK)
            ->where('status', AbsensiPengajuan::STATUS_MENUNGGU)
            ->count());
        $this->assertDatabaseMissing('absensi', ['siswa_id' => $siswa->id]);
    }

    public function test_guru_tidak_bisa_mengesahkan_pengajuan_dari_kelas_lain(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create(['role' => 'guru']);
        $pengajuan = AbsensiPengajuan::create([
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'jenis' => AbsensiPengajuan::JENIS_MASUK,
            'diminta_pada' => now(),
        ]);

        $this->actingAs($guru)
            ->post(route('guru.kehadiran.pengajuan.setujui', $pengajuan))
            ->assertForbidden();

        $this->assertDatabaseMissing('absensi', ['siswa_id' => $siswa->id]);
    }

    public function test_guru_hanya_bisa_melihat_dan_mengubah_kelas_wali(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create(['role' => 'guru']);
        $kelasGuru = Kelas::create([
            'nama' => 'X WALI GURU',
            'tingkat' => 'X',
            'wali_kelas_id' => $guru->id,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.kehadiran', ['kelas' => $siswa->kelas_id]))
            ->assertOk()
            ->assertDontSee($siswa->user->name)
            ->assertDontSee('Daftar Siswa — X TEST 1');

        $this->actingAs($guru)
            ->post(route('guru.kehadiran.tandai', $siswa->kelas_id), [
                'tanggal' => today()->toDateString(),
                'status' => [$siswa->id => Absensi::STATUS_HADIR],
            ])
            ->assertForbidden();

        $this->actingAs($guru)
            ->get(route('guru.kehadiran', ['kelas' => $kelasGuru->id]))
            ->assertOk()
            ->assertSee('X WALI GURU');
    }

    public function test_pengajuan_masuk_yang_ditolak_bisa_diajukan_ulang(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create(['role' => 'guru']);
        Kelas::whereKey($siswa->kelas_id)->update(['wali_kelas_id' => $guru->id]);

        $this->actingAs($siswa->user)->post(route('siswa.absen.masuk'), [
            'latitude' => -6.2,
            'longitude' => 106.8,
            'accuracy' => 10,
        ]);
        $pengajuanPertama = AbsensiPengajuan::firstOrFail();

        $this->actingAs($guru)
            ->post(route('guru.kehadiran.pengajuan.tolak', $pengajuanPertama), [
                'catatan_penolakan' => 'Siswa belum terlihat di kelas.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($siswa->user)->post(route('siswa.absen.masuk'), [
            'latitude' => -6.2,
            'longitude' => 106.8,
            'accuracy' => 10,
        ])->assertSessionHas('success');

        $this->assertDatabaseCount('absensi_pengajuan', 2);
        $this->assertDatabaseMissing('absensi', ['siswa_id' => $siswa->id]);
    }

    public function test_siswa_tidak_bisa_absen_dua_kali(): void
    {
        $siswa = $this->buatSiswa();

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'status' => Absensi::STATUS_HADIR,
            'jam_masuk' => '07:00:00',
        ]);

        $this->actingAs($siswa->user)
            ->from(route('siswa.absen'))
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertSame(1, Absensi::where('siswa_id', $siswa->id)->count());
    }

    public function test_absen_masuk_ditolak_di_luar_radius_sekolah(): void
    {
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->from(route('siswa.absen'))
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.3,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_absen_masuk_ditolak_jika_lokasi_sekolah_belum_dikonfigurasi(): void
    {
        config(['absensi.lokasi_latitude' => null, 'absensi.lokasi_longitude' => null]);
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->from(route('siswa.absen'))
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_admin_bisa_membuka_dan_menyimpan_pengaturan_lokasi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.pengaturan.lokasi'))
            ->assertOk()
            ->assertSee('peta-lokasi-sekolah');

        $this->actingAs($admin)
            ->get(route('admin.siswa.index'))
            ->assertOk()
            ->assertSee('Pengaturan Lokasi');

        $this->actingAs($admin)
            ->put(route('admin.pengaturan.lokasi.update'), [
                'latitude' => '-6.21',
                'longitude' => '106.81',
                'radius' => 250,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('school_settings', [
            'id' => 1,
            'latitude' => -6.21,
            'longitude' => 106.81,
            'radius' => 250,
        ]);
    }

    public function test_pengaturan_lokasi_admin_menolak_input_di_luar_rentang(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.pengaturan.lokasi'))
            ->put(route('admin.pengaturan.lokasi.update'), [
                'latitude' => 95,
                'longitude' => 106.81,
                'radius' => 5,
            ])
            ->assertSessionHasErrors(['latitude', 'radius']);

        $this->assertDatabaseCount('school_settings', 0);
    }

    public function test_hanya_admin_yang_bisa_mengubah_pengaturan_lokasi(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($guru)
            ->get(route('admin.pengaturan.lokasi'))
            ->assertForbidden();
    }

    public function test_absensi_menggunakan_titik_yang_disimpan_admin(): void
    {
        $pengaturan = new SchoolSetting([
            'latitude' => -6.21,
            'longitude' => 106.81,
            'radius' => 100,
        ]);
        $pengaturan->id = 1;
        $pengaturan->save();

        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->post(route('siswa.absen.masuk'), [
                'latitude' => -6.21,
                'longitude' => 106.81,
                'accuracy' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('absensi', [
            'siswa_id' => $siswa->id,
            'tanggal' => today()->toDateString(),
        ]);
        $this->assertTrue(AbsensiPengajuan::where('siswa_id', $siswa->id)
            ->where('jenis', AbsensiPengajuan::JENIS_MASUK)
            ->where('status', AbsensiPengajuan::STATUS_MENUNGGU)
            ->exists());
    }

    public function test_absen_pulang_juga_ditolak_di_luar_radius_sekolah(): void
    {
        Carbon::setTestNow(today()->setTime(15, 0));
        $siswa = $this->buatSiswa();
        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'status' => Absensi::STATUS_HADIR,
            'jam_masuk' => '07:00:00',
        ]);

        $this->actingAs($siswa->user)
            ->from(route('siswa.absen'))
            ->post(route('siswa.absen.pulang'), [
                'latitude' => -6.3,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertNull($absensi->fresh()->jam_pulang);
    }

    public function test_absen_pulang_ditolak_sebelum_jam_pulang(): void
    {
        Carbon::setTestNow(today()->setTime(14, 29));
        $siswa = $this->buatSiswa();
        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'status' => Absensi::STATUS_HADIR,
            'jam_masuk' => '07:00:00',
        ]);

        $this->actingAs($siswa->user)
            ->from(route('siswa.absen'))
            ->post(route('siswa.absen.pulang'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertNull($absensi->fresh()->jam_pulang);
    }

    public function test_absen_pulang_ditolak_setelah_batas_waktu(): void
    {
        Carbon::setTestNow(today()->setTime(17, 1));
        $siswa = $this->buatSiswa();
        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'status' => Absensi::STATUS_HADIR,
            'jam_masuk' => '07:00:00',
        ]);

        $this->actingAs($siswa->user)
            ->from(route('siswa.absen'))
            ->post(route('siswa.absen.pulang'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertSessionHas('error');

        $this->assertNull($absensi->fresh()->jam_pulang);
    }

    public function test_absen_pulang_di_dalam_jam_yang_diizinkan_berhasil(): void
    {
        Carbon::setTestNow(today()->setTime(14, 30));
        $siswa = $this->buatSiswa();
        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'status' => Absensi::STATUS_HADIR,
            'jam_masuk' => '07:00:00',
        ]);

        $this->actingAs($siswa->user)
            ->post(route('siswa.absen.pulang'), [
                'latitude' => -6.2,
                'longitude' => 106.8,
                'accuracy' => 10,
            ])
            ->assertRedirect();

        $this->assertNull($absensi->fresh()->jam_pulang);

        $pengajuan = AbsensiPengajuan::where('siswa_id', $siswa->id)
            ->where('jenis', AbsensiPengajuan::JENIS_PULANG)
            ->firstOrFail();
        $guru = User::factory()->create(['role' => 'guru']);
        Kelas::whereKey($siswa->kelas_id)->update(['wali_kelas_id' => $guru->id]);

        $this->actingAs($guru)
            ->post(route('guru.kehadiran.pengajuan.setujui', $pengajuan))
            ->assertSessionHas('success');

        $this->assertSame('14:30:00', $absensi->fresh()->jam_pulang);
    }

    public function test_guru_bisa_menandai_kehadiran_kelas(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create(['role' => 'guru']);
        Kelas::whereKey($siswa->kelas_id)->update(['wali_kelas_id' => $guru->id]);

        $this->actingAs($guru)
            ->post(route('guru.kehadiran.tandai', $siswa->kelas_id), [
                'tanggal' => now()->toDateString(),
                'status' => [$siswa->id => 'alfa'],
                'keterangan' => [$siswa->id => 'Tanpa keterangan'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('absensi', [
            'siswa_id' => $siswa->id,
            'status' => 'alfa',
        ]);
    }

    public function test_bk_bisa_memberi_poin_pelanggaran(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);
        $kategori = KategoriPelanggaran::create(['nama' => 'Tes Pelanggaran', 'poin' => 10]);

        $this->actingAs($bk)
            ->post(route('bk.pelanggaran.store'), [
                'siswa_id' => $siswa->id,
                'kategori_pelanggaran_id' => $kategori->id,
                'poin' => 10,
                'tanggal' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pelanggaran', ['siswa_id' => $siswa->id, 'poin' => 10]);
        $this->assertSame(10, $siswa->fresh()->totalPoin());
    }

    public function test_siswa_bisa_melihat_poinnya(): void
    {
        $siswa = $this->buatSiswa();
        $kategori = KategoriPelanggaran::create(['nama' => 'Tes', 'poin' => 15]);

        Pelanggaran::create([
            'siswa_id' => $siswa->id,
            'kategori_pelanggaran_id' => $kategori->id,
            'poin' => 15,
            'tanggal' => today(),
        ]);

        $this->actingAs($siswa->user)
            ->get(route('siswa.poin'))
            ->assertOk()
            ->assertSee('15');
    }

    public function test_guru_tidak_bisa_akses_dashboard_bk(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($guru)
            ->get(route('bk.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_bisa_kelola_siswa(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama' => 'X ADMIN', 'tingkat' => 'X']);

        $this->actingAs($admin)
            ->post(route('admin.siswa.store'), [
                'name' => 'Siswa Baru',
                'email' => 'baru@sekolah.id',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'nis' => '8888',
                'kelas_id' => $kelas->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('siswa', ['nis' => '8888']);
        $this->assertDatabaseHas('users', ['email' => 'baru@sekolah.id', 'role' => 'siswa']);
    }

    public function test_admin_bisa_menyimpan_akun_guru_dan_bk(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['guru', 'bk'] as $role) {
            $email = $role.'-baru@sekolah.id';

            $this->actingAs($admin)
                ->post(route('admin.guru.store'), [
                    'name' => 'Akun '.strtoupper($role),
                    'email' => $email,
                    'password' => 'PasswordDemo#123',
                    'password_confirmation' => 'PasswordDemo#123',
                    'role' => $role,
                ])
                ->assertSessionHasNoErrors();

            $this->assertDatabaseHas('users', ['email' => $email, 'role' => $role]);
        }
    }

    public function test_form_guru_menampilkan_pesan_jika_password_tidak_cocok(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.guru.index'))
            ->post(route('admin.guru.store'), [
                'name' => 'Guru Gagal',
                'email' => 'guru-gagal@sekolah.id',
                'password' => 'PasswordDemo#123',
                'password_confirmation' => 'PasswordBerbeda#123',
                'role' => 'guru',
            ])
            ->assertSessionHasErrors('password');

        $this->get(route('admin.guru.index'))
            ->assertOk()
            ->assertSee('Data guru/BK belum tersimpan');

        $this->assertDatabaseMissing('users', ['email' => 'guru-gagal@sekolah.id']);
    }

    public function test_halaman_utama_setiap_role(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create(['role' => 'guru']);
        $bk = User::factory()->create(['role' => 'bk']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($siswa->user)->get(route('siswa.absen'))->assertOk();
        $this->actingAs($siswa->user)->get(route('siswa.riwayat'))->assertOk();
        $this->actingAs($guru)->get(route('guru.kehadiran'))->assertOk();
        $this->actingAs($bk)->get(route('bk.dashboard'))->assertOk();
        $this->actingAs($bk)->get(route('bk.pelanggaran.index'))->assertOk();
        $this->actingAs($bk)->get(route('bk.pelanggaran.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.kategori.index'))->assertOk();
    }

    public function test_bk_bisa_edit_pelanggaran(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);
        $kategoriLama = KategoriPelanggaran::create(['nama' => 'Kategori Lama', 'poin' => 5]);
        $kategoriBaru = KategoriPelanggaran::create(['nama' => 'Kategori Baru', 'poin' => 25]);

        $pelanggaran = Pelanggaran::create([
            'siswa_id' => $siswa->id,
            'kategori_pelanggaran_id' => $kategoriLama->id,
            'poin' => 5,
            'tanggal' => today(),
        ]);

        $this->actingAs($bk)
            ->get(route('bk.pelanggaran.edit', $pelanggaran))
            ->assertOk();

        $this->actingAs($bk)
            ->put(route('bk.pelanggaran.update', $pelanggaran), [
                'kategori_pelanggaran_id' => $kategoriBaru->id,
                'poin' => 25,
                'tanggal' => today()->toDateString(),
                'keterangan' => 'Diperbarui lewat form edit',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pelanggaran', [
            'id' => $pelanggaran->id,
            'poin' => 25,
            'keterangan' => 'Diperbarui lewat form edit',
        ]);
    }

    public function test_halaman_detail_riwayat_poin_siswa(): void
    {
        $siswa = $this->buatSiswa();
        $kategori = KategoriPelanggaran::create(['nama' => 'Tes', 'poin' => 10]);

        Pelanggaran::create([
            'siswa_id' => $siswa->id,
            'kategori_pelanggaran_id' => $kategori->id,
            'poin' => 10,
            'tanggal' => today(),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'bk']))
            ->get(route('bk.pelanggaran.siswa', $siswa))
            ->assertOk()
            ->assertSee($siswa->user->name);
    }

    public function test_bk_bisa_upload_foto_bukti_pelanggaran(): void
    {
        Storage::fake('public');

        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);
        $kategori = KategoriPelanggaran::create(['nama' => 'Tes Foto', 'poin' => 10]);

        // PNG 1x1 asli agar lolos validasi image (tanpa ekstensi GD)
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $file = UploadedFile::fake()->createWithContent('bukti.png', $png);

        $this->actingAs($bk)
            ->post(route('bk.pelanggaran.store'), [
                'siswa_id' => $siswa->id,
                'kategori_pelanggaran_id' => $kategori->id,
                'poin' => 10,
                'tanggal' => now()->toDateString(),
                'gambar' => $file,
            ])
            ->assertSessionHasNoErrors();

        $pelanggaran = Pelanggaran::firstOrFail();

        $this->assertNotNull($pelanggaran->gambar);
        Storage::disk('public')->assertExists($pelanggaran->gambar);
    }

    public function test_admin_bisa_import_siswa_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Kelas::create(['nama' => 'X IMPOR', 'tingkat' => 'X']);

        $csv = "name,email,nis,nisn,kelas,password\n"
            ."Siswa Impor Satu,impor1@sekolah.id,7771,00977771,X IMPOR,AwalUnik-Satu-2026\n"
            ."Siswa Impor Dua,impor2@sekolah.id,7772,,X IMPOR,AwalUnik-Dua-2026\n";

        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.siswa.import'), ['file' => $file])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'impor1@sekolah.id', 'role' => 'siswa']);
        $this->assertDatabaseHas('siswa', ['nis' => '7771']);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check(
            'AwalUnik-Satu-2026',
            User::where('email', 'impor1@sekolah.id')->value('password'),
        ));
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check(
            'AwalUnik-Satu-2026',
            User::where('email', 'impor2@sekolah.id')->value('password'),
        ));
        $this->assertDatabaseHas('siswa', ['nis' => '7772']);
    }

    public function test_import_csv_menandai_baris_bermasalah(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Kelas::create(['nama' => 'X IMPOR', 'tingkat' => 'X']);

        $csv = "name,email,nis,nisn,kelas,password\n"
            ."Siswa Valid,valid@sekolah.id,7781,,X IMPOR,AwalUnik-Valid-2026\n"
            ."Siswa Salah,kurang@sekolah.id,7782,,X TIDAK ADA,AwalUnik-Salah-2026\n";

        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.siswa.import'), ['file' => $file])
            ->assertSessionHas('import_errors');

        $this->assertDatabaseHas('siswa', ['nis' => '7781']);
        $this->assertDatabaseMissing('siswa', ['nis' => '7782']);
    }

    public function test_import_csv_menolak_password_awal_di_luar_batas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Kelas::create(['nama' => 'X IMPOR', 'tingkat' => 'X']);

        foreach ([
            ['pendek@sekolah.id', 'pendek'],
            ['panjang@sekolah.id', str_repeat('a', 73)],
        ] as [$email, $password]) {
            $csv = "name,email,nis,kelas,password\n"
                ."Siswa Password Tidak Valid,{$email},7791,X IMPOR,{$password}\n";
            $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

            $this->actingAs($admin)
                ->post(route('admin.siswa.import'), ['file' => $file])
                ->assertSessionHas('import_errors', fn (array $errors) => str_contains($errors[0], 'password wajib diisi 12-72 karakter'));

            $this->assertDatabaseMissing('users', ['email' => $email]);
        }
    }

    public function test_bk_bisa_akses_laporan_dan_guru_tidak(): void
    {
        $bk = User::factory()->create(['role' => 'bk']);
        $guru = User::factory()->create(['role' => 'guru']);
        $kelas = Kelas::create(['nama' => 'X LAPORAN', 'tingkat' => 'X']);

        $this->actingAs($bk)
            ->get(route('bk.laporan.pelanggaran'))
            ->assertOk()
            ->assertSee('Rekap Poin Pelanggaran Siswa');

        $this->actingAs($bk)
            ->get(route('bk.laporan.kehadiran', [
                'kelas' => $kelas->id,
                'awal' => now()->startOfMonth()->toDateString(),
                'akhir' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Rekap Kehadiran Siswa');

        $this->actingAs($guru)->get(route('bk.laporan.pelanggaran'))->assertForbidden();
    }
}
