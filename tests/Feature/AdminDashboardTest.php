<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(now()->setTime(9, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_bisa_membuka_dasbor_dan_melihat_statistik(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        User::factory()->create(['role' => 'siswa']);
        User::factory()->create(['role' => 'siswa']);
        User::factory()->create(['role' => 'guru', 'last_login_at' => now()]);
        User::factory()->create(['role' => 'bk', 'last_login_at' => now()->subDays(2)]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dasbor Admin')
            ->assertSee('Total akun')
            ->assertSee('Login hari ini')
            ->assertSee('Guru/BK login hari ini');
    }

    public function test_dasbor_admin_menampilkan_jumlah_login_guru(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 2 guru login hari ini, 1 guru login lama, 1 BK login minggu ini
        User::factory()->create(['role' => 'guru', 'last_login_at' => now()->subHour()]);
        User::factory()->create(['role' => 'guru', 'last_login_at' => now()->subMinutes(30)]);
        User::factory()->create(['role' => 'guru', 'last_login_at' => now()->subDays(20)]);
        User::factory()->create(['role' => 'bk', 'last_login_at' => now()->subDays(3)]);

        $respons = $this->actingAs($admin)->get(route('admin.dashboard'));

        $respons->assertOk()
            ->assertViewHas('guruLoginHariIni', 2)
            ->assertViewHas('guruLoginMingguIni', 3)
            ->assertViewHas('loginHariIni', 2)
            ->assertViewHas('loginMingguIni', 3)
            ->assertViewHas('totalAkun', 5);
    }

    public function test_login_merekam_waktu_login_terakhir(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'password' => bcrypt('rahasia123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_login_admin_diarahkan_ke_dasbor_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => bcrypt('rahasia123')]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_dasbor_admin_menghitung_kehadiran_hari_ini(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama' => 'X DASH 1', 'tingkat' => 'X']);

        foreach (range(1, 3) as $i) {
            $user = User::factory()->create(['role' => 'siswa']);
            $siswa = Siswa::create([
                'user_id' => $user->id,
                'kelas_id' => $kelas->id,
                'nis' => "950{$i}",
            ]);

            Absensi::create([
                'siswa_id' => $siswa->id,
                'tanggal' => today(),
                'status' => $i === 3 ? 'alfa' : 'hadir',
                'jam_masuk' => $i === 3 ? null : '07:00:00',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('hadir', 2)
            ->assertViewHas('alfa', 1)
            ->assertViewHas('persenHadir', 66.7);
    }

    public function test_hanya_admin_yang_bisa_membuka_dasbor_admin(): void
    {
        foreach (['siswa', 'guru', 'bk'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('admin.dashboard'))
                ->assertForbidden();
        }
    }
}
