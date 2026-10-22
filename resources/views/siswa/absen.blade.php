@extends('layouts.app')

@section('title', 'Absensi Saya')

@section('content')
    {{-- Kartu ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card flex items-start gap-4 p-5">
            <div
                class="icon-tile {{ $absensiHariIni?->status === \App\Models\Absensi::STATUS_HADIR ? 'bg-emerald-50 text-emerald-600' : ($absensiHariIni ? 'bg-orange-50 text-orange-600' : 'bg-slate-100 text-slate-500') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Hari ini</p>
                <p class="mt-1 truncate text-lg font-bold text-slate-900">
                    {{ $absensiHariIni?->statusLabel() ?? 'Belum absen' }}</p>
                @if ($absensiHariIni?->jam_masuk)
                    <p class="mt-0.5 text-xs text-slate-500">Masuk {{ substr($absensiHariIni->jam_masuk, 0, 5) }}
                        @if ($absensiHariIni->jam_pulang)
                            · Pulang {{ substr($absensiHariIni->jam_pulang, 0, 5) }}
                        @endif
                    </p>
                @endif
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-blue-50 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Hadir bulan ini</p>
                <p class="mt-1 text-lg font-bold text-slate-900">
                    {{ ($rekapBulan['hadir'] ?? 0) + ($rekapBulan['terlambat'] ?? 0) }} hari</p>
                <p class="mt-0.5 text-xs text-slate-500">Terlambat: {{ $rekapBulan['terlambat'] ?? 0 }} · Alfa:
                    {{ $rekapBulan['alfa'] ?? 0 }}</p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile bg-amber-50 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Izin / Sakit</p>
                <p class="mt-1 text-lg font-bold text-slate-900">
                    {{ ($rekapBulan['izin'] ?? 0) + ($rekapBulan['sakit'] ?? 0) }} hari</p>
                <p class="mt-0.5 text-xs text-slate-500">Izin: {{ $rekapBulan['izin'] ?? 0 }} · Sakit:
                    {{ $rekapBulan['sakit'] ?? 0 }}</p>
            </div>
        </div>

        <div class="card flex items-start gap-4 p-5">
            <div class="icon-tile {{ $totalPoin > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Poin pelanggaran saya</p>
                <p class="mt-1 text-lg font-bold {{ $totalPoin > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $totalPoin }} poin</p>
                <a href="{{ route('siswa.poin') }}" class="mt-0.5 inline-block text-xs font-semibold text-emerald-700 hover:text-emerald-900">Lihat rincian →</a>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-5">
        {{-- Absen hari ini --}}
        <div class="card lg:col-span-3">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Absen Hari Ini</h2>
                    <p class="card-subtitle">
                        Tanggal {{ now()->translatedFormat('d F Y') }} · Masuk {{ $jamMasukMulai }}–{{ $jamMasukBatas }}
                        (terlambat setelah {{ $jamMasuk }}) · Pulang {{ $jamPulang }}–{{ $jamPulangBatas }}
                    </p>
                </div>
                @php
                    $menungguVerifikasi = $pengajuanMasukHariIni?->status === \App\Models\AbsensiPengajuan::STATUS_MENUNGGU
                        || $pengajuanPulangHariIni?->status === \App\Models\AbsensiPengajuan::STATUS_MENUNGGU;
                @endphp
                <span class="badge {{ $absensiHariIni ? 'badge-green' : ($menungguVerifikasi ? 'badge-yellow' : 'badge-slate') }}">
                    {{ $absensiHariIni ? 'Tercatat' : ($menungguVerifikasi ? 'Menunggu verifikasi' : 'Belum absen') }}
                </span>
            </div>
            <div class="p-6">
                @if (! $absensiHariIni)
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                        @if ($pengajuanMasukHariIni?->status === \App\Models\AbsensiPengajuan::STATUS_MENUNGGU)
                            <p class="font-semibold text-amber-700">Permintaan masuk menunggu verifikasi guru.</p>
                            <p class="mt-2 text-xs text-slate-500">Diajukan pukul {{ $pengajuanMasukHariIni->diminta_pada->format('H:i') }}. Absensi belum dihitung hadir sebelum disahkan.</p>
                        @else
                            @if ($pengajuanMasukHariIni?->status === \App\Models\AbsensiPengajuan::STATUS_DITOLAK)
                                <p class="mb-3 text-sm text-rose-700">
                                    Permintaan sebelumnya ditolak.
                                    @if ($pengajuanMasukHariIni->catatan_penolakan)
                                        Catatan guru: {{ $pengajuanMasukHariIni->catatan_penolakan }}
                                    @endif
                                </p>
                            @else
                                <p class="text-sm text-slate-500">Anda belum mengajukan absensi masuk hari ini.</p>
                            @endif
                            <form method="POST" action="{{ route('siswa.absen.masuk') }}" class="mt-4" data-absen-lokasi>
                                @csrf
                                <input type="hidden" name="latitude">
                                <input type="hidden" name="longitude">
                                <input type="hidden" name="accuracy">
                                <button type="submit" class="btn btn-success btn-block !py-3.5 text-base">
                                    ✅ Ajukan Absen Masuk
                                </button>
                                <p class="mt-3 text-xs text-slate-500" data-lokasi-status role="status">
                                    Lokasi hanya sinyal tambahan; guru perlu mengesahkan kehadiran Anda.
                                </p>
                            </form>
                        @endif
                    </div>
                @elseif (! $absensiHariIni->isHadir())
                    <div
                        class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="mt-0.5 h-4 w-4 shrink-0">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 16Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 11Zm0-2.25a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
                                clip-rule="evenodd" />
                        </svg>
                        Kehadiran Anda hari ini ditandai
                        <strong>{{ $absensiHariIni->statusLabel() }}</strong> oleh guru.
                        @if ($absensiHariIni->keterangan)
                            <span class="block mt-1 text-xs text-slate-500">Keterangan:
                                {{ $absensiHariIni->keterangan }}</span>
                        @endif
                        Hubungi guru Anda bila ada kesalahan.
                    </div>
                @elseif (! $absensiHariIni->jam_pulang)
                    <div
                        class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="mt-0.5 h-4 w-4 shrink-0">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                clip-rule="evenodd" />
                        </svg>
                        Anda sudah absen masuk pukul {{ $absensiHariIni->jam_masuk ? substr($absensiHariIni->jam_masuk, 0, 5) : '—' }}.
                    </div>
                    @if ($pengajuanPulangHariIni?->status === \App\Models\AbsensiPengajuan::STATUS_MENUNGGU)
                        <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Permintaan pulang pukul {{ $pengajuanPulangHariIni->diminta_pada->format('H:i') }} menunggu verifikasi guru.
                        </p>
                    @else
                        @if ($pengajuanPulangHariIni?->status === \App\Models\AbsensiPengajuan::STATUS_DITOLAK)
                            <p class="mt-4 text-sm text-rose-700">
                                Permintaan pulang sebelumnya ditolak.
                                @if ($pengajuanPulangHariIni->catatan_penolakan)
                                    Catatan guru: {{ $pengajuanPulangHariIni->catatan_penolakan }}
                                @endif
                            </p>
                        @endif
                        <form method="POST" action="{{ route('siswa.absen.pulang') }}" class="mt-4" data-absen-lokasi>
                            @csrf
                            <input type="hidden" name="latitude">
                            <input type="hidden" name="longitude">
                            <input type="hidden" name="accuracy">
                            <button type="submit" class="btn btn-primary btn-block !py-3.5 text-base">
                                🏠 Ajukan Absen Pulang
                            </button>
                            <p class="mt-3 text-xs text-slate-500" data-lokasi-status role="status">
                                Lokasi hanya sinyal tambahan; guru perlu mengesahkan kepulangan Anda.
                            </p>
                        </form>
                    @endif
                @else
                    <div
                        class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="mt-0.5 h-4 w-4 shrink-0">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Absensi hari ini sudah lengkap (masuk
                            {{ $absensiHariIni->jam_masuk ? substr($absensiHariIni->jam_masuk, 0, 5) : '—' }}, pulang
                            {{ $absensiHariIni->jam_pulang ? substr($absensiHariIni->jam_pulang, 0, 5) : '—' }}). Sampai jumpa besok!</span>
                    </div>
                @endif

                @if ($absensiHariIni && $absensiHariIni->status === \App\Models\Absensi::STATUS_TERLAMBAT)
                    <p class="mt-4 flex items-start gap-2 text-xs font-medium text-orange-600">
                        <span>⚠️</span> Anda tercatat terlambat hari ini (lewat {{ $jamMasuk }}).
                    </p>
                @endif
            </div>
        </div>

        {{-- Profil --}}
        <div class="card lg:col-span-2">
            <div class="card-header">
                <h2 class="card-title">Profil Saya</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-lg font-bold text-white">
                        {{ strtoupper(substr($siswa->user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-bold text-slate-900">{{ $siswa->user->name }}</p>
                        <p class="truncate text-sm text-slate-500">{{ $siswa->user->email }}</p>
                    </div>
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">NIS / NISN</dt>
                        <dd class="font-semibold text-slate-900">{{ $siswa->nis }} / {{ $siswa->nisn ?? '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">Kelas</dt>
                        <dd class="font-semibold text-slate-900">{{ $siswa->kelas->nama ?? '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Wali kelas</dt>
                        <dd class="font-semibold text-slate-900">{{ $siswa->kelas?->waliKelas?->name ?? '-' }}</dd>
                    </div>
                </dl>

                <a href="{{ route('siswa.riwayat') }}" class="btn btn-soft btn-block mt-6">
                    Lihat riwayat absensi →
                </a>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-absen-lokasi]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.lokasiSiap === 'true') {
                    delete form.dataset.lokasiSiap;
                    return;
                }

                event.preventDefault();

                const tombol = form.querySelector('button[type="submit"]');
                const status = form.querySelector('[data-lokasi-status]');

                if (!navigator.geolocation) {
                    status.textContent = 'Browser ini tidak mendukung akses lokasi.';
                    return;
                }

                tombol.disabled = true;
                status.textContent = 'Memeriksa lokasi perangkat...';

                navigator.geolocation.getCurrentPosition(
                    (posisi) => {
                        form.querySelector('[name="latitude"]').value = posisi.coords.latitude;
                        form.querySelector('[name="longitude"]').value = posisi.coords.longitude;
                        form.querySelector('[name="accuracy"]').value = posisi.coords.accuracy;
                        form.dataset.lokasiSiap = 'true';
                        status.textContent = 'Lokasi diterima sebagai sinyal tambahan. Mengirim permintaan untuk diverifikasi guru...';
                        tombol.disabled = false;
                        form.requestSubmit(tombol);
                    },
                    () => {
                        status.textContent = 'Lokasi gagal didapat. Izinkan akses lokasi dan pastikan GPS aktif.';
                        tombol.disabled = false;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            });
        });
    </script>
@endsection
