<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $daftar = Kelas::withCount('siswa')->with('waliKelas')->orderBy('nama')->get();

        return view('admin.kelas', [
            'daftar' => $daftar,
            'guru' => \App\Models\User::whereIn('role', ['guru', 'bk'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:kelas,nama'],
            'tingkat' => ['nullable', 'string', 'max:20'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
        ]);

        Kelas::create($data);

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:kelas,nama,'.$kelas->id],
            'tingkat' => ['nullable', 'string', 'max:20'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
        ]);

        $kelas->update($data);

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        if ($kelas->siswa()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus kelas yang masih berisi siswa.');
        }

        $kelas->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}
