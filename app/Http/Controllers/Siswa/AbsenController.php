<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiPengajuan;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AbsenController extends Controller
{
    public function index(Request $request): View
    {
        $siswa = $request->user()->profilSiswa()->with('kelas')->firstOrFail();

        $absensiHariIni = $siswa->absensi()->whereDate('tanggal', today())->first();

        $bulanIni = $siswa->absensi()
            ->whereBetween('tanggal', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('siswa.absen', [
            'siswa' => $siswa,
            'absensiHariIni' => $absensiHariIni,
            'pengajuanMasukHariIni' => AbsensiPengajuan::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', today())
                ->where('jenis', AbsensiPengajuan::JENIS_MASUK)
                ->latest()
                ->first(),
            'pengajuanPulangHariIni' => AbsensiPengajuan::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', today())
                ->where('jenis', AbsensiPengajuan::JENIS_PULANG)
                ->latest()
                ->first(),
            'rekapBulan' => $bulanIni,
            'totalPoin' => $siswa->totalPoin(),
            'jamMasukMulai' => config('absensi.jam_masuk_mulai'),
            'jamMasuk' => config('absensi.jam_masuk'),
            'jamMasukBatas' => config('absensi.jam_masuk_batas'),
            'jamPulang' => config('absensi.jam_pulang'),
            'jamPulangBatas' => config('absensi.jam_pulang_batas'),
        ]);
    }

    public function absenMasuk(Request $request): RedirectResponse
    {
        if ($response = $this->pastikanDalamRentangWaktu(
            config('absensi.jam_masuk_mulai'),
            config('absensi.jam_masuk_batas'),
            'masuk',
        )) {
            return $response;
        }

        if ($response = $this->pastikanDiLokasiSekolah($request)) {
            return $response;
        }

        $siswa = $request->user()->profilSiswa()->firstOrFail();

        $berhasil = DB::transaction(function () use ($siswa) {
            $siswa = $siswa->newQuery()->lockForUpdate()->findOrFail($siswa->id);

            if ($siswa->absensi()->whereDate('tanggal', today())->exists()) {
                return false;
            }

            $pengajuanMenunggu = AbsensiPengajuan::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', today())
                ->where('jenis', AbsensiPengajuan::JENIS_MASUK)
                ->where('status', AbsensiPengajuan::STATUS_MENUNGGU)
                ->exists();

            if ($pengajuanMenunggu) {
                return false;
            }

            AbsensiPengajuan::create([
                'siswa_id' => $siswa->id,
                'tanggal' => today(),
                'jenis' => AbsensiPengajuan::JENIS_MASUK,
                'diminta_pada' => now(),
                'status' => AbsensiPengajuan::STATUS_MENUNGGU,
            ]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('error', 'Absensi masuk sudah tercatat atau masih menunggu persetujuan guru.');
        }

        return back()->with('success', 'Permintaan absensi masuk dikirim. Kehadiran baru tercatat setelah diverifikasi guru.');
    }

    public function absenPulang(Request $request): RedirectResponse
    {
        if ($response = $this->pastikanDalamRentangWaktu(
            config('absensi.jam_pulang'),
            config('absensi.jam_pulang_batas'),
            'pulang',
        )) {
            return $response;
        }

        if ($response = $this->pastikanDiLokasiSekolah($request)) {
            return $response;
        }

        $siswa = $request->user()->profilSiswa()->firstOrFail();

        $absensi = $siswa->absensi()->whereDate('tanggal', today())->first();

        if (! $absensi) {
            return back()->with('error', 'Anda belum absen masuk hari ini.');
        }

        if (! $absensi->isHadir()) {
            return back()->with('error', 'Kehadiran Anda hari ini ditandai '.$absensi->statusLabel().' oleh guru, sehingga absen pulang tidak tersedia.');
        }

        if ($absensi->jam_pulang) {
            return back()->with('error', 'Anda sudah menandai kepulangan hari ini.');
        }

        $berhasil = DB::transaction(function () use ($siswa, $absensi) {
            $siswa->newQuery()->lockForUpdate()->findOrFail($siswa->id);
            $absensiTerkunci = Absensi::whereKey($absensi->id)->lockForUpdate()->firstOrFail();

            if ($absensiTerkunci->jam_pulang) {
                return false;
            }

            $pengajuanMenunggu = AbsensiPengajuan::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', today())
                ->where('jenis', AbsensiPengajuan::JENIS_PULANG)
                ->where('status', AbsensiPengajuan::STATUS_MENUNGGU)
                ->exists();

            if ($pengajuanMenunggu) {
                return false;
            }

            AbsensiPengajuan::create([
                'siswa_id' => $siswa->id,
                'tanggal' => today(),
                'jenis' => AbsensiPengajuan::JENIS_PULANG,
                'diminta_pada' => now(),
                'status' => AbsensiPengajuan::STATUS_MENUNGGU,
            ]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('error', 'Permintaan pulang sudah tercatat atau masih menunggu persetujuan guru.');
        }

        return back()->with('success', 'Permintaan absensi pulang dikirim dan menunggu persetujuan guru.');
    }

    private function pastikanDalamRentangWaktu(string $mulai, string $batas, string $jenis): ?RedirectResponse
    {
        $jamSekarang = now()->format('H:i');

        if ($jamSekarang < $mulai || $jamSekarang > $batas) {
            return back()->with('error', "Absensi {$jenis} hanya dapat dilakukan pukul {$mulai}–{$batas}.");
        }

        return null;
    }

    private function pastikanDiLokasiSekolah(Request $request): ?RedirectResponse
    {
        $pengaturan = SchoolSetting::currentOrDefault();
        $latitudeSekolah = $pengaturan->latitude;
        $longitudeSekolah = $pengaturan->longitude;
        $radius = $pengaturan->radius;

        if (! is_numeric($latitudeSekolah) || ! is_numeric($longitudeSekolah) || $radius < 1) {
            return back()->with('error', 'Lokasi sekolah belum dikonfigurasi. Hubungi administrator.');
        }

        $validator = Validator::make($request->all(), [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Lokasi perangkat tidak tersedia. Izinkan akses lokasi lalu coba lagi.');
        }

        $latitude = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');
        $accuracy = (float) $request->input('accuracy');
        $jarak = $this->jarakDalamMeter(
            $latitude,
            $longitude,
            (float) $latitudeSekolah,
            (float) $longitudeSekolah,
        );

        if ($jarak + $accuracy > $radius) {
            return back()->with('error', 'Absensi hanya dapat dilakukan di area sekolah. Pastikan Anda berada di sekolah dan GPS aktif.');
        }

        return null;
    }

    private function jarakDalamMeter(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        $radiusBumi = 6371000;
        $deltaLatitude = deg2rad($latitude2 - $latitude1);
        $deltaLongitude = deg2rad($longitude2 - $longitude1);
        $a = sin($deltaLatitude / 2) ** 2
            + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($deltaLongitude / 2) ** 2;

        return 2 * $radiusBumi * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function riwayat(Request $request): View
    {
        $siswa = $request->user()->profilSiswa()->with('kelas')->firstOrFail();

        $riwayat = $siswa->absensi()
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('siswa.riwayat', [
            'siswa' => $siswa,
            'riwayat' => $riwayat,
        ]);
    }

    public function poin(Request $request): View
    {
        $siswa = $request->user()->profilSiswa()->with(['kelas', 'pelanggaran.kategori'])->firstOrFail();

        $pelanggaran = $siswa->pelanggaran()
            ->with('kategori')
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('siswa.poin', [
            'siswa' => $siswa,
            'totalPoin' => $siswa->totalPoin(),
            'pelanggaran' => $pelanggaran,
        ]);
    }
}
