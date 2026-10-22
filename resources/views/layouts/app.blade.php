<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aplikasi Absensi') - SMK NU Bandar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100/70 text-slate-800 antialiased">
    <div class="mx-auto flex min-h-screen max-w-[1600px]">
        {{-- Sidebar --}}
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-800/60 bg-slate-950 text-slate-300 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:translate-x-0">
            {{-- Logo --}}
            <div class="flex items-center gap-3 border-b border-slate-800/60 px-6 py-5">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-900/40">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold tracking-tight text-white">SMK NU Bandar</p>
                    <p class="text-[11px] text-slate-400">Sistem Absensi &amp; Pelanggaran</p>
                </div>
            </div>

            {{-- Menu --}}
            <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-5 text-sm">
                @php
                    $user = auth()->user();
                    $role = $user->role;

                    $ikon = [
                        'absensi' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
                        'riwayat' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
                        'poin' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />',
                        'kehadiran' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
                        'dashboard' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 10.5 3v6.75h9.75L13.5 21v-6.75H3.75Z" />',
                        'pelanggaran' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
                        'kategori' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />',
                        'siswa' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />',
                        'guru' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />',
                        'kelas' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />',
                        'lokasi' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.25 7-12a7 7 0 1 0-14 0c0 6.75 7 12 7 12Zm0-9a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />',
                        'laporan' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.2.7-.527 1.365-.98 1.912C5.198 16.576 4.876 17 4.5 17H3m13.5 0h1.125c.491 0 .965-.194 1.313-.54a4.5 4.5 0 0 0-1.674-7.418m0 0a4.502 4.502 0 0 0-8.872 0m8.872 0a4.498 4.498 0 0 1 1.674 7.418M9 9.75A2.25 2.25 0 0 1 11.25 7.5h1.5a2.25 2.25 0 0 1 2.25 2.25v.75m6-3.75H18M5.25 16.5h13.5a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 18.75 4.5H5.25A2.25 2.25 0 0 0 3 6.75v7.5a2.25 2.25 0 0 0 2.25 2.25Z" />',
                        'pengajuan' =>
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />',
                    ];

                    $menu = match ($role) {
                        'siswa' => [
                            ['route' => 'siswa.absen', 'label' => 'Absensi Saya', 'ikon' => 'absensi'],
                            ['route' => 'siswa.riwayat', 'label' => 'Riwayat Absensi', 'ikon' => 'riwayat'],
                            ['route' => 'siswa.pengajuan-izin', 'label' => 'Ajukan Izin/Sakit', 'ikon' => 'pengajuan'],
                            ['route' => 'siswa.poin', 'label' => 'Poin Pelanggaran', 'ikon' => 'poin'],
                        ],
                        'guru' => [
                            ['route' => 'guru.kehadiran', 'label' => 'Kehadiran Kelas', 'ikon' => 'kehadiran'],
                        ],
                        'bk' => [
                            ['route' => 'bk.dashboard', 'label' => 'Dashboard BK', 'ikon' => 'dashboard'],
                            ['route' => 'bk.pelanggaran.index', 'label' => 'Pelanggaran Siswa', 'ikon' => 'pelanggaran'],
                            ['route' => 'bk.pengajuan-izin.index', 'label' => 'Pengajuan Izin/Sakit', 'ikon' => 'pengajuan'],
                            ['route' => 'guru.kehadiran', 'label' => 'Kehadiran Kelas', 'ikon' => 'kehadiran'],
                            ['route' => 'admin.kategori.index', 'label' => 'Kategori Pelanggaran', 'ikon' => 'kategori'],
                            ['route' => 'bk.laporan.pelanggaran', 'label' => 'Laporan', 'ikon' => 'laporan'],
                        ],
                        default => [
                            ['route' => 'admin.dashboard', 'label' => 'Dasbor Admin', 'ikon' => 'dashboard'],
                            ['route' => 'admin.siswa.index', 'label' => 'Data Siswa', 'ikon' => 'siswa'],
                            ['route' => 'admin.guru.index', 'label' => 'Data Guru & BK', 'ikon' => 'guru'],
                            ['route' => 'admin.kelas.index', 'label' => 'Data Kelas', 'ikon' => 'kelas'],
                            ['route' => 'guru.kehadiran', 'label' => 'Kehadiran Kelas', 'ikon' => 'kehadiran'],
                            ['route' => 'bk.pelanggaran.index', 'label' => 'Pelanggaran Siswa', 'ikon' => 'pelanggaran'],
                            ['route' => 'bk.pengajuan-izin.index', 'label' => 'Pengajuan Izin/Sakit', 'ikon' => 'pengajuan'],
                            ['route' => 'admin.kategori.index', 'label' => 'Kategori Pelanggaran', 'ikon' => 'kategori'],
                            ['route' => 'admin.pengaturan.lokasi', 'label' => 'Pengaturan Lokasi', 'ikon' => 'lokasi'],
                            ['route' => 'bk.laporan.pelanggaran', 'label' => 'Laporan', 'ikon' => 'laporan'],
                        ],
                    };
                @endphp

                <div>
                    <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Menu utama
                    </p>
                    <ul class="space-y-1">
                        @foreach ($menu as $item)
                            @php $aktif = request()->routeIs($item['route']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-colors {{ $aktif
                                        ? 'bg-emerald-600/15 text-emerald-300 ring-1 ring-inset ring-emerald-500/25'
                                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.8" stroke="currentColor"
                                        class="h-5 w-5 shrink-0 {{ $aktif ? 'text-emerald-400' : 'text-slate-500 group-hover:text-slate-300' }}">
                                        {!! $ikon[$item['ikon']] !!}
                                    </svg>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </nav>

            {{-- Profil --}}
            <div class="border-t border-slate-800/60 p-4">
                <div class="flex items-center gap-3 rounded-xl bg-slate-900/70 p-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-xs font-bold text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                        <p class="truncate text-[11px] uppercase tracking-wide text-slate-400">
                            {{ $user->roleLabel() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Overlay mobile --}}
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-sm lg:hidden"></div>

        {{-- Konten --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header
                class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/80 px-6 py-4 backdrop-blur">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="btn btn-soft !px-3 !py-2 lg:hidden"
                        aria-label="Buka menu">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                            @yield('title', 'Dashboard')</h1>
                        <p class="hidden text-xs text-slate-500 sm:block">
                            {{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
                <span class="badge badge-green">{{ $user->roleLabel() }}</span>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const terbuka = !sidebar.classList.contains('-translate-x-full');
            sidebar.classList.toggle('-translate-x-full', terbuka);
            overlay.classList.toggle('hidden', terbuka);
        }
    </script>
</body>

</html>
