<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class GuruController extends Controller
{
    public function index(): View
    {
        $daftar = User::whereIn('role', [User::ROLE_GURU, User::ROLE_BK])
            ->withCount('profilSiswa as dibimbing')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.guru', ['daftar' => $daftar]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:guru,bk'],
            'telepon' => ['nullable', 'string', 'max:20'],
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return back()->with('success', 'Akun guru/BK berhasil ditambahkan.');
    }

    public function update(Request $request, User $guru): RedirectResponse
    {
        $this->authorizeGuru($guru);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email,'.$guru->id],
            'role' => ['required', 'in:guru,bk'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $guru->update($data);

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(User $guru): RedirectResponse
    {
        $this->authorizeGuru($guru);

        $guru->delete();

        return back()->with('success', 'Akun guru berhasil dihapus.');
    }

    private function authorizeGuru(User $guru): void
    {
        abort_unless(in_array($guru->role, [User::ROLE_GURU, User::ROLE_BK], true), 404);
    }
}
