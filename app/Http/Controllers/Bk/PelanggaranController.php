<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\KategoriPelanggaran;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PelanggaranController extends Controller
{
    public function index(Request $request): View
    {
        $daftarSiswa = Siswa::with(['user', 'kelas'])
            ->withSum('pelanggaran as total_poin', 'poin')
            ->orderByDesc('total_poin')
            ->get();

        $pelanggaran = Pelanggaran::with(['siswa.user', 'siswa.kelas', 'kategori', 'ditambahkanOleh'])
            ->when($request->query('siswa'), fn ($q, $id) => $q->where('siswa_id', $id))
            ->when($request->query('kelas'), fn ($q, $id) => $q->whereHas('siswa', fn ($qq) => $qq->where('kelas_id', $id)))
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('bk.pelanggaran.index', [
            'daftarSiswa' => $daftarSiswa,
            'pelanggaran' => $pelanggaran,
            'kelas' => Kelas::orderBy('nama')->get(),
            'siswaDipilih' => $request->query('siswa'),
            'kelasDipilih' => $request->query('kelas'),
        ]);
    }

    public function create(Request $request): View
    {
        $siswaDipilih = Siswa::with(['user', 'kelas'])->find($request->query('siswa'));

        return view('bk.pelanggaran.create', [
            'kategori' => KategoriPelanggaran::orderBy('nama')->get(),
            'siswa' => Siswa::with(['user', 'kelas'])
                ->withSum('pelanggaran as total_poin', 'poin')
                ->orderBy('nis')
                ->get(),
            'siswaDipilih' => $siswaDipilih,
            'kelas' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function siswa(Siswa $siswa): View
    {
        $siswa->load(['user', 'kelas.waliKelas']);

        $totalPoin = (int) $siswa->pelanggaran()->sum('poin');
        $jumlahPelanggaran = $siswa->pelanggaran()->count();

        $perKategori = Pelanggaran::query()
            ->where('siswa_id', $siswa->id)
            ->join('kategori_pelanggaran', 'kategori_pelanggaran.id', '=', 'pelanggaran.kategori_pelanggaran_id')
            ->selectRaw('kategori_pelanggaran.nama as nama, count(*) as jumlah, sum(pelanggaran.poin) as total')
            ->groupBy('kategori_pelanggaran.nama')
            ->orderByDesc('total')
            ->get();

        $absensiBulanIni = Absensi::query()
            ->where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->toDateString()])
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $pelanggaran = Pelanggaran::with(['kategori', 'ditambahkanOleh'])
            ->where('siswa_id', $siswa->id)
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15);

        return view('bk.pelanggaran.siswa', [
            'siswa' => $siswa,
            'totalPoin' => $totalPoin,
            'jumlahPelanggaran' => $jumlahPelanggaran,
            'perKategori' => $perKategori,
            'absensiBulanIni' => $absensiBulanIni,
            'pelanggaran' => $pelanggaran,
        ]);
    }

    public function edit(Pelanggaran $pelanggaran): View
    {
        $pelanggaran->load(['siswa.user', 'siswa.kelas', 'kategori']);

        return view('bk.pelanggaran.edit', [
            'pelanggaran' => $pelanggaran,
            'kategori' => KategoriPelanggaran::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Pelanggaran $pelanggaran): RedirectResponse
    {
        $data = $request->validate([
            'kategori_pelanggaran_id' => ['required', 'exists:kategori_pelanggaran,id'],
            'poin' => ['required', 'integer', 'min:1', 'max:100'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_gambar' => ['nullable', 'boolean'],
        ], [
            'gambar.image' => 'Foto bukti harus berupa gambar (JPG, PNG, atau WebP).',
            'gambar.max' => 'Ukuran foto bukti maksimal 2 MB.',
        ]);

        if ($request->hasFile('gambar')) {
            if ($pelanggaran->gambar) {
                Storage::disk('public')->delete($pelanggaran->gambar);
            }

            $data['gambar'] = $request->file('gambar')->store('pelanggaran', 'public');
        } elseif ($request->boolean('hapus_gambar') && $pelanggaran->gambar) {
            Storage::disk('public')->delete($pelanggaran->gambar);
            $data['gambar'] = null;
        } else {
            unset($data['gambar']);
        }

        unset($data['hapus_gambar']);

        $pelanggaran->update($data);

        return redirect()
            ->route('bk.pelanggaran.siswa', $pelanggaran->siswa_id)
            ->with('success', 'Catatan pelanggaran berhasil diperbarui.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswa,id'],
            'kategori_pelanggaran_id' => ['required', 'exists:kategori_pelanggaran,id'],
            'poin' => ['required', 'integer', 'min:1', 'max:100'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'gambar.image' => 'Foto bukti harus berupa gambar (JPG, PNG, atau WebP).',
            'gambar.max' => 'Ukuran foto bukti maksimal 2 MB.',
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('pelanggaran', 'public');
        }

        $data['ditambahkan_oleh'] = $request->user()->id;

        Pelanggaran::create($data);

        $siswa = Siswa::with('user')->findOrFail($data['siswa_id']);

        return redirect()
            ->route('bk.pelanggaran.index', ['siswa' => $siswa->id])
            ->with('success', 'Poin pelanggaran untuk '.$siswa->user->name.' berhasil ditambahkan.');
    }

    public function destroy(Pelanggaran $pelanggaran): RedirectResponse
    {
        $siswaId = $pelanggaran->siswa_id;

        if ($pelanggaran->gambar) {
            Storage::disk('public')->delete($pelanggaran->gambar);
        }

        $pelanggaran->delete();

        return redirect()
            ->route('bk.pelanggaran.index', ['siswa' => $siswaId])
            ->with('success', 'Catatan pelanggaran dihapus.');
    }
}
