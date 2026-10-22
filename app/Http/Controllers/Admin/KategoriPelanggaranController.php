<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriPelanggaranController extends Controller
{
    public function index(): View
    {
        $daftar = KategoriPelanggaran::withCount('pelanggaran')->orderBy('nama')->get();

        return view('admin.kategori', ['daftar' => $daftar]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:kategori_pelanggaran,nama'],
            'poin' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        KategoriPelanggaran::create($data);

        return back()->with('success', 'Kategori pelanggaran berhasil ditambahkan.');
    }

    public function update(Request $request, KategoriPelanggaran $kategori): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:kategori_pelanggaran,nama,'.$kategori->id],
            'poin' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $kategori->update($data);

        return back()->with('success', 'Kategori pelanggaran berhasil diperbarui.');
    }

    public function destroy(KategoriPelanggaran $kategori): RedirectResponse
    {
        if ($kategori->pelanggaran()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena sudah dipakai pada catatan pelanggaran.');
        }

        $kategori->delete();

        return back()->with('success', 'Kategori pelanggaran dihapus.');
    }
}
