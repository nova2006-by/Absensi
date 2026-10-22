@extends('layouts.cetak')

@section('title', 'Rekap Kehadiran Siswa')

@section('isi')
    {{-- Filter (hanya tampil di layar, tidak ikut dicetak) --}}
    <form method="GET" action="{{ route('bk.laporan.kehadiran') }}"
        class="no-print card mb-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-44">
            <label class="label">Kelas *</label>
            <select name="kelas" required class="input">
                <option value="">— Pilih kelas —</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}" {{ $kelas->id == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Dari tanggal</label>
            <input type="date" name="awal" value="{{ $awal }}" required class="input">
        </div>
        <div>
            <label class="label">Sampai tanggal</label>
            <input type="date" name="akhir" value="{{ $akhir }}" required class="input">
        </div>
        <button type="submit" class="btn btn-soft">Tampilkan</button>
    </form>

    <div class="kop">
        <h1>SMK NU BANDAR</h1>
        <p>Yayasan Pendidikan Islam Al-Ikhlas Bandar &nbsp;•&nbsp; Telp. (0271) 555-0123</p>
    </div>

    <p class="judul-laporan">Rekap Kehadiran Siswa</p>
    <p class="periode">
        Kelas {{ $kelas->nama }}
        &nbsp;•&nbsp;
        {{ \Illuminate\Support\Carbon::parse($awal)->translatedFormat('d F Y') }}
        s.d. {{ \Illuminate\Support\Carbon::parse($akhir)->translatedFormat('d F Y') }}
        &nbsp;•&nbsp;
        Hari efektif: {{ $hariEfektif }} hari
    </p>

    <table>
        <thead>
            <tr>
                <th class="tengah" style="width: 40px;">No</th>
                <th style="width: 90px;">NIS</th>
                <th>Nama Siswa</th>
                <th class="tengah" style="width: 62px;">Hadir</th>
                <th class="tengah" style="width: 74px;">Terlambat</th>
                <th class="tengah" style="width: 52px;">Izin</th>
                <th class="tengah" style="width: 56px;">Sakit</th>
                <th class="tengah" style="width: 50px;">Alfa</th>
                <th class="tengah" style="width: 76px;">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarSiswa as $i => $s)
                @php
                    $r = $rekap->get($s->id);
                    $hit = fn (string $status) => (int) ($r?->get($status) ?? 0);
                    $hadir = $hit(\App\Models\Absensi::STATUS_HADIR);
                    $terlambat = $hit(\App\Models\Absensi::STATUS_TERLAMBAT);
                    $izin = $hit(\App\Models\Absensi::STATUS_IZIN);
                    $sakit = $hit(\App\Models\Absensi::STATUS_SAKIT);
                    $alfa = $hit(\App\Models\Absensi::STATUS_ALFA);
                    $persen = $hariEfektif > 0 ? round((($hadir + $terlambat) / $hariEfektif) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="tengah">{{ $i + 1 }}</td>
                    <td>{{ $s->nis }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td class="tengah">{{ $hadir }}</td>
                    <td class="tengah">{{ $terlambat }}</td>
                    <td class="tengah">{{ $izin }}</td>
                    <td class="tengah">{{ $sakit }}</td>
                    <td class="tengah">{{ $alfa }}</td>
                    <td class="tengah"><strong>{{ $persen }}%</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="kosong">Belum ada siswa terdaftar di kelas ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="tanda-tangan">
        <div class="kolom">
            <p>Mengetahui,</p>
            <p>Wali Kelas {{ $kelas->nama }}</p>
            <p class="nama">{{ $kelas->waliKelas?->name ?? '…………………………' }}</p>
        </div>
        <div class="kolom">
            <p>Mengetahui,</p>
            <p>Guru BK</p>
            <p class="nama">{{ auth()->user()->name }}</p>
        </div>
    </div>
@endsection
