<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiPengajuan;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KehadiranController extends Controller
{
    public function index(Request $request): View
    {
        $daftarKelas = Kelas::query()->withCount('siswa');

        if ($request->user()->role === User::ROLE_GURU) {
            $daftarKelas->where('wali_kelas_id', $request->user()->id);
        }

        $kelas = $daftarKelas->orderBy('nama')->get();

        $kelasDipilih = $request->query('kelas');
        $tanggal = $request->query('tanggal', now()->toDateString());

        $kelasAktif = null;
        $daftarSiswa = collect();
        $ringkas = [];

        if ($kelasDipilih) {
            $kelasAktif = (clone $daftarKelas)->with('waliKelas')->whereKey($kelasDipilih)->first();

            if ($kelasAktif) {
                $daftarSiswa = $kelasAktif->siswa()
                    ->with(['user', 'absensi' => function ($q) use ($tanggal) {
                        $q->whereDate('tanggal', $tanggal);
                    }])
                    ->orderBy('nis')
                    ->get();

                $ringkas = $daftarSiswa->countBy(fn ($s) => $s->absensi->first()?->status ?? 'belum_absen');
            }
        }

        $pengajuanMenunggu = $kelasAktif
            ? AbsensiPengajuan::with('siswa.user')
                ->whereDate('tanggal', $tanggal)
                ->where('status', AbsensiPengajuan::STATUS_MENUNGGU)
                ->whereHas('siswa', fn ($query) => $query->where('kelas_id', $kelasAktif->id))
                ->orderBy('diminta_pada')
                ->get()
            : collect();

        return view('guru.kehadiran', [
            'kelas' => $kelas,
            'kelasAktif' => $kelasAktif,
            'daftarSiswa' => $daftarSiswa,
            'pengajuanMenunggu' => $pengajuanMenunggu,
            'ringkas' => $ringkas,
            'tanggal' => $tanggal,
            'semuaStatus' => Absensi::SEMUA_STATUS,
        ]);
    }

    public function tandai(Request $request, Kelas $kelas): RedirectResponse
    {
        $this->pastikanAksesKelas($request, $kelas);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:'.implode(',', array_keys(Absensi::SEMUA_STATUS))],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ]);

        $tanggal = Carbon::parse($data['tanggal'])->toDateString();
        $hariIni = today()->toDateString();
        $siswaIds = $kelas->siswa()->pluck('id');

        DB::transaction(function () use ($siswaIds, $data, $tanggal, $hariIni, $request) {
            foreach ($siswaIds as $siswaId) {
                $status = $data['status'][$siswaId] ?? null;

                if (! $status) {
                    continue;
                }

                $hadir = in_array($status, [Absensi::STATUS_HADIR, Absensi::STATUS_TERLAMBAT], true);
                $absensi = DB::table('absensi')
                    ->where('siswa_id', $siswaId)
                    ->where('tanggal', $tanggal)
                    ->first();

                // Pertahankan jam yang sudah tercatat (mis. absensi mandiri siswa);
                // jam masuk hanya diisi otomatis bila tanggalnya hari ini.
                $jamMasuk = $absensi->jam_masuk
                    ?? ($hadir && $tanggal === $hariIni ? now()->format('H:i:s') : null);
                $jamPulang = $hadir ? $absensi->jam_pulang : null;

                DB::table('absensi')->updateOrInsert(
                    ['siswa_id' => $siswaId, 'tanggal' => $tanggal],
                    [
                        'status' => $status,
                        'jam_masuk' => $jamMasuk,
                        'jam_pulang' => $jamPulang,
                        'keterangan' => $data['keterangan'][$siswaId] ?? null,
                        'ditandai_oleh' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });

        return redirect()
            ->route('guru.kehadiran', ['kelas' => $kelas->id, 'tanggal' => $tanggal])
            ->with('success', 'Kehadiran kelas '.$kelas->nama.' berhasil disimpan.');
    }

    public function setujuiPengajuan(Request $request, AbsensiPengajuan $pengajuan): RedirectResponse
    {
        $pengajuan->loadMissing('siswa.kelas');
        $kelas = $pengajuan->siswa->kelas;
        $this->pastikanAksesKelas($request, $kelas);

        $hasil = DB::transaction(function () use ($request, $pengajuan) {
            $pengajuan = AbsensiPengajuan::whereKey($pengajuan->id)->lockForUpdate()->firstOrFail();

            if ($pengajuan->status !== AbsensiPengajuan::STATUS_MENUNGGU) {
                return 'sudah_ditinjau';
            }

            $absensi = Absensi::where('siswa_id', $pengajuan->siswa_id)
                ->whereDate('tanggal', $pengajuan->tanggal)
                ->lockForUpdate()
                ->first();

            if ($pengajuan->jenis === AbsensiPengajuan::JENIS_MASUK) {
                if ($absensi) {
                    return 'absensi_sudah_ada';
                }

                $status = $pengajuan->diminta_pada->format('H:i') > config('absensi.jam_masuk')
                    ? Absensi::STATUS_TERLAMBAT
                    : Absensi::STATUS_HADIR;

                Absensi::create([
                    'siswa_id' => $pengajuan->siswa_id,
                    'tanggal' => $pengajuan->tanggal,
                    'status' => $status,
                    'jam_masuk' => $pengajuan->diminta_pada->format('H:i:s'),
                    'ditandai_oleh' => $request->user()->id,
                ]);
            } elseif (
                ! $absensi
                || ! $absensi->isHadir()
                || $absensi->jam_pulang
            ) {
                return 'absensi_masuk_belum_disahkan';
            } else {
                $absensi->update([
                    'jam_pulang' => $pengajuan->diminta_pada->format('H:i:s'),
                ]);
            }

            $pengajuan->update([
                'status' => AbsensiPengajuan::STATUS_DISETUJUI,
                'ditinjau_oleh' => $request->user()->id,
                'ditinjau_pada' => now(),
                'catatan_penolakan' => null,
            ]);

            return 'disetujui';
        });

        if ($hasil !== 'disetujui') {
            $pesan = match ($hasil) {
                'absensi_sudah_ada' => 'Absensi siswa sudah tercatat dan tidak dapat ditimpa oleh pengajuan ini.',
                'absensi_masuk_belum_disahkan' => 'Sahkan absensi masuk terlebih dahulu sebelum menyetujui permintaan pulang.',
                default => 'Pengajuan ini sudah ditinjau sebelumnya.',
            };

            return back()->with('error', $pesan);
        }

        return back()->with('success', 'Pengajuan absensi berhasil disahkan.');
    }

    public function tolakPengajuan(Request $request, AbsensiPengajuan $pengajuan): RedirectResponse
    {
        $pengajuan->loadMissing('siswa.kelas');
        $this->pastikanAksesKelas($request, $pengajuan->siswa->kelas);

        $data = $request->validate([
            'catatan_penolakan' => ['nullable', 'string', 'max:255'],
        ]);

        $ditolak = DB::transaction(function () use ($request, $pengajuan, $data) {
            $pengajuan = AbsensiPengajuan::whereKey($pengajuan->id)->lockForUpdate()->firstOrFail();

            if ($pengajuan->status !== AbsensiPengajuan::STATUS_MENUNGGU) {
                return false;
            }

            $pengajuan->update([
                'status' => AbsensiPengajuan::STATUS_DITOLAK,
                'ditinjau_oleh' => $request->user()->id,
                'ditinjau_pada' => now(),
                'catatan_penolakan' => $data['catatan_penolakan'] ?? null,
            ]);

            return true;
        });

        if (! $ditolak) {
            return back()->with('error', 'Pengajuan ini sudah ditinjau sebelumnya.');
        }

        return back()->with('success', 'Pengajuan absensi ditolak.');
    }

    private function pastikanAksesKelas(Request $request, Kelas $kelas): void
    {
        abort_if(
            $request->user()->role === User::ROLE_GURU
                && (int) $kelas->wali_kelas_id !== (int) $request->user()->id,
            403,
        );
    }
}
