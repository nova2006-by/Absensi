<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SiswaController extends Controller
{
    public function index(Request $request): View
    {
        $daftar = Siswa::with(['user', 'kelas'])
            ->withSum('pelanggaran as total_poin', 'poin')
            ->when($request->query('kelas'), fn ($q, $id) => $q->where('kelas_id', $id))
            ->when($request->query('q'), function ($q, $keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('nis', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->orderBy('nis')
            ->paginate(15)
            ->withQueryString();

        return view('admin.siswa', [
            'daftar' => $daftar,
            'kelas' => Kelas::orderBy('nama')->get(),
            'kelasDipilih' => $request->query('kelas'),
            'q' => $request->query('q'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'nis' => ['required', 'string', 'max:30', 'unique:siswa,nis'],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:siswa,nisn'],
            'kelas_id' => ['required', 'exists:kelas,id'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => User::ROLE_SISWA,
            ]);

            $user->profilSiswa()->create([
                'kelas_id' => $data['kelas_id'],
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
            ]);
        });

        return back()->with('success', 'Siswa baru berhasil ditambahkan.');
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email,'.$siswa->user_id],
            'nis' => ['required', 'string', 'max:30', 'unique:siswa,nis,'.$siswa->id],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:siswa,nisn,'.$siswa->id],
            'kelas_id' => ['required', 'exists:kelas,id'],
        ]);

        $siswa->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $siswa->update([
            'nis' => $data['nis'],
            'nisn' => $data['nisn'] ?? null,
            'kelas_id' => $data['kelas_id'],
        ]);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        $siswa->user->delete();

        return back()->with('success', 'Data siswa beserta akunnya dihapus.');
    }

    /**
     * Unduh template CSV untuk import siswa massal.
     */
    public function importTemplate(): Response
    {
        $csv = "name,email,nis,nisn,kelas,password\n"
            ."Ahmad Fauzi,ahmad.fauzi.smknubandar@gmail.com,2026001,009000001,X RPL 1,\n"
            ."Budi Hartono,budi.hartono.smknubandar@gmail.com,2026002,,X RPL 1,\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-siswa.csv"',
        ]);
    }

    /**
     * Import siswa massal dari file CSV dengan kata sandi unik per akun.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'file.mimes' => 'File harus berformat CSV.',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if ($handle === false) {
            return back()->with('error', 'File CSV tidak bisa dibaca.');
        }

        $header = fgetcsv($handle, 0, ',', '"', '\\');

        // Buang BOM UTF-8 yang dibawa Excel pada kolom pertama
        $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header ?? []);

        $kolomWajib = ['name', 'email', 'nis', 'kelas', 'password'];
        $kurang = array_diff($kolomWajib, $header);

        if ($kurang !== []) {
            fclose($handle);

            return back()->with('error', 'Header CSV tidak lengkap. Kolom wajib: name, email, nis, kelas, password. Kolom opsional: nisn.');
        }

        $berhasil = 0;
        $gagal = [];
        $barisKe = 1;

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $barisKe++;

            // Lewati baris kosong
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $kolom = array_slice(array_pad($row, count($header), ''), 0, count($header));
            $data = array_combine($header, array_map(fn ($v) => trim((string) $v), $kolom));

            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $nis = $data['nis'] ?? '';
            $nisn = $data['nisn'] ?? '';
            $kelasNama = $data['kelas'] ?? '';
            $password = $data['password'] ?? '';

            $masalah = [];

            if ($name === '') {
                $masalah[] = 'nama kosong';
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $masalah[] = 'email tidak valid';
            } elseif (User::where('email', $email)->exists()) {
                $masalah[] = 'email sudah terdaftar';
            }

            if ($nis === '') {
                $masalah[] = 'nis kosong';
            } elseif (Siswa::where('nis', $nis)->exists()) {
                $masalah[] = 'nis sudah terdaftar';
            }

            if (mb_strlen($password) < 12 || mb_strlen($password) > 72) {
                $masalah[] = 'password wajib diisi 12-72 karakter';
            }

            $kelas = Kelas::where('nama', $kelasNama)->first();

            if (! $kelas) {
                $masalah[] = 'kelas "'.$kelasNama.'" tidak ditemukan';
            }

            if ($masalah !== []) {
                $gagal[] = 'Baris '.$barisKe.': '.implode(', ', $masalah);

                continue;
            }

            DB::transaction(function () use ($name, $email, $password, $nis, $nisn, $kelas) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => User::ROLE_SISWA,
                ]);

                $user->profilSiswa()->create([
                    'kelas_id' => $kelas->id,
                    'nis' => $nis,
                    'nisn' => $nisn ?: null,
                ]);
            });

            $berhasil++;
        }

        fclose($handle);

        if ($berhasil === 0 && $gagal === []) {
            return back()->with('error', 'File CSV tidak berisi data siswa.');
        }

        $pesan = 'Import selesai: '.$berhasil.' siswa berhasil ditambahkan.';

        return back()
            ->with('success', $pesan)
            ->with('import_errors', $gagal);
    }
}
