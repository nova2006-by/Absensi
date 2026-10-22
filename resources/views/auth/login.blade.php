<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem Absensi SMK NU Bandar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-slate-200 antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panel kiri: branding --}}
        <div class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between lg:p-12">
            {{-- Latar gradien + glow --}}
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950"></div>
            <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-emerald-600/20 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-teal-600/20 blur-3xl"></div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-900/50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-white">SMK NU Bandar</p>
                        <p class="text-xs text-slate-400">Sistem Absensi &amp; Pelanggaran Sekolah</p>
                    </div>
                </div>

                <h2 class="mt-16 max-w-md text-3xl font-bold leading-tight tracking-tight text-white">
                    Disiplin siswa, terukur dan terdata.
                </h2>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-400">
                    Satu pintu untuk absensi harian, rekap kehadiran per kelas, hingga poin pelanggaran —
                    untuk siswa, guru, wali kelas, dan guru BK.
                </p>

                <dl class="mt-10 grid max-w-md grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                        <dt class="text-[11px] uppercase tracking-wider text-slate-500">Absensi</dt>
                        <dd class="mt-1 text-sm font-semibold text-white">Masuk &amp; pulang</dd>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                        <dt class="text-[11px] uppercase tracking-wider text-slate-500">Kehadiran</dt>
                        <dd class="mt-1 text-sm font-semibold text-white">Rekap per kelas</dd>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                        <dt class="text-[11px] uppercase tracking-wider text-slate-500">BK</dt>
                        <dd class="mt-1 text-sm font-semibold text-white">Poin pelanggaran</dd>
                    </div>
                </dl>
            </div>                    <p class="relative text-xs text-slate-500">&copy; {{ now()->year }} SMK NU Bandar. Semua hak cipta dilindungi.</p>
        </div>

        {{-- Panel kanan: form login --}}
        <div class="flex items-center justify-center bg-white px-6 py-12 sm:px-12">
            <div class="w-full max-w-sm">
                <div class="lg:hidden">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                        </svg>
                    </div>
                </div>

                <h1 class="mt-8 text-2xl font-bold tracking-tight text-slate-900">Selamat datang 👋</h1>
                <p class="mt-1.5 text-sm text-slate-500">Masuk menggunakan akun sekolah Anda.</p>

                @if ($errors->any())
                    <div
                        class="mt-6 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="mt-0.5 h-4 w-4 shrink-0">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@gmail.com"
                            class="input @error('email') input-error @enderror">
                    </div>
                    <div>
                        <label for="password" class="label">Password</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                            class="input @error('password') input-error @enderror">
                    </div>
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1"
                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/30">
                        Ingat saya
                    </label>
                    <button type="submit" class="btn btn-primary btn-block">
                        Masuk
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
