@extends('layouts.app')

@section('title', 'Data Guru & BK')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <p class="font-semibold">Data guru/BK belum tersimpan. Periksa kembali:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="card-title">Daftar Guru &amp; BK</h2>
                    <span class="badge badge-slate">{{ $daftar->total() }} akun</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl tbl-compact">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Telepon</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $g)
                                <tr>
                                    <td>
                                        <p class="font-medium text-slate-900">{{ $g->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $g->email }}</p>
                                    </td>
                                    <td>
                                        <span class="badge {{ $g->role === 'bk' ? 'badge-purple' : 'badge-blue' }}">
                                            {{ $g->roleLabel() }}</span>
                                    </td>
                                    <td>{{ $g->telepon ?? '—' }}</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <button type="button" class="text-action tombol-ubah"
                                                data-id="{{ $g->id }}" data-name="{{ $g->name }}"
                                                data-email="{{ $g->email }}" data-role="{{ $g->role }}"
                                                data-telepon="{{ $g->telepon }}">Ubah</button>
                                            <form method="POST" action="{{ route('admin.guru.destroy', $g) }}"
                                                onsubmit="return confirm('Hapus akun ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-danger-action">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="!py-12 text-center text-slate-400">Belum ada akun guru/BK.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-6 py-4">{{ $daftar->links() }}</div>
            </div>
        </div>

        <div>
            <div class="card p-6">
                <h2 class="text-sm font-semibold tracking-wide text-slate-900" id="form-judul">➕ Tambah Guru / BK</h2>
                <form method="POST" id="form-guru" action="{{ route('admin.guru.store') }}"
                    class="mt-5 space-y-4 text-sm">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="form-method">
                    <div>
                        <label class="label">Nama *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="input @error('name') input-error @enderror">
                        @error('name')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="input @error('email') input-error @enderror">
                        @error('email')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Role *</label>
                        <select name="role" class="input @error('role') input-error @enderror">
                            <option value="guru" {{ old('role', 'guru') === 'guru' ? 'selected' : '' }}>Guru / Wali Kelas</option>
                            <option value="bk" {{ old('role') === 'bk' ? 'selected' : '' }}>Guru BK</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label">Telepon</label>
                        <input type="text" name="telepon" value="{{ old('telepon') }}"
                            class="input @error('telepon') input-error @enderror">
                    </div>
                    <div id="password-field">
                        <label class="label">Password *</label>
                        <input type="password" name="password"
                            required class="input @error('password') input-error @enderror">
                        @error('password')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="password-confirm-field">
                        <label class="label">Ulangi Password</label>
                        <input type="password" name="password_confirmation" required
                            class="input @error('password_confirmation') input-error @enderror">
                        @error('password_confirmation')
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
        function isiForm(data) {
            const form = document.getElementById('form-guru');
            form.action = '{{ url('admin/guru') }}/' + data.id;
            document.getElementById('form-method').value = 'PUT';
            form.querySelector('[name=name]').value = data.name;
            form.querySelector('[name=email]').value = data.email;
            form.querySelector('[name=role]').value = data.role;
            form.querySelector('[name=telepon]').value = data.telepon || '';
            document.getElementById('password-field').classList.add('hidden');
            document.getElementById('password-confirm-field').classList.add('hidden');
            document.getElementById('form-judul').textContent = '✏️ Ubah Guru / BK';
            document.getElementById('btn-batal').classList.remove('hidden');
            form.scrollIntoView({ behavior: 'smooth' });
        }

        document.querySelectorAll('.tombol-ubah').forEach((tombol) => {
            tombol.addEventListener('click', () => isiForm(tombol.dataset));
        });

        function resetForm() {
            const form = document.getElementById('form-guru');
            form.action = '{{ route('admin.guru.store') }}';
            document.getElementById('form-method').value = 'POST';
            form.reset();
            document.getElementById('password-field').classList.remove('hidden');
            document.getElementById('password-confirm-field').classList.remove('hidden');
            document.getElementById('form-judul').textContent = '➕ Tambah Guru / BK';
            document.getElementById('btn-batal').classList.add('hidden');
        }
    </script>
@endsection
