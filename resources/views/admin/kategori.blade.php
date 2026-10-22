@extends('layouts.app')

@section('title', 'Kategori Pelanggaran')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="card-title">Daftar Kategori Pelanggaran</h2>
                    <span class="badge badge-slate">{{ $daftar->count() }} kategori</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl tbl-compact">
                        <thead>
                            <tr>
                                <th>Pelanggaran</th>
                                <th class="!text-right">Poin</th>
                                <th class="!text-right">Dipakai</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $k)
                                <tr>
                                    <td class="font-medium text-slate-900">{{ $k->nama }}</td>
                                    <td class="!text-right"><span class="badge badge-red">+{{ $k->poin }}</span></td>
                                    <td class="!text-right text-slate-400">{{ $k->pelanggaran_count }} catatan</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <button type="button" class="text-action"
                                                onclick="isiForm({{ $k->id }}, '{{ $k->nama }}', {{ $k->poin }})">Ubah</button>
                                            <form method="POST"
                                                action="{{ route('admin.kategori-pelanggaran.destroy', $k) }}"
                                                onsubmit="return confirm('Hapus kategori ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-danger-action">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="!py-12 text-center text-slate-400">Belum ada kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="card p-6">
                <h2 class="text-sm font-semibold tracking-wide text-slate-900" id="form-judul">➕ Tambah Kategori</h2>
                <form method="POST" id="form-kategori" action="{{ route('admin.kategori-pelanggaran.store') }}"
                    class="mt-5 space-y-4 text-sm">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="form-method">
                    <div>
                        <label class="label">Nama Pelanggaran *</label>
                        <input type="text" name="nama" required placeholder="mis. Tidak memakai seragam"
                            class="input @error('nama') input-error @enderror">
                        @error('nama')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Poin (1–100) *</label>
                        <input type="number" name="poin" required min="1" max="100"
                            class="input @error('poin') input-error @enderror">
                        @error('poin')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                    <button type="button" id="btn-batal" onclick="resetForm()"
                        class="btn btn-soft btn-block hidden">Batal ubah</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function isiForm(id, nama, poin) {
            const form = document.getElementById('form-kategori');
            form.action = '{{ url('admin/kategori-pelanggaran') }}/' + id;
            document.getElementById('form-method').value = 'PUT';
            form.querySelector('[name=nama]').value = nama;
            form.querySelector('[name=poin]').value = poin;
            document.getElementById('form-judul').textContent = '✏️ Ubah Kategori';
            document.getElementById('btn-batal').classList.remove('hidden');
            form.scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            const form = document.getElementById('form-kategori');
            form.action = '{{ route('admin.kategori-pelanggaran.store') }}';
            document.getElementById('form-method').value = 'POST';
            form.reset();
            document.getElementById('form-judul').textContent = '➕ Tambah Kategori';
            document.getElementById('btn-batal').classList.add('hidden');
        }
    </script>
@endsection
