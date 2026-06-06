# Panduan Instalasi & Pembaruan Sistem (Humas Hub)

Dokumen ini berisi panduan langkah demi langkah untuk melakukan instalasi awal (*Fresh Install*) di server hosting (cPanel/sejenisnya) serta tata cara melakukan pembaruan sistem (*Update*) menggunakan fitur **Dashboard System Manager** secara aman dan praktis.

---

## 1. Panduan Instalasi Awal (Fresh Install)

Ikuti langkah-langkah di bawah ini untuk meng-onlinekan aplikasi Humas Hub pertama kali di server produksi Anda.

### A. Persiapan File Aplikasi
1. Dapatkan berkas **[release.zip](file:///c:/xampp/htdocs/humashubci/release.zip)** dari root proyek Anda. Berkas ini sudah dikemas bersih tanpa ada konfigurasi lokal (`.env`), lock file, maupun file sampah pengembangan lainnya.

### B. Unggah & Ekstrak di Server
1. Masuk ke **cPanel** atau panel manajemen file server Anda.
2. Buka **File Manager** dan masuk ke direktori tujuan domain Anda (biasanya di dalam folder **`public_html`**).
3. Unggah berkas `release.zip` ke folder tersebut.
4. Klik kanan berkas `release.zip` di File Manager hosting, lalu pilih **Extract** untuk mengekstrak seluruh isinya.

### C. Konfigurasi Web Root Domain (Pilihan Struktur)
Aplikasi ini dikembangkan menggunakan framework CodeIgniter 4 yang memiliki folder `public/` sebagai gerbang utama web. Pilih salah satu metode di bawah ini agar website dapat diakses langsung menggunakan domain utama Anda (misal: `http://humas.ar-raniry.ac.id`):

#### **Metode A: Pemisahan Kode Inti (Sangat Direkomendasikan & Aman)**
Metode ini menyembunyikan kode inti PHP di luar folder web root agar tidak dapat diakses langsung dari URL browser:
1. Pindahkan semua folder seperti `app/`, `system/`, `writable/`, `tests/` ke **satu tingkat di atas `public_html`** (di home directory hosting Anda, sejajar dengan folder `public_html`).
2. Pindahkan seluruh isi yang ada **di dalam folder `public/`** langsung ke dalam folder **`public_html/`**. (Setelah dipindahkan, folder `public/` yang kosong dapat Anda hapus).
3. Buka dan edit berkas **`public_html/index.php`** menggunakan editor cPanel, pastikan baris kode path berikut mengarah dengan benar ke folder `app` Anda (secara default jika diletakkan satu tingkat di atas, baris ini tidak perlu diubah):
   ```php
   require FCPATH . '../app/Config/Paths.php';
   ```

#### **Metode B: Pengalihan `.htaccess` (Praktis & Instan)**
Metode ini membiarkan seluruh folder tetap berada di dalam `public_html` dan mengarahkan lalu lintas data menggunakan file konfigurasi server:
1. Biarkan seluruh folder hasil ekstrak tetap berada di dalam `public_html/`.
2. Buat atau edit berkas bernama **`.htaccess`** langsung di dalam folder root **`public_html/`**, lalu masukkan aturan penulisan ulang (*rewrite rules*) berikut:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   ```

### D. Buat Database MySQL
1. Buka menu **MySQL® Databases** di cPanel Anda.
2. Buat database baru (misal: `arraniry_humashub`).
3. Buat user database baru dan buat password yang kuat.
4. Tambahkan user tersebut ke database yang baru dibuat dengan mencentang pilihan **ALL PRIVILEGES** (Semua Hak Akses).

### F. Jalankan Wizard Instalasi Web
1. Buka browser Anda dan akses halaman instalasi aplikasi:
   * **Jika menggunakan Metode A**: Akses `http://humas.ar-raniry.ac.id/install.php`
   * **Jika menggunakan Metode B**: Akses `http://humas.ar-raniry.ac.id/public/install.php`
2. Halaman **Wizard Instalasi** akan terbuka:
   * **Langkah 1 (Cek Server)**: Menampilkan status kelayakan server (PHP >= 8.1, modul mysqli, pdo, dsb). Klik **Lanjutkan ke Konfigurasi**.
   * **Langkah 2 (Konfigurasi)**: 
     - Masukkan kredensial database yang Anda buat pada **Bagian D** (Host, Nama DB, User, Pass).
     - Masukkan alamat **Base URL** aplikasi (wizard akan mendeteksi domain server secara otomatis, sesuaikan jika dirasa kurang pas).
     - Buat Akun **Super Admin** baru dengan mengisi username, nama lengkap, dan password kustom pilihan Anda.
     - Klik **Pasang Sistem Sekarang**.
3. Sistem secara otomatis menulis berkas `.env`, membuat tabel-tabel di database hosting, melakukan seeding data default, dan mengunci instalasi.

### G. Keamanan Akhir
> [!WARNING]
> Demi alasan keamanan server, segeralah hapus berkas **`install.php`** yang berada di folder web root Anda (`public_html/` atau `public_html/public/`) setelah instalasi selesai agar setup tidak dapat dijalankan ulang oleh pihak luar.

---

## 2. Panduan Pembaruan Sistem (Update via Dashboard)

Setiap kali Anda selesai melakukan pengembangan di lingkungan lokal, gunakan fitur **Dashboard System Manager** untuk menerapkan pembaruan ke server hosting produksi dengan mudah melalui berkas ZIP.

### A. Persiapan Pembaruan di Lokal
1. Selesaikan semua modifikasi kode di komputer lokal Anda.
2. Buka folder `frontend/` di terminal lokal dan jalankan perintah kompilasi aset:
   ```bash
   npm run build
   ```
   *Perintah ini akan secara otomatis mengompilasi file Vue terbaru ke dalam folder publik backend.*
3. Perbarui nomor versi dan log perubahan pada berkas **`backend/version.json`** lokal Anda, contoh:
   ```json
   {
       "version": "1.0.1",
       "build_date": "2026-05-25",
       "changelog": "Menambahkan modul News Sindikasi dan perbaikan bug CORS."
   }
   ```
4. Kompres seluruh isi folder **`backend/`** lokal Anda menjadi sebuah berkas ZIP (misal: `update-v1.0.1.zip`).
   > [!TIP]
   > Jangan kompres folder `node_modules` jika ada di backend untuk menghemat ruang dan mempercepat proses unggah.

### B. Unggah & Terapkan Update
1. Masuk ke Dashboard Admin Humas Hub yang sudah online.
2. Masuk ke menu **Update & Backup** di sidebar (menu **Sistem**).
3. Di tab **Pembaruan Sistem (Update)**, seret berkas `update-v1.0.1.zip` Anda ke dalam area dropzone (atau klik untuk memilih file).
4. Sistem akan otomatis memverifikasi isi berkas ZIP Anda dan menampilkan perbandingan versi:
   * *Versi Saat Ini* ➔ *Versi Pembaruan* (disertai tanggal rilis dan catatan perubahan).
5. Klik tombol **Terapkan Pembaruan Sekarang**.
6. Sistem akan otomatis mengekstrak file pembaruan menimpa file lama di server, menjalankan migrasi database (`migrationRunner->latest()`), membersihkan cache, dan memperbarui informasi versi sistem secara instan.

---

## 3. Catatan Penyelesaian Masalah (Troubleshooting)

### A. Muncul Toast "Gagal memproses pencadangan..."
* **Gejala**: Saat mengklik unduh backup database atau file sistem, muncul notifikasi gagal di pojok kanan bawah.
* **Solusi**:
  1. Pastikan Anda sudah login kembali setelah melakukan update (sesi token JWT lama kemungkinan kadaluarsa).
  2. Pastikan file otentikasi di browser menyimpan token dengan kunci `hh_token`.
  3. Pastikan izin tulis (*write permission*) pada direktori `writable/` di server hosting diatur ke `755` atau `777`.

### B. Mengembalikan Data Cadangan (Restore)
* **Restore Database**: Ekstrak berkas `db-backup-*.zip` hasil unduhan Anda, lalu import file `.sql` di dalamnya melalui menu **phpMyAdmin** cPanel Anda.
* **Restore File**: Ekstrak berkas `system-backup-*.zip` hasil unduhan Anda langsung ke folder web root hosting untuk mengembalikan seluruh file proyek ke kondisi saat backup dibuat.
