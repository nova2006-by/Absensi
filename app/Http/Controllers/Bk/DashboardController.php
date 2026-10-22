<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tanggalRequest = $request->query('tanggal');
        $tanggal = $tanggalRequest && Carbon::hasFormat($tanggalRequest, 'Y-m-d')
            ? $tanggalRequest
            : now()->toDateString();
        $kelasId = $request->query('kelas');

        $queryAbsensi = Absensi::query()->whereDate('tanggal', $tanggal);

        if ($kelasId) {
            $queryAbsensi->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId));
        }

        $kehadiranHariIni = $queryAbsensi
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $totalSiswaHariIni = Siswa::query()
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->count();

        $alfaMingguIni = Absensi::query()
            ->where('status', Absensi::STATUS_ALFA)
            ->where('tanggal', '>=', now()->subDays(7)->toDateString())
            ->when($kelasId, fn ($q) => $q->whereHas('siswa', fn ($qq) => $qq->where('kelas_id', $kelasId)))
            ->count();

        $siswaBerisiko = Siswa::query()
            ->with(['user', 'kelas'])
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->withSum('pelanggaran as total_poin', 'poin')
            ->orderByDesc('total_poin')
            ->limit(10)
            ->get()
            ->filter(fn ($s) => ($s->total_poin ?? 0) > 0)
            ->values();

        $pelanggaranTerbaru = Pelanggaran::with(['siswa.user', 'siswa.kelas', 'kategori'])
            ->latest('tanggal')
            ->latest('id')
            ->limit(8)
            ->get();

        // Data grafik: poin & jumlah pelanggaran 6 bulan terakhir
        $grafikBulan = ['labels' => [], 'poin' => [], 'jumlah' => []];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->startOfMonth()->subMonths($i);
            $qBulan = Pelanggaran::query()
                ->whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->when($kelasId, fn ($qq) => $qq->whereHas('siswa', fn ($q3) => $q3->where('kelas_id', $kelasId)));

            $grafikBulan['labels'][] = $bulan->translatedFormat('M Y');
            $grafikBulan['poin'][] = (int) (clone $qBulan)->sum('poin');
            $grafikBulan['jumlah'][] = (int) (clone $qBulan)->count();
        }

        $alfaTerbanyak = DB::table('absensi')
            ->join('siswa', 'siswa.id', '=', 'absensi.siswa_id')
            ->where('absensi.status', Absensi::STATUS_ALFA)
            ->where('absensi.tanggal', '>=', now()->subDays(30)->toDateString())
            ->when($kelasId, fn ($q) => $q->where('siswa.kelas_id', $kelasId))
            ->groupBy('absensi.siswa_id')
            ->selectRaw('absensi.siswa_id, count(*) as jumlah')
            ->orderByDesc('jumlah')
            ->limit(5)
            ->get();

        $petaSiswa = Siswa::with(['user', 'kelas'])
            ->whereIn('id', $alfaTerbanyak->pluck('siswa_id'))
            ->get()
            ->keyBy('id');

        $siswaAlfa = $alfaTerbanyak
            ->map(fn ($item) => tap($item, fn ($i) => $i->siswa = $petaSiswa->get($i->siswa_id)))
            ->filter(fn ($i) => $i->siswa)
            ->values();

        return view('bk.dashboard', [
            'tanggal' => $tanggal,
            'pengajuanMenunggu' => PengajuanIzin::where('status', PengajuanIzin::STATUS_MENUNGGU)->count(),
            'kelasDipilih' => $kelasId ? Kelas::find($kelasId) : null,
            'kelas' => Kelas::orderBy('nama')->get(),
            'kehadiranHariIni' => $kehadiranHariIni,
            'totalSiswa' => $totalSiswaHariIni,
            'alfaMingguIni' => $alfaMingguIni,
            'siswaBerisiko' => $siswaBerisiko,
            'pelanggaranTerbaru' => $pelanggaranTerbaru,
            'siswaAlfa' => $siswaAlfa,
            'grafikBulan' => $grafikBulan,
        ]);
    }
}
