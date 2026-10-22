@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="card-title">Daftar Kelas</h2>
                    <span class="badge badge-slate">{{ $daftar->count() }} kelas</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl tbl-compact">
                        <thead>
                            <tr>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Wali Kelas</th>
                                <th class="!text-right">Jumlah Siswa</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $k)
                                <tr>
                                    <td class="font-medium text-slate-900">{{ $k->nama }}</td>
                                    <td><span class="badge badge-blue">{{ $k->tingkat ?? '—' }}</span></td>
                                    <td>{{ $k->waliKelas?->name ?? '—' }}</td>
                                    <td class="!text-right">{{ $k->siswa_count }} siswa</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <button type="button" class="text-action tombol-ubah"
                                                data-id="{{ $k->id }}" data-nama="{{ $k->nama }}"
                                                data-tingkat="{{ $k->tingkat }}"
                                                data-wali="{{ $k->wali_kelas_id }}">Ubah</button>
                                            <form method="POST" action="{{ route('admin.kelas.destroy', $k) }}"
                                                onsubmit="return confirm('Hapus kelas ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-danger-action">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="!py-12 text-center text-slate-400">Belum ada kelas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="card p-6">
                <h2 class="text-sm font-semibold tracking-wide text-slate-900" id="form-judul">➕ Tambah Kelas</h2>
                <form method="POST" id="form-kelas" action="{{ route('admin.kelas.store') }}"
                    class="mt-5 space-y-4 text-sm">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="form-method">
                    <div>
                        <label class="label">Nama Kelas *</label>
                        <input type="text" name="nama" required placeholder="mis. XI RPL 1"
                            class="input @error('nama') input-error @enderror">
                        @error('nama')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Tingkat</label>
                        <input type="text" name="tingkat" placeholder="mis. XI"
                            class="input @error('tingkat') input-error @enderror">
                    </div>
                    <div>
                        <label class="label">Wali Kelas</label>
                        <select name="wali_kelas_id" class="input">
                            <option value="">— Belum ditentukan —</option>
                            @foreach ($guru as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->roleLabel() }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                    <button type="button" id="btn-batal" onclick="resetForm()"
                        class="btn btn-soft btn-block hidden">Batal ubah</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function isiForm(data) {
            const form = document.getElementById('form-kelas');
            form.action = '{{ url('admin/kelas') }}/' + data.id;
            document.getElementById('form-method').value = 'PUT';
            form.querySelector('[name=nama]').value = data.nama;
            form.querySelector('[name=tingkat]').value = data.tingkat || '';
            form.querySelector('[name=wali_kelas_id]').value = data.wali || '';
            document.getElementById('form-judul').textContent = '✏️ Ubah Kelas';
            document.getElementById('btn-batal').classList.remove('hidden');
            form.scrollIntoView({ behavior: 'smooth' });
        }

        document.querySelectorAll('.tombol-ubah').forEach((tombol) => {
            tombol.addEventListener('click', () => isiForm(tombol.dataset));
        });

        function resetForm() {
            const form = document.getElementById('form-kelas');
            form.action = '{{ route('admin.kelas.store') }}';
            document.getElementById('form-method').value = 'POST';
            form.reset();
            document.getElementById('form-judul').textContent = '➕ Tambah Kelas';
            document.getElementById('btn-batal').classList.add('hidden');
        }
    </script>
@endsection
