# Dokumen Handover & Rencana Migrasi Humas Hub (Native PHP -> CI4 + Vue 3 SPA)

Dokumen ini berfungsi sebagai **memori percakapan dan peta jalan teknis** untuk melanjutkan migrasi Humas Hub. Jika percakapan terhenti atau dilanjutkan di hari lain, dokumen ini memberikan konteks penuh bagi Anda atau AI Asisten lainnya untuk melanjutkan tanpa kehilangan riwayat keputusan.

---

## 📌 Konteks & Keputusan yang Dikonfirmasi

Setelah berdiskusi dengan pemilik proyek, beberapa keputusan penting telah disepakati:

1. **Tujuan Utama**: Meningkatkan performa, skalabilitas, dan struktur aplikasi Humas Hub dengan bermigrasi dari **Native PHP** ke arsitektur modern **Headless REST API (CodeIgniter 4)** dengan frontend **Single Page Application (Vue.js 3 + Vite)**.
2. **Skala Kerja Awal**: Fokus pada **migrasi fungsionalitas 100% sama (Feature Parity)** tanpa melakukan penambahan atau perubahan fitur terlebih dahulu. Ini dilakukan untuk meminimalkan risiko bug pada fase awal.
3. **Penyimpanan Lokal (Development)**: Menggunakan direktori baru yang terisolasi penuh di:
   `c:\xampp\htdocs\humashubci`
   (Agar tidak mengganggu aplikasi native PHP yang sedang berjalan aktif di `c:\xampp\htdocs\humashub`).
4. **Target Server Produksi (Online)**: **Shared Hosting dengan cPanel atau CloudPanel**.
5. **Struktur Build & Deployment**: **Single Domain / Unified Directory**. 
   * Hasil build Vue 3 SPA ditaruh langsung di folder `public/` CodeIgniter 4.
   * Tidak membutuhkan subdomain terpisah untuk API, sehingga menghindari masalah CORS dan memudahkan pemasangan di cPanel biasa.

---

## 🧙 Strategi Migrasi Data & Server Online (Zero Downtime)

Agar pembaruan di server produksi aman dan tidak merusak data aktif operator:

1. **Migrasi Database Cerdas**:
   Semua migrasi database di CodeIgniter 4 ditulis menggunakan kondisional `if (!$this->db->tableExists('nama_tabel'))`. Saat dijalankan di server online, ia mendeteksi tabel lama yang sudah berisi data dan melewatinya secara aman tanpa menimpa data.
2. **Tahap Staging Subdomain**:
   Sebelum go-live, sistem baru dideploy ke subdomain staging (contoh: `beta.humashub.com`) dengan duplikat data produksi untuk diuji coba langsung di server online oleh operator.
3. **Switchover Instan**:
   Setelah stabil, root directory domain utama di cPanel/CloudPanel cukup diarahkan dari folder native lama ke folder `public/` CodeIgniter 4 baru. Proses pergantian ini memakan waktu kurang dari 5 detik dan memiliki jaminan **rollback instan** jika ada kendala.

---

## 📂 Struktur Folder Proyek Baru (`humashubci`)

```
humashubci/
├── MIGRATION_PLAN.md         # Berkas panduan ini
├── backend/                  # Kerangka Kerja CodeIgniter 4 (REST API)
│   ├── app/
│   │   ├── Controllers/Api/  # Endpoint Controller (mengembalikan JSON)
│   │   ├── Models/           # Representasi ORM database
│   │   └── Database/
│   │       └── Migrations/   # Skema pembuatan tabel
│   └── public/               # Direktori publik (tempat build Vue SPA diletakkan)
│
└── frontend/                 # Kerangka Kerja Vue.js 3 (Single Page Application)
    ├── src/
    │   ├── components/       # Komponen UI reusable (Card, Modal, Datatable)
    │   ├── router/           # Routing halaman SPA
    │   ├── stores/           # Pinia global state (Auth, News)
    │   └── views/            # Halaman Dashboard, Berita, Kliping
    └── vite.config.js        # Konfigurasi bundler Vite
```

---

## 🚀 Langkah Kerja yang Harus Dilakukan Selanjutnya

Jika Anda melanjutkan project ini di sesi berikutnya, ikuti urutan langkah di bawah ini:

### Langkah 1: Instalasi Framework Backend (CodeIgniter 4)
* Pasang CodeIgniter 4 di folder `backend/` menggunakan Composer atau manual zip.
* Atur file `.env` untuk menghubungkan ke database lokal `humashub`.
* Buat struktur dasar `Controllers/Api` untuk menguji endpoint JSON pertama.

### Langkah 2: Instalasi Frontend (Vue.js 3 SPA)
* Inisialisasi proyek Vue 3 menggunakan Vite di folder `frontend/`.
* Pasang dependensi utama: `vue-router`, `pinia`, `axios`, `tailwindcss`, dan `primevue`.
* Atur konfigurasi proxy di `vite.config.js` agar request mengarah ke `http://localhost/humashubci/backend/public/api`.

### Langkah 3: Konversi Database ke CI4 Migrations
* Buat file migrasi CI4 untuk tabel: `users`, `categories`, `media`, `news_online`, `news_online_category_rel`, `clippings`, `documentation`, dan `activity_log`.

### Langkah 4: Slicing UI & API Development (Modul per Modul)
* Selesaikan Autentikasi (Login JWT backend + Vue router guard frontend).
* Selesaikan Modul Berita Online (Multi-kategori, Filter media, Impor CSV).
* Selesaikan Modul Kliping & Dokumentasi.
