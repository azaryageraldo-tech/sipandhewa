# SI TERNAK Buleleng - Sistem Informasi Pengelolaan Ternak

Sistem Informasi Pengelolaan Ternak Kabupaten Buleleng adalah aplikasi web untuk mengelola data peternakan secara terintegrasi di wilayah Kabupaten Buleleng.

## 📋 Fitur Utama

- ✅ **Dashboard** - Statistik real-time dan grafik visualisasi data
- ✅ **Master Data** - Pengelolaan kecamatan dan desa di Buleleng
- ✅ **Populasi Ternak** - Input dan monitoring populasi ternak per desa
- ✅ **Peternakan** - Data unit usaha peternakan
- ✅ **Produksi** - Data produksi ternak (daging, susu, telur)
- ✅ **Pemotongan** - Data pemotongan hewan ternak
- ✅ **Vaksinasi** - Data program vaksinasi ternak
- ✅ **Penyakit Hewan** - Monitoring dan penanganan penyakit ternak
- ✅ **Survei Pasar** - Harga pasar komoditas ternak
- ✅ **User Management** - Multi-role system (admin/user)

## 🛠 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi (8.0+ direkomendasikan)
- Web server (Apache/Nginx)
- Browser modern
- Composer (untuk dependency management)

## 🚀 Instalasi

### 1. Setup Database

1. Buat database MySQL baru:
   ```sql
   CREATE DATABASE si_ternak_buleleng;
Import file SQL yang telah disediakan:

bash
mysql -u username -p si_ternak_buleleng < si_ternak_buleleng.sql
2. Konfigurasi Aplikasi
Edit file config/database.php:

php
define('DB_HOST', 'localhost');
define('DB_NAME', 'si_ternak_buleleng');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('BASE_URL', 'http://localhost/siternak_ariani/'); // Sesuaikan dengan path Anda
3. Struktur Folder
text
siternak_ariani/
├── assets/           # File CSS, JS, gambar
├── config/
│   └── database.php  # Konfigurasi database
├── includes/
│   ├── functions.php # Fungsi helper
│   ├── sidebar.php   # Menu sidebar
│   └── topbar.php    # Header/navigation
├── modules/
│   ├── dashboard/    # Dashboard utama
│   ├── populasi/     # Modul populasi ternak
│   ├── peternakan/   # Data unit usaha
│   ├── produksi/     # Data produksi
│   ├── pemotongan/   # Data pemotongan
│   ├── vaksinasi/    # Data vaksinasi
│   ├── penyakit/     # Monitoring penyakit
│   └── survei_pasar/ # Survei harga pasar
├── index.php         # Halaman utama
├── login.php         # Halaman login
├── logout.php        # Logout
├── README.md         # Dokumentasi ini
└── README_SETUP.md   # Panduan setup
4. Akses Aplikasi
Buka browser dan akses: http://localhost/siternak_ariani/

Login dengan akun default:

Admin:

Username: admin

Email: admin@siternak.com

Password: admin123

User:

Username: sadam

Email: sadam@gmail.com

Password: sadam123

📊 Struktur Database
Tabel Utama:
users - Data pengguna sistem

kecamatan - Data kecamatan di Buleleng (9 kecamatan)

desa - Data desa per kecamatan

populasi_ternak - Data populasi ternak per desa

peternakan - Data unit usaha peternakan

produksi - Data produksi ternak

pemotongan - Data pemotongan hewan

vaksinasi - Data program vaksinasi

penyakit_hewan - Monitoring penyakit ternak

survei_pasar - Data survei harga pasar

👥 Hak Akses
1. Admin
✅ Mengelola semua data

✅ Mengelola user (tambah/edit/hapus)

✅ Reset password user

✅ Backup database

2. User (Petugas Lapangan)
✅ Input data populasi ternak

✅ Input data produksi

✅ Input data pemotongan

✅ Input data vaksinasi

✅ Input data penyakit hewan

✅ Input survei pasar

✅ Melihat laporan

✅ Edit data yang diinput sendiri

📖 Panduan Penggunaan
1. Login
Masuk dengan username/email dan password

Pastikan role sesuai dengan hak akses

2. Dashboard
Melihat statistik utama

Grafik perkembangan populasi

Notifikasi dan alert

3. Input Data Populasi
Pilih menu Populasi Ternak

Pilih Kecamatan dan Desa

Input data per jenis ternak

Sistem otomatis menghitung total

4. Input Data Peternakan
Pilih menu Peternakan

Isi data unit usaha

Pilih lokasi (kecamatan/desa)

Input kapasitas dan populasi

5. Monitoring Penyakit
Pilih menu Penyakit Hewan

Input data kasus penyakit

Tandai status penanganan

Upload data sampel (jika ada)

6. Survei Pasar
Pilih menu Survei Pasar

Pilih lokasi pasar (Banyuasri/Anyar/Buleleng)

Input harga komoditas

Tambah catatan khusus