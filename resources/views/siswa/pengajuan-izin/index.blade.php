@extends('layouts.app')

@section('title', 'Ajukan Izin / Sakit')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Form pengajuan --}}
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Formulir Pengajuan</h2>
                        <p class="card-subtitle">Ajukan izin atau sakit langsung dari HP Anda.</p>
                    </div>
                    <span class="badge badge-blue">Baru</span>
                </div>

                @if ($adaMenunggu)
                    <div class="m-6 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <span>⏳</span>
                        <span>Anda memiliki pengajuan yang masih menunggu tinjauan guru BK. Ajukan lagi hanya bila
                            diperlukan.</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('siswa.pengajuan-izin.store') }}" enctype="multipart/form-data"
                    class="space-y-5 p-6">
                    @csrf
                    <div>
                        <label class="label">Jenis *</label>
                        <select name="jenis" required class="input @error('jenis') input-error @enderror">
                            @foreach (\App\Models\PengajuanIzin::SEMUA_JENIS as $nilai => $label)
                                <option value="{{ $nilai }}" {{ old('jenis') === $nilai ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                        @error('jenis')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="label">Dari Tanggal *</label>
                            <input type="date" name="tanggal_mulai" required
                                value="{{ old('tanggal_mulai', now()->toDateString()) }}" min="{{ today()->toDateString() }}"
                                class="input @error('tanggal_mulai') input-error @enderror">
                            @error('tanggal_mulai')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="label">Sampai Tanggal</label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                                min="{{ old('tanggal_mulai', now()->toDateString()) }}"
                                class="input @error('tanggal_selesai') input-error @enderror">
                            @error('tanggal_selesai')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="-mt-3 text-xs text-slate-400">Kosongkan "Sampai Tanggal" bila hanya satu hari. Maksimal
                        {{ $maksHari }} hari.</p>

                    <div>
                        <label class="label">Alasan *</label>
                        <textarea name="alasan" rows="3" required maxlength="1000"
                            placeholder="mis. demam, diperiksa dokter, ada keperluan keluarga…"
                            class="input @error('alasan') input-error @enderror">{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Foto Bukti (opsional)</label>
                        <input type="file" name="bukti" id="input-bukti" accept="image/*"
                            class="input file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 @error('bukti') input-error @enderror">
                        <p class="mt-1.5 text-xs text-slate-400">Surat dokumen/obat, maksimal 2 MB (JPG/PNG/WebP).</p>
                        @error('bukti')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                        <img id="preview-bukti" class="mt-3 hidden h-28 rounded-xl border border-slate-200 object-cover"
                            alt="Preview bukti">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">📨 Kirim Pengajuan</button>
                </form>
            </div>
        </div>

        {{-- Riwayat pengajuan --}}
        <div class="lg:col-span-2">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Riwayat Pengajuan Saya</h2>
                        <p class="card-subtitle">Pengajuan yang disetujui otomatis tercatat sebagai absensi
                            {{ \App\Models\Absensi::STATUS_IZIN }}/{{ \App\Models\Absensi::STATUS_SAKIT }}.</p>
                    </div>
                    <span class="badge badge-slate">{{ $pengajuan->total() }} pengajuan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl tbl-compact">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Jenis</th>
                                <th>Alasan</th>
                                <th class="!text-center">Bukti</th>
                                <th>Status</th>
                                <th>Ditinjau</th>
                                <th></th>
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
                                @endphp
                                <tr>
                                    <td class="font-medium text-slate-900">
                                        {{ $p->tanggal_mulai->translatedFormat('d M Y') }}
                                        @if ($p->tanggal_selesai && ! $p->tanggal_selesai->eq($p->tanggal_mulai))
                                            <span class="text-slate-400">s.d.</span>
                                            {{ $p->tanggal_selesai->translatedFormat('d M Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $p->jenis === \App\Models\PengajuanIzin::JENIS_SAKIT ? 'badge-orange' : 'badge-blue' }}">
                                            {{ $p->jenisLabel() }}</span>
                                    </td>
                                    <td class="max-w-56 text-slate-500">{{ \Illuminate\Support\Str::limit($p->alasan, 60) }}</td>
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
                                    <td><span class="badge {{ $badge }}">{{ $p->statusLabel() }}</span></td>
                                    <td class="text-slate-500">
                                        @if ($p->status === \App\Models\PengajuanIzin::STATUS_DITOLAK && $p->catatan_penolakan)
                                            <span class="block text-xs text-rose-600">{{ $p->catatan_penolakan }}</span>
                                        @endif
                                        {{ $p->ditinjauOleh?->name ?? '—' }}
                                    </td>
                                    <td>
                                        @if ($p->isMenunggu())
                                            <form method="POST" action="{{ route('siswa.pengajuan-izin.destroy', $p) }}"
                                                onsubmit="return confirm('Batalkan pengajuan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger-action">Batalkan</button>
                                            </form>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="!py-12 text-center text-slate-400">Belum ada pengajuan izin/sakit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-6 py-4">{{ $pengajuan->links() }}</div>
            </div>
        </div>
    </div>

    <script>
        const inputBukti = document.getElementById('input-bukti');
        const previewBukti = document.getElementById('preview-bukti');
        inputBukti?.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                previewBukti.src = URL.createObjectURL(file);
                previewBukti.classList.remove('hidden');
            } else {
                previewBukti.classList.add('hidden');
            }
        });
    </script>
@endsection
