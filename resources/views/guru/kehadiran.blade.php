@extends('layouts.app')

@section('title', 'Kehadiran Kelas')

@section('content')
    {{-- Filter --}}
    <form method="GET" action="{{ route('guru.kehadiran') }}"
        class="card mb-6 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-48">
            <label class="label">Kelas</label>
            <select name="kelas" required class="input">
                <option value="">— Pilih kelas —</option>
                @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasAktif?->id === $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} ({{ $k->siswa_count }} siswa)
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-44">
            <label class="label">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}" class="input">
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    @if ($kelasAktif)
        @php
            $chip = [
                'hadir' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'Hadir'],
                'terlambat' => ['bg-orange-50 text-orange-700 ring-orange-600/20', 'Terlambat'],
                'izin' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'Izin'],
                'sakit' => ['bg-amber-50 text-amber-700 ring-amber-600/20', 'Sakit'],
                'alfa' => ['bg-rose-50 text-rose-700 ring-rose-600/20', 'Alfa'],
                'belum_absen' => ['bg-slate-100 text-slate-600 ring-slate-500/10', 'Belum absen'],
            ];
        @endphp

        {{-- Ringkasan cepat --}}
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($chip as $status => [$warna, $label])
                <div class="rounded-2xl p-4 text-center ring-1 ring-inset {{ $warna }}">
                    <p class="text-2xl font-black tracking-tight">{{ $ringkas->get($status, 0) }}</p>
                    <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider">{{ $label }}</p>
                </div>
            @endforeach
        </div>

        @if ($pengajuanMenunggu->isNotEmpty())
            <div class="card mb-6 overflow-hidden">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Permintaan Absensi Menunggu Verifikasi</h2>
                        <p class="card-subtitle">Periksa kehadiran siswa secara langsung sebelum menyetujui permintaan.</p>
                    </div>
                    <span class="badge badge-yellow">{{ $pengajuanMenunggu->count() }} menunggu</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($pengajuanMenunggu as $pengajuan)
                        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $pengajuan->siswa->user->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    NIS {{ $pengajuan->siswa->nis }} ·
                                    {{ $pengajuan->jenis === \App\Models\AbsensiPengajuan::JENIS_MASUK ? 'Absen masuk' : 'Absen pulang' }}
                                    pukul {{ $pengajuan->diminta_pada->format('H:i') }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <form method="POST" action="{{ route('guru.kehadiran.pengajuan.setujui', $pengajuan) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success">Sahkan</button>
                                </form>
                                <form method="POST" action="{{ route('guru.kehadiran.pengajuan.tolak', $pengajuan) }}"
                                    class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <input type="text" name="catatan_penolakan" maxlength="255"
                                        placeholder="Alasan (opsional)" class="input !w-48 !py-2">
                                    <button type="submit" class="btn btn-soft text-rose-700">Tolak</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Daftar siswa --}}
        <form method="POST" action="{{ route('guru.kehadiran.tandai', $kelasAktif) }}">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Daftar Siswa — {{ $kelasAktif->nama }}</h2>
                        <p class="card-subtitle">Wali kelas: {{ $kelasAktif->waliKelas?->name ?? '—' }} · Ubah status
                            lalu tekan Simpan</p>
                    </div>
                    <span class="badge badge-blue">{{ $daftarSiswa->count() }} siswa</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftarSiswa as $s)
                                @php $absen = $s->absensi->first(); @endphp
                                <tr>
                                    <td>{{ $s->nis }}</td>
                                    <td class="font-medium text-slate-900">{{ $s->user->name }}</td>
                                    <td>
                                        <select name="status[{{ $s->id }}]" class="input !w-auto !py-2">
                                            @foreach ($semuaStatus as $nilai => $label)
                                                <option value="{{ $nilai }}"
                                                    {{ ($absen?->status ?? 'hadir') === $nilai ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="keterangan[{{ $s->id }}]"
                                            value="{{ $absen?->keterangan }}" placeholder="mis. izin orang tua"
                                            class="input !py-2">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="!py-12 text-center text-slate-400">Belum ada siswa di kelas
                                        ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($daftarSiswa->isNotEmpty())
                    <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-6 py-4">
                        <p class="text-xs text-slate-500">Perubahan langsung mengganti data absensi pada tanggal
                            terpilih.</p>
                        <button type="submit" class="btn btn-success">
                            💾 Simpan Kehadiran
                        </button>
                    </div>
                @endif
            </div>
        </form>
    @else
        <div class="card p-12 text-center">
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="h-7 w-7">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <p class="mt-4 font-medium text-slate-600">Pilih kelas dan tanggal untuk melihat daftar kehadiran siswa.
            </p>
            <p class="mt-1 text-sm text-slate-400">Gunakan formulir di atas untuk memulai.</p>
        </div>
    @endif
@endsection
