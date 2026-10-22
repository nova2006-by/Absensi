@extends('layouts.app')

@section('title', 'Ubah Pelanggaran')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Ubah Catatan Pelanggaran</h2>
                    <p class="card-subtitle">Perbarui kategori, poin, tanggal, atau keterangan pelanggaran.</p>
                </div>
                <span class="badge badge-blue">Edit</span>
            </div>

            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 text-sm">
                <p class="font-semibold text-slate-900">{{ $pelanggaran->siswa?->user->name }}</p>
                <p class="text-xs text-slate-500">
                    NIS {{ $pelanggaran->siswa?->nis }} &nbsp;•&nbsp;
                    Kelas {{ $pelanggaran->siswa?->kelas?->nama }} &nbsp;•&nbsp;
                    Dicatat oleh {{ $pelanggaran->ditambahkanOleh?->name ?? '—' }}
                </p>
            </div>

            <form method="POST" action="{{ route('bk.pelanggaran.update', $pelanggaran) }}" enctype="multipart/form-data"
                class="space-y-5 p-6">
                @csrf
                @method('PUT')

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="label">Kategori Pelanggaran *</label>
                        <select id="kategori" name="kategori_pelanggaran_id" required
                            class="input @error('kategori_pelanggaran_id') input-error @enderror">
                            @foreach ($kategori as $k)
                                <option value="{{ $k->id }}" data-poin="{{ $k->poin }}"
                                    {{ old('kategori_pelanggaran_id', $pelanggaran->kategori_pelanggaran_id) == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }} (+{{ $k->poin }})
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_pelanggaran_id')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Poin *</label>
                        <input type="number" id="poin" name="poin" min="1" max="100" required
                            value="{{ old('poin', $pelanggaran->poin) }}"
                            class="input @error('poin') input-error @enderror">
                        @error('poin')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="label">Tanggal Kejadian *</label>
                        <input type="date" name="tanggal" required
                            value="{{ old('tanggal', $pelanggaran->tanggal->toDateString()) }}"
                            class="input @error('tanggal') input-error @enderror">
                        @error('tanggal')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Keterangan</label>
                        <input type="text" name="keterangan"
                            value="{{ old('keterangan', $pelanggaran->keterangan) }}"
                            placeholder="mis. memakai sepatu non-standar"
                            class="input @error('keterangan') input-error @enderror">
                        @error('keterangan')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Foto Bukti</label>
                    @if ($pelanggaran->gambar)
                        <div class="mb-3 flex items-center gap-4">
                            <img id="gambar-saat-ini" src="{{ Storage::disk('public')->url($pelanggaran->gambar) }}" alt="Foto bukti pelanggaran"
                                class="h-28 rounded-xl border border-slate-200 object-cover">
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="hapus_gambar" value="1" class="h-4 w-4 rounded border-slate-300"
                                    onchange="document.getElementById('gambar-saat-ini').style.opacity = this.checked ? '0.25' : '1'">
                                Hapus foto saat ini
                            </label>
                        </div>
                    @endif
                    <input type="file" name="gambar" id="input-gambar" accept="image/*"
                        class="input file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 @error('gambar') input-error @enderror">
                    <p class="mt-1.5 text-xs text-slate-400">Kosongkan jika foto tidak berubah. Format JPG/PNG/WebP,
                        maksimal 2 MB.</p>
                    @error('gambar')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <img id="preview-gambar" class="mt-3 hidden h-28 rounded-xl border border-slate-200 object-cover"
                        alt="Preview foto bukti">
                </div>

                <div class="flex gap-3 border-t border-slate-100 pt-5">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('bk.pelanggaran.siswa', $pelanggaran->siswa_id) }}" class="btn btn-soft">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const kategori = document.getElementById('kategori');
        const poin = document.getElementById('poin');
        kategori?.addEventListener('change', function() {
            const opsi = this.selectedOptions[0];
            if (opsi?.dataset.poin) {
                poin.value = opsi.dataset.poin;
            }
        });

        const inputGambar = document.getElementById('input-gambar');
        const previewGambar = document.getElementById('preview-gambar');
        inputGambar?.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                previewGambar.src = URL.createObjectURL(file);
                previewGambar.classList.remove('hidden');
            }
        });
    </script>
@endsection
