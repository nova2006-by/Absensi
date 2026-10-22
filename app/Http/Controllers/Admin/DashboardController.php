<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $hariIni = today()->toDateString();

        // --- Akun & aktivitas login ---
        $akunPerRole = User::query()
            ->selectRaw('role, count(*) as jumlah')
            ->groupBy('role')
            ->pluck('jumlah', 'role');

        $loginHariIni = User::whereDate('last_login_at', $hariIni)->count();
        $loginMingguIni = User::aktif(7)->count();

        $guruLoginHariIni = User::whereIn('role', [User::ROLE_GURU, User::ROLE_BK])
            ->whereDate('last_login_at', $hariIni)
            ->count();
        $guruLoginMingguIni = User::whereIn('role', [User::ROLE_GURU, User::ROLE_BK])
            ->aktif(7)
            ->count();

        $siswaLoginHariIni = User::where('role', User::ROLE_SISWA)
            ->whereDate('last_login_at', $hariIni)
            ->count();

        $guruTerbaru = User::whereIn('role', [User::ROLE_GURU, User::ROLE_BK])
            ->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->limit(8)
            ->get();

        // --- Kehadiran hari ini ---
        $kehadiranHariIni = Absensi::query()
            ->whereDate('tanggal', $hariIni)
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $totalSiswa = Siswa::count();
        $totalKelas = Kelas::count();

        $hadir = ($kehadiranHariIni['hadir'] ?? 0) + ($kehadiranHariIni['terlambat'] ?? 0);
        $persenHadir = $totalSiswa > 0 ? round(($hadir / $totalSiswa) * 100, 1) : 0;

        // --- Pelanggaran & pengajuan bulan ini ---
        $pelanggaranBulanIni = Pelanggaran::query()
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->selectRaw('count(*) as jumlah, coalesce(sum(poin), 0) as total_poin')
            ->first();

        $pengajuanMenunggu = PengajuanIzin::where('status', PengajuanIzin::STATUS_MENUNGGU)->count();

        return view('admin.dashboard', [
            'akunPerRole' => $akunPerRole,
            'totalAkun' => $akunPerRole->sum(),
            'loginHariIni' => $loginHariIni,
            'loginMingguIni' => $loginMingguIni,
            'guruLoginHariIni' => $guruLoginHariIni,
            'guruLoginMingguIni' => $guruLoginMingguIni,
            'siswaLoginHariIni' => $siswaLoginHariIni,
            'guruTerbaru' => $guruTerbaru,
            'kehadiranHariIni' => $kehadiranHariIni,
            'totalSiswa' => $totalSiswa,
            'totalKelas' => $totalKelas,
            'hadir' => $hadir,
            'persenHadir' => $persenHadir,
            'alfa' => $kehadiranHariIni['alfa'] ?? 0,
            'izin' => $kehadiranHariIni['izin'] ?? 0,
            'sakit' => $kehadiranHariIni['sakit'] ?? 0,
            'terlambat' => $kehadiranHariIni['terlambat'] ?? 0,
            'belumAbsen' => max(0, $totalSiswa - $kehadiranHariIni->sum()),
            'pelanggaranBulanIni' => $pelanggaranBulanIni,
            'pengajuanMenunggu' => $pengajuanMenunggu,
        ]);
    }
}
