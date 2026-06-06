<template>
  <div class="system-manager-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-left">
        <h2>Manajemen & Pembaruan Sistem</h2>
        <p>Kelola pembaruan kode aplikasi Humas Hub dan lakukan pencadangan data berkala</p>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="tabs-container">
      <button :class="['tab-btn', { active: activeTab === 'update' }]" @click="activeTab = 'update'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        Pembaruan Sistem (Update)
      </button>
      <button :class="['tab-btn', { active: activeTab === 'backup' }]" @click="activeTab = 'backup'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V10.125m16.5 0v3.75m-16.5-3.75v3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
        </svg>
        Pencadangan Data (Backup)
      </button>
      <button :class="['tab-btn', { active: activeTab === 'settings' }]" @click="activeTab = 'settings'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        Pengaturan Aplikasi (Settings)
      </button>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content">
      
      <!-- ─── TAB 1: PEMBARUAN SISTEM ─────────────────────────── -->
      <div v-if="activeTab === 'update'" class="update-tab">
        <div class="grid-split">
          
          <!-- Column 1: Current status & upload -->
          <div class="flex-column gap-24">
            
            <!-- Current version status card -->
            <div class="card-section info-card">
              <div class="info-header">
                <span class="info-tag">Aktif</span>
                <h3>Informasi Versi Aplikasi</h3>
              </div>
              <div class="version-badge-container">
                <span class="v-label">Versi Aktif</span>
                <span class="v-number">v{{ currentVersion }}</span>
              </div>
              <div class="version-meta">
                <div class="meta-item">
                  <span class="meta-label">Tanggal Rilis:</span>
                  <span class="meta-val">{{ buildDate }}</span>
                </div>
                <div class="meta-item">
                  <span class="meta-label">Status DB Migrasi:</span>
                  <span class="meta-val text-emerald-600 font-semibold">Tersinkronisasi</span>
                </div>
              </div>
            </div>

            <!-- Upload drag-and-drop container -->
            <div class="card-section" v-if="!verifiedData">
              <h3>Unggah Berkas Pembaruan</h3>
              <p class="description-text">Kompres folder pembaruan Anda menjadi format <strong>.zip</strong> (pastikan terdapat file <code>version.json</code> di dalamnya) dan unggah di bawah ini.</p>
              
              <!-- Drag and drop zone -->
              <div 
                :class="['dropzone', { dragging: isDragging }]"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleFileDrop"
                @click="triggerFileSelect"
              >
                <input 
                  type="file" 
                  ref="fileInput" 
                  accept=".zip" 
                  class="hidden-file-input" 
                  @change="handleFileSelect"
                >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="upload-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                </svg>
                <p class="dz-title">Tarik & Lepaskan Berkas <code>.zip</code> di sini</p>
                <p class="dz-subtitle">atau klik untuk memilih berkas dari komputer Anda</p>
              </div>

              <!-- Uploading / Verifying states -->
              <div v-if="verifying" class="verifying-overlay">
                <div class="loader"></div>
                <span>Sedang memverifikasi berkas update...</span>
              </div>
            </div>

            <!-- Verified Confirmation Screen -->
            <div class="card-section verified-card animate-slide" v-else>
              <div class="verified-header">
                <div class="status-icon success">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                </div>
                <div>
                  <h3>Berkas Pembaruan Valid</h3>
                  <p class="description-text">Verifikasi meta rilis berhasil dilakukan.</p>
                </div>
              </div>

              <!-- Version Comparison Grid -->
              <div class="comparison-grid">
                <div class="comp-box">
                  <span class="comp-lbl">Versi Saat Ini</span>
                  <span class="comp-ver">v{{ currentVersion }}</span>
                </div>
                <div class="comp-arrow">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                  </svg>
                </div>
                <div class="comp-box highlight">
                  <span class="comp-lbl">Versi Pembaruan</span>
                  <span class="comp-ver">v{{ verifiedData.new_version }}</span>
                </div>
              </div>

              <div class="changelog-box">
                <span class="cl-title">Catatan Perubahan (Changelog):</span>
                <p class="cl-desc">{{ verifiedData.changelog }}</p>
                <span class="cl-date" v-if="verifiedData.build_date">Tanggal Build Rilis: {{ verifiedData.build_date }}</span>
              </div>

              <div class="alert-warning-box">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="alert-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <p><strong>Peringatan!</strong> Pembaruan ini akan menimpa file sistem yang ada. Direkomendasikan untuk melakukan backup database dan backup sistem di Tab Pencadangan terlebih dahulu.</p>
              </div>

              <div class="form-actions-row">
                <button class="btn-cancel" @click="cancelUpdate" :disabled="updating">Batal</button>
                <button class="btn-submit-primary" @click="applyUpdate" :disabled="updating">
                  <span v-if="updating" class="spin-sm"></span>
                  Terapkan Pembaruan Sekarang
                </button>
              </div>
            </div>
          </div>

          <!-- Column 2: Documentation update info -->
          <div class="card-section instruction-card">
            <h3>Panduan Pembaruan Sistem</h3>
            <div class="instructions">
              <div class="inst-step">
                <span class="step-num">1</span>
                <div class="step-content">
                  <h4>Kompilasi Aset Frontend di Lokal</h4>
                  <p>Jalankan perintah <code>npm run build</code> pada terminal folder frontend lokal Anda untuk menghasilkan folder kompilasi rilis di backend.</p>
                </div>
              </div>
              <div class="inst-step">
                <span class="step-num">2</span>
                <div class="step-content">
                  <h4>Buat Berkas metadata Versi</h4>
                  <p>Pastikan berkas <code>version.json</code> di root proyek lokal Anda sudah diperbarui nomor versinya sebelum dikompresi.</p>
                </div>
              </div>
              <div class="inst-step">
                <span class="step-num">3</span>
                <div class="step-content">
                  <h4>Kompres Menjadi ZIP</h4>
                  <p>Kompres seluruh file proyek (kecuali folder <code>node_modules/</code> untuk menghemat bandwidth) ke dalam format berkas ZIP.</p>
                </div>
              </div>
              <div class="inst-step">
                <span class="step-num">4</span>
                <div class="step-content">
                  <h4>Unggah dan Terapkan</h4>
                  <p>Unggah ZIP tersebut ke halaman ini. Sistem akan mengekstrak file, meremigrasi struktur database baru, dan me-refresh cache secara otomatis.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── TAB 2: PENCADANGAN DATA (BACKUP) ──────────────────── -->
      <div v-if="activeTab === 'backup'" class="backup-tab">
        <div class="grid-split">
          
          <!-- Column 1: Backup action cards -->
          <div class="flex-column gap-20">
            <div class="alert-info-box">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="info-icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 11.708 1.3l-.042.02a.75.75 0 01-.707-1.3l.041-.02zM12.75 15v.75H12V15h.75zM12 21a9 9 0 110-18 9 9 0 010 18z" />
              </svg>
              <p>Mencadangkan database dan sistem secara berkala adalah langkah penting untuk mencegah kehilangan data akibat kerusakan server atau kegagalan update file.</p>
            </div>

            <!-- Database Backup Card -->
            <div class="backup-card">
              <div class="b-icon-box db-color">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V10.125m16.5 0v3.75m-16.5-3.75v3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                </svg>
              </div>
              <div class="b-info-box">
                <h4>Cadangkan Database Utama (SQL)</h4>
                <p>Ekstrak skema tabel, relasi data, dan seluruh isi transaksi ke dalam file SQL terkompresi ZIP. Aman untuk dipulihkan kembali sewaktu-waktu.</p>
                <button class="btn-backup db-btn" @click="downloadDbBackup" :disabled="backingUpDb">
                  <span v-if="backingUpDb" class="spin-sm"></span>
                  {{ backingUpDb ? 'Memproses Dump SQL...' : 'Unduh Cadangan SQL (.zip)' }}
                </button>
              </div>
            </div>

            <!-- Full System Backup Card -->
            <div class="backup-card">
              <div class="b-icon-box file-color">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0A2.25 2.25 0 0 0 4.5 15h15a2.25 2.25 0 0 0 2.25-2.25m-19.5 0v.25A2.25 2.25 0 0 0 4.5 17.5h15a2.25 2.25 0 0 0 2.25-2.25v-.25m-19.5 0V9A2.25 2.25 0 0 1 4.5 6.75h5.062C10.9 6.75 12.25 5.4 12.25 3.75h1.5c0 1.65 1.35 3.75 2.688 3.75H19.5A2.25 2.25 0 0 1 21.75 9v3.75m-19.5 0v3.75M3 5.25h18" />
                </svg>
              </div>
              <div class="b-info-box">
                <h4>Cadangkan Seluruh Berkas Sistem</h4>
                <p>Arsipkan seluruh struktur folder proyek Humas Hub (file PHP, aset gambar uploads, kompilasi frontend) ke dalam format berkas ZIP tunggal.</p>
                <button class="btn-backup file-btn" @click="downloadFilesBackup" :disabled="backingUpFiles">
                  <span v-if="backingUpFiles" class="spin-sm"></span>
                  {{ backingUpFiles ? 'Mengompresi Berkas...' : 'Unduh Arsip Sistem (.zip)' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Column 2: Warning check panel -->
          <div class="card-section instruction-card">
            <h3>Hal Penting Sebelum Melakukan Update</h3>
            <div class="alert-checklist">
              <div class="check-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="check-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <p>Pastikan tidak ada aktivitas input data penting atau transaksi berjalan saat Anda sedang menimpa file.</p>
              </div>
              <div class="check-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="check-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <p>File SQL hasil cadangan database berisi skema <code>DROP TABLE IF EXISTS</code>. Merestore file ini ke server akan menghapus data lama pada tabel yang sama.</p>
              </div>
              <div class="check-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="check-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <p>Arsip sistem secara otomatis mengecualikan data folder temporer seperti cache, session, dan debugbar untuk memangkas ukuran berkas download.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── TAB 3: PENGATURAN APLIKASI (SETTINGS) ────────────── -->
      <div v-if="activeTab === 'settings'" class="settings-tab">
        <form @submit.prevent="saveSettings" class="settings-form-grid">
          <div class="flex-column gap-24">
            
            <!-- Card 1: Identitas Aplikasi -->
            <div class="card-section">
              <h3>Identitas Aplikasi</h3>
              <p class="description-text">Atur informasi dasar penamaan dan tampilan hak cipta footer.</p>
              
              <div class="form-group-vertical">
                <label class="field-label-bold">Nama Aplikasi (Judul)</label>
                <input v-model="settingsForm.app_name" type="text" placeholder="Contoh: Portal Humas UIN Ar-Raniry" class="input-text-styled" required>
              </div>

              <div class="form-group-vertical">
                <label class="field-label-bold">Sub Judul Aplikasi</label>
                <input v-model="settingsForm.app_subtitle" type="text" placeholder="Contoh: Portal Informasi & Data Terpadu" class="input-text-styled">
              </div>

              <div class="form-group-vertical">
                <label class="field-label-bold">Teks Copyright Footer</label>
                <input v-model="settingsForm.app_footer" type="text" placeholder="Contoh: © 2026 Humas Hub. All Rights Reserved." class="input-text-styled" required>
              </div>
            </div>

            <!-- Card 2: Pengaturan SEO Metadata -->
            <div class="card-section">
              <h3>Optimasi Mesin Pencari (SEO)</h3>
              <p class="description-text">Konfigurasi meta tags untuk memudahkan halaman diindeks oleh Google.</p>

              <div class="form-group-vertical">
                <label class="field-label-bold">SEO Title</label>
                <input v-model="settingsForm.seo_title" type="text" placeholder="Judul Halaman Utama untuk Tab Browser" class="input-text-styled">
              </div>

              <div class="form-group-vertical">
                <label class="field-label-bold">SEO Meta Description</label>
                <textarea v-model="settingsForm.app_description" placeholder="Deskripsi ringkas situs (muncul di hasil pencarian Google)" class="textarea-styled" rows="3"></textarea>
              </div>

              <div class="form-group-vertical">
                <label class="field-label-bold">SEO Keywords (Kata Kunci)</label>
                <input v-model="settingsForm.seo_keywords" type="text" placeholder="Contoh: humas, uin ar-raniry, liputan berita (pisahkan dengan koma)" class="input-text-styled">
              </div>

              <div class="form-group-vertical">
                <label class="field-label-bold">SEO Author / Organisasi</label>
                <input v-model="settingsForm.seo_author" type="text" placeholder="Contoh: UIN Ar-Raniry Banda Aceh" class="input-text-styled">
              </div>

              <div class="branding-upload-item" style="margin-top: 16px;">
                <label class="field-label-bold">Foto Meta Share (SEO Image / OpenGraph)</label>
                <div class="preview-and-upload">
                  <div class="logo-preview-box seo-image-preview">
                    <img v-if="seoImagePreview" :src="seoImagePreview" alt="SEO Meta Image Preview" class="img-preview logo-fit">
                    <div v-else class="preview-placeholder">
                      <span>No Image</span>
                    </div>
                  </div>
                  <div class="upload-controls">
                    <input type="file" ref="seoImageInput" accept="image/*" class="hidden-input" @change="handleSeoImageSelect">
                    <button type="button" class="btn-select-file" @click="$refs.seoImageInput.click()">Pilih Foto SEO</button>
                    <button v-if="settingsForm.seo_image || seoImageFile" type="button" class="btn-clear-file" @click="clearSeoImage">Hapus</button>
                  </div>
                </div>
                <p class="field-help-text">Gambar yang akan muncul saat link web dibagikan ke media sosial (WhatsApp, Facebook, Twitter). Dimensi ideal: 1200x630 piksel.</p>
              </div>
            </div>
            
          </div>

          <div class="flex-column gap-24">
            
            <!-- Card 3: Branding Assets (Logo & Favicon) -->
            <div class="card-section">
              <h3>Aset Branding</h3>
              <p class="description-text">Unggah logo institusi dan favicon (ikon tab browser).</p>
              
              <!-- Logo Upload Section -->
              <div class="branding-upload-item">
                <label class="field-label-bold">Logo Utama Aplikasi</label>
                <div class="preview-and-upload">
                  <div class="logo-preview-box">
                    <img v-if="logoPreview" :src="logoPreview" alt="Logo Preview" class="img-preview logo-fit">
                    <div v-else class="preview-placeholder">
                      <span>No Logo</span>
                    </div>
                  </div>
                  <div class="upload-controls">
                    <input type="file" ref="logoInput" accept="image/*" class="hidden-input" @change="handleLogoSelect">
                    <button type="button" class="btn-select-file" @click="$refs.logoInput.click()">Pilih Logo</button>
                    <button v-if="settingsForm.app_logo || logoFile" type="button" class="btn-clear-file" @click="clearLogo">Hapus</button>
                  </div>
                </div>
                <p class="field-help-text">Rekomendasi format PNG transparan dengan aspek rasio lebar (landscape).</p>
              </div>

              <hr class="divider">

              <!-- Favicon Upload Section -->
              <div class="branding-upload-item">
                <label class="field-label-bold">Favicon (Ikon Tab)</label>
                <div class="preview-and-upload">
                  <div class="favicon-preview-box">
                    <img v-if="faviconPreview" :src="faviconPreview" alt="Favicon Preview" class="img-preview favicon-fit">
                    <div v-else class="preview-placeholder">
                      <span>No Icon</span>
                    </div>
                  </div>
                  <div class="upload-controls">
                    <input type="file" ref="faviconInput" accept="image/x-icon,image/png,image/jpeg" class="hidden-input" @change="handleFaviconSelect">
                    <button type="button" class="btn-select-file" @click="$refs.faviconInput.click()">Pilih Favicon</button>
                    <button v-if="settingsForm.app_favicon || faviconFile" type="button" class="btn-clear-file" @click="clearFavicon">Hapus</button>
                  </div>
                </div>
                <p class="field-help-text">Gunakan format .ico atau PNG berukuran 32x32/64x64 piksel.</p>
              </div>
            </div>

            <!-- Card 4: Aksi Simpan -->
            <div class="card-section save-settings-card">
              <button type="submit" class="btn-save-settings" :disabled="savingSettings">
                <span v-if="savingSettings" class="spin-sm"></span>
                {{ savingSettings ? 'Menyimpan...' : 'Simpan Pengaturan Aplikasi' }}
              </button>
            </div>

          </div>
        </form>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// ─── STATE ────────────────────────────────────────────────
const activeTab       = ref('update')
const currentVersion  = ref('1.0.0')
const buildDate       = ref('2026-05-25')
const verifying       = ref(false)
const updating        = ref(false)
const isDragging      = ref(false)
const fileInput       = ref(null)
const selectedFile    = ref(null)
const verifiedData    = ref(null)

const backingUpDb     = ref(false)
const backingUpFiles  = ref(false)

const toast           = ref({ show: false, message: '', type: 'success' })

// --- SETTINGS STATE & METHODS ---
const getFileUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http') || path.startsWith('blob:') || path.startsWith('data:')) {
    return path
  }
  
  let apiBase = ''
  if (import.meta.env.DEV) {
    apiBase = 'http://localhost/humashubci/backend/public'
  } else {
    const pathname = window.location.pathname
    let prefix = ''
    const routeKeywords = ['/dashboard', '/login', '/berita', '/kliping', '/dokumentasi', '/rss', '/pencarian']
    let matchIndex = -1
    for (const kw of routeKeywords) {
      const idx = pathname.indexOf(kw)
      if (idx !== -1) {
        if (matchIndex === -1 || idx < matchIndex) {
          matchIndex = idx
        }
      }
    }
    if (matchIndex !== -1) {
      prefix = pathname.substring(0, matchIndex)
    } else {
      let base = pathname
      if (base.endsWith('index.html')) {
        base = base.substring(0, base.lastIndexOf('/'))
      }
      if (base.endsWith('/')) {
        base = base.slice(0, -1)
      }
      prefix = base
    }
    apiBase = window.location.origin + prefix
  }
  
  const cleanPath = path.startsWith('/') ? path.substring(1) : path
  return `${apiBase}/${cleanPath}`
}

const settingsForm = ref({
  app_name: '',
  app_subtitle: '',
  app_footer: '',
  app_description: '',
  seo_title: '',
  seo_keywords: '',
  seo_author: '',
  app_logo: '',
  app_favicon: '',
  seo_image: ''
})
const savingSettings = ref(false)

const logoInput = ref(null)
const logoFile = ref(null)
const logoPreview = ref(null)
const clearLogoRequested = ref(false)

const handleLogoSelect = (e) => {
  const file = e.target.files[0]
  if (file) {
    logoFile.value = file
    logoPreview.value = URL.createObjectURL(file)
    clearLogoRequested.value = false
  }
}

const clearLogo = () => {
  logoFile.value = null
  logoPreview.value = null
  settingsForm.value.app_logo = ''
  clearLogoRequested.value = true
  if (logoInput.value) logoInput.value.value = ''
}

const faviconInput = ref(null)
const faviconFile = ref(null)
const faviconPreview = ref(null)
const clearFaviconRequested = ref(false)

const handleFaviconSelect = (e) => {
  const file = e.target.files[0]
  if (file) {
    faviconFile.value = file
    faviconPreview.value = URL.createObjectURL(file)
    clearFaviconRequested.value = false
  }
}

const clearFavicon = () => {
  faviconFile.value = null
  faviconPreview.value = null
  settingsForm.value.app_favicon = ''
  clearFaviconRequested.value = true
  if (faviconInput.value) faviconInput.value.value = ''
}

const seoImageInput = ref(null)
const seoImageFile = ref(null)
const seoImagePreview = ref(null)
const clearSeoImageRequested = ref(false)

const handleSeoImageSelect = (e) => {
  const file = e.target.files[0]
  if (file) {
    seoImageFile.value = file
    seoImagePreview.value = URL.createObjectURL(file)
    clearSeoImageRequested.value = false
  }
}

const clearSeoImage = () => {
  seoImageFile.value = null
  seoImagePreview.value = null
  settingsForm.value.seo_image = ''
  clearSeoImageRequested.value = true
  if (seoImageInput.value) seoImageInput.value.value = ''
}

const fetchSettings = async () => {
  try {
    const res = await axios.get('/api/admin/system/settings')
    const data = res.data.data || {}
    settingsForm.value = { ...data }
    logoPreview.value = data.app_logo ? getFileUrl(data.app_logo) : null
    faviconPreview.value = data.app_favicon ? getFileUrl(data.app_favicon) : null
    seoImagePreview.value = data.seo_image ? getFileUrl(data.seo_image) : null
  } catch (e) {
    console.error(e)
    showToast('Gagal memuat pengaturan aplikasi.', 'error')
  }
}

const saveSettings = async () => {
  savingSettings.value = true
  showToast('Menyimpan pengaturan aplikasi...', 'success')
  try {
    const formData = new FormData()
    formData.append('app_name', settingsForm.value.app_name || '')
    formData.append('app_subtitle', settingsForm.value.app_subtitle || '')
    formData.append('app_footer', settingsForm.value.app_footer || '')
    formData.append('app_description', settingsForm.value.app_description || '')
    formData.append('seo_title', settingsForm.value.seo_title || '')
    formData.append('seo_keywords', settingsForm.value.seo_keywords || '')
    formData.append('seo_author', settingsForm.value.seo_author || '')
    
    if (logoFile.value) {
      formData.append('logo', logoFile.value)
    }
    if (faviconFile.value) {
      formData.append('favicon', faviconFile.value)
    }
    if (seoImageFile.value) {
      formData.append('seo_image', seoImageFile.value)
    }
    if (clearLogoRequested.value) {
      formData.append('clear_logo', '1')
    }
    if (clearFaviconRequested.value) {
      formData.append('clear_favicon', '1')
    }
    if (clearSeoImageRequested.value) {
      formData.append('clear_seo_image', '1')
    }

    const res = await axios.post('/api/admin/system/settings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    showToast(res.data.message || 'Pengaturan aplikasi berhasil disimpan.')
    
    logoFile.value = null
    faviconFile.value = null
    seoImageFile.value = null
    clearLogoRequested.value = false
    clearFaviconRequested.value = false
    clearSeoImageRequested.value = false
    
    const updated = res.data.data || {}
    settingsForm.value = { ...updated }
    logoPreview.value = updated.app_logo ? getFileUrl(updated.app_logo) : null
    faviconPreview.value = updated.app_favicon ? getFileUrl(updated.app_favicon) : null
    seoImagePreview.value = updated.seo_image ? getFileUrl(updated.seo_image) : null
  } catch (e) {
    console.error(e)
    showToast(e.response?.data?.message || 'Gagal menyimpan pengaturan.', 'error')
  } finally {
    savingSettings.value = false
  }
}

// ─── TOAST HELPER ─────────────────────────────────────────
const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 4000)
}

// ─── FETCH VERSION ────────────────────────────────────────
const fetchCurrentVersion = async () => {
  try {
    const res = await axios.get('/api/admin/system/version')
    const data = res.data.data || {}
    currentVersion.value = data.version ?? '1.0.0'
    buildDate.value = data.build_date ?? '2026-05-25'
  } catch (e) {
    console.error(e)
  }
}

// ─── FILE UPLOAD HANDLERS ─────────────────────────────────
const triggerFileSelect = () => {
  if (fileInput.value) {
    fileInput.value.click()
  }
}

const handleFileSelect = (e) => {
  const files = e.target.files
  if (files.length > 0) {
    processFile(files[0])
  }
}

const handleFileDrop = (e) => {
  isDragging.value = false
  const files = e.dataTransfer.files
  if (files.length > 0) {
    processFile(files[0])
  }
}

const processFile = async (file) => {
  // Hanya terima file zip
  if (!file.name.endsWith('.zip')) {
    showToast('Tipe berkas tidak didukung. Harap unggah berkas arsip berformat .zip', 'error')
    return
  }

  selectedFile.value = file
  verifying.value = true
  verifiedData.value = null

  try {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('action', 'verify')

    const res = await axios.post('/api/admin/system/update-zip', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    verifiedData.value = res.data.data
    showToast('Berkas update berhasil diverifikasi.')
  } catch (e) {
    selectedFile.value = null
    const errorMsg = e.response?.data?.message || 'Gagal memproses berkas zip update.'
    showToast(errorMsg, 'error')
  } finally {
    verifying.value = false
    if (fileInput.value) {
      fileInput.value.value = '' // reset input
    }
  }
}

const cancelUpdate = () => {
  selectedFile.value = null
  verifiedData.value = null
}

const applyUpdate = async () => {
  if (!selectedFile.value) return
  
  updating.value = true
  showToast('Memulai ekstraksi pembaruan. Jangan tutup halaman ini...', 'success')

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('action', 'update')

    const res = await axios.post('/api/admin/system/update-zip', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    showToast(res.data.message || 'Sistem berhasil diperbarui!', 'success')
    
    // Reset state & reload info versi aktif
    cancelUpdate()
    await fetchCurrentVersion()
  } catch (e) {
    const errorMsg = e.response?.data?.message || 'Gagal menerapkan pembaruan sistem.'
    showToast(errorMsg, 'error')
  } finally {
    updating.value = false
  }
}

// ─── BACKUP DOWNLOAD HANDLERS ─────────────────────────────
const downloadDbBackup = async () => {
  backingUpDb.value = true
  showToast('Sedang memproses salinan data database, silakan tunggu...', 'success')
  
  try {
    const res = await axios.get('/api/admin/system/backup-db', {
      responseType: 'blob'
    })

    const blob = new Blob([res.data], { type: 'application/zip' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url

    // Baca header content-disposition
    const contentDisp = res.headers['content-disposition']
    let filename = `db-backup-${new Date().toISOString().slice(0, 10)}.zip`
    if (contentDisp) {
      const match = /filename="([^"]+)"/i.exec(contentDisp)
      if (match && match[1]) filename = match[1]
    }

    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    
    showToast('Unduhan berkas cadangan database dimulai.')
  } catch (e) {
    console.error(e)
    showToast('Gagal memproses pencadangan database.', 'error')
  } finally {
    backingUpDb.value = false
  }
}

const downloadFilesBackup = async () => {
  backingUpFiles.value = true
  showToast('Sedang merangkum berkas proyek sistem. Proses ini membutuhkan waktu beberapa saat...', 'success')

  try {
    const res = await axios.get('/api/admin/system/backup-files', {
      responseType: 'blob'
    })

    const blob = new Blob([res.data], { type: 'application/zip' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url

    const contentDisp = res.headers['content-disposition']
    let filename = `system-backup-${new Date().toISOString().slice(0, 10)}.zip`
    if (contentDisp) {
      const match = /filename="([^"]+)"/i.exec(contentDisp)
      if (match && match[1]) filename = match[1]
    }

    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    showToast('Unduhan berkas kompresi sistem dimulai.')
  } catch (e) {
    console.error(e)
    showToast('Gagal memproses pencadangan sistem berkas.', 'error')
  } finally {
    backingUpFiles.value = false
  }
}

// ─── INIT ─────────────────────────────────────────────────
onMounted(() => {
  fetchCurrentVersion()
  fetchSettings()
})
</script>

<style scoped>
.system-manager-page {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.page-header h2 {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.page-header p {
  font-size: 13.5px;
  color: #64748b;
  margin-top: 4px;
}

/* Tabs */
.tabs-container {
  display: flex;
  gap: 6px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 2px;
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  border-bottom: 2px solid transparent;
  background: transparent;
  color: #64748b;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: -2px;
}

.tab-btn svg {
  width: 18px;
  height: 18px;
}

.tab-btn:hover {
  color: #0f172a;
}

.tab-btn.active {
  color: #4f46e5;
  border-bottom-color: #4f46e5;
}

/* Tab Layout Grid split */
.grid-split {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 24px;
}

@media (max-width: 960px) {
  .grid-split {
    grid-template-columns: 1fr;
  }
}

.flex-column {
  display: flex;
  flex-direction: column;
}

.gap-20 { gap: 20px; }
.gap-24 { gap: 24px; }

/* Card Section */
.card-section {
  background: white;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.02);
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: relative;
}

.card-section h3 {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.description-text {
  font-size: 13.5px;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

/* Info Card */
.info-card {
  background: linear-gradient(135deg, #1e293b, #0f172a);
  color: white;
  border: none;
}

.info-card h3 {
  color: white;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-tag {
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
  font-size: 10.5px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 12px;
  text-transform: uppercase;
}

.version-badge-container {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.v-label {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 600;
}

.v-number {
  font-size: 22px;
  font-weight: 900;
  color: #60a5fa;
  letter-spacing: 0.5px;
}

.version-meta {
  display: flex;
  flex-direction: column;
  gap: 10px;
  border-top: 1px solid rgba(255,255,255,0.08);
  padding-top: 14px;
}

.meta-item {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.meta-label {
  color: #94a3b8;
}

.meta-val {
  color: #f1f5f9;
  font-weight: 600;
}

/* Dropzone */
.dropzone {
  border: 2px dashed #cbd5e1;
  border-radius: 16px;
  background: #fafafa;
  padding: 40px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s ease;
}

.dropzone:hover, .dropzone.dragging {
  border-color: #4f46e5;
  background: #f5f3ff;
}

.upload-icon {
  width: 44px;
  height: 44px;
  color: #94a3b8;
  margin-bottom: 12px;
  transition: transform 0.2s;
}

.dropzone:hover .upload-icon, .dropzone.dragging .upload-icon {
  color: #4f46e5;
  transform: translateY(-4px);
}

.dz-title {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.dz-subtitle {
  font-size: 12px;
  color: #64748b;
  margin: 0;
}

.hidden-file-input {
  display: none;
}

.verifying-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  border-radius: 20px;
  color: #4f46e5;
  font-size: 13.5px;
  font-weight: 600;
  z-index: 10;
}

/* Verified Confirmation Screen */
.verified-card {
  border-color: #22c55e;
  box-shadow: 0 4px 20px rgba(34, 197, 94, 0.05);
}

.verified-header {
  display: flex;
  align-items: center;
  gap: 14px;
}

.status-icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.status-icon.success {
  background: #dcfce7;
  color: #15803d;
}

.status-icon.success svg {
  width: 20px;
  height: 20px;
}

.comparison-grid {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 16px;
  border-radius: 12px;
}

.comp-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.comp-lbl {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
}

.comp-ver {
  font-size: 18px;
  font-weight: 800;
  color: #475569;
}

.comp-box.highlight .comp-ver {
  color: #4f46e5;
  font-size: 20px;
}

.comp-arrow svg {
  width: 18px;
  height: 18px;
  color: #94a3b8;
}

.changelog-box {
  background: #fafafa;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.cl-title {
  font-size: 12.5px;
  font-weight: 800;
  color: #0f172a;
}

.cl-desc {
  font-size: 13px;
  color: #475569;
  line-height: 1.5;
  margin: 0;
  white-space: pre-wrap;
}

.cl-date {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 4px;
  font-weight: 500;
}

.alert-warning-box {
  background: #fffbeb;
  border: 1px solid #fef3c7;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.alert-icon {
  width: 20px;
  height: 20px;
  color: #d97706;
  flex-shrink: 0;
  margin-top: 2px;
}

.alert-warning-box p {
  font-size: 12.5px;
  color: #b45309;
  line-height: 1.5;
  margin: 0;
}

.form-actions-row {
  display: flex;
  gap: 12px;
  border-top: 1px solid #f1f5f9;
  padding-top: 18px;
}

.btn-cancel {
  flex: 1;
  padding: 12px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #475569;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.btn-submit-primary {
  flex: 1.8;
  padding: 12px;
  border-radius: 10px;
  border: none;
  background: #0f172a;
  color: white;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.2s;
}

.btn-submit-primary:hover:not(:disabled) {
  background: #1e293b;
}

.btn-submit-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Instruction Card / Guide */
.instruction-card {
  background: #f8fafc;
}

.instructions {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.inst-step {
  display: flex;
  gap: 14px;
}

.step-num {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #0f172a;
  color: white;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.step-content h4 {
  font-size: 13.5px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px 0;
}

.step-content p {
  font-size: 12.5px;
  color: #64748b;
  line-height: 1.5;
  margin: 0;
}

/* Tab 2: Backup Panel */
.alert-info-box {
  background: #eff6ff;
  border: 1px solid #dbeafe;
  border-radius: 14px;
  padding: 14px 18px;
  display: flex;
  gap: 12px;
}

.info-icon {
  width: 20px;
  height: 20px;
  color: #2563eb;
  flex-shrink: 0;
}

.alert-info-box p {
  font-size: 13px;
  color: #1e40af;
  line-height: 1.55;
  margin: 0;
}

.backup-card {
  background: white;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.015);
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.b-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.b-icon-box svg {
  width: 24px;
  height: 24px;
}

.b-icon-box.db-color {
  background: #eef2ff;
  color: #4f46e5;
}

.b-icon-box.file-color {
  background: #fdf2f8;
  color: #db2777;
}

.b-info-box {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.b-info-box h4 {
  font-size: 15px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.b-info-box p {
  font-size: 13px;
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 10px 0;
}

.btn-backup {
  align-self: flex-start;
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  color: white;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.btn-backup:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn-backup:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.db-btn {
  background: #4f46e5;
}
.db-btn:hover:not(:disabled) {
  background: #4338ca;
  box-shadow: 0 6px 14px rgba(79, 70, 229, 0.2);
}

.file-btn {
  background: #db2777;
}
.file-btn:hover:not(:disabled) {
  background: #be185d;
  box-shadow: 0 6px 14px rgba(219, 39, 119, 0.2);
}

/* Alert checklist */
.alert-checklist {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.check-item {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.check-icon {
  width: 16px;
  height: 16px;
  color: #22c55e;
  flex-shrink: 0;
  margin-top: 3px;
}

.check-item p {
  font-size: 13px;
  color: #475569;
  line-height: 1.5;
  margin: 0;
}

/* Loader / spin */
.loader {
  width: 24px;
  height: 24px;
  border: 3px solid #e2e8f0;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.spin-sm {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-slide {
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Toast */
.toast {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 20000;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 20px;
  border-radius: 14px;
  font-size: 13.5px;
  font-weight: 600;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
  min-width: 260px;
}

.toast svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.toast.success {
  background: #0f172a;
  color: white;
}

.toast.error {
  background: #dc2626;
  color: white;
}

.toast-enter-active, .toast-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.95);
}

@media (max-width: 640px) {
  .backup-card {
    flex-direction: column;
    gap: 14px;
    align-items: stretch;
  }
  .btn-backup {
    width: 100%;
    justify-content: center;
  }
  .toast {
    max-width: calc(100vw - 32px);
    right: 16px;
    bottom: 16px;
  }
}

/* --- Settings Tab Styles --- */
.settings-form-grid {
  display: grid;
  grid-template-columns: 1.25fr 1fr;
  gap: 24px;
  align-items: start;
}

@media (max-width: 960px) {
  .settings-form-grid {
    grid-template-columns: 1fr;
  }
}

.form-group-vertical {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;
}

.field-label-bold {
  font-size: 13.5px;
  font-weight: 700;
  color: #1e293b;
}

.input-text-styled {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #1e293b;
  font-size: 13.5px;
  transition: all 0.2s ease;
  font-family: inherit;
}

.input-text-styled:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

.textarea-styled {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #1e293b;
  font-size: 13.5px;
  transition: all 0.2s ease;
  font-family: inherit;
  resize: vertical;
}

.textarea-styled:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

/* Branding uploads */
.branding-upload-item {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.preview-and-upload {
  display: flex;
  align-items: center;
  gap: 18px;
}

.logo-preview-box {
  width: 140px;
  height: 60px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: #fafafa;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.favicon-preview-box {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background: #fafafa;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.img-preview {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.logo-fit {
  padding: 4px;
}

.favicon-fit {
  width: 24px;
  height: 24px;
}

.preview-placeholder {
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
}

.upload-controls {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.hidden-input {
  display: none;
}

.btn-select-file {
  padding: 8px 14px;
  border-radius: 8px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #475569;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-select-file:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #94a3b8;
}

.btn-clear-file {
  padding: 8px 14px;
  border-radius: 8px;
  border: none;
  background: rgba(239, 68, 68, 0.08);
  color: #ef4444;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.btn-clear-file:hover {
  background: rgba(239, 68, 68, 0.15);
}

.divider {
  border: none;
  border-top: 1px solid #f1f5f9;
  margin: 6px 0;
  width: 100%;
}

.field-help-text {
  font-size: 11.5px;
  color: #94a3b8;
  margin: 0;
}

.save-settings-card {
  align-items: stretch;
  background: #fafafa;
  border-style: dashed;
}

.btn-save-settings {
  width: 100%;
  padding: 14px;
  border-radius: 12px;
  border: none;
  background: #4f46e5;
  color: white;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
  transition: all 0.2s ease;
}

.btn-save-settings:hover:not(:disabled) {
  background: #4338ca;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
}

.btn-save-settings:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
