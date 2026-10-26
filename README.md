<p align="center">
    <img src="public/images/logo_160.jpg" width="96" alt="Logo SIAP-PROTAP">
</p>

<h1 align="center">SIAP-PROTAP</h1>

<p align="center">
    Sistem Informasi Administrasi Pelaporan Proteksi Tanaman Perkebunan<br>
    Dinas Perkebunan Provinsi Jambi
</p>

---

SIAP-PROTAP membantu petugas POPT (Pengendali Organisme Pengganggu Tumbuhan) mencatat hasil pengamatan serangan OPT di lapangan secara terpusat. Administrator memakai data itu untuk memantau kepatuhan UPPT dan menyusun laporan bulanan, triwulan, dan tahunan tanpa rekap manual.

## Fitur

**Administrator BPTP**

- Dashboard kepatuhan: UPPT yang sudah dan belum melakukan pengamatan pada bulan yang dipilih.
- Riwayat Pengamatan: satu rantai pengamatan selama 12 bulan, lengkap dengan perhitungan kondisi tiap bulan.
- Laporan Bulanan (per UPPT/kecamatan), Triwulan, dan Tahunan (per kabupaten), dengan ekspor PDF dan Excel.
- Master data: komoditas, OPT beserta jenisnya, wilayah UPPT dan kecamatan, serta akun petugas (aktif/nonaktif).

**Petugas POPT**

- Input pengamatan: lokasi, komoditas, OPT, luas komoditi, tambahan serangan (ringan/berat), dan pengendalian per sumber dana.
- Formulir menampilkan kondisi sebelumnya dan batas pengendalian sebelum data dikirim.
- Dashboard berisi kondisi serangan di wilayah UPPT-nya.
- Riwayat Pengamatan rantai di UPPT-nya sendiri (Kabupaten/Kota dan UPPT terkunci).
- Pendaftaran akun mandiri; akun baru berstatus nonaktif sampai disetujui Administrator.

## Konsep penting

| Konsep | Aturan |
|---|---|
| Periode | Februari tahun Y sampai Januari tahun Y+1, disebut "Periode Y". Triwulan: TW I Feb–Apr, TW II Mei–Jul, TW III Agu–Okt, TW IV Nov–Jan. |
| Rantai | UPPT + Kecamatan + Komoditas + OPT + Jenis OPT dalam satu periode. Rantai mulai dari 0 setiap Februari. |
| Kondisi serangan | Kondisi sebelumnya + tambahan serangan − pengendalian. Kondisi akhir bulan menjadi kondisi awal bulan berikutnya; bulan tanpa input membawa angka bulan sebelumnya. |
| Validasi | Pengendalian tidak boleh melebihi kondisi sebelumnya + tambahan serangan. Kondisi tidak boleh minus atau melebihi luas komoditi, termasuk akibat edit/hapus data di tanggal lain pada rantai yang sama. Serangan kumulatif boleh melebihi luas komoditi (lahan yang sudah dikendalikan bisa terserang lagi). |
| Batas edit | POPT hanya bisa mengubah atau menghapus data selama 2 hari sejak diinput. Administrator tidak dibatasi. |

Perhitungan rantai ada di `app/Services/PengamatanHistoryService.php`, dan aturan periode di `app/Services/PeriodePengamatan.php`.

## Teknologi

- PHP 8.4, Laravel 13, MySQL (SQLite untuk test)
- Blade, Alpine.js, Tailwind CSS 3, Vite
- `barryvdh/laravel-dompdf` (PDF) dan `maatwebsite/excel` (Excel)
- PHPUnit dan Laravel Pint

## Instalasi lokal

Kebutuhan: PHP 8.4, Composer, Node.js, dan MySQL.

```bash
git clone https://github.com/regialhabib/siap-protap.git
cd siap-protap
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Sesuaikan `.env`:

```dotenv
APP_NAME="SIAP-PROTAP"
APP_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siap_protap
DB_USERNAME=root
DB_PASSWORD=
```

`APP_LOCALE=id` wajib agar nama bulan dan pesan validasi tampil dalam bahasa Indonesia.

Buat tabel dan data awal (kabupaten, UPPT, kecamatan, komoditas, OPT, dan dua akun contoh):

```bash
php artisan migrate --seed
```

| Peran | Email | Password |
|---|---|---|
| Administrator | `admin@siap-protap.com` | `password` |
| POPT (UPPT Rimbo Bujang) | `popt.rimbobujang@siap-protap.com` | `password` |

> Ganti password akun contoh, atau jangan jalankan seeder akun ini, di server production.

Data contoh pengamatan untuk development (4 UPPT, 12 bulan Periode 2026). **Seeder ini menghapus semua pengamatan yang ada** dan menolak berjalan di production:

```bash
php artisan db:seed --class=KecamatanSeeder
php artisan db:seed --class=PengamatanDemoSeeder
```

Jalankan aplikasi:

```bash
composer run dev
```

Perintah ini menjalankan server Laravel, Vite, queue, dan log sekaligus. Buka http://127.0.0.1:8000.

## Test dan format kode

```bash
php artisan test --compact      # semua test
vendor/bin/pint --dirty         # rapikan file PHP yang berubah
```

## Deploy ke production

Server production mengambil kode dari git dan **tidak menjalankan `npm run build`**. Karena itu folder `public/build` sengaja ikut di-commit.

Setiap kali ada perubahan pada Blade, CSS, atau JavaScript:

1. Jalankan `npm run build`.
2. Commit isi `public/build` (biasanya sebagai commit `build: rebuild frontend assets`).

Di server production:

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

Pastikan `.env` production memakai `APP_ENV=production`, `APP_DEBUG=false`, `APP_LOCALE=id`, dan `APP_NAME="SIAP-PROTAP"`.

## Konvensi kontribusi

- Pesan commit memakai [Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `style:`, `refactor:`, `test:`, `build:`, `docs:`, `chore:`. Subjek huruf kecil, maksimal 50 karakter, satu perubahan per commit.
- Tambahkan atau perbarui feature test untuk setiap perubahan perilaku, lalu jalankan Pint sebelum commit.
- Stage file secara eksplisit, jangan `git commit -a`, agar hasil build tidak tercampur ke commit lain.

## Kredit

Foto hero: kebun teh Kayu Aro, Kerinci, oleh [Muhamad Izzul Fiqih](https://commons.wikimedia.org/wiki/File:Gunung_Kerinci_dari_kebun_teh.jpg), lisensi [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/).
