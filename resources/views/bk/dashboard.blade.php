@extends('layouts.app')

@section('title', 'Dashboard Guru BK')

@section('content')
    {{-- Filter --}}
    <form method="GET" action="{{ route('bk.dashboard') }}"
        class="card mb-6 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-48">
            <label class="label">Kelas (opsional)</label>
            <select name="kelas" class="input">
                <option value="">Semua kelas</option>
                @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasDipilih?->id === $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-44">
            <label class="label">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}" class="input">
        </div>
        <button type="submit" class="btn btn-primary">Terapkan</button>
    </form>

    @if ($pengajuanMenunggu > 0)
        <a href="{{ route('bk.pengajuan-izin.index', ['status' => 'menunggu']) }}"
            class="mb-6 flex flex-wrap items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900 transition hover:bg-amber-100">
            <span>📬</span>
            <span><strong>{{ $pengajuanMenunggu }} pengajuan izin/sakit</strong> menunggu tinjauan Anda → tinjau sekarang</span>
        </a>
    @endif

    @php
        $hadir = ($kehadiranHariIni['hadir'] ?? 0) + ($kehadiranHariIni['terlambat'] ?? 0);
        $tidakHadir = $kehadiranHariIni['alfa'] ?? 0;
    @endphp

    {{-- Kartu statistik --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-emerald-50 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Hadir
                    ({{ \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('d M Y') }})</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $hadir }}
                    <span class="text-sm font-semibold text-slate-500">siswa</span></p>
                <p class="mt-0.5 text-xs text-slate-400">dari {{ $totalSiswa }} siswa terdaftar</p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-rose-50 text-rose-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Alfa hari ini</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $tidakHadir }}
                    <span class="text-sm font-semibold text-slate-500">siswa</span></p>
                <p class="mt-0.5 text-xs text-slate-400">Izin: {{ $kehadiranHariIni['izin'] ?? 0 }} · Sakit:
                    {{ $kehadiranHariIni['sakit'] ?? 0 }}</p>
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
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Alfa 7 hari terakhir</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $alfaMingguIni }}
                    <span class="text-sm font-semibold text-slate-500">kejadian</span></p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-violet-50 text-violet-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Siswa poin tertinggi</p>
                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900">
                    {{ $siswaBerisiko->first()?->total_poin ?? 0 }}
                    <span class="text-sm font-semibold text-slate-500">poin</span></p>
                <p class="mt-0.5 truncate text-xs text-slate-400">
                    {{ $siswaBerisiko->first()?->user->name ?? 'Tidak ada pelanggaran' }}</p>
            </div>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="card-title">🥧 Kehadiran Hari Ini</h2>
            </div>
            <div class="relative h-64 p-4">
                <canvas id="grafik-kehadiran"></canvas>
                @if ($kehadiranHariIni->sum() === 0)
                    <p class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                        Belum ada data kehadiran untuk tanggal ini.
                    </p>
                @endif
            </div>
        </div>

        <div class="card overflow-hidden lg:col-span-2">
            <div class="card-header">
                <h2 class="card-title">📊 Total Poin Pelanggaran (6 Bulan Terakhir)</h2>
                <span class="badge badge-slate">{{ $kelasDipilih?->nama ?? 'Semua kelas' }}</span>
            </div>
            <div class="relative h-64 p-4">
                <canvas id="grafik-pelanggaran-bulan"></canvas>
                @if (array_sum($grafikBulan['poin']) === 0)
                    <p class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                        Belum ada data poin pelanggaran dalam 6 bulan terakhir.
                    </p>
                @endif
            </div>
        </div>

        <div class="card overflow-hidden lg:col-span-3">
            <div class="card-header">
                <h2 class="card-title">🏆 Top 5 Siswa Poin Tertinggi</h2>
            </div>
            <div class="relative h-56 p-4">
                <canvas id="grafik-top-siswa"></canvas>
                @if ($siswaBerisiko->isEmpty())
                    <p class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                        Belum ada data siswa dengan poin pelanggaran.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <script>
        window.GRAFIK = {
            kehadiran: @json($kehadiranHariIni),
            labelStatus: @json(\App\Models\Absensi::SEMUA_STATUS),
            bulan: @json($grafikBulan),
            topSiswa: @json($siswaBerisiko->take(5)->map(fn ($s) => [
                'nama' => $s->user->name,
                'poin' => (int) ($s->total_poin ?? 0),
            ])),
        };
    </script>
    @vite(['resources/js/dashboard.js'])

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        {{-- Siswa poin tertinggi --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="card-title">⭐ Siswa dengan Poin Tertinggi</h2>
                <a href="{{ route('bk.pelanggaran.index') }}" class="text-action">Kelola →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="tbl tbl-compact">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th class="!text-right">Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($siswaBerisiko as $s)
                            <tr>
                                <td>
                                    <a class="font-medium text-emerald-700 hover:text-emerald-900"
                                        href="{{ route('bk.pelanggaran.index', ['siswa' => $s->id]) }}">
                                        {{ $s->user->name }}</a>
                                </td>
                                <td>{{ $s->kelas->nama ?? '—' }}</td>
                                <td class="!text-right">
                                    <span
                                        class="badge {{ $s->total_poin >= 20 ? 'badge-red' : 'badge-orange' }}">
                                        {{ $s->total_poin }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="!py-12 text-center text-slate-400">Belum ada pelanggaran. 🎉</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pelanggaran terbaru --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="card-title">🕒 Pelanggaran Terbaru</h2>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($pelanggaranTerbaru as $p)
                    <li class="flex items-center justify-between gap-4 px-6 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">
                                {{ $p->siswa?->user->name }}
                                <span class="font-normal text-slate-400">({{ $p->siswa?->kelas?->nama }})</span>
                            </p>
                            <p class="truncate text-xs text-slate-500">{{ $p->kategori?->nama }} ·
                                {{ $p->tanggal->translatedFormat('d M Y') }}</p>
                        </div>
                        <span class="badge badge-red shrink-0">+{{ $p->poin }} poin</span>
                    </li>
                @empty
                    <li class="px-6 py-12 text-center text-sm text-slate-400">Belum ada pelanggaran tercatat.</li>
                @endforelse
            </ul>
        </div>

        {{-- Siswa sering alfa --}}
        <div class="card overflow-hidden xl:col-span-2">
            <div class="card-header">
                <h2 class="card-title">🚨 Siswa Sering Alfa (30 hari terakhir)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="tbl tbl-compact">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th class="!text-right">Jumlah alfa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($siswaAlfa as $item)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $item->siswa->user->name }}</td>
                                <td>{{ $item->siswa->kelas?->nama ?? '—' }}</td>
                                <td class="!text-right"><span class="badge badge-red">{{ $item->jumlah }} kali</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="!py-12 text-center text-slate-400">Tidak ada siswa yang alfa
                                    bulan ini. 🎉</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
