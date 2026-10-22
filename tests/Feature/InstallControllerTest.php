<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pasang_ditolak_tanpa_kunci_pada_konfigurasi(): void
    {
        config(['app.install_key' => null]);

        $this->get('/pasang?key=apa-saja')->assertNotFound();
    }

    public function test_pasang_ditolak_dengan_kunci_salah(): void
    {
        config(['app.install_key' => 'kunci-asli-123']);

        $this->get('/pasang?key=salah')
            ->assertForbidden();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_pasang_berhasil_migrasi_dan_seed(): void
    {
        config(['app.install_key' => 'kunci-asli-123']);

        $this->get('/pasang?key=kunci-asli-123')
            ->assertOk()
            ->assertSee('PEMASANGAN SELESAI')
            ->assertSee('Migrasi database selesai');

        // Seeder membuat 16 akun siap pakai
        $this->assertDatabaseCount('users', 16);
        $this->assertDatabaseHas('users', [
            'email' => 'admin.smknubandar@gmail.com',
            'role' => 'admin',
        ]);
    }

    public function test_pasang_produksi_hanya_membuat_admin_dengan_kredensial_konfigurasi(): void
    {
        config([
            'app.env' => 'production',
            'app.install_key' => 'kunci-asli-123',
            'app.initial_admin.name' => 'Admin Produksi',
            'app.initial_admin.email' => 'admin@sekolah.id',
            'app.initial_admin.password' => 'KataSandiUnik-2026!',
        ]);

        $this->get('/pasang?key=kunci-asli-123')
            ->assertOk()
            ->assertSee('Seeder akun bawaan tidak dijalankan di produksi')
            ->assertDontSee('KataSandiUnik-2026!');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name' => 'Admin Produksi',
            'email' => 'admin@sekolah.id',
            'role' => User::ROLE_ADMIN,
        ]);
        $this->assertTrue(Hash::check(
            'KataSandiUnik-2026!',
            User::where('email', 'admin@sekolah.id')->value('password'),
        ));
    }

    public function test_pasang_produksi_menolak_kredensial_admin_yang_tidak_lengkap(): void
    {
        config([
            'app.env' => 'production',
            'app.install_key' => 'kunci-asli-123',
        ]);

        $this->get('/pasang?key=kunci-asli-123')->assertServiceUnavailable();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_seeder_akun_bawaan_ditolak_di_produksi(): void
    {
        config(['app.env' => 'production']);

        $this->expectException(\RuntimeException::class);

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_seeder_demo_ditolak_di_produksi(): void
    {
        config(['app.env' => 'production']);

        $this->expectException(\RuntimeException::class);

        $this->seed(\Database\Seeders\DemoDataSeeder::class);
    }

    public function test_pasang_tidak_mengulang_seed_bila_sudah_ada_data(): void
    {
        config(['app.install_key' => 'kunci-asli-123']);

        User::factory()->create(['email' => 'sudah-ada@gmail.com']);

        $this->get('/pasang?key=kunci-asli-123')
            ->assertOk()
            ->assertSee('sudah pernah dijalankan');

        // Tidak ada akun baru yang ditambahkan oleh seeder
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseMissing('users', ['email' => 'admin.smknubandar@gmail.com']);
    }

    public function test_url_upload_bukti_mengarah_ke_folder_public(): void
    {
        $this->assertSame(
            '/uploads/pelanggaran/bukti.png',
            \Illuminate\Support\Facades\Storage::disk('public')->url('pelanggaran/bukti.png'),
        );
    }
}
