@extends('layouts.app')

@section('title', 'Pengaturan Lokasi Sekolah')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="card p-6">
            <h2 class="card-title">Titik dan radius absensi</h2>
            <p class="mt-2 text-sm text-slate-600">
                Klik peta untuk memilih titik sekolah. Siswa hanya dapat absen dalam radius yang ditentukan dari titik ini.
            </p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">Pengaturan belum tersimpan. Periksa kembali nilai berikut:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.pengaturan.lokasi.update') }}" class="mt-5 space-y-5">
                @csrf
                @method('PUT')

                <div id="peta-lokasi-sekolah" class="h-[420px] overflow-hidden rounded-xl border border-slate-200"
                    aria-label="Peta untuk memilih lokasi sekolah"></div>
                <p class="text-xs text-slate-500">
                    Peta menggunakan OpenStreetMap. Klik pada peta atau geser penanda untuk mengatur titik.
                </p>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="latitude" class="label">Latitude</label>
                        <input id="latitude" name="latitude" type="number" step="any" min="-90" max="90"
                            value="{{ old('latitude', $pengaturan->latitude) }}" required
                            class="input @error('latitude') input-error @enderror">
                        @error('latitude')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="longitude" class="label">Longitude</label>
                        <input id="longitude" name="longitude" type="number" step="any" min="-180" max="180"
                            value="{{ old('longitude', $pengaturan->longitude) }}" required
                            class="input @error('longitude') input-error @enderror">
                        @error('longitude')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="radius" class="label">Radius absensi (meter)</label>
                        <input id="radius" name="radius" type="number" min="10" max="5000" step="1"
                            value="{{ old('radius', $pengaturan->radius ?? 100) }}" required
                            class="input @error('radius') input-error @enderror">
                        @error('radius')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan lokasi sekolah</button>
            </form>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Pastikan penanda tepat di area sekolah dan radius sesuai kebutuhan. Lokasi browser dapat dipalsukan pada
            perangkat yang dimodifikasi.
        </div>
    </div>

    <script>
        window.LOKASI_SEKOLAH = {
            latitude: @json(old('latitude', $pengaturan->latitude)),
            longitude: @json(old('longitude', $pengaturan->longitude)),
            radius: @json(old('radius', $pengaturan->radius ?? 100)),
        };
    </script>
    @vite(['resources/js/admin-map.js'])
@endsection
