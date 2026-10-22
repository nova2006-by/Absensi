<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Data demo untuk uji coba

Untuk mengisi dashboard dengan 50 akun demo serta contoh absensi dan pelanggaran, jalankan di lingkungan lokal:

```bash
php artisan db:seed --class=DemoDataSeeder
```

Seeder ini tidak menghapus data yang sudah ada dan aman dijalankan ulang. Akun demo: 1 admin (`demo.admin@sekolah.test`), 5 guru (`demo.guru01@sekolah.test`–`demo.guru05@sekolah.test`), 4 BK (`demo.bk01@sekolah.test`–`demo.bk04@sekolah.test`), dan 40 siswa (`demo.siswa001@sekolah.test`–`demo.siswa040@sekolah.test`). Semua menggunakan password `UjiCoba#2026`. Kredensial ini hanya untuk uji coba lokal; jangan gunakan atau seed di produksi.

## Pemasangan produksi

Sebelum membuka route `/pasang`, atur `.env` produksi dengan `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` HTTPS yang benar, `INSTALL_KEY` acak, serta `INITIAL_ADMIN_EMAIL` dan `INITIAL_ADMIN_PASSWORD` yang unik. Kata sandi admin awal harus 12-72 karakter dan hanya digunakan untuk akun tersebut. Simpan nilai rahasia itu dengan aman; jangan masukkan `.env` ke Git.

Pemasangan produksi hanya menjalankan migrasi dan membuat satu akun admin menggunakan kredensial awal tersebut. Seeder akun bawaan dan data demo ditolak pada lingkungan produksi. Setelah pemasangan, hapus nilai `INSTALL_KEY`, `INITIAL_ADMIN_EMAIL`, dan `INITIAL_ADMIN_PASSWORD` dari `.env`, lalu bersihkan/bangun ulang cache konfigurasi melalui panel hosting atau CLI. Jangan jalankan `db:seed` atau `DemoDataSeeder` pada database produksi.

Untuk import siswa, template CSV memerlukan kolom `password` berisi kata sandi unik 12-72 karakter untuk setiap akun. Bagikan kredensial melalui jalur aman dan hapus file CSV setelah selesai.

### MySQL lokal dengan XAMPP

Untuk pengembangan lokal di Windows, jalankan MySQL dari XAMPP Control Panel dan buat database `absensi_sekolah` melalui phpMyAdmin. Atur `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=absensi_sekolah`, serta kredensial MySQL lokal di `.env`. Nilai `root` tanpa kata sandi hanya boleh digunakan pada XAMPP lokal; buat pengguna database khusus yang memiliki hak hanya pada database aplikasi sebelum aplikasi dapat diakses dari jaringan atau dipasang ke hosting.

Untuk membuat tabel pada database baru, jalankan `php artisan migrate`. Jika memindahkan database yang sudah dipakai, buat cadangan terlebih dahulu dan salin data melalui proses impor terverifikasi; menjalankan migrasi saja hanya membuat struktur tabel. Jangan hapus berkas SQLite sumber sampai jumlah baris dan fungsi aplikasi pada database MySQL sudah diverifikasi. Berkas `.env` tidak boleh diunggah ke Git atau hosting.

### Shared hosting/cPanel dengan MySQL

XAMPP tidak diperlukan pada server hosting; XAMPP hanya pilihan untuk menjalankan server lokal di komputer sendiri. Hosting harus menyediakan versi PHP yang didukung proyek, ekstensi `pdo_mysql`, MySQL/MariaDB, HTTPS, dan document root yang dapat diarahkan ke folder `public` Laravel. Jangan arahkan document root ke folder utama proyek karena `.env`, `vendor`, dan berkas internal lainnya tidak boleh dapat diunduh dari web.

Gunakan `.env.production.example` sebagai acuan untuk membuat `.env` di server. Ganti domain, host serta nama basis data, pengguna dan kata sandi MySQL sesuai informasi dari cPanel. Buat basis data dan pengguna di cPanel lalu berikan hak akses pengguna ke basis data tersebut. Buat `APP_KEY` unik di server, gunakan HTTPS, dan isi koordinat sekolah. Jangan menyalin `.env` lokal ke hosting atau mengunggahnya ke Git.

Bangun aset frontend sebelum mengunggah dengan `npm ci` dan `npm run build`. Unggah aplikasi dengan document root diarahkan ke `public`, isi `.env` produksi di luar web, lalu jalankan pemasangan satu kali melalui `/pasang` menggunakan `INSTALL_KEY` acak sementara. Setelah pemasangan, hapus `INSTALL_KEY` dan kredensial admin awal, lalu bersihkan cache konfigurasi melalui CLI atau panel hosting. Jika hosting tidak menyediakan pekerja antrean yang selalu aktif, gunakan `QUEUE_CONNECTION=sync` seperti pada template.

### Demo di Vercel dan Supabase

Repositori ini menyediakan `Dockerfile.vercel` untuk mencoba aplikasi Laravel pada **Vercel Container Runtime (Beta)** dengan PostgreSQL Supabase. Vercel Hobby berharga $0 dengan kuota bulanan, tetapi Vercel dapat meminta verifikasi pembayaran dan tidak dijamin bebas kartu. Paket Hobby ditujukan untuk proyek pribadi/nonkomersial; gunakan hanya untuk demonstrasi pribadi dengan data palsu, bukan operasional sekolah atau data siswa nyata. Container Runtime masih Beta dan filesystem-nya sementara. Berkas upload lokal tidak bertahan setelah instance diganti; gunakan penyimpanan objek persisten sebelum mengandalkan upload.

1. Buat proyek Supabase khusus aplikasi ini. Simpan password database dengan aman dan jangan gunakan database yang berisi data aplikasi lain.
2. Di Supabase, buka **Connect** dan salin URI **Session pooler** (port `5432`). Ganti placeholder password; percent-encode karakter khusus yang ada di password. Jangan kirim atau commit URI/password tersebut.
3. Pastikan kode terbaru ada di repository GitHub privat. Jangan unggah `.env`, database SQLite, kredensial, atau data asli.
4. Di Vercel, pilih **Add New → Project**, impor repository, dan deploy menggunakan `Dockerfile.vercel` yang berada di root. Jika Container Runtime belum tersedia pada akun/proyek, jangan ganti ke runtime komunitas secara otomatis—runtime tersebut perlu konfigurasi berbeda.
5. Tambahkan environment variables di pengaturan proyek Vercel: `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, `APP_KEY`, `DB_CONNECTION=pgsql`, `DB_URL`, `SESSION_DRIVER=database`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`, `CACHE_STORE=database`, `QUEUE_CONNECTION=sync`, `FILESYSTEM_DISK=public`, `LOG_LEVEL=warning`, `INSTALL_KEY`, `INITIAL_ADMIN_NAME`, `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_PASSWORD`, koordinat dan jadwal absensi, serta `MAIL_MAILER=log`. Buat `APP_KEY` secara lokal dengan `php artisan key:generate --show`; gunakan URI Session pooler sebagai `DB_URL`. Jangan simpan nilai rahasia dalam Git.
6. Setelah Vercel memberikan domain HTTPS, set `APP_URL` ke domain tersebut lalu redeploy. Container menjalankan migrasi saat mulai; jangan jalankan seeder demo pada database produksi.
7. Buka `https://DOMAIN-VERCEL/pasang?key=KUNCI-PEMASANGAN` satu kali untuk membuat admin. Setelah berhasil, hapus `INSTALL_KEY`, `INITIAL_ADMIN_EMAIL`, dan `INITIAL_ADMIN_PASSWORD` dari environment Vercel, kemudian redeploy.

Vercel Hobby dan Supabase Free memiliki kuota serta kebijakan penggunaan masing-masing; batas atau syarat dapat berubah. Supabase dapat menghentikan sementara proyek yang tidak aktif. Simpan backup terpisah dan periksa ketentuan terbaru. Jangan gunakan paket demo gratis sebagai satu-satunya penyimpanan data.

## Pembatasan lokasi absensi siswa

Absensi masuk dan pulang siswa menggunakan lokasi perangkat dan hanya diterima di dalam radius sekolah. Atur koordinat pusat sekolah pada `.env`:

```dotenv
ABSEN_LOKASI_LATITUDE=-6.200000
ABSEN_LOKASI_LONGITUDE=106.800000
ABSEN_LOKASI_RADIUS=100
```

Ganti contoh koordinat dengan latitude dan longitude sekolah (misalnya salin koordinat titik sekolah dari Google Maps). Radius menggunakan meter. Setelah mengubah `.env`, jalankan `php artisan config:clear` atau bangun ulang cache konfigurasi dengan `php artisan config:cache`.

Browser harus diizinkan mengakses lokasi dan situs produksi harus menggunakan HTTPS. Tanpa konfigurasi koordinat, absensi siswa akan ditolak. Lokasi browser/GPS membantu membatasi absensi berdasarkan area, tetapi tidak dapat mencegah pemalsuan lokasi pada perangkat yang telah dimodifikasi.

Koordinat GPS dari browser adalah sinyal yang tidak tepercaya dan tidak langsung membuat catatan kehadiran. Siswa mengajukan absensi masuk dan pulang, lalu wali kelas (atau BK/admin) harus memeriksa siswa secara langsung dan menyetujui permintaan pada halaman **Kehadiran Kelas**. Hanya setelah disetujui, absensi masuk/pulang tercatat resmi. Guru dibatasi hanya pada kelas yang ditugaskan kepadanya. Verifikasi ini mengurangi pemalsuan lokasi jarak jauh, tetapi tidak mencegah kolusi atau persetujuan tanpa pemeriksaan langsung.

Rentang waktu absensi diatur dengan `ABSEN_JAM_MASUK_MULAI`, `ABSEN_JAM_MASUK`, `ABSEN_JAM_MASUK_BATAS`, `ABSEN_JAM_PULANG_MULAI`, dan `ABSEN_JAM_PULANG_BATAS`. Nilai standar: masuk 06:30–08:30, terlambat setelah 07:30, dan pulang 14:30–17:00. Permintaan di luar rentang masuk atau pulang ditolak.

Titik sekolah dan radius absensi juga dapat diubah oleh admin melalui menu **Pengaturan Lokasi**. Pilih titik pada peta, atur radius, lalu simpan; koordinat tersimpan di database dan langsung digunakan untuk memeriksa lokasi absensi. Jalankan `php artisan migrate` setelah pembaruan kode.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
