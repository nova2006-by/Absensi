<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /**
     * Cetak rekap poin pelanggaran (per kelas / semua, dengan periode tanggal).
     */
    public function pelanggaran(Request $request): View
    {
        $kelasId = $request->query('kelas');

        $request->merge([
            'awal' => $request->query('awal') ?: now()->startOfMonth()->toDateString(),
            'akhir' => $request->query('akhir') ?: now()->toDateString(),
        ]);

        $request->validate([
            'kelas' => ['nullable', 'exists:kelas,id'],
            'awal' => ['required', 'date'],
            'akhir' => ['required', 'date', 'after_or_equal:awal'],
        ]);

        $awal = $request->query('awal') ?: now()->startOfMonth()->toDateString();
        $akhir = $request->query('akhir') ?: now()->toDateString();
        $adaPelanggaran = $request->boolean('ada_pelanggaran', true);

        $daftarSiswa = Siswa::query()
            ->with(['user', 'kelas'])
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->withCount([
                'pelanggaran as jumlah_pelanggaran' => fn ($q) => $q->whereBetween('tanggal', [$awal, $akhir]),
            ])
            ->withSum([
                'pelanggaran as total_poin' => fn ($q) => $q->whereBetween('tanggal', [$awal, $akhir]),
            ], 'poin')
            ->orderByDesc('total_poin')
            ->orderBy('nis')
            ->get()
            ->when($adaPelanggaran, fn ($c) => $c->filter(fn ($s) => ($s->jumlah_pelanggaran ?? 0) > 0)->values());

        return view('bk.laporan.pelanggaran', [
            'daftarSiswa' => $daftarSiswa,
            'kelas' => Kelas::orderBy('nama')->get(),
            'kelasDipilih' => $kelasId ? Kelas::find($kelasId) : null,
            'awal' => $awal,
            'akhir' => $akhir,
            'adaPelanggaran' => $adaPelanggaran,
        ]);
    }

    /**
     * Cetak rekap kehadiran siswa per kelas pada periode tertentu.
     */
    public function kehadiran(Request $request): View
    {
        $request->validate([
            'kelas' => ['required', 'exists:kelas,id'],
            'awal' => ['required', 'date'],
            'akhir' => ['required', 'date', 'after_or_equal:awal'],
        ]);

        $kelasId = $request->query('kelas');
        $awal = $request->query('awal');
        $akhir = $request->query('akhir');

        $daftarSiswa = Siswa::with('user')
            ->where('kelas_id', $kelasId)
            ->orderBy('nis')
            ->get();

        $rekap = Absensi::query()
            ->whereBetween('tanggal', [$awal, $akhir])
            ->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId))
            ->selectRaw('siswa_id, status, count(*) as jumlah')
            ->groupBy('siswa_id', 'status')
            ->get()
            ->groupBy('siswa_id');

        $hariEfektif = Absensi::query()
            ->whereBetween('tanggal', [$awal, $akhir])
            ->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId))
            ->distinct()
            ->count('tanggal');

        return view('bk.laporan.kehadiran', [
            'daftarSiswa' => $daftarSiswa,
            'rekap' => $rekap,
            'kelas' => Kelas::with('waliKelas')->findOrFail($kelasId),
            'daftarKelas' => Kelas::orderBy('nama')->get(),
            'awal' => $awal,
            'akhir' => $akhir,
            'hariEfektif' => $hariEfektif,
        ]);
    }
}
