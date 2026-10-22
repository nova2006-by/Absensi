<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaturanLokasiController extends Controller
{
    public function index(): View
    {
        return view('admin.pengaturan-lokasi', [
            'pengaturan' => SchoolSetting::currentOrDefault(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'between:10,5000'],
        ]);

        $pengaturan = SchoolSetting::query()->find(1) ?? new SchoolSetting;
        $pengaturan->id = 1;
        $pengaturan->fill($data);
        $pengaturan->save();

        return back()->with('success', 'Lokasi sekolah berhasil disimpan.');
    }
}
