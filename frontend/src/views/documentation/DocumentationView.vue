<template>
  <div class="doc-view-page">
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Memuat detail dokumentasi...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="goBack" class="btn-back">Kembali ke Daftar</button>
    </div>

    <div v-else-if="doc" class="doc-container">
      <!-- Top Header -->
      <div class="doc-view-container">
        <!-- Back Header -->
        <div class="view-header">
          <router-link to="/dashboard/documentation" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Daftar Dokumentasi</span>
          </router-link>
          <div style="flex: 1"></div>
          <button @click="editDoc" class="btn-edit">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
            <span>Edit Dokumentasi</span>
          </button>
        </div>
      </div>

      <!-- Banner Thumbnail -->
      <div class="doc-banner">
        <img v-if="doc.thumbnail_url" :src="getImageUrl(doc.thumbnail_url)" :alt="doc.event_name" referrerpolicy="no-referrer" @error="handleImageError" />
        <div v-else class="banner-placeholder">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
          </svg>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="content-grid">
        
        <!-- Left Column: Info & Attendees -->
        <div class="main-column">
          <!-- Title & Desc Card -->
          <div class="info-card">
            <h1 class="doc-title">{{ doc.event_name }}</h1>
            <p class="doc-desc">{{ doc.description || 'Tidak ada deskripsi kegiatan.' }}</p>
            
            <a v-if="doc.news_link" :href="doc.news_link" target="_blank" class="news-link-btn">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
              </svg>
              Baca berita terkait di Web Resmi
            </a>
          </div>

          <!-- Attendees Card -->
          <div class="attendees-card">
            <div class="card-header-clean">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
              <h3>Pimpinan & Tokoh yang Hadir</h3>
            </div>

            <div class="attendees-body" v-if="doc.attendees && doc.attendees.length > 0">
              <!-- Rektorat -->
              <div class="attendee-group" v-if="rektoratList.length > 0">
                <h4 class="group-title">PIMPINAN REKTORAT</h4>
                <div class="attendee-grid">
                  <div class="attendee-item" v-for="(att, i) in rektoratList" :key="i">
                    <span class="att-name">{{ att.person_name }}</span>
                    <span class="att-pos">{{ att.position }}</span>
                  </div>
                </div>
              </div>

              <!-- Fakultas -->
              <div class="attendee-group" v-if="fakultasList.length > 0">
                <h4 class="group-title">PIMPINAN FAKULTAS/PASCA</h4>
                <div class="attendee-grid">
                  <div class="attendee-item" v-for="(att, i) in fakultasList" :key="i">
                    <span class="att-name">{{ att.person_name }}</span>
                    <span class="att-pos">{{ att.position }}</span>
                  </div>
                </div>
              </div>

              <!-- Lainnya -->
              <div class="attendee-group" v-if="otherList.length > 0">
                <h4 class="group-title">TOKOH/PEJABAT LAINNYA</h4>
                <div class="attendee-grid">
                  <div class="attendee-item" v-for="(att, i) in otherList" :key="i">
                    <span class="att-name">{{ att.person_name }}</span>
                    <span class="att-pos">{{ att.position }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="no-data-text">Tidak ada data kehadiran pimpinan/tokoh.</div>
          </div>
        </div>

        <!-- Right Column: Meta Details -->
        <div class="side-column">
          <div class="meta-card">
            <div class="card-header-clean">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
              </svg>
              <h3>Detail Kegiatan</h3>
            </div>
            
            <div class="meta-list">
              <div class="meta-item">
                <div class="meta-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                </div>
                <div class="meta-content">
                  <span class="meta-label">TANGGAL</span>
                  <span class="meta-value">{{ formatDateFull(doc.event_date) }}</span>
                </div>
              </div>

              <div class="meta-item">
                <div class="meta-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                </div>
                <div class="meta-content">
                  <span class="meta-label">KATEGORI</span>
                  <div class="meta-categories" v-if="doc.categories && doc.categories.length > 0">
                    <span v-for="cat in doc.categories" :key="cat.id" class="category-badge">{{ cat.name }}</span>
                  </div>
                  <span class="meta-value" v-else>Tanpa Kategori</span>
                </div>
              </div>

              <div class="meta-item">
                <div class="meta-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                </div>
                <div class="meta-content">
                  <span class="meta-label">LOKASI</span>
                  <span class="meta-value font-semibold">{{ doc.location_name || '—' }}</span>
                  <span class="meta-subvalue">{{ doc.location_type }}</span>
                </div>
              </div>

              <div class="meta-item">
                <div class="meta-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </div>
                <div class="meta-content">
                  <span class="meta-label">KREDIT MEDIA</span>
                  <span class="meta-value">{{ doc.creator_name || 'Tidak ada data pembuat' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gallery Section (Only show if at least one link exists) -->
      <div class="gallery-section" v-if="doc.photo_folder_link || doc.video_folder_link">
        <div class="gallery-header">
          <div class="gh-left">
            <div class="gh-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
            </div>
            <div>
              <h2>Galeri Dokumentasi</h2>
              <p>Akses folder foto dan video via Google Drive</p>
            </div>
          </div>
          <div class="gh-right">
            <a v-if="doc.photo_folder_link" :href="doc.photo_folder_link" target="_blank" class="btn-gdrive btn-photo">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
              Folder Foto
            </a>
            <a v-if="doc.video_folder_link" :href="doc.video_folder_link" target="_blank" class="btn-gdrive btn-video">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" /></svg>
              Folder Video
            </a>
          </div>
        </div>

        <div class="iframe-grid" :class="{'single-col': !doc.photo_folder_link || !doc.video_folder_link}">
          <div class="iframe-box" v-if="doc.photo_folder_link">
            <div class="iframe-label">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
              Pratinjau Foto
            </div>
            <iframe v-if="getIframeUrl(doc.photo_folder_link)" :src="getIframeUrl(doc.photo_folder_link)" frameborder="0" allowfullscreen></iframe>
            <div v-else class="iframe-error">Link folder foto tidak valid untuk pratinjau.</div>
          </div>

          <div class="iframe-box" v-if="doc.video_folder_link">
            <div class="iframe-label" style="color: #8b5cf6;">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" /></svg>
              Pratinjau Video
            </div>
            <iframe v-if="getIframeUrl(doc.video_folder_link)" :src="getIframeUrl(doc.video_folder_link)" frameborder="0" allowfullscreen></iframe>
            <div v-else class="iframe-error">Link folder video tidak valid untuk pratinjau.</div>
          </div>
        </div>
      </div>

    </div>

    <!-- Form Modal (Edit Only) -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-header">
            <h4>Edit Dokumentasi</h4>
            <button @click="closeModal" class="btn-close">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveDoc" class="modal-form">
            <!-- Card 1: Informasi Utama -->
            <div class="form-card">
              <div class="card-header">
                <div class="card-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                </div>
                <h5>Informasi Utama</h5>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <label>Nama Kegiatan *</label>
                  <input v-model="form.event_name" type="text" placeholder="Masukkan nama kegiatan..." required />
                </div>
                <div class="form-group">
                  <label>Deskripsi Kegiatan</label>
                  <textarea v-model="form.description" rows="3" placeholder="Deskripsi singkat kegiatan..."></textarea>
                </div>
                <div class="form-group">
                  <label>Tautan Berita Terkait (opsional)</label>
                  <input v-model="form.news_link" type="url" placeholder="https://..." />
                </div>
              </div>
            </div>

            <!-- Card 2: Pelaksanaan -->
            <div class="form-card">
              <div class="card-header">
                <div class="card-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                  </svg>
                </div>
                <h5>Waktu & Tempat Pelaksanaan</h5>
              </div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group flex-1">
                    <label>Tanggal Kegiatan *</label>
                    <input v-model="form.event_date" type="date" required />
                  </div>
                  <div class="form-group flex-1">
                    <label>Cakupan / Skala Kegiatan</label>
                    <select v-model="form.location_type">
                      <option value="Internal Kampus">Internal Kampus</option>
                      <option value="Lokal Daerah">Lokal Daerah</option>
                      <option value="Nasional">Nasional</option>
                      <option value="Internasional">Internasional</option>
                    </select>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group flex-2">
                    <label>Lokasi Pelaksanaan</label>
                    <input v-model="form.location_name" type="text" placeholder="Nama gedung / tempat kegiatan..." />
                  </div>
                  <div class="form-group flex-1">
                    <label>Fotografer / Pembuat</label>
                    <input v-model="form.creator_name" type="text" placeholder="Nama fotografer..." />
                  </div>
                </div>
              </div>
            </div>

            <!-- Card 3: Kategorisasi -->
            <div class="form-card">
              <div class="card-header">
                <div class="card-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                  </svg>
                </div>
                <h5>Kategorisasi</h5>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <label>Pilih Kategori (Bisa Lebih dari Satu)</label>
                  <div class="categories-checkbox-grid">
                    <label v-for="cat in categoryList" :key="cat.id" class="checkbox-label">
                      <input type="checkbox" :value="cat.id" v-model="form.category_ids" />
                      <span class="custom-checkbox"></span>
                      <span class="checkbox-text">{{ cat.name }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card 4: Kehadiran Pimpinan -->
            <div class="form-card">
              <div class="card-header">
                <div class="card-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                  </svg>
                </div>
                <h5>Kehadiran Pimpinan</h5>
              </div>
              <div class="card-body">
                <!-- Rektorat Section -->
                <div class="attendance-block">
                  <div class="attendance-block-header">
                    <h6>Pimpinan Tk. Rektorat</h6>
                    <button type="button" @click="addRektoratAttendee" class="btn-add-row">+ Tambah</button>
                  </div>
                  <div class="attendance-rows-container">
                    <div v-for="(att, idx) in rektoratAttendees" :key="idx" class="attendee-row">
                      <div class="form-group flex-1">
                        <label>Jabatan</label>
                        <select v-model="att.position" required>
                          <option value="" disabled>-- Pilih --</option>
                          <option value="Rektor">Rektor</option>
                          <option value="Wakil Rektor 1">Wakil Rektor 1</option>
                          <option value="Wakil Rektor 2">Wakil Rektor 2</option>
                          <option value="Wakil Rektor 3">Wakil Rektor 3</option>
                          <option value="Ka. Biro AUPK">Ka. Biro AUPK</option>
                          <option value="Ka. Biro AAKK">Ka. Biro AAKK</option>
                        </select>
                      </div>
                      <div class="form-group flex-2">
                        <label>Nama Pejabat</label>
                        <input v-model="att.person_name" type="text" placeholder="Nama lengkap & gelar" required />
                      </div>
                      <button type="button" @click="removeRektoratAttendee(idx)" class="btn-remove-row" title="Hapus baris ini">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="rektoratAttendees.length === 0" class="no-attendees">Belum ada pimpinan rektorat yang ditambahkan.</p>
                  </div>
                </div>

                <!-- Fakultas Section -->
                <div class="attendance-block">
                  <div class="attendance-block-header">
                    <h6>Pimpinan Tk. Fakultas / Pascasarjana</h6>
                    <button type="button" @click="addFakultasAttendee" class="btn-add-row">+ Tambah</button>
                  </div>
                  <div class="attendance-rows-container">
                    <div v-for="(att, idx) in fakultasAttendees" :key="idx" class="attendee-row">
                      <div class="form-group flex-1">
                        <label>Jabatan</label>
                        <select v-model="att.position" required>
                          <option value="" disabled>-- Pilih --</option>
                          <option value="Dekan">Dekan</option>
                          <option value="Wakil Dekan 1">Wakil Dekan 1</option>
                          <option value="Wakil Dekan 2">Wakil Dekan 2</option>
                          <option value="Wakil Dekan 3">Wakil Dekan 3</option>
                          <option value="Dir. Pascasarjana">Dir. Pascasarjana</option>
                          <option value="Wadir Pascasarjana">Wadir Pascasarjana</option>
                          <option value="Ka. Prodi">Ka. Prodi</option>
                          <option value="Lainnya">Lainnya</option>
                        </select>
                      </div>
                      <div class="form-group flex-2">
                        <label>Nama Pejabat</label>
                        <input v-model="att.person_name" type="text" placeholder="Nama lengkap & gelar" required />
                      </div>
                      <button type="button" @click="removeFakultasAttendee(idx)" class="btn-remove-row" title="Hapus baris ini">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="fakultasAttendees.length === 0" class="no-attendees">Belum ada pimpinan fakultas/pascasarjana yang ditambahkan.</p>
                  </div>
                </div>

                <!-- Tokoh Lainnya Section -->
                <div class="attendance-block">
                  <div class="attendance-block-header">
                    <h6>Tokoh / Pejabat Lainnya</h6>
                    <button type="button" @click="addOtherAttendee" class="btn-add-row">+ Tambah</button>
                  </div>
                  <div class="attendance-rows-container">
                    <div v-for="(att, idx) in otherAttendees" :key="idx" class="attendee-row">
                      <div class="form-group flex-1">
                        <label>Jabatan / Instansi</label>
                        <input v-model="att.position" type="text" placeholder="Contoh: Bupati Aceh Besar" required />
                      </div>
                      <div class="form-group flex-2">
                        <label>Nama Tokoh</label>
                        <input v-model="att.person_name" type="text" placeholder="Nama lengkap & gelar" required />
                      </div>
                      <button type="button" @click="removeOtherAttendee(idx)" class="btn-remove-row" title="Hapus baris ini">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="otherAttendees.length === 0" class="no-attendees">Belum ada tokoh lainnya yang ditambahkan.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card 5: Tautan Media -->
            <div class="form-card">
              <div class="card-header">
                <div class="card-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                  </svg>
                </div>
                <h5>Tautan Media</h5>
              </div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group flex-1">
                    <label>Thumbnail URL</label>
                    <input v-model="form.thumbnail_url" type="url" placeholder="https://... (link gambar sampul)" />
                  </div>
                  <div class="form-group flex-1">
                    <label>Folder Foto (Google Drive / dll)</label>
                    <input v-model="form.photo_folder_link" type="url" placeholder="https://drive.google.com/..." />
                  </div>
                  <div class="form-group flex-1">
                    <label>Folder Video / YouTube</label>
                    <input v-model="form.video_folder_link" type="url" placeholder="https://youtube.com/..." />
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" @click="closeModal" class="btn-cancel">Batal</button>
              <button type="submit" class="btn-save" :disabled="saving">
                <span v-if="!saving">Simpan Data</span>
                <div v-else class="loader loader-sm"></div>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const doc = ref(null)
const loading = ref(true)
const error = ref(null)

const fetchDoc = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await axios.get(`/api/admin/documentation/${route.params.id}`)
    if (res.data.status === 'success') {
      doc.value = res.data.data
    } else {
      error.value = 'Gagal memuat data dokumentasi.'
    }
  } catch (err) {
    console.error(err)
    error.value = 'Terjadi kesalahan jaringan atau data tidak ditemukan.'
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.push('/dashboard/documentation')
}

const getImageUrl = (url) => {
  if (!url) return '';
  const gdriveMatch = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
  if (gdriveMatch && gdriveMatch[1]) {
    // Gunakan lh3.googleusercontent.com yang lebih stabil dan bersifat publik (tanpa login Google)
    return `https://lh3.googleusercontent.com/d/${gdriveMatch[1]}=w1200`;
  }
  return url;
}

const handleImageError = (e) => {
  e.target.style.display = 'none';
  e.target.insertAdjacentHTML('afterend', `<div class="banner-placeholder error-placeholder">Gambar gagal dimuat</div>`);
}

const formatDateFull = (dateStr) => {
  if (!dateStr) return '-'
  const dateObj = new Date(dateStr)
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  }).format(dateObj)
}

// Iframe URL Extractor
const getIframeUrl = (url) => {
  if (!url) return null;
  // Match folder ID
  const folderMatch = url.match(/folders\/([a-zA-Z0-9_-]+)/) || url.match(/id=([a-zA-Z0-9_-]+)/);
  if (folderMatch && folderMatch[1]) {
    return `https://drive.google.com/embeddedfolderview?id=${folderMatch[1]}#grid`;
  }
  return null;
}

// Attendees grouping
const docId = computed(() => route.params.id)

const showModal = ref(false)
const saving = ref(false)
const categoryList = ref([])

const form = ref({
  id: null,
  event_name: '',
  event_date: '',
  description: '',
  news_link: '',
  location_name: '',
  location_type: 'Internal Kampus',
  thumbnail_url: '',
  photo_folder_link: '',
  video_folder_link: '',
  creator_name: '',
  category_ids: [],
  attendees: [],
})

const rektoratAttendees = ref([])
const fakultasAttendees = ref([])
const otherAttendees = ref([])

const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/admin/categories')
    if (res.data.status === 'success') categoryList.value = res.data.data
  } catch (err) {
    console.error('Gagal memuat kategori:', err)
  }
}

const editDoc = () => {
  if (!doc.value) return
  const d = doc.value
  form.value = {
    id:                d.id,
    event_name:        d.event_name,
    event_date:        d.event_date,
    description:       d.description || '',
    news_link:         d.news_link || '',
    location_name:     d.location_name || '',
    location_type:     d.location_type || 'Internal Kampus',
    thumbnail_url:     d.thumbnail_url || '',
    photo_folder_link: d.photo_folder_link || '',
    video_folder_link: d.video_folder_link || '',
    creator_name:      d.creator_name || '',
    category_ids:      d.categories ? d.categories.map(c => c.id) : [],
    attendees:         d.attendees || [],
  }
  rektoratAttendees.value = (d.attendees || []).filter(a => a.level === 'Rektorat')
  fakultasAttendees.value = (d.attendees || []).filter(a => a.level === 'Fakultas')
  otherAttendees.value    = (d.attendees || []).filter(a => a.level === 'Lainnya' || a.level === 'Other')
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const addRektoratAttendee    = () => rektoratAttendees.value.push({ level: 'Rektorat', position: '', person_name: '' })
const removeRektoratAttendee = (idx) => rektoratAttendees.value.splice(idx, 1)

const addFakultasAttendee    = () => fakultasAttendees.value.push({ level: 'Fakultas', position: '', person_name: '' })
const removeFakultasAttendee = (idx) => fakultasAttendees.value.splice(idx, 1)

const addOtherAttendee       = () => otherAttendees.value.push({ level: 'Lainnya', position: '', person_name: '' })
const removeOtherAttendee    = (idx) => otherAttendees.value.splice(idx, 1)

const saveDoc = async () => {
  saving.value = true
  try {
    form.value.attendees = [
      ...rektoratAttendees.value.filter(a => a.person_name.trim() !== '').map(a => ({ level: 'Rektorat', position: a.position, person_name: a.person_name.trim() })),
      ...fakultasAttendees.value.filter(a => a.person_name.trim() !== '').map(a => ({ level: 'Fakultas', position: a.position, person_name: a.person_name.trim() })),
      ...otherAttendees.value.filter(a => a.person_name.trim() !== '').map(a => ({ level: 'Lainnya', position: a.position, person_name: a.person_name.trim() }))
    ]

    const res = await axios.put(`/api/admin/documentation/${form.value.id}`, form.value)
    if (res.data.status === 'success') {
      closeModal()
      fetchDoc()
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan dokumentasi.')
  } finally {
    saving.value = false
  }
}

const rektoratList = computed(() => {
  if (!doc.value || !doc.value.attendees) return []
  const titles = ['Rektor', 'Wakil Rektor 1', 'Wakil Rektor 2', 'Wakil Rektor 3', 'Ka. Biro AUPK', 'Ka. Biro AAKK']
  return doc.value.attendees.filter(a => titles.includes(a.position))
})

const fakultasList = computed(() => {
  if (!doc.value || !doc.value.attendees) return []
  const titles = ['Dekan', 'Wakil Dekan 1', 'Wakil Dekan 2', 'Wakil Dekan 3', 'Dir. Pascasarjana', 'Wadir Pascasarjana', 'Ka. Prodi', 'Lainnya']
  return doc.value.attendees.filter(a => titles.includes(a.position))
})

const otherList = computed(() => {
  if (!doc.value || !doc.value.attendees) return []
  const rektoratTitles = ['Rektor', 'Wakil Rektor 1', 'Wakil Rektor 2', 'Wakil Rektor 3', 'Ka. Biro AUPK', 'Ka. Biro AAKK']
  const fakultasTitles = ['Dekan', 'Wakil Dekan 1', 'Wakil Dekan 2', 'Wakil Dekan 3', 'Dir. Pascasarjana', 'Wadir Pascasarjana', 'Ka. Prodi', 'Lainnya']
  return doc.value.attendees.filter(a => !rektoratTitles.includes(a.position) && !fakultasTitles.includes(a.position))
})

onMounted(() => {
  fetchDoc()
  fetchCategories()
})
</script>

<style scoped>
.doc-view-page {
  padding: 24px;
  background-color: #f1f5f9;
  min-height: calc(100vh - 64px);
  color: #0f172a;
}

.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 50vh;
}

.spinner {
  width: 40px; height: 40px;
  border: 4px solid #e2e8f0;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #64748b;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
  padding: 8px 12px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}
.btn-back svg { width: 16px; height: 16px; transition: transform 0.2s; }
.btn-back:hover { color: #8b5cf6; border-color: #ddd6fe; background: #faf5ff; }
.btn-back:hover svg { transform: translateX(-3px); }

.doc-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.view-header {
  display: flex;
  align-items: center;
}

.btn-edit {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: white;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
  padding: 8px 16px;
  background: #10b981;
  border: none;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2);
  cursor: pointer;
}
.btn-edit svg { width: 16px; height: 16px; }
.btn-edit:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }

.doc-banner {
  width: 100%;
  height: 360px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  background: #cbd5e1;
}
.doc-banner img {
  width: 100%; height: 100%;
  object-fit: cover;
}
.banner-placeholder {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #e2e8f0, #f8fafc);
  color: #94a3b8;
}
.banner-placeholder svg { width: 64px; height: 64px; }
.error-placeholder { background: #fee2e2; color: #ef4444; font-weight: 600; }

.content-grid {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 24px;
  align-items: start;
}

@media (max-width: 960px) {
  .content-grid { grid-template-columns: 1fr; }
  .doc-banner { height: 240px; }
}

.info-card, .attendees-card, .meta-card, .gallery-section {
  background: white;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  margin-bottom: 24px;
}

.info-card { padding-bottom: 40px; }
.doc-title {
  font-size: 28px; font-weight: 700; color: #0f172a;
  margin-bottom: 16px; line-height: 1.3;
}
.doc-title::after {
  content: ''; display: block;
  width: 60px; height: 4px;
  background: #10b981; border-radius: 2px;
  margin-top: 16px;
}
.doc-desc {
  font-size: 16px; line-height: 1.6; color: #475569;
  margin-bottom: 32px; white-space: pre-wrap;
}
.news-link-btn {
  display: inline-flex; align-items: center; gap: 8px;
  color: #10b981; font-weight: 600; text-decoration: none;
  font-size: 15px;
}
.news-link-btn svg { width: 20px; height: 20px; }

.card-header-clean {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 24px;
}
.card-header-clean h3 { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; }
.card-header-clean svg { width: 24px; height: 24px; color: #10b981; }

.group-title {
  font-size: 11px; font-weight: 700; color: #10b981;
  text-transform: uppercase; letter-spacing: 1px;
  margin-bottom: 12px; margin-top: 24px;
}
.attendee-group:first-child .group-title { margin-top: 0; }

.attendee-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}
.attendee-item { display: flex; flex-direction: column; }
.att-name { font-weight: 600; color: #1e293b; font-size: 15px; }
.att-pos { font-size: 13px; color: #64748b; }

.no-data-text { color: #94a3b8; font-style: italic; font-size: 14px; }

.meta-list { display: flex; flex-direction: column; gap: 20px; }
.meta-item { display: flex; gap: 16px; align-items: flex-start; }
.meta-icon {
  width: 36px; height: 36px; border-radius: 8px;
  background: #f1f5f9; color: #64748b;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.meta-icon svg { width: 20px; height: 20px; }
.meta-content { display: flex; flex-direction: column; gap: 4px; }
.meta-label { font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; }
.meta-value { font-size: 15px; color: #1e293b; font-weight: 500; }
.meta-subvalue { font-size: 12px; color: #94a3b8; }
.font-semibold { font-weight: 600; }

.meta-categories { display: flex; flex-wrap: wrap; gap: 6px; }
.category-badge {
  background: #eef2ff; color: #4f46e5;
  padding: 4px 10px; border-radius: 100px;
  font-size: 12px; font-weight: 600;
}

.gallery-section { padding: 32px; }
.gallery-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 24px; flex-wrap: wrap; gap: 16px;
  background: #f8fafc; padding: 24px; border-radius: 12px;
}
.gh-left { display: flex; align-items: center; gap: 16px; }
.gh-icon {
  width: 48px; height: 48px; border-radius: 12px;
  background: #eef2ff; color: #10b981;
  display: flex; align-items: center; justify-content: center;
}
.gh-icon svg { width: 24px; height: 24px; }
.gh-left h2 { font-size: 20px; font-weight: 700; margin: 0 0 4px 0; color: #0f172a; }
.gh-left p { font-size: 14px; color: #64748b; margin: 0; }

.gh-right { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-gdrive {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 8px 16px; border-radius: 8px;
  font-size: 13px; font-weight: 600; text-decoration: none;
  transition: all 0.2s;
}
.btn-photo { background: #eff6ff; color: #3b82f6; }
.btn-photo:hover { background: #dbeafe; }
.btn-video { background: #faf5ff; color: #a855f7; }
.btn-video:hover { background: #f3e8ff; }
.btn-gdrive svg { width: 18px; height: 18px; }

.iframe-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
}
.iframe-grid.single-col { grid-template-columns: 1fr; }
@media (max-width: 768px) {
  .iframe-grid { grid-template-columns: 1fr; }
}

.iframe-box {
  background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;
  overflow: hidden; display: flex; flex-direction: column;
}
.iframe-label {
  padding: 12px 16px; background: white; border-bottom: 1px solid #e2e8f0;
  font-size: 14px; font-weight: 600; color: #3b82f6;
  display: flex; align-items: center; gap: 8px;
}
.iframe-label svg { width: 18px; height: 18px; }
.iframe-box iframe {
  width: 100%; height: 400px; border: none;
}
.iframe-error {
  height: 400px; display: flex; align-items: center; justify-content: center;
  color: #94a3b8; font-style: italic; background: #f1f5f9;
}
/* Scoped Modal Styles */
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(15,23,42,0.45); backdrop-filter: blur(8px);
  z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal-card {
  width: 100%; max-width: 960px; background: white; border-radius: 20px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.2); display: flex; flex-direction: column;
  max-height: 90vh; overflow: hidden; animation: scaleIn 0.3s ease-out;
}
@keyframes scaleIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px; border-bottom: 1px solid #e2e8f0; }
.modal-header h4 { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
.btn-close { border: none; background: transparent; color: #94a3b8; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.btn-close svg { width: 22px; height: 22px; }
.btn-close:hover { color: #0f172a; }

.modal-form { padding: 24px; overflow-y: auto; display: flex; flex-direction: column; background: #f1f5f9; text-align: left; }
.form-card { flex-shrink: 0; background: white; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: visible; }
.form-card .card-header { padding: 18px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px; background: #f8fafc; }
.card-icon { width: 36px; height: 36px; border-radius: 10px; background: #eef2ff; color: #6366f1; display: flex; align-items: center; justify-content: center; }
.card-icon svg { width: 20px; height: 20px; }
.form-card .card-header h5 { font-size: 15.5px; font-weight: 800; color: #1e293b; margin: 0; }
.card-body { padding: 24px; display: flex; flex-direction: column; gap: 18px; }

.form-row { display: flex; gap: 16px; }
.flex-1 { flex: 1; } .flex-2 { flex: 2; }
.form-group { display: flex; flex-direction: column; text-align: left; }
.form-group label { font-size: 12.5px; font-weight: 700; color: #475569; margin-bottom: 6px; }
.form-group input[type="text"],
.form-group input[type="url"],
.form-group input[type="date"],
.form-group select,
.form-group textarea {
  height: 46px; padding: 0 14px; background: #f8fafc; border: 1.5px solid #e2e8f0;
  border-radius: 10px; font-family: inherit; font-size: 14px; color: #1e293b; outline: none; transition: all 0.2s;
  box-sizing: border-box;
}
.form-group textarea { height: auto; padding: 12px 14px; resize: vertical; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  border-color: #6366f1; background: white; box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.attendance-block { margin-top: 16px; }
.attendance-block:first-child { margin-top: 0; }
.attendance-block-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.attendance-block-header h6 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0; }
.btn-add-row {
  padding: 6px 14px; border-radius: 8px; border: none;
  background: #eef2ff; color: #4338ca; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all 0.2s;
}
.btn-add-row:hover { background: #e0e7ff; }

/* Kategori */
.categories-checkbox-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 10px; padding: 14px; background: #f8fafc; border-radius: 12px; border: 1.5px solid #e2e8f0;
}
.checkbox-label {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  gap: 10px !important;
  cursor: pointer;
  user-select: none;
  font-size: 13px !important;
  margin-bottom: 0 !important;
  text-align: left !important;
  font-weight: 500 !important;
  color: #475569 !important;
}
.checkbox-label input { display: none; }
.custom-checkbox { width: 20px; height: 20px; border: 1.5px solid #cbd5e1; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: white; transition: all 0.2s; flex-shrink: 0; }
.checkbox-label input:checked + .custom-checkbox { background: #6366f1; border-color: #6366f1; }
.checkbox-label input:checked + .custom-checkbox::after { content: ''; width: 6px; height: 10px; border: solid white; border-width: 0 2.5px 2.5px 0; transform: rotate(45deg) translate(-0.5px, -1px); }

/* Attendee rows */
.attendee-row { display: flex; gap: 10px; align-items: flex-end; margin-bottom: 12px; }
.btn-remove-row {
  width: 38px; height: 38px; flex-shrink: 0; border: none;
  background: #fef2f2; color: #ef4444; border-radius: 10px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: all 0.2s; margin-bottom: 0;
}
.btn-remove-row:hover { background: #fee2e2; }
.btn-remove-row svg { width: 16px; height: 16px; }
.no-attendees { font-size: 13px; color: #94a3b8; font-style: italic; text-align: center; padding: 12px; margin: 0; }

.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 20px 24px; background: white; border-top: 1px solid #e2e8f0; margin: 24px -24px -24px -24px; position: sticky; bottom: -24px; z-index: 10; border-radius: 0 0 20px 20px; }
.btn-cancel { padding: 12px 24px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: white; font-family: inherit; font-size: 14px; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.2s; }
.btn-save { padding: 12px 28px; border-radius: 10px; border: none; background: linear-gradient(135deg, #6366f1, #4338ca); color: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 15px rgba(99,102,241,0.2); transition: all 0.2s; display: flex; align-items: center; justify-content: center; min-width: 120px; }
.btn-save:hover:not(:disabled) { transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }
.loader-sm { width: 20px; height: 20px; border: 2.5px solid #e2e8f0; border-top-color: white; border-radius: 50%; animation: spin 0.8s infinite linear; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .form-row { flex-direction: column; gap: 14px; }
  .modal-card { border-radius: 16px; max-height: 95vh; }
  .modal-form { padding: 16px; }
  .modal-header { padding: 16px; }
  .attendee-row { flex-direction: column; gap: 8px; align-items: stretch; }
  .attendee-row .flex-1,
  .attendee-row .flex-2 { flex: none; }
  .btn-remove-row { align-self: flex-end; }
}

@media (max-width: 640px) {
  .categories-checkbox-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
