<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\PengajuanIzin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PengajuanIzinController extends Controller
{
    public function index(Request $request): View
    {
        $statusDipilih = $request->query('status');
        $kelasDipilih = $request->query('kelas');

        $pengajuan = PengajuanIzin::with(['siswa.user', 'siswa.kelas', 'ditinjauOleh'])
            ->when($statusDipilih, fn ($q, $s) => $q->where('status', $s))
            ->when($kelasDipilih, fn ($q, $id) => $q->whereHas('siswa', fn ($qq) => $qq->where('kelas_id', $id)))
            ->orderByRaw("case status when 'menunggu' then 0 else 1 end")
            ->latest('tanggal_mulai')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $jumlahMenunggu = PengajuanIzin::where('status', PengajuanIzin::STATUS_MENUNGGU)->count();

        return view('bk.pengajuan-izin.index', [
            'pengajuan' => $pengajuan,
            'kelas' => Kelas::orderBy('nama')->get(),
            'statusDipilih' => $statusDipilih,
            'kelasDipilih' => $kelasDipilih,
            'jumlahMenunggu' => $jumlahMenunggu,
        ]);
    }

    public function setujui(Request $request, PengajuanIzin $pengajuan): RedirectResponse
    {
        if (! $pengajuan->isMenunggu()) {
            return back()->with('error', 'Pengajuan ini sudah ditinjau sebelumnya.');
        }

        $dilewati = 0;
        $tercatat = 0;

        DB::transaction(function () use ($request, $pengajuan, &$dilewati, &$tercatat) {
            foreach ($pengajuan->rentangTanggal() as $tanggal) {
                $sudahAda = DB::table('absensi')
                    ->where('siswa_id', $pengajuan->siswa_id)
                    ->whereDate('tanggal', $tanggal)
                    ->exists();

                // Jangan timpa absensi yang sudah tercatat (mis. siswa sudah absen mandiri).
                if ($sudahAda) {
                    $dilewati++;

                    continue;
                }

                DB::table('absensi')->insert([
                    'siswa_id' => $pengajuan->siswa_id,
                    'tanggal' => $tanggal,
                    'status' => $pengajuan->jenis === PengajuanIzin::JENIS_SAKIT
                        ? Absensi::STATUS_SAKIT
                        : Absensi::STATUS_IZIN,
                    'jam_masuk' => null,
                    'jam_pulang' => null,
                    'keterangan' => Str::limit($pengajuan->alasan, 250),
                    'ditandai_oleh' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $tercatat++;
            }

            $pengajuan->update([
                'status' => PengajuanIzin::STATUS_DISETUJUI,
                'ditinjau_oleh' => $request->user()->id,
                'ditinjau_at' => now(),
                'catatan_penolakan' => null,
            ]);
        });

        $pesan = "Pengajuan disetujui: {$tercatat} hari absensi tercatat";

        if ($dilewati > 0) {
            $pesan .= ", {$dilewati} hari dilewati karena sudah ada absensi";
        }

        return back()->with('success', $pesan.'.');
    }

    public function tolak(Request $request, PengajuanIzin $pengajuan): RedirectResponse
    {
        if (! $pengajuan->isMenunggu()) {
            return back()->with('error', 'Pengajuan ini sudah ditinjau sebelumnya.');
        }

        $data = $request->validate([
            'catatan_penolakan' => ['nullable', 'string', 'max:255'],
        ], [
            'catatan_penolakan.max' => 'Catatan penolakan maksimal 255 karakter.',
        ]);

        $pengajuan->update([
            'status' => PengajuanIzin::STATUS_DITOLAK,
            'ditinjau_oleh' => $request->user()->id,
            'ditinjau_at' => now(),
            'catatan_penolakan' => $data['catatan_penolakan'] ?: null,
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }
}
