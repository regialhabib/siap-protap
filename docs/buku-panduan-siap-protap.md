# Buku Panduan Penggunaan SIAP-PROTAP
**Sistem Informasi Aplikasi Pelaporan - Pengamatan Organisme Pengganggu Tumbuhan**

---

## 1. Pendahuluan
Selamat datang di panduan penggunaan **SIAP-PROTAP**. Aplikasi ini dirancang khusus untuk memfasilitasi pelaporan dan pemantauan data pengamatan lapangan terkait Organisme Pengganggu Tumbuhan (OPT). Melalui sistem ini, petugas dapat melaporkan serangan OPT secara akurat dan tersentralisasi, sehingga pihak pengelola (Administrator) dapat langsung mengambil keputusan berdasarkan data *real-time*.

## 2. Hak Akses Pengguna (Peran / *Role*)
SIAP-PROTAP memiliki 2 (dua) jenis pengguna utama:
- **Administrator BPTP (Admin)**: Bertugas mengelola *Master Data* (komoditas, OPT, data wilayah), mendaftarkan dan memantau akun petugas, serta mencetak laporan rekapitulasi.
- **Petugas Lapangan (POPT)**: Bertugas untuk terjun ke lapangan dan menginputkan hasil pengamatannya langsung ke dalam sistem.

---

## 3. Panduan Umum (Untuk Seluruh Pengguna)

### A. Cara Login
1. Buka aplikasi web SIAP-PROTAP melalui peramban (*browser*) Anda.
2. Masukkan **Alamat Email** dan **Password** yang telah didaftarkan.
3. Klik tombol **Log in**. Jika kredensial sesuai, Anda akan otomatis diarahkan ke halaman **Dashboard**.

### B. Mengubah Profil dan Kata Sandi
1. Pada menu sebelah kiri bawah, klik nama profil Anda.
2. Anda akan masuk ke halaman pengaturan profil.
3. Di sana, Anda dapat merubah:
   - **Informasi Profil**: Nama dan alamat email Anda.
   - **Ubah Password**: Pastikan menggunakan kata sandi yang kuat dan aman.

### C. Membaca Dashboard
Saat baru pertama masuk, Anda disuguhkan dengan ringkasan status aplikasi.
- Terdapat metrik jumlah pengamatan, luasan serangan, dan komoditas terdampak pada bulan berjalan.
- **Grafik Kepatuhan UPPT**: Memperlihatkan persentase dan daftar petugas UPPT mana saja yang **Sudah** ataupun **Belum** mengirimkan laporan pada bulan ini.

---

## 4. Panduan Khusus Administrator BPTP

### A. Manajemen Petugas (Pengguna)
Menu ini digunakan untuk mengatur siapa saja yang berhak masuk ke dalam sistem.
1. Navigasi ke menu **Manajemen Petugas**.
2. **Menambah Petugas**: Klik tombol "Tambah Pengguna", lalu isi Nama, Email, Password sementara, dan perannya (Admin atau POPT).
3. **Status Akun**: Anda bisa menonaktifkan akun petugas secara sementara dengan mengeklik fitur ubah status (Aktif/Non-aktif). Petugas non-aktif tidak akan bisa *login* kembali sampai diaktifkan ulang.

### B. Mengelola Wilayah & UPPT
UPPT (Unit Pelayanan Perlindungan Tanaman) merupakan posko tempat petugas POPT bernaung.
1. Masuk ke menu **Wilayah & UPPT**.
2. Anda dapat mendaftarkan UPPT baru, menyesuaikan daftar **Kecamatan** yang menjadi cakupan wilayah mereka.
3. Hal ini penting agar saat petugas melapor, wilayah administrasinya tidak tertukar.

### C. Master Data (Komoditas & OPT)
- **Master Komoditas**: Tempat untuk mendaftarkan jenis-jenis tanaman (misal: Padi, Jagung, Bawang).
- **Master OPT**: Tempat untuk mendaftarkan nama-nama Hama atau Penyakit tanaman.
*Admin wajib memastikan data ini lengkap sebelum menugaskan POPT untuk turun ke lapangan.*

### D. Pelaporan dan Unduh Data
1. Buka menu **Laporan**. Anda akan melihat tabel besar riwayat seluruh laporan yang masuk dari berbagai UPPT.
2. Anda dapat melakukan *Filter* pencarian berdasarkan periode waktu (Tanggal), nama UPPT, ataupun jenis Komoditas.
3. Tersedia tombol **Export PDF** (untuk mencetak arsip laporan resmi) dan **Export Excel** (untuk olah data lebih lanjut pada *spreadsheet*).

---

## 5. Panduan Khusus Petugas Lapangan (POPT)

Sebagai petugas lapangan, Anda adalah ujung tombak penyedia data pengamatan.
### A. Input Data Pengamatan
1. Pada *Dashboard* atau *Sidebar*, klik tombol hijau **Input Pengamatan**.
2. Anda akan dihadapkan pada Formulir Pengamatan Lapangan. Silakan isi secara cermat:
   - **Tanggal Pengamatan**: (Biasanya terisi otomatis dengan tanggal hari ini, dapat diubah sesuai kondisi log lapangan).
   - **UPPT**: Pilih lokasi tugas Anda.
   - **Komoditas & OPT**: Pilih tanaman yang diteliti dan jenis hama yang menyerang.
   - **Luas Komoditi (Ha)**: Total luas hamparan tanaman.
3. **Intensitas Serangan**:
   - Tuliskan rincian luasan serangan **Ringan**, **Sedang**, dan **Berat**.
   - Kolom **Serangan Jumlah** akan menjumlahkan secara otomatis.
4. **Tindakan Pengendalian**:
   - Jika ada pengendalian, tuliskan pendanaan/luasannya apakah berasal dari APBD Kab, APBD Prov, APBN, atau secara swadaya oleh Masyarakat.
5. Tekan tombol **Simpan**. Laporan Anda akan langsung masuk ke pusat data dan status *Dashboard* Anda (serta Admin) akan berubah menjadi hijau (Sudah Lapor).

---

## 6. Bantuan Tambahan
- Jika Anda lupa *password* sebelum *login*, gunakan fitur **Lupa Password** di halaman muka *login* untuk mereset via *email*.
- Pastikan koneksi internet stabil ketika menyimpan Laporan Pengamatan agar data tidak terputus di tengah jalan.
