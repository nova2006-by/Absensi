<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\PengajuanIzin;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengajuanIzinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(now()->setTime(7, 0));
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
            'kelas_id' => Kelas::create(['nama' => 'X IZIN 1', 'tingkat' => 'X'])->id,
            'nis' => '9101',
        ], $atribut));
    }

    public function test_siswa_bisa_mengajukan_izin(): void
    {
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->post(route('siswa.pengajuan-izin.store'), [
                'jenis' => 'izin',
                'tanggal_mulai' => today()->toDateString(),
                'alasan' => 'Ada keperluan keluarga mendesak.',
            ])
            ->assertRedirect(route('siswa.pengajuan-izin'));

        $this->assertDatabaseHas('pengajuan_izin', [
            'siswa_id' => $siswa->id,
            'jenis' => 'izin',
            'status' => PengajuanIzin::STATUS_MENUNGGU,
        ]);

        // Belum ada absensi otomatis sebelum disetujui
        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_pengajuan_wajib_alasan(): void
    {
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->from(route('siswa.pengajuan-izin'))
            ->post(route('siswa.pengajuan-izin.store'), [
                'jenis' => 'sakit',
                'tanggal_mulai' => today()->toDateString(),
                'alasan' => '',
            ])
            ->assertSessionHasErrors('alasan');

        $this->assertDatabaseCount('pengajuan_izin', 0);
    }

    public function test_pengajuan_tidak_bisa_lewat_30_hari(): void
    {
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->post(route('siswa.pengajuan-izin.store'), [
                'jenis' => 'sakit',
                'tanggal_mulai' => today()->toDateString(),
                'tanggal_selesai' => today()->addDays(40)->toDateString(),
                'alasan' => 'Sakit panjang berkepanjangan.',
            ])
            ->assertSessionHasErrors('tanggal_selesai');

        $this->assertDatabaseCount('pengajuan_izin', 0);
    }

    public function test_pengajuan_tidak_bisa_untuk_tanggal_lampau(): void
    {
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa->user)
            ->post(route('siswa.pengajuan-izin.store'), [
                'jenis' => 'izin',
                'tanggal_mulai' => today()->subDays(3)->toDateString(),
                'alasan' => 'Mengajukan izin setelah kejadian.',
            ])
            ->assertSessionHasErrors('tanggal_mulai');
    }

    public function test_siswa_bisa_membatalkan_pengajuan_menunggu(): void
    {
        Storage::fake('public');

        $siswa = $this->buatSiswa();
        $bukti = UploadedFile::fake()->createWithContent('bukti.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'sakit',
            'tanggal_mulai' => today(),
            'alasan' => 'Demam tinggi.',
            'bukti' => 'pengajuan-izin/bukti.png',
        ]);

        Storage::disk('public')->put('pengajuan-izin/bukti.png', 'dummy');

        $this->actingAs($siswa->user)
            ->delete(route('siswa.pengajuan-izin.destroy', $pengajuan))
            ->assertRedirect();

        $this->assertDatabaseMissing('pengajuan_izin', ['id' => $pengajuan->id]);
        Storage::disk('public')->assertMissing('pengajuan-izin/bukti.png');
    }

    public function test_siswa_tidak_bisa_membatalkan_pengajuan_orang_lain(): void
    {
        $siswaLain = $this->buatSiswa(['nis' => '9102']);

        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'kelas_id' => $siswaLain->kelas_id,
            'nis' => '9103',
        ]);

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswaLain->id,
            'jenis' => 'izin',
            'tanggal_mulai' => today(),
            'alasan' => 'Keperluan keluarga.',
        ]);

        $this->actingAs($siswa->user)
            ->delete(route('siswa.pengajuan-izin.destroy', $pengajuan))
            ->assertNotFound();

        $this->assertDatabaseHas('pengajuan_izin', ['id' => $pengajuan->id]);
    }

    public function test_bk_bisa_menyetujui_dan_absensi_tercatat_otomatis(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'sakit',
            'tanggal_mulai' => today(),
            'tanggal_selesai' => today()->addDays(2),
            'alasan' => 'Sakit demam, ada surat dokter.',
        ]);

        $this->actingAs($bk)
            ->patch(route('bk.pengajuan-izin.setujui', $pengajuan))
            ->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertSame(PengajuanIzin::STATUS_DISETUJUI, $pengajuan->status);
        $this->assertSame($bk->id, $pengajuan->ditinjau_oleh);

        $this->assertDatabaseCount('absensi', 3);
        $this->assertSame(3, Absensi::where('siswa_id', $siswa->id)
            ->where('status', Absensi::STATUS_SAKIT)->count());
    }

    public function test_persetujuan_izin_mencatat_status_izin(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'izin',
            'tanggal_mulai' => today(),
            'alasan' => 'Mengurus administrasi keluarga.',
        ]);

        $this->actingAs($bk)
            ->patch(route('bk.pengajuan-izin.setujui', $pengajuan));

        $this->assertDatabaseHas('absensi', [
            'siswa_id' => $siswa->id,
            'tanggal' => today()->toDateString(),
            'status' => Absensi::STATUS_IZIN,
        ]);
    }

    public function test_persetujuan_tidak_menimpa_absensi_yang_sudah_ada(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);

        // Siswa sudah absen masuk sendiri pada hari pertama
        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'status' => Absensi::STATUS_HADIR,
            'jam_masuk' => '07:05:00',
        ]);

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'sakit',
            'tanggal_mulai' => today(),
            'tanggal_selesai' => today()->addDay(),
            'alasan' => 'Sakit setelah absen masuk.',
        ]);

        $this->actingAs($bk)
            ->patch(route('bk.pengajuan-izin.setujui', $pengajuan))
            ->assertSessionHas('success');

        // Hari pertama tidak ditimpa, hari kedua tercatat sakit
        $this->assertTrue(Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today())
            ->where('status', Absensi::STATUS_HADIR)
            ->exists());
        $this->assertTrue(Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today()->addDay())
            ->where('status', Absensi::STATUS_SAKIT)
            ->exists());
        $this->assertDatabaseCount('absensi', 2);
    }

    public function test_bk_bisa_menolak_dengan_catatan(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'izin',
            'tanggal_mulai' => today(),
            'alasan' => 'Acara keluarga.',
        ]);

        $this->actingAs($bk)
            ->patch(route('bk.pengajuan-izin.tolak', $pengajuan), [
                'catatan_penolakan' => 'Mohon lampirkan surat undangan.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertSame(PengajuanIzin::STATUS_DITOLAK, $pengajuan->status);
        $this->assertSame('Mohon lampirkan surat undangan.', $pengajuan->catatan_penolakan);
        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_pengajuan_yang_sudah_ditinjau_tidak_bisa_ditinjau_ulang(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'izin',
            'tanggal_mulai' => today(),
            'tanggal_selesai' => today()->addDays(2),
            'alasan' => 'Keperluan mendesak.',
            'status' => PengajuanIzin::STATUS_DISETUJUI,
            'ditinjau_oleh' => $bk->id,
            'ditinjau_at' => now(),
        ]);

        $this->actingAs($bk)
            ->patch(route('bk.pengajuan-izin.tolak', $pengajuan))
            ->assertSessionHas('error');

        $this->assertSame(PengajuanIzin::STATUS_DISETUJUI, $pengajuan->fresh()->status);
        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_siswa_tidak_bisa_menyetujui_pengajuan_sendiri(): void
    {
        $siswa = $this->buatSiswa();

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'izin',
            'tanggal_mulai' => today(),
            'alasan' => 'Keperluan keluarga.',
        ]);

        $this->actingAs($siswa->user)
            ->patch(route('bk.pengajuan-izin.setujui', $pengajuan))
            ->assertForbidden();

        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_guru_tidak_bisa_membuka_halaman_pengajuan_bk(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($guru)
            ->get(route('bk.pengajuan-izin.index'))
            ->assertForbidden();
    }

    public function test_halaman_pengajuan_untuk_siswa_dan_bk(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);

        PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'sakit',
            'tanggal_mulai' => today(),
            'alasan' => 'Sakit perut.',
        ]);

        $this->actingAs($siswa->user)
            ->get(route('siswa.pengajuan-izin'))
            ->assertOk()
            ->assertSee('Formulir Pengajuan')
            ->assertSee('Sakit perut.');

        $this->actingAs($bk)
            ->get(route('bk.pengajuan-izin.index'))
            ->assertOk()
            ->assertSee('Daftar Pengajuan')
            ->assertSee('Sakit perut.');
    }

    public function test_absensi_hasil_persetujuan_muncul_di_riwayat_siswa(): void
    {
        $siswa = $this->buatSiswa();
        $bk = User::factory()->create(['role' => 'bk']);

        $pengajuan = PengajuanIzin::create([
            'siswa_id' => $siswa->id,
            'jenis' => 'sakit',
            'tanggal_mulai' => today(),
            'alasan' => 'Demam, istirahat dokter.',
        ]);

        $this->actingAs($bk)
            ->patch(route('bk.pengajuan-izin.setujui', $pengajuan));

        $this->actingAs($siswa->user)
            ->get(route('siswa.riwayat'))
            ->assertOk()
            ->assertSee('Sakit');
    }
}
