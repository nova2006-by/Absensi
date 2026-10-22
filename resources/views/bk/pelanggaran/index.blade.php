@extends('layouts.app')

@section('title', 'Kelola Pelanggaran Siswa')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <form method="GET" action="{{ route('bk.pelanggaran.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="label">Filter siswa</label>
                <select name="siswa" class="input !w-auto">
                    <option value="">Semua siswa</option>
                    @foreach ($daftarSiswa as $s)
                        <option value="{{ $s->id }}" {{ $siswaDipilih == $s->id ? 'selected' : '' }}>
                            {{ $s->user->name }} ({{ $s->kelas?->nama }}) — {{ $s->total_poin ?? 0 }} poin
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Filter kelas</label>
                <select name="kelas" class="input !w-auto">
                    <option value="">Semua kelas</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelasDipilih == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-soft">Filter</button>
        </form>
        <a href="{{ route('bk.pelanggaran.create') }}" class="btn btn-danger">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Poin Pelanggaran
        </a>
    </div>

    {{-- Rekap poin --}}
    <div class="card mb-6 overflow-hidden">
        <div class="card-header">
            <h2 class="card-title">📊 Rekap Poin Siswa</h2>
            <span class="badge badge-slate">{{ $daftarSiswa->count() }} siswa</span>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl tbl-compact">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th class="!text-right">Total poin</th>
                        <th>Predikat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarSiswa as $s)
                        @php $poin = $s->total_poin ?? 0; @endphp
                        <tr>
                            <td>{{ $s->nis }}</td>
                            <td class="font-medium text-slate-900">{{ $s->user->name }}</td>
                            <td>{{ $s->kelas?->nama ?? '—' }}</td>
                            <td class="!text-right font-bold {{ $poin >= 20 ? 'text-rose-600' : ($poin > 0 ? 'text-orange-500' : 'text-slate-400') }}">
                                {{ $poin }}</td>
                            <td>
                                @if ($poin >= 40)
                                    <span class="badge badge-red">Perlu pembinaan intensif</span>
                                @elseif ($poin >= 20)
                                    <span class="badge badge-orange">Perlu perhatian</span>
                                @elseif ($poin > 0)
                                    <span class="badge badge-yellow">Ringan</span>
                                @else
                                    <span class="badge badge-green">Tertib</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('bk.pelanggaran.siswa', $s->id) }}"
                                    class="text-action">Rincian →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!py-12 text-center text-slate-400">Belum ada siswa terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Catatan pelanggaran --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <h2 class="card-title">Catatan Pelanggaran</h2>
            @if ($siswaDipilih)
                <a href="{{ route('bk.pelanggaran.index') }}" class="text-action">Tampilkan semua →</a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="tbl tbl-compact">
                <thead>                            <tr>
                                <th>Tanggal</th>
                                <th>Siswa</th>
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
                            <td>
                                {{ $p->siswa?->user->name }}
                                <span class="text-xs text-slate-400">({{ $p->siswa?->kelas?->nama }})</span>
                            </td>
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
                            <td colspan="8" class="!py-12 text-center text-slate-400">Tidak ada catatan pelanggaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">
            {{ $pelanggaran->links() }}
        </div>
    </div>
@endsection
