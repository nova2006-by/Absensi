<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PengajuanIzinController extends Controller
{
    public function index(Request $request): View
    {
        $siswa = $request->user()->profilSiswa()->with('kelas')->firstOrFail();

        $pengajuan = PengajuanIzin::where('siswa_id', $siswa->id)
            ->with('ditinjauOleh')
            ->latest('tanggal_mulai')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $adaMenunggu = PengajuanIzin::where('siswa_id', $siswa->id)
            ->where('status', PengajuanIzin::STATUS_MENUNGGU)
            ->exists();

        return view('siswa.pengajuan-izin.index', [
            'siswa' => $siswa,
            'pengajuan' => $pengajuan,
            'adaMenunggu' => $adaMenunggu,
            'maksHari' => PengajuanIzin::MAKS_HARI,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $siswa = $request->user()->profilSiswa()->firstOrFail();

        $data = $request->validate([
            'jenis' => ['required', 'in:'.implode(',', array_keys(PengajuanIzin::SEMUA_JENIS))],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'min:5', 'max:1000'],
            'bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'bukti.image' => 'Foto bukti harus berupa gambar (JPG, PNG, atau WebP).',
            'bukti.max' => 'Ukuran foto bukti maksimal 2 MB.',
        ]);

        $tanggalSelesai = $data['tanggal_selesai'] ?? $data['tanggal_mulai'];

        if (now()->startOfDay()->diffInDays(Carbon::parse($tanggalSelesai), false) > PengajuanIzin::MAKS_HARI) {
            return back()
                ->withInput()
                ->withErrors(['tanggal_selesai' => 'Durasi pengajuan maksimal '.PengajuanIzin::MAKS_HARI.' hari.']);
        }

        if ($request->hasFile('bukti')) {
            $data['bukti'] = $request->file('bukti')->store('pengajuan-izin', 'public');
        } else {
            unset($data['bukti']);
        }

        $data['siswa_id'] = $siswa->id;
        $data['tanggal_selesai'] = $tanggalSelesai;

        PengajuanIzin::create($data);

        return redirect()
            ->route('siswa.pengajuan-izin')
            ->with('success', 'Pengajuan '.PengajuanIzin::SEMUA_JENIS[$data['jenis']].' berhasil dikirim dan menunggu persetujuan guru BK.');
    }

    public function destroy(Request $request, PengajuanIzin $pengajuan): RedirectResponse
    {
        $siswa = $request->user()->profilSiswa()->firstOrFail();

        abort_unless($pengajuan->siswa_id === $siswa->id, 404);

        if (! $pengajuan->isMenunggu()) {
            return back()->with('error', 'Pengajuan yang sudah ditinjau tidak dapat dibatalkan. Hubungi guru BK bila perlu perubahan.');
        }

        if ($pengajuan->bukti) {
            Storage::disk('public')->delete($pengajuan->bukti);
        }

        $pengajuan->delete();

        return back()->with('success', 'Pengajuan dibatalkan.');
    }
}
