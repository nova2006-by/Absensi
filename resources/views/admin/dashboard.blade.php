@extends('layouts.app')

@section('title', 'Dasbor Admin')

@section('content')
    {{-- Kartu kontrol akun & login --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-blue-50 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Total akun</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $totalAkun }}</p>
                <p class="mt-0.5 truncate text-xs text-slate-400">
                    {{ $akunPerRole['siswa'] ?? 0 }} siswa · {{ $akunPerRole['guru'] ?? 0 }} guru ·
                    {{ $akunPerRole['bk'] ?? 0 }} BK · {{ $akunPerRole['admin'] ?? 0 }} admin
                </p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-emerald-50 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Login hari ini</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $loginHariIni }}
                    <span class="text-sm font-semibold text-slate-500">pengguna</span></p>
                <p class="mt-0.5 text-xs text-slate-400">{{ $loginMingguIni }} aktif dalam 7 hari terakhir</p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-violet-50 text-violet-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Guru/BK login hari ini</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $guruLoginHariIni }}
                    <span class="text-sm font-semibold text-slate-500">guru</span></p>
                <p class="mt-0.5 text-xs text-slate-400">{{ $guruLoginMingguIni }} aktif dalam 7 hari terakhir</p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-amber-50 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Siswa login hari ini</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $siswaLoginHariIni }}
                    <span class="text-sm font-semibold text-slate-500">siswa</span></p>
                <p class="mt-0.5 text-xs text-slate-400">dari {{ $totalSiswa }} siswa terdaftar</p>
            </div>
        </div>
    </div>

    {{-- Kehadiran & pelanggaran --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-emerald-50 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Kehadiran hari ini</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $persenHadir }}%</p>
                <p class="mt-0.5 text-xs text-slate-400">{{ $hadir }} hadir/terlambat · belum absen
                    {{ $belumAbsen }}</p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-rose-50 text-rose-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Alfa hari ini</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $alfa }}</p>
                <p class="mt-0.5 text-xs text-slate-400">Izin: {{ $izin }} · Sakit: {{ $sakit }} · Terlambat:
                    {{ $terlambat }}</p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-orange-50 text-orange-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Pelanggaran bulan ini</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">
                    {{ $pelanggaranBulanIni->jumlah }}
                    <span class="text-sm font-semibold text-slate-500">catatan</span></p>
                <p class="mt-0.5 text-xs text-slate-400">{{ $pelanggaranBulanIni->total_poin }} total poin</p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-amber-50 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Pengajuan izin menunggu</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $pengajuanMenunggu }}</p>
                <a href="{{ route('bk.pengajuan-izin.index', ['status' => 'menunggu']) }}"
                    class="mt-0.5 inline-block text-xs font-semibold text-emerald-700 hover:text-emerald-900">Tinjau →</a>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Login guru/BK terakhir --}}
        <div class="card overflow-hidden xl:col-span-2">
            <div class="card-header">
                <div>
                    <h2 class="card-title">🧑‍🏫 Guru &amp; BK — Login Terakhir</h2>
                    <p class="card-subtitle">Aktivitas masuk akun pendidik 7 hari terakhir.</p>
                </div>
                <a href="{{ route('admin.guru.index') }}" class="text-action">Kelola akun →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="tbl tbl-compact">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Login terakhir</th>
                            <th class="!text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guruTerbaru as $g)
                            <tr>
                                <td>
                                    <p class="font-medium text-slate-900">{{ $g->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $g->email }}</p>
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $g->role === 'bk' ? 'badge-purple' : 'badge-blue' }}">{{ $g->roleLabel() }}</span>
                                </td>
                                <td class="text-slate-500">{{ $g->last_login_at->diffForHumans() }}</td>
                                <td class="!text-center">
                                    @if ($g->last_login_at->isToday())
                                        <span class="badge badge-green">Login hari ini</span>
                                    @elseif ($g->last_login_at->gte(now()->subDays(7)))
                                        <span class="badge badge-yellow">7 hari terakhir</span>
                                    @else
                                        <span class="badge badge-slate">Lebih lama</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="!py-12 text-center text-slate-400">Belum ada guru/BK yang
                                    pernah login.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Akses cepat --}}
        <div class="card p-6">
            <h2 class="text-sm font-semibold tracking-wide text-slate-900">⚡ Akses Cepat</h2>
            <div class="mt-4 space-y-3">
                <a href="{{ route('admin.siswa.index') }}"
                    class="btn btn-soft btn-block justify-between">Kelola Siswa <span>→</span></a>
                <a href="{{ route('admin.guru.index') }}"
                    class="btn btn-soft btn-block justify-between">Kelola Guru &amp; BK <span>→</span></a>
                <a href="{{ route('admin.kelas.index') }}"
                    class="btn btn-soft btn-block justify-between">Kelola Kelas <span>→</span></a>
                <a href="{{ route('admin.kategori.index') }}"
                    class="btn btn-soft btn-block justify-between">Kategori Pelanggaran <span>→</span></a>
                <a href="{{ route('guru.kehadiran') }}"
                    class="btn btn-soft btn-block justify-between">Kehadiran Kelas <span>→</span></a>
                <a href="{{ route('bk.laporan.pelanggaran') }}"
                    class="btn btn-soft btn-block justify-between">Laporan Cetak <span>→</span></a>
                <a href="{{ route('admin.pengaturan.lokasi') }}"
                    class="btn btn-soft btn-block justify-between">Pengaturan Lokasi <span>→</span></a>
            </div>

            <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-500">
                <p class="font-semibold text-slate-700">Ringkasan data</p>
                <p class="mt-1">{{ $totalSiswa }} siswa dalam {{ $totalKelas }} kelas ·
                    {{ $akunPerRole['guru'] ?? 0 }} guru · {{ $akunPerRole['bk'] ?? 0 }} guru BK</p>
            </div>
        </div>
    </div>
@endsection
