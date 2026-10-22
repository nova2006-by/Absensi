@extends('layouts.app')

@section('title', 'Poin Pelanggaran Saya')

@section('content')
    <div class="card">
        <div class="flex flex-wrap items-center justify-between gap-6 p-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Poin Pelanggaran</h2>
                <p class="mt-0.5 text-sm text-slate-500">{{ $siswa->user->name }} · Kelas
                    {{ $siswa->kelas->nama ?? '-' }} · NIS {{ $siswa->nis }}</p>
            </div>
            <div
                class="rounded-2xl px-8 py-4 text-center ring-1 ring-inset {{ $totalPoin >= 20
                    ? 'bg-rose-50 text-rose-700 ring-rose-600/20'
                    : ($totalPoin > 0 ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20') }}">
                <p class="text-3xl font-black tracking-tight">{{ $totalPoin }}</p>
                <p class="text-xs font-medium uppercase tracking-wider">poin total</p>
            </div>
        </div>

        @if ($totalPoin >= 20)
            <div class="mx-6 mb-2 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <span>⚠️</span> Poin Anda tergolong tinggi. Segera hubungi guru BK untuk pembinaan.
            </div>
        @elseif ($totalPoin > 0)
            <div class="mx-6 mb-2 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <span>💡</span> Jaga terus perilaku dan kedisiplinanmu ya!
            </div>
        @else
            <div class="mx-6 mb-2 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <span>🎉</span> Bagus! Belum ada catatan pelanggaran.
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="tbl tbl-compact">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pelanggaran</th>
                        <th class="!text-right">Poin</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pelanggaran as $item)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $item->tanggal->translatedFormat('d M Y') }}</td>
                            <td>{{ $item->kategori->nama ?? '—' }}</td>
                            <td class="!text-right"><span class="badge badge-red">+{{ $item->poin }}</span></td>
                            <td class="text-slate-500">{{ $item->keterangan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="!py-12 text-center text-slate-400">Tidak ada catatan pelanggaran.
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
