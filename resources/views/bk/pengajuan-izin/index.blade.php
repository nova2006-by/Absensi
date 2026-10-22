@extends('layouts.app')

@section('title', 'Pengajuan Izin / Sakit Siswa')

@section('content')
    {{-- Filter --}}
    <form method="GET" action="{{ route('bk.pengajuan-izin.index') }}"
        class="card mb-6 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-44">
            <label class="label">Status</label>
            <select name="status" class="input">
                <option value="">Semua status</option>
                @foreach (\App\Models\PengajuanIzin::SEMUA_STATUS as $nilai => $label)
                    <option value="{{ $nilai }}" {{ $statusDipilih === $nilai ? 'selected' : '' }}>
                        {{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-48">
            <label class="label">Kelas</label>
            <select name="kelas" class="input">
                <option value="">Semua kelas</option>
                @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasDipilih == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-soft">Terapkan</button>
        @if ($statusDipilih || $kelasDipilih)
            <a href="{{ route('bk.pengajuan-izin.index') }}" class="text-action">Reset filter →</a>
        @endif
    </form>

    @if ($jumlahMenunggu > 0)
        <div
            class="mb-6 flex flex-wrap items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span>📬</span>
            <span><strong>{{ $jumlahMenunggu }} pengajuan</strong> menunggu tinjauan. Persetujuan akan langsung
                mencatat absensi izin/sakit pada tanggal yang diajukan.</span>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="card-header">
            <div>
                <h2 class="card-title">Daftar Pengajuan</h2>
                <p class="card-subtitle">Siswa mengajukan izin/sakit; absensi tercatat otomatis bila disetujui.</p>
            </div>
            <span class="badge badge-slate">{{ $pengajuan->total() }} pengajuan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl tbl-compact">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Periode</th>
                        <th>Jenis</th>
                        <th>Alasan</th>
                        <th class="!text-center">Bukti</th>
                        <th>Status</th>
                        <th>Ditinjau</th>
                        <th class="!text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuan as $p)
                        @php
                            $badge = [
                                \App\Models\PengajuanIzin::STATUS_MENUNGGU => 'badge-yellow',
                                \App\Models\PengajuanIzin::STATUS_DISETUJUI => 'badge-green',
                                \App\Models\PengajuanIzin::STATUS_DITOLAK => 'badge-red',
                            ][$p->status] ?? 'badge-slate';
                            $hari = $p->tanggal_mulai->diffInDays($p->tanggalAkhir()) + 1;
                        @endphp
                        <tr class="{{ $p->isMenunggu() ? 'bg-amber-50/40' : '' }}">
                            <td>
                                <a class="font-medium text-blue-700 hover:text-blue-900"
                                    href="{{ route('bk.pelanggaran.siswa', $p->siswa_id) }}">
                                    {{ $p->siswa?->user->name }}</a>
                                <p class="text-xs text-slate-400">{{ $p->siswa?->kelas?->nama }} · NIS
                                    {{ $p->siswa?->nis }}</p>
                            </td>
                            <td class="font-medium text-slate-900">
                                {{ $p->tanggal_mulai->translatedFormat('d M Y') }}
                                @if ($p->tanggal_selesai && ! $p->tanggal_selesai->eq($p->tanggal_mulai))
                                    <span class="text-slate-400">s.d.</span>
                                    {{ $p->tanggal_selesai->translatedFormat('d M Y') }}
                                @endif
                                <p class="text-xs text-slate-400">{{ $hari }} hari</p>
                            </td>
                            <td>
                                <span
                                    class="badge {{ $p->jenis === \App\Models\PengajuanIzin::JENIS_SAKIT ? 'badge-orange' : 'badge-blue' }}">
                                    {{ $p->jenisLabel() }}</span>
                            </td>
                            <td class="max-w-64 text-slate-500">{{ $p->alasan }}</td>
                            <td class="!text-center">
                                @if ($p->bukti)
                                    <a href="{{ Storage::disk('public')->url($p->bukti) }}" target="_blank">
                                        <img src="{{ Storage::disk('public')->url($p->bukti) }}" alt="Foto bukti"
                                            class="mx-auto h-10 w-10 rounded-lg object-cover ring-1 ring-slate-200">
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $badge }}">{{ $p->statusLabel() }}</span>
                                @if ($p->status === \App\Models\PengajuanIzin::STATUS_DITOLAK && $p->catatan_penolakan)
                                    <p class="mt-1 text-xs text-rose-600">{{ $p->catatan_penolakan }}</p>
                                @endif
                            </td>
                            <td class="text-slate-500">
                                {{ $p->ditinjauOleh?->name ?? '—' }}
                                @if ($p->ditinjau_at)
                                    <p class="text-xs text-slate-400">{{ $p->ditinjau_at->translatedFormat('d M Y H:i') }}</p>
                                @endif
                            </td>
                            <td>
                                @if ($p->isMenunggu())
                                    <div class="flex items-center justify-center gap-3">
                                        <form method="POST"
                                            action="{{ route('bk.pengajuan-izin.setujui', $p) }}"
                                            onsubmit="return confirm('Setujui pengajuan ini? Absensi akan tercatat otomatis.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-action">✔ Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('bk.pengajuan-izin.tolak', $p) }}"
                                            id="tolak-{{ $p->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="catatan_penolakan">
                                            <button type="submit" class="text-danger-action"
                                                onclick="isiCatatan('{{ 'tolak-' . $p->id }}')">✖ Tolak</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="!py-12 text-center text-slate-400">Tidak ada pengajuan izin/sakit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $pengajuan->links() }}</div>
    </div>

    <script>
        function isiCatatan(formId) {
            const form = document.getElementById(formId);
            const catatan = prompt('Catatan penolakan (opsional, mis. alasan kurang jelas):');
            if (catatan === null) {
                return false; // pembatal prompt = batal menolak
            }
            form.querySelector('[name="catatan_penolakan"]').value = catatan;
            return true;
        }
    </script>
@endsection
