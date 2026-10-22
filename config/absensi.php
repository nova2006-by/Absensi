<?php

return [
    // Rentang waktu siswa boleh absen masuk
    'jam_masuk_mulai' => env('ABSEN_JAM_MASUK_MULAI', '06:30'),

    // Jam batas siswa dianggap hadir tepat waktu
    'jam_masuk' => env('ABSEN_JAM_MASUK', '07:30'),
    'jam_masuk_batas' => env('ABSEN_JAM_MASUK_BATAS', '08:30'),

    // Rentang waktu siswa boleh absen pulang
    'jam_pulang' => env('ABSEN_JAM_PULANG_MULAI', '14:30'),
    'jam_pulang_batas' => env('ABSEN_JAM_PULANG_BATAS', '17:00'),

    // Koordinat pusat sekolah dan radius area absensi dalam meter
    'lokasi_latitude' => env('ABSEN_LOKASI_LATITUDE'),
    'lokasi_longitude' => env('ABSEN_LOKASI_LONGITUDE'),
    'lokasi_radius' => (int) env('ABSEN_LOKASI_RADIUS', 100),
];
