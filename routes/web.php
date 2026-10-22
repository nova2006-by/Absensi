<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\KategoriPelanggaranController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\PengaturanLokasiController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Bk\DashboardController as BkDashboard;
use App\Http\Controllers\Bk\LaporanController;
use App\Http\Controllers\Bk\PelanggaranController;
use App\Http\Controllers\Bk\PengajuanIzinController as BkPengajuanIzin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guru\KehadiranController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Siswa\AbsenController;
use App\Http\Controllers\Siswa\PengajuanIzinController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Pemasangan sekali-pakai via browser (hosting tanpa SSH). Terkunci INSTALL_KEY.
Route::get('/pasang', InstallController::class)->name('install');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Siswa
    Route::prefix('siswa')->name('siswa.')->middleware('role:siswa')->group(function () {
        Route::get('/absen', [AbsenController::class, 'index'])->name('absen');
        Route::post('/absen/masuk', [AbsenController::class, 'absenMasuk'])->name('absen.masuk');
        Route::post('/absen/pulang', [AbsenController::class, 'absenPulang'])->name('absen.pulang');
        Route::get('/riwayat', [AbsenController::class, 'riwayat'])->name('riwayat');
        Route::get('/poin', [AbsenController::class, 'poin'])->name('poin');

        // Pengajuan izin/sakit oleh siswa, disetujui guru BK
        Route::get('/pengajuan-izin', [PengajuanIzinController::class, 'index'])->name('pengajuan-izin');
        Route::post('/pengajuan-izin', [PengajuanIzinController::class, 'store'])->name('pengajuan-izin.store');
        Route::delete('/pengajuan-izin/{pengajuan}', [PengajuanIzinController::class, 'destroy'])->name('pengajuan-izin.destroy');
    });

    // Guru / wali kelas: kehadiran kelas
    Route::prefix('guru')->name('guru.')->middleware('role:guru,bk,admin')->group(function () {
        Route::get('/kehadiran', [KehadiranController::class, 'index'])->name('kehadiran');
        Route::post('/kehadiran/{kelas}', [KehadiranController::class, 'tandai'])->name('kehadiran.tandai');
        Route::post('/pengajuan-absensi/{pengajuan}/setujui', [KehadiranController::class, 'setujuiPengajuan'])->name('kehadiran.pengajuan.setujui');
        Route::post('/pengajuan-absensi/{pengajuan}/tolak', [KehadiranController::class, 'tolakPengajuan'])->name('kehadiran.pengajuan.tolak');
    });

    // Guru BK
    Route::prefix('bk')->name('bk.')->middleware('role:bk,admin')->group(function () {
        Route::get('/', BkDashboard::class)->name('dashboard');
        Route::get('/pelanggaran', [PelanggaranController::class, 'index'])->name('pelanggaran.index');
        Route::get('/pelanggaran/create', [PelanggaranController::class, 'create'])->name('pelanggaran.create');
        Route::post('/pelanggaran', [PelanggaranController::class, 'store'])->name('pelanggaran.store');
        Route::get('/pelanggaran/siswa/{siswa}', [PelanggaranController::class, 'siswa'])->name('pelanggaran.siswa');
        Route::get('/pelanggaran/{pelanggaran}/edit', [PelanggaranController::class, 'edit'])->name('pelanggaran.edit');
        Route::put('/pelanggaran/{pelanggaran}', [PelanggaranController::class, 'update'])->name('pelanggaran.update');
        Route::delete('/pelanggaran/{pelanggaran}', [PelanggaranController::class, 'destroy'])->name('pelanggaran.destroy');

        // Tinjauan pengajuan izin/sakit dari siswa
        Route::get('/pengajuan-izin', [BkPengajuanIzin::class, 'index'])->name('pengajuan-izin.index');
        Route::patch('/pengajuan-izin/{pengajuan}/setujui', [BkPengajuanIzin::class, 'setujui'])->name('pengajuan-izin.setujui');
        Route::patch('/pengajuan-izin/{pengajuan}/tolak', [BkPengajuanIzin::class, 'tolak'])->name('pengajuan-izin.tolak');

        // Laporan cetak (rekap poin & rekap kehadiran)
        Route::get('/laporan/pelanggaran', [LaporanController::class, 'pelanggaran'])->name('laporan.pelanggaran');
        Route::get('/laporan/kehadiran', [LaporanController::class, 'kehadiran'])->name('laporan.kehadiran');
    });

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('/pengaturan/lokasi', [PengaturanLokasiController::class, 'index'])->name('pengaturan.lokasi');
        Route::put('/pengaturan/lokasi', [PengaturanLokasiController::class, 'update'])->name('pengaturan.lokasi.update');
        Route::resource('siswa', SiswaController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/siswa/import/template', [SiswaController::class, 'importTemplate'])->name('siswa.import.template');
        Route::post('/siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
        Route::resource('guru', GuruController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::resource('kelas', KelasController::class)
            ->only(['store', 'update', 'destroy'])
            ->except(['index'])
            ->parameters(['kelas' => 'kelas']);
    });

    // Kategori pelanggaran (admin & BK)
    Route::prefix('admin')->name('admin.')->middleware('role:admin,bk')->group(function () {
        Route::get('/kategori-pelanggaran', [KategoriPelanggaranController::class, 'index'])->name('kategori.index');
        Route::resource('kategori-pelanggaran', KategoriPelanggaranController::class)
            ->only(['store', 'update', 'destroy'])
            ->except(['index'])
            ->parameters(['kategori-pelanggaran' => 'kategori']);
    });
});
