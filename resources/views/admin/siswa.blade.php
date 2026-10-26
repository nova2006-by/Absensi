@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
    {{-- Toolbar pencarian --}}
    <form method="GET" action="{{ route('admin.siswa.index') }}"
        class="card mb-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-48 flex-1">
            <label class="label">Cari</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Nama atau NIS…" class="input">
        </div>
        <div class="min-w-44">
            <label class="label">Kelas</label>
            <select name="kelas" class="input">
                <option value="">Semua kelas</option>
                @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasDipilih == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-soft">Cari</button>
        <a href="{{ route('admin.siswa.import.template') }}" class="btn btn-soft">⬇️ Unduh Template CSV</a>
        <button type="button" onclick="document.getElementById('panel-import').classList.toggle('hidden')"
            class="btn btn-primary">📤 Import CSV</button>
    </form>

    {{-- Panel import siswa massal --}}
    <div id="panel-import" class="card mb-6 hidden p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold tracking-wide text-slate-900">Import Siswa dari CSV</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Kolom wajib: <code class="rounded bg-slate-100 px-1">name</code>,
                    <code class="rounded bg-slate-100 px-1">email</code>,
                    <code class="rounded bg-slate-100 px-1">nis</code>,
                    <code class="rounded bg-slate-100 px-1">kelas</code>,
                    <code class="rounded bg-slate-100 px-1">password</code>
                    (nama kelas harus sudah ada, contoh: X RPL 1).
                    Kolom opsional: <code class="rounded bg-slate-100 px-1">nisn</code>.
                    Isi password unik 12-72 karakter untuk setiap siswa dan bagikan melalui jalur aman.
                    Hapus file CSV setelah kredensial dibagikan.
                </p>
            </div>
            <a href="{{ route('admin.siswa.import.template') }}" class="text-action">⬇️ Unduh template</a>
        </div>
        <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data"
            class="mt-4 flex flex-wrap items-end gap-3">
            @csrf
            <div class="min-w-64 flex-1">
                <label class="label">File CSV</label>
                <input type="file" name="file" accept=".csv,.txt" required
                    class="input file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700">
                @error('file')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Import Sekarang</button>
        </form>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- min-w-0: tanpa ini, lebar tabel memaksa grid melebar & halaman bisa
             digeser ke samping di layar HP. --}}
        <div class="min-w-0 xl:col-span-2">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="card-title">Daftar Siswa</h2>
                    <span class="badge badge-slate">{{ $daftar->total() }} siswa</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl tbl-compact">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th class="!text-right">Poin</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $s)
                                <tr>
                                    <td>{{ $s->nis }}</td>
                                    <td>
                                        <p class="font-medium text-slate-900">{{ $s->user->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $s->user->email }}</p>
                                    </td>
                                    <td>{{ $s->kelas?->nama ?? '—' }}</td>
                                    <td class="!text-right">
                                        <span
                                            class="badge {{ ($s->total_poin ?? 0) > 0 ? 'badge-red' : 'badge-slate' }}">
                                            {{ $s->total_poin ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <button type="button" class="text-action tombol-ubah"
                                                data-id="{{ $s->id }}" data-name="{{ $s->user->name }}"
                                                data-email="{{ $s->user->email }}" data-nis="{{ $s->nis }}"
                                                data-nisn="{{ $s->nisn }}" data-kelas="{{ $s->kelas_id }}">Ubah</button>
                                            <form method="POST" action="{{ route('admin.siswa.destroy', $s) }}"
                                                onsubmit="return confirm('Hapus siswa beserta akun & datanya?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-danger-action">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="!py-12 text-center text-slate-400">Tidak ada siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-6 py-4">{{ $daftar->links() }}</div>
            </div>
        </div>

        <div class="min-w-0">
            <div class="card p-6">
                <h2 class="text-sm font-semibold tracking-wide text-slate-900" id="form-judul">➕ Tambah Siswa</h2>
                <form method="POST" id="form-siswa" action="{{ route('admin.siswa.store') }}"
                    class="mt-5 space-y-4 text-sm">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="form-method">
                    <div>
                        <label class="label">Nama Lengkap *</label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="input @error('name') input-error @enderror">
                        @error('name')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Email *</label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                            class="input @error('email') input-error @enderror">
                        @error('email')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">NIS *</label>
                            <input type="text" name="nis" required value="{{ old('nis') }}"
                                class="input @error('nis') input-error @enderror">
                            @error('nis')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="label">NISN</label>
                            <input type="text" name="nisn" value="{{ old('nisn') }}"
                                class="input @error('nisn') input-error @enderror">
                            @error('nisn')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="label">Kelas *</label>
                        <select name="kelas_id" required class="input @error('kelas_id') input-error @enderror">
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }}</option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Password *</label>
                        <input type="password" name="password" id="input-password"
                            class="input @error('password') input-error @enderror">
                        <p class="mt-1.5 text-xs text-slate-400" id="password-hint">Minimal 8 karakter, hanya untuk
                            siswa baru.</p>
                        @error('password')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Ulangi Password</label>
                        <input type="password" name="password_confirmation" class="input">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                    <button type="button" id="btn-batal" onclick="resetForm()"
                        class="btn btn-soft btn-block hidden">Batal ubah</button>
                </form>
                @if ($errors->any())
                    <p class="mt-3 text-xs font-medium text-rose-600">{{ $errors->first() }}</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        function isiForm(data) {
            const form = document.getElementById('form-siswa');
            form.action = '{{ url('admin/siswa') }}/' + data.id;
            document.getElementById('form-method').value = 'PUT';
            form.querySelector('[name=name]').value = data.name;
            form.querySelector('[name=email]').value = data.email;
            form.querySelector('[name=nis]').value = data.nis;
            form.querySelector('[name=nisn]').value = data.nisn || '';
            form.querySelector('[name=kelas_id]').value = data.kelas;
            document.getElementById('input-password').closest('div').classList.add('hidden');
            document.getElementById('form-judul').textContent = '✏️ Ubah Siswa';
            document.getElementById('btn-batal').classList.remove('hidden');
            form.scrollIntoView({ behavior: 'smooth' });
        }

        document.querySelectorAll('.tombol-ubah').forEach((tombol) => {
            tombol.addEventListener('click', () => isiForm(tombol.dataset));
        });

        function resetForm() {
            const form = document.getElementById('form-siswa');
            form.action = '{{ route('admin.siswa.store') }}';
            document.getElementById('form-method').value = 'POST';
            form.reset();
            document.getElementById('input-password').closest('div').classList.remove('hidden');
            document.getElementById('form-judul').textContent = '➕ Tambah Siswa';
            document.getElementById('btn-batal').classList.add('hidden');
        }
    </script>
@endsection
