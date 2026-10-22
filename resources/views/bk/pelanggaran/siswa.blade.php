@extends('layouts.app')

@section('title', 'Riwayat Poin Siswa')

@section('content')
    {{-- Kartu profil & ringkasan --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <div class="card p-6 lg:col-span-2">
            <div class="flex flex-wrap items-center gap-4">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-lg font-bold text-white">
                    {{ strtoupper(substr($siswa->user->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-lg font-bold tracking-tight text-slate-900">{{ $siswa->user->name }}</p>
                    <p class="text-sm text-slate-500">
                        NIS {{ $siswa->nis }} &nbsp;•&nbsp;
                        NISN {{ $siswa->nisn ?? '—' }} &nbsp;•&nbsp;
                        Kelas {{ $siswa->kelas?->nama ?? '—' }}
                    </p>
                    <p class="text-xs text-slate-400">Wali kelas: {{ $siswa->kelas?->waliKelas?->name ?? '—' }}</p>
                </div>
                @php $poin = $totalPoin; @endphp
                <div class="text-right">
                    @if ($poin >= 40)
                        <span class="badge badge-red">Perlu pembinaan intensif</span>
                    @elseif ($poin >= 20)
                        <span class="badge badge-orange">Perlu perhatian</span>
                    @elseif ($poin > 0)
                        <span class="badge badge-yellow">Ringan</span>
                    @else
                        <span class="badge badge-green">Tertib</span>
                    @endif
                    <p class="mt-2 text-3xl font-extrabold tracking-tight {{ $poin >= 20 ? 'text-rose-600' : ($poin > 0 ? 'text-orange-500' : 'text-slate-400') }}">
                        {{ $poin }}
                    </p>
                    <p class="text-[11px] uppercase tracking-widest text-slate-400">total poin</p>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-sm font-semibold tracking-wide text-slate-900">Absensi bulan ini</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                @foreach (\App\Models\Absensi::SEMUA_STATUS as $nilai => $label)
                    <div class="rounded-xl bg-slate-50 px-3 py-2">
                        <p class="text-xs text-slate-500">{{ $label }}</p>
                        <p class="font-bold text-slate-900">{{ $absensiBulanIni[$nilai] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Riwayat pelanggaran --}}
        <div class="xl:col-span-2">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="card-title">Riwayat Pelanggaran ({{ $jumlahPelanggaran }} catatan)</h2>
                    <a href="{{ route('bk.pelanggaran.create', ['siswa' => $siswa->id]) }}" class="btn btn-danger !py-2">
                        + Tambah Poin
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl tbl-compact">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pelanggaran</th>
                                <th class="!text-right">Poin</th>
                                <th>Keterangan</th>
                                <th class="!text-center">Bukti</th>
                                <th>Dicatat oleh</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pelanggaran as $p)
                                <tr>
                                    <td class="font-medium text-slate-900">{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                                    <td>{{ $p->kategori?->nama }}</td>
                                    <td class="!text-right"><span class="badge badge-red">+{{ $p->poin }}</span></td>
                                    <td class="text-slate-500">{{ $p->keterangan ?? '—' }}</td>
                                    <td class="!text-center">
                                        @if ($p->gambar)
                                            <a href="{{ Storage::disk('public')->url($p->gambar) }}" target="_blank">
                                                <img src="{{ Storage::disk('public')->url($p->gambar) }}" alt="Foto bukti"
                                                    class="mx-auto h-10 w-10 rounded-lg object-cover ring-1 ring-slate-200">
                                            </a>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="text-slate-500">{{ $p->ditambahkanOleh?->name ?? '—' }}</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('bk.pelanggaran.edit', $p) }}" class="text-action">Ubah</a>
                                            <form method="POST" action="{{ route('bk.pelanggaran.destroy', $p) }}"
                                                onsubmit="return confirm('Hapus catatan pelanggaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger-action">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="!py-12 text-center text-slate-400">Belum ada pelanggaran.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-6 py-4">{{ $pelanggaran->links() }}</div>
            </div>
        </div>

        {{-- Rekap per kategori --}}
        <div>
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="card-title">Rekap per Kategori</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl tbl-compact">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="!text-center">Jml</th>
                                <th class="!text-right">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perKategori as $k)
                                <tr>
                                    <td>{{ $k->nama }}</td>
                                    <td class="!text-center">{{ $k->jumlah }}</td>
                                    <td class="!text-right font-semibold text-rose-600">{{ $k->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="!py-8 text-center text-slate-400">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <a href="{{ route('bk.pelanggaran.index') }}"
                class="btn btn-soft btn-block mt-6">← Kembali ke daftar pelanggaran</a>
        </div>
    </div>
@endsection
