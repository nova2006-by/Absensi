<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role) {
            'siswa' => redirect()->route('siswa.absen'),
            'guru' => redirect()->route('guru.kehadiran'),
            'bk' => redirect()->route('bk.dashboard'),
            default => redirect()->route('admin.dashboard'),
        };
    }
}
