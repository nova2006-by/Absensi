@extends('layouts.cetak')

@section('title', 'Rekap Poin Pelanggaran')

@section('isi')
    {{-- Filter (hanya tampil di layar, tidak ikut dicetak) --}}
    <form method="GET" action="{{ route('bk.laporan.pelanggaran') }}"
        class="no-print card mb-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-44">
            <label class="label">Kelas</label>
            <select name="kelas" class="input">
                <option value="">Semua kelas</option>
                @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasDipilih?->id == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Dari tanggal</label>
            <input type="date" name="awal" value="{{ $awal }}" class="input">
        </div>
        <div>
            <label class="label">Sampai tanggal</label>
            <input type="date" name="akhir" value="{{ $akhir }}" class="input">
        </div>
        <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-600">
            <input type="checkbox" name="ada_pelanggaran" value="0" class="h-4 w-4 rounded border-slate-300">
            Tampilkan semua siswa (termasuk 0 poin)
        </label>
        <button type="submit" class="btn btn-soft">Tampilkan</button>
    </form>

    <div class="kop">
        <h1>SMK NU BANDAR</h1>
        <p>Yayasan Pendidikan Islam Al-Ikhlas Bandar &nbsp;•&nbsp; Telp. (0271) 555-0123</p>
    </div>

    <p class="judul-laporan">Rekap Poin Pelanggaran Siswa</p>
    <p class="periode">
        @if ($kelasDipilih)
            Kelas {{ $kelasDipilih->nama }} &nbsp;•&nbsp;
        @else
            Seluruh Kelas &nbsp;•&nbsp;
        @endif
        Periode {{ \Illuminate\Support\Carbon::parse($awal)->translatedFormat('d F Y') }}
        s.d. {{ \Illuminate\Support\Carbon::parse($akhir)->translatedFormat('d F Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th class="tengah" style="width: 40px;">No</th>
                <th style="width: 90px;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 110px;">Kelas</th>
                <th class="tengah" style="width: 70px;">Jml Pel.</th>
                <th class="kanan" style="width: 70px;">Poin</th>
                <th style="width: 150px;">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarSiswa as $i => $s)
                @php $poin = $s->total_poin ?? 0; @endphp
                <tr>
                    <td class="tengah">{{ $i + 1 }}</td>
                    <td>{{ $s->nis }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td>{{ $s->kelas?->nama ?? '—' }}</td>
                    <td class="tengah">{{ $s->jumlah_pelanggaran ?? 0 }}</td>
                    <td class="kanan"><strong>{{ $poin }}</strong></td>
                    <td>
                        @if ($poin >= 40)
                            Perlu pembinaan intensif
                        @elseif ($poin >= 20)
                            Perlu perhatian
                        @elseif ($poin > 0)
                            Ringan
                        @else
                            Tertib
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="kosong">Tidak ada data pelanggaran pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="tanda-tangan">
        <div class="kolom"></div>
        <div class="kolom">
            <p>Mengetahui,</p>
            <p>Guru BK</p>
            <p class="nama">{{ auth()->user()->name }}</p>
        </div>
    </div>
@endsection
