<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class InstallController extends Controller
{
    /**
     * Jalankan migrasi & seeding dari browser.
     * Diperuntukkan hosting gratis tanpa SSH; dikunci INSTALL_KEY dan
     * hanya bisa dijalankan sekali (saat tabel users masih kosong).
     */
    public function __invoke(Request $request): Response
    {
        $kunciKonfigurasi = (string) config('app.install_key');

        abort_if($kunciKonfigurasi === '', 404);

        abort_unless(
            hash_equals($kunciKonfigurasi, (string) $request->query('key')),
            403,
            'Kunci pemasangan salah. Periksa nilai INSTALL_KEY di .env'
        );

        if (Schema::hasTable('users') && DB::table('users')->count() > 0) {
            return response(
                "Pemasangan sudah pernah dijalankan. Aplikasi siap dipakai.\n".
                "Untuk keamanan, kosongkan INSTALL_KEY di .env sekarang juga.\n",
                200,
                ['Content-Type' => 'text/plain; charset=utf-8']
            );
        }

        $adminEmail = (string) config('app.initial_admin.email');
        $adminPassword = (string) config('app.initial_admin.password');

        $isProduction = config('app.env') === 'production';

        if ($isProduction) {
            abort_unless(
                filter_var($adminEmail, FILTER_VALIDATE_EMAIL) !== false
                    && mb_strlen($adminPassword) >= 12
                    && mb_strlen($adminPassword) <= 72,
                503,
                'Konfigurasikan INITIAL_ADMIN_EMAIL dan INITIAL_ADMIN_PASSWORD unik sepanjang 12-72 karakter sebelum pemasangan produksi.'
            );
        }

        $langkah = [];

        Artisan::call('migrate', ['--force' => true]);
        $langkah[] = '✔ Migrasi database selesai.';

        if ($isProduction) {
            User::create([
                'name' => (string) config('app.initial_admin.name', 'Administrator'),
                'email' => $adminEmail,
                'password' => $adminPassword,
                'role' => User::ROLE_ADMIN,
            ]);
            $langkah[] = '✔ Akun admin awal dibuat. Seeder akun bawaan tidak dijalankan di produksi.';
        } else {
            Artisan::call('db:seed', ['--force' => true]);
            $langkah[] = '✔ Data awal (akun admin/guru/BK/siswa, kelas, absensi contoh) dibuat.';
        }

        // Cache menulis file permanen ke bootstrap/cache — jangan dijalankan
        // saat testing karena meracuni konfigurasi test lainnya.
        if (! app()->runningUnitTests()) {
            config([
                'app.initial_admin.email' => null,
                'app.initial_admin.password' => null,
            ]);

            Artisan::call('config:cache');
            $langkah[] = '✔ Konfigurasi di-cache.';

            Artisan::call('view:cache');
            $langkah[] = '✔ Template di-cache.';
        }

        $langkah[] = '';
        $langkah[] = 'PEMASANGAN SELESAI. Langkah selanjutnya:';
        $langkah[] = '1. Login sebagai admin, buka Pengaturan Lokasi, isi titik & radius GPS sekolah.';
        $langkah[] = '2. Kosongkan INSTALL_KEY dan kredensial admin awal di .env, lalu bangun ulang cache konfigurasi.';

        return response(implode("\n", $langkah)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}
