@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Riwayat Absensi</h2>
                <p class="card-subtitle">{{ $siswa->user->name }} · Kelas {{ $siswa->kelas->nama ?? '-' }}</p>
            </div>
            <span class="badge badge-blue">{{ $riwayat->total() }} catatan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $item)
                        @php
                            $badge = [
                                'hadir' => 'badge-green',
                                'terlambat' => 'badge-orange',
                                'izin' => 'badge-blue',
                                'sakit' => 'badge-yellow',
                                'alfa' => 'badge-red',
                            ][$item->status] ?? 'badge-slate';
                        @endphp
                        <tr>
                            <td class="font-medium text-slate-900">{{ $item->tanggal->translatedFormat('d M Y') }}</td>
                            <td><span class="badge {{ $badge }}">{{ $item->statusLabel() }}</span></td>
                            <td>{{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) : '—' }}</td>
                            <td>{{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) : '—' }}</td>
                            <td class="text-slate-500">{{ $item->keterangan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="!py-12 text-center text-slate-400">Belum ada riwayat absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">
            {{ $riwayat->links() }}
        </div>
    </div>
@endsection
