@extends('layouts.app')

@section('title', 'Tambah Poin Pelanggaran')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Tambah Poin Pelanggaran</h2>
                    <p class="card-subtitle">Pilih siswa, kategori pelanggaran, lalu poin akan terisi otomatis.</p>
                </div>
                <span class="badge badge-red">+poin</span>
            </div>

            <form method="POST" action="{{ route('bk.pelanggaran.store') }}" enctype="multipart/form-data"
                class="space-y-5 p-6">
                @csrf
                <div>
                    <label class="label">Siswa *</label>
                    <select name="siswa_id" required class="input @error('siswa_id') input-error @enderror">
                        <option value="">— Pilih siswa —</option>
                        @foreach ($siswa as $s)
                            <option value="{{ $s->id }}" data-poin="{{ $s->total_poin ?? 0 }}"
                                {{ old('siswa_id', $siswaDipilih?->id) == $s->id ? 'selected' : '' }}>
                                {{ $s->user->name }} — {{ $s->kelas?->nama }} (NIS {{ $s->nis }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-slate-500">Total poin terkini siswa terpilih:
                        <span id="poin-terpilih"
                            class="font-semibold text-slate-700">{{ $siswaDipilih?->totalPoin() ?? 0 }}</span> poin</p>
                    @error('siswa_id')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="label">Kategori Pelanggaran *</label>
                        <select id="kategori" name="kategori_pelanggaran_id" required
                            class="input @error('kategori_pelanggaran_id') input-error @enderror">
                            <option value="">— Pilih kategori —</option>
                            @foreach ($kategori as $k)
                                <option value="{{ $k->id }}" data-poin="{{ $k->poin }}"
                                    {{ old('kategori_pelanggaran_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }} (+{{ $k->poin }})
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
                            value="{{ old('poin') }}"
                            class="input @error('poin') input-error @enderror">
                        @error('poin')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="label">Tanggal Kejadian *</label>
                        <input type="date" name="tanggal" required value="{{ old('tanggal', now()->toDateString()) }}"
                            class="input @error('tanggal') input-error @enderror">
                        @error('tanggal')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                            placeholder="mis. memakai sepatu non-standar"
                            class="input @error('keterangan') input-error @enderror">
                        @error('keterangan')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Foto Bukti (opsional)</label>
                    <input type="file" name="gambar" id="input-gambar" accept="image/*"
                        class="input file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 @error('gambar') input-error @enderror">
                    <p class="mt-1.5 text-xs text-slate-400">Format JPG/PNG/WebP, maksimal 2 MB. Foto tempat/kejadian
                        pelanggaran.</p>
                    @error('gambar')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <img id="preview-gambar" class="mt-3 hidden h-28 rounded-xl border border-slate-200 object-cover"
                        alt="Preview foto bukti">
                </div>

                <div class="flex gap-3 border-t border-slate-100 pt-5">
                    <button type="submit" class="btn btn-danger">Simpan Poin</button>
                    <a href="{{ route('bk.pelanggaran.index') }}" class="btn btn-soft">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const siswaSelect = document.querySelector('[name="siswa_id"]');
        const poinTerpilih = document.getElementById('poin-terpilih');
        siswaSelect?.addEventListener('change', function() {
            poinTerpilih.textContent = this.selectedOptions[0]?.dataset.poin ?? 0;
        });

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
            } else {
                previewGambar.classList.add('hidden');
            }
        });
    </script>
@endsection
