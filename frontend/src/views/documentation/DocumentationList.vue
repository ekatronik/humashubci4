<template>
  <div class="doc-container">
    <!-- Header -->
    <div class="page-action-header">
      <div class="header-text">
        <h3>Dokumentasi Kegiatan</h3>
        <p>Arsip foto &amp; video kegiatan resmi humas UIN Ar-Raniry</p>
      </div>
      <button @click="openCreateModal" class="btn-create">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Kegiatan</span>
      </button>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
      <div class="filter-grid-layout">
        <!-- Row 1: Filters -->
        <div class="filters-row">
          <!-- Rentang Waktu Kegiatan -->
          <div class="filter-group date-range-group">
            <label class="filter-label">Rentang Waktu Kegiatan</label>
            <div class="date-inputs-wrapper">
              <div class="date-input-field">
                <span class="date-field-label">Mulai</span>
                <input type="date" v-model="filterStartDate" @change="onDateRangeChange" class="date-input-native" />
              </div>
              <div class="date-range-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
              </div>
              <div class="date-input-field">
                <span class="date-field-label">Selesai</span>
                <input type="date" v-model="filterEndDate" @change="onDateRangeChange" class="date-input-native" />
              </div>
            </div>
          </div>

          <!-- Cakupan -->
          <div class="filter-group">
            <label class="filter-label">Cakupan</label>
            <select v-model="filterLocationType" @change="fetchDocs" class="filter-select">
              <option value="">Semua Cakupan</option>
              <option value="Internal Kampus">Internal Kampus</option>
              <option value="Lokal Daerah">Lokal Daerah</option>
              <option value="Nasional">Nasional</option>
              <option value="Internasional">Internasional</option>
            </select>
          </div>

          <!-- Kategori -->
          <div class="filter-group">
            <label class="filter-label">Kategori</label>
            <select v-model="filterCategory" @change="fetchDocs" class="filter-select">
              <option value="">Semua Kategori</option>
              <option v-for="c in categoryList" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Triwulan -->
          <div class="filter-group">
            <label class="filter-label">Triwulan (TW)</label>
            <select v-model="filterQuarter" @change="onQuarterChange" class="filter-select">
              <option value="">Pilih Triwulan</option>
              <option value="1">Tw 1 (Jan-Mar)</option>
              <option value="2">Tw 2 (Apr-Jun)</option>
              <option value="3">Tw 3 (Jul-Sep)</option>
              <option value="4">Tw 4 (Okt-Des)</option>
            </select>
          </div>
        </div>

        <!-- Row 2: Search & Reset -->
        <div class="search-row">
          <div class="filter-group keyword-search-group">
            <label class="filter-label">Cari Kata Kunci</label>
            <div class="search-actions-row">
              <div class="search-input-container">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                </svg>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari nama kegiatan, lokasi, atau fotografer..."
                  @input="debounceFetch"
                  class="search-input-field"
                />
              </div>
              <button @click="resetFilters" class="btn-filter-reset" title="Reset Filter">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>Reset Filter</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gallery Grid / Table View -->
    <div class="table-card">
      <div v-if="loading" class="table-loading">
        <div class="loader"></div>
        <span>Mengambil data dokumentasi...</span>
      </div>

      <div v-else-if="items.length === 0" class="table-empty">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        <p>Tidak ditemukan data dokumentasi yang sesuai.</p>
      </div>

      <div v-else class="table-wrapper">
        <table class="datatable">
          <thead>
            <tr>
              <th class="col-no-header">NO</th>
              <th>THUMBNAIL</th>
              <th>INFO KEGIATAN</th>
              <th>LOKASI</th>
              <th style="text-align: center;">TAUTAN G-DRIVE</th>
              <th class="actions-header">AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="item.id">
              <td class="col-no">
                {{ String((currentPage - 1) * (parseInt(limitSize) || 0) + index + 1).padStart(2, '0') }}
              </td>
              <td class="col-thumb">
                <div class="thumb-preview-box">
                  <img v-if="item.thumbnail_url" :src="getImageUrl(item.thumbnail_url)" :alt="item.event_name" loading="lazy" referrerpolicy="no-referrer" @error="handleImageError" />
                  <div v-else class="thumb-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                  </div>
                </div>
              </td>
              <td class="col-info-kegiatan">
                <div class="event-title-text pointer-edit" @click="openEditModal(item)" title="Edit data ini">
                  {{ item.event_name }}
                </div>
                <div class="event-meta-info">
                  <div class="categories-list inline-flex">
                    <span v-for="cat in item.categories" :key="cat.id" class="category-pill-green">{{ cat.name }}</span>
                    <span v-if="item.categories.length === 0" class="no-category">Tanpa Kategori</span>
                  </div>
                  <span class="event-date-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="inline-calendar-icon">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    {{ formatDate(item.event_date) }}
                  </span>
                </div>
              </td>
              <td class="col-lokasi">
                <div class="location-bold">{{ item.location_name || '—' }}</div>
                <div class="location-type-sub">{{ item.location_type }}</div>
              </td>
              <td class="col-tautan-gdrive">
                <div class="circular-links-container">
                  <a v-if="item.photo_folder_link" :href="item.photo_folder_link" target="_blank" class="circle-link link-photo" title="Buka Folder Foto (Google Drive)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                  </a>
                  <a v-if="item.video_folder_link" :href="item.video_folder_link" target="_blank" class="circle-link link-video" title="Buka Folder Video (Google Drive)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                  </a>
                  <a v-if="item.news_link" :href="item.news_link" target="_blank" class="circle-link link-news" title="Buka Tautan Berita Terkait">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                  </a>
                </div>
              </td>
              <td class="col-actions">
                <button @click="viewDetail(item.id)" class="btn-action btn-view" title="Lihat Detail">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </button>
                <button @click="handleDelete(item.id)" class="btn-action btn-delete" title="Hapus">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="items.length > 0" class="pagination-footer">
        <div class="page-size-selector">
          <span>Tampilkan:</span>
          <select v-model="limitSize" @change="onLimitChange">
            <option :value="12">12</option>
            <option :value="24">24</option>
            <option :value="48">48</option>
            <option :value="96">96</option>
            <option value="all">Semua</option>
          </select>
        </div>

        <div v-if="limitSize !== 'all' && totalPages > 1" class="pagination-buttons">
          <button @click="changePage(1)" :disabled="currentPage === 1" class="btn-page-arrow" title="Halaman Pertama">«</button>
          <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="btn-page-arrow" title="Sebelumnya">‹</button>
          <button
            v-for="p in visiblePages"
            :key="p"
            @click="changePage(p)"
            :class="['btn-page-num', { active: currentPage === p }]"
          >{{ p }}</button>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="btn-page-arrow" title="Berikutnya">›</button>
          <button @click="changePage(totalPages)" :disabled="currentPage === totalPages" class="btn-page-arrow" title="Halaman Terakhir">»</button>
        </div>

        <div class="pagination-info-text">
          <span v-if="limitSize === 'all'">
            Menampilkan seluruh {{ totalItems }} data kegiatan.
          </span>
          <span v-else>
            Menampilkan {{ (currentPage - 1) * (parseInt(limitSize) || 12) + 1 }} - {{ Math.min(currentPage * (parseInt(limitSize) || 12), totalItems) }} dari {{ totalItems }} data kegiatan.
          </span>
        </div>
      </div>
    </div>

    <!-- Form Modal (Create / Edit) -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-header">
            <h4>{{ isEditMode ? 'Edit Dokumentasi' : 'Tambah Dokumentasi Kegiatan' }}</h4>
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
const items              = ref([])
const categoryList       = ref([])

const searchQuery        = ref('')
const filterLocationType = ref('')
const filterCategory     = ref('')
const filterStartDate    = ref('')
const filterEndDate      = ref('')
const filterQuarter      = ref('')
const currentPage        = ref(1)
const totalPages         = ref(1)
const totalItems         = ref(0)
const limitSize          = ref(12)

const loading    = ref(false)
const saving     = ref(false)
const showModal  = ref(false)
const isEditMode = ref(false)

const getImageUrl = (url) => {
  if (!url) return '';
  // Convert Google Drive view links to direct image links
  const gdriveMatch = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
  if (gdriveMatch && gdriveMatch[1]) {
    // Gunakan lh3.googleusercontent.com yang lebih stabil dan bersifat publik (tanpa login Google)
    return `https://lh3.googleusercontent.com/d/${gdriveMatch[1]}=w800`;
  }
  return url;
}

const handleImageError = (e) => {
  // If the image fails to load, replace it with a fallback transparent pixel or hide it
  e.target.style.display = 'none';
  e.target.insertAdjacentHTML('afterend', `
    <div class="thumb-placeholder error-placeholder" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#fee2e2; color:#ef4444;" title="Gambar gagal dimuat">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>
  `);
}

const resetForm = () => ({
  id: null,
  event_name: '',
  event_date: new Date().toISOString().split('T')[0],
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

const form = ref(resetForm())

const rektoratAttendees = ref([])
const fakultasAttendees = ref([])
const otherAttendees    = ref([])

// ── Fetch ──────────────────────────────────────────────────
const fetchDocs = async () => {
  loading.value = true
  try {
    const params = {
      search:        searchQuery.value,
      location_type: filterLocationType.value,
      category_id:   filterCategory.value,
      start_date:    filterStartDate.value,
      end_date:      filterEndDate.value,
      quarter:       filterQuarter.value,
      page:          currentPage.value,
      limit:         limitSize.value === 'all' ? 1000 : limitSize.value,
    }
    const res = await axios.get('/api/admin/documentation', { params })
    if (res.data.status === 'success') {
      const d = res.data.data
      items.value      = d.items
      totalItems.value = d.total_items
      totalPages.value = d.total_pages

      // Check if we need to auto-open edit modal
      if (route.query.edit) {
        const itemToEdit = items.value.find(i => i.id == route.query.edit)
        if (itemToEdit) {
          openEditModal(itemToEdit)
        }
        // Remove query param without reloading to prevent opening it again on refresh
        router.replace({ query: { ...route.query, edit: undefined } })
      }
    }
  } catch (err) {
    console.error('Gagal memuat dokumentasi:', err)
  } finally {
    loading.value = false
  }
}

const fetchMetadata = async () => {
  try {
    const res = await axios.get('/api/admin/categories')
    if (res.data.status === 'success') categoryList.value = res.data.data
  } catch (err) {
    console.error('Gagal memuat kategori:', err)
  }
}

let debTimer = null
const debounceFetch = () => {
  clearTimeout(debTimer)
  debTimer = setTimeout(() => { currentPage.value = 1; fetchDocs() }, 400)
}

const onQuarterChange = () => {
  if (filterQuarter.value) {
    const year = new Date().getFullYear()
    switch (filterQuarter.value) {
      case '1':
        filterStartDate.value = `${year}-01-01`
        filterEndDate.value   = `${year}-03-31`
        break
      case '2':
        filterStartDate.value = `${year}-04-01`
        filterEndDate.value   = `${year}-06-30`
        break
      case '3':
        filterStartDate.value = `${year}-07-01`
        filterEndDate.value   = `${year}-09-30`
        break
      case '4':
        filterStartDate.value = `${year}-10-01`
        filterEndDate.value   = `${year}-12-31`
        break
    }
  } else {
    filterStartDate.value = ''
    filterEndDate.value   = ''
  }
  currentPage.value = 1
  fetchDocs()
}

const onDateRangeChange = () => {
  filterQuarter.value = '' // Clear quarter selector since user edited manually
  currentPage.value = 1
  fetchDocs()
}

const resetFilters = () => {
  searchQuery.value        = ''
  filterLocationType.value = ''
  filterCategory.value     = ''
  filterStartDate.value    = ''
  filterEndDate.value      = ''
  filterQuarter.value      = ''
  currentPage.value        = 1
  fetchDocs()
}

const changePage = (p) => {
  if (p >= 1 && p <= totalPages.value) { currentPage.value = p; fetchDocs() }
}

const viewDetail = (id) => {
  router.push(`/dashboard/documentation/${id}`)
}

// ── Helpers ─────────────────────────────────────────────────
const formatDate = (d) => {
  if (!d) return '—'
  const dt = new Date(d)
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  
  const dayName = days[dt.getDay()]
  const dateNum = dt.getDate()
  const monthName = months[dt.getMonth()]
  const year = dt.getFullYear()
  
  return `${dayName}, ${dateNum} ${monthName} ${year}`
}

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - 2)
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

const onLimitChange = () => {
  currentPage.value = 1
  fetchDocs()
}

const slugify = (str) => str ? str.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '') : ''

// ── Peserta ──────────────────────────────────────────────────
const addRektoratAttendee    = () => rektoratAttendees.value.push({ level: 'Rektorat', position: '', person_name: '' })
const removeRektoratAttendee = (idx) => rektoratAttendees.value.splice(idx, 1)

const addFakultasAttendee    = () => fakultasAttendees.value.push({ level: 'Fakultas', position: '', person_name: '' })
const removeFakultasAttendee = (idx) => fakultasAttendees.value.splice(idx, 1)

const addOtherAttendee       = () => otherAttendees.value.push({ level: 'Lainnya', position: '', person_name: '' })
const removeOtherAttendee    = (idx) => otherAttendees.value.splice(idx, 1)

// ── Modal ────────────────────────────────────────────────────
const openCreateModal = () => {
  isEditMode.value = false
  form.value = resetForm()
  rektoratAttendees.value = []
  fakultasAttendees.value = []
  otherAttendees.value = []
  showModal.value = true
}

const openEditModal = async (item) => {
  isEditMode.value = true
  try {
    const res = await axios.get(`/api/admin/documentation/${item.id}`)
    if (res.data.status === 'success') {
      const d = res.data.data
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
        category_ids:      d.categories.map(c => c.id),
        attendees:         d.attendees || [],
      }
      rektoratAttendees.value = (d.attendees || []).filter(a => a.level === 'Rektorat')
      fakultasAttendees.value = (d.attendees || []).filter(a => a.level === 'Fakultas')
      otherAttendees.value    = (d.attendees || []).filter(a => a.level === 'Lainnya' || a.level === 'Other')
    }
  } catch (err) {
    alert('Gagal memuat detail dokumentasi.')
    return
  }
  showModal.value = true
}

const closeModal = () => { showModal.value = false }

// ── CRUD ─────────────────────────────────────────────────────
const saveDoc = async () => {
  saving.value = true
  try {
    form.value.attendees = [
      ...rektoratAttendees.value.filter(a => a.person_name.trim() !== '').map(a => ({ level: 'Rektorat', position: a.position, person_name: a.person_name.trim() })),
      ...fakultasAttendees.value.filter(a => a.person_name.trim() !== '').map(a => ({ level: 'Fakultas', position: a.position, person_name: a.person_name.trim() })),
      ...otherAttendees.value.filter(a => a.person_name.trim() !== '').map(a => ({ level: 'Lainnya', position: a.position, person_name: a.person_name.trim() }))
    ]

    let res
    if (isEditMode.value) {
      res = await axios.put(`/api/admin/documentation/${form.value.id}`, form.value)
    } else {
      res = await axios.post('/api/admin/documentation', form.value)
    }
    if (res.data.status === 'success') { closeModal(); fetchDocs() }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan dokumentasi.')
  } finally {
    saving.value = false
  }
}

const handleDelete = async (id) => {
  if (!confirm('Yakin ingin menghapus dokumentasi kegiatan ini?')) return
  try {
    const res = await axios.delete(`/api/admin/documentation/${id}`)
    if (res.data.status === 'success') fetchDocs()
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus dokumentasi.')
  }
}

onMounted(() => { fetchMetadata(); fetchDocs() })
</script>

<style scoped>
.doc-container {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.page-action-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; }
.header-text h3 { font-size: 22px; font-weight: 800; color: #0f172a; }
.header-text p  { font-size: 13.5px; color: #64748b; margin-top: 4px; }

.btn-create {
  display: flex; align-items: center; gap: 8px; padding: 12px 20px;
  border-radius: 12px; border: none;
  background: linear-gradient(135deg, #6366f1, #4338ca);
  color: white; font-size: 14px; font-weight: 700; cursor: pointer;
  box-shadow: 0 4px 15px rgba(99,102,241,0.25); transition: all 0.2s;
}
.btn-create svg { width: 18px; height: 18px; }

.btn-create:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.35); }

/* Filter Card */
.filter-card {
  padding: 24px;
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.filter-grid-layout {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
}

.filters-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  align-items: flex-end;
}

@media (min-width: 1024px) {
  .filters-row {
    grid-template-columns: 1.8fr 1.1fr 1.1fr 1fr 1fr;
  }
}

.search-row {
  border-top: 1px solid #f1f5f9;
  padding-top: 16px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-label {
  font-size: 11px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Date Range Picker Input Container styling matching premium native Litepicker look */
.date-inputs-wrapper {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 4px 12px;
  gap: 8px;
  height: 48px;
  transition: all 0.2s;
}

.date-inputs-wrapper:focus-within {
  border-color: #22c55e;
  background: white;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.date-input-field {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
  position: relative;
}

.date-field-label {
  font-size: 8px;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
  margin-bottom: 1px;
  letter-spacing: 0.5px;
}

.date-input-native {
  border: none !important;
  background: transparent !important;
  padding: 0 !important;
  margin: 0 !important;
  height: 20px !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  color: #0f172a !important;
  outline: none !important;
  cursor: pointer;
  width: 100%;
}

.date-range-arrow {
  display: flex;
  align-items: center;
  color: #cbd5e1;
}

.date-range-arrow svg {
  width: 16px;
  height: 16px;
}

.filter-select {
  height: 48px;
  padding: 0 36px 0 16px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-family: inherit;
  font-size: 13.5px;
  color: #475569;
  font-weight: 600;
  outline: none;
  cursor: pointer;
  transition: all 0.2s;
  appearance: none;
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 14px center;
  background-size: 14px;
}

.filter-select:focus {
  border-color: #22c55e;
  background-color: white;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.keyword-search-group {
  width: 100%;
}

.search-actions-row {
  display: flex;
  gap: 12px;
  align-items: center;
  width: 100%;
}

.search-input-container {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
}

.search-icon {
  position: absolute;
  left: 16px;
  width: 18px;
  height: 18px;
  color: #94a3b8;
}

.search-input-field {
  width: 100%;
  height: 48px;
  padding: 0 16px 0 48px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-family: inherit;
  font-size: 14px;
  color: #1e293b;
  outline: none;
  transition: all 0.2s;
}

.search-input-field:focus {
  border-color: #22c55e;
  background: white;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.btn-filter-reset {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 48px;
  padding: 0 20px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.btn-filter-reset svg {
  width: 16px;
  height: 16px;
}

.btn-filter-reset:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  color: #0f172a;
}

/* Table Card (wraps the grid) */
.table-card {
  box-shadow: 0 4px 15px rgba(0,0,0,0.02); overflow: hidden;
}
.table-loading, .table-empty {
  padding: 80px 40px; display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 16px; color: #64748b;
}
.table-empty svg { width: 48px; height: 48px; color: #cbd5e1; }
.loader { width: 32px; height: 32px; border: 3.5px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s infinite linear; }
@keyframes spin { to { transform: rotate(360deg); } }

.table-wrapper { overflow-x: auto; }

.datatable { width: 100%; border-collapse: collapse; }
.datatable th {
  background: #f8fafc; padding: 16px 20px; text-align: left;
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.5px; color: #475569; border-bottom: 2px solid #e2e8f0;
}
.datatable td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; }
.datatable tr:last-child td { border-bottom: none; }
.datatable tr:hover td { background: #e0e7ff; }

.col-no {
  font-size: 16px;
  font-weight: 800;
  color: #cbd5e1;
  text-align: center;
  width: 60px;
  min-width: 60px;
}
.col-no-header {
  text-align: center !important;
  width: 60px;
}

/* Doc Table Columns */
.col-thumb { width: 140px; }
.thumb-preview-box {
  width: 120px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
}
.thumb-preview-box img { width: 100%; height: 100%; object-fit: cover; }
.thumb-placeholder svg { width: 32px; height: 32px; color: #cbd5e1; }

.col-info-kegiatan { max-width: 300px; }
.event-title-text {
  font-size: 14.5px;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 6px;
  line-height: 1.4;
}
.event-meta-info { display: flex; flex-direction: column; gap: 6px; }
.categories-list { display: flex; flex-wrap: wrap; gap: 4px; }
.category-pill-green {
  padding: 3px 8px;
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
}
.no-category { font-size: 11px; color: #94a3b8; font-style: italic; }
.event-date-info {
  font-size: 12px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 4px;
}

.col-lokasi { width: 180px; }
.location-bold { font-size: 13.5px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
.location-type-sub { font-size: 12px; color: #64748b; }

.col-tautan-gdrive { width: 180px; text-align: center; }
.circular-links-container {
  display: flex;
  gap: 8px;
  justify-content: center;
}
.circle-link {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.link-photo { background: #eff6ff; color: #2563eb; }
.link-photo:hover { background: #dbeafe; }
.link-video { background: #fef2f2; color: #ef4444; }
.link-video:hover { background: #fee2e2; }
.link-news { background: #f0fdfa; color: #0d9488; }
.link-news:hover { background: #ccfbf1; }

.col-actions { width: 100px; text-align: center; }
.actions-header { text-align: center !important; }
.btn-action {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  margin: 0 4px;
}
.btn-view { background: #f1f5f9; color: #475569; }
.btn-view:hover { background: #e2e8f0; color: #0f172a; }
.btn-delete { background: #fef2f2; color: #ef4444; }
.btn-delete:hover { background: #fee2e2; }

.doc-card:hover .doc-thumbnail img { transform: scale(1.04); }
.doc-thumbnail-placeholder {
  width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
}
.doc-thumbnail-placeholder svg { width: 48px; height: 48px; color: #a5b4fc; }

.location-badge {
  position: absolute; top: 12px; left: 12px;
  padding: 4px 10px; border-radius: 8px;
  font-size: 11px; font-weight: 700;
}
.loc-internal-kampus  { background: rgba(99,102,241,0.85); color: white; }
.loc-lokal-daerah     { background: rgba(16,185,129,0.85); color: white; }
.loc-nasional         { background: rgba(245,158,11,0.85); color: white; }
.loc-internasional    { background: rgba(239,68,68,0.85); color: white; }

/* Overlay on hover */
.doc-card-overlay {
  position: absolute; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(15,23,42,0.5); display: flex; align-items: center; justify-content: center;
  gap: 12px; opacity: 0; transition: opacity 0.25s;
}
.doc-card:hover .doc-card-overlay { opacity: 1; }
.overlay-btn {
  width: 42px; height: 42px; border-radius: 12px; border: none;
  background: white; color: #475569; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.2s;
}
.overlay-btn:hover { background: #f1f5f9; }
.overlay-btn-danger { color: #ef4444; }
.overlay-btn-danger:hover { background: #fef2f2; }
.overlay-btn svg { width: 20px; height: 20px; }

/* Card Body */
.doc-card-body { padding: 16px; display: flex; flex-direction: column; gap: 8px; }
.doc-date { font-size: 12px; font-weight: 600; color: #94a3b8; font-family: monospace; }
.doc-title { font-size: 15px; font-weight: 800; color: #0f172a; line-height: 1.4; margin: 0; }
.pointer-edit {
  cursor: pointer;
  transition: all 0.2s ease;
}
.pointer-edit:hover {
  color: #6366f1;
  text-decoration: underline;
}
.doc-location {
  display: flex; align-items: center; gap: 6px;
  font-size: 12.5px; color: #64748b; margin: 0;
}
.doc-location svg { width: 14px; height: 14px; flex-shrink: 0; }

.doc-categories { display: flex; flex-wrap: wrap; gap: 5px; }
.category-pill {
  padding: 3px 8px; background: #eef2ff; border: 1px solid #c7d2fe;
  color: #4338ca; border-radius: 6px; font-size: 11px; font-weight: 600;
}

.doc-links { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 4px; }
.doc-link {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;
  text-decoration: none; transition: all 0.2s;
}
.doc-link svg { width: 14px; height: 14px; }
.doc-link-photo { background: #eff6ff; color: #2563eb; }
.doc-link-photo:hover { background: #dbeafe; }
.doc-link-video { background: #fef2f2; color: #ef4444; }
.doc-link-video:hover { background: #fee2e2; }
.attendees-count {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 12px; color: #64748b; font-weight: 500;
}
.attendees-count svg { width: 14px; height: 14px; }

.circle-link svg { width: 20px; height: 20px; }
.btn-action svg { width: 18px; height: 18px; }

/* Pagination */
.pagination-footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 24px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}
.page-size-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13.5px;
  color: #475569;
}
.page-size-selector select {
  height: 36px;
  padding: 0 32px 0 12px;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-family: inherit;
  font-size: 13.5px;
  color: #1e293b;
  outline: none;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 12px;
}
.pagination-buttons {
  display: flex;
  align-items: center;
  gap: 6px;
}
.btn-page-arrow, .btn-page-num {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #475569;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-page-arrow:hover:not(:disabled), .btn-page-num:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
  color: #0f172a;
}
.btn-page-arrow:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.btn-page-num.active {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}
.pagination-info-text {
  font-size: 13.5px;
  color: #64748b;
  text-align: center;
}
.inline-calendar-icon {
  width: 14px;
  height: 14px;
  color: #94a3b8;
  margin-right: 4px;
  display: inline-block;
  vertical-align: middle;
}
.doc-date {
  font-size: 12px;
  font-weight: 600;
  color: #94a3b8;
  font-family: monospace;
  display: flex;
  align-items: center;
}

/* Modal */
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
.modal-header h4 { font-size: 18px; font-weight: 800; color: #0f172a; }
.btn-close { border: none; background: transparent; color: #94a3b8; cursor: pointer; }
.btn-close svg { width: 22px; height: 22px; }
.btn-close:hover { color: #0f172a; }

.modal-form { padding: 24px; overflow-y: auto; display: flex; flex-direction: column; background: #f1f5f9; }
.form-card { flex-shrink: 0; background: white; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: visible; }
.card-header { padding: 18px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px; background: #f8fafc; }
.card-icon { width: 36px; height: 36px; border-radius: 10px; background: #eef2ff; color: #6366f1; display: flex; align-items: center; justify-content: center; }
.card-icon svg { width: 20px; height: 20px; }
.card-header h5 { font-size: 15.5px; font-weight: 800; color: #1e293b; margin: 0; }
.card-body { padding: 24px; display: flex; flex-direction: column; gap: 18px; }

.form-row { display: flex; gap: 16px; }
.flex-1 { flex: 1; } .flex-2 { flex: 2; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-size: 12.5px; font-weight: 700; color: #475569; margin-bottom: 6px; }
.form-group input[type="text"],
.form-group input[type="url"],
.form-group input[type="date"],
.form-group select,
.form-group textarea {
  height: 46px; padding: 0 14px; background: #f8fafc; border: 1.5px solid #e2e8f0;
  border-radius: 10px; font-family: inherit; font-size: 14px; color: #1e293b; outline: none; transition: all 0.2s;
}
.form-group textarea { height: auto; padding: 12px 14px; resize: vertical; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  border-color: #6366f1; background: white; box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

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
.attendee-row { display: flex; gap: 10px; align-items: flex-end; }
.btn-remove-row {
  width: 38px; height: 38px; flex-shrink: 0; border: none;
  background: #fef2f2; color: #ef4444; border-radius: 10px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: all 0.2s; margin-bottom: 0;
}
.btn-remove-row:hover { background: #fee2e2; }
.btn-remove-row svg { width: 16px; height: 16px; }
.no-attendees { font-size: 13px; color: #94a3b8; font-style: italic; text-align: center; padding: 12px; }

.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 20px 24px; background: white; border-top: 1px solid #e2e8f0; margin: 0 -24px -24px -24px; position: sticky; bottom: -24px; z-index: 10; border-radius: 0 0 20px 20px; }
.btn-cancel { padding: 12px 24px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: white; font-family: inherit; font-size: 14px; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.2s; }
.btn-save { padding: 12px 28px; border-radius: 10px; border: none; background: linear-gradient(135deg, #6366f1, #4338ca); color: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 15px rgba(99,102,241,0.2); transition: all 0.2s; display: flex; align-items: center; justify-content: center; min-width: 120px; }
.btn-save:hover:not(:disabled) { transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }
.loader-sm { width: 20px; height: 20px; border-width: 2.5px; border-top-color: white; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .page-action-header { flex-direction: column; align-items: flex-start; gap: 12px; }
  .btn-create { width: 100%; justify-content: center; }
  .filter-card { flex-direction: column; align-items: stretch; gap: 12px; }
  .filter-controls { flex-direction: column; gap: 10px; }
  .search-box { max-width: none; }
  .filter-group select { width: 100%; }
  .form-row { flex-direction: column; gap: 14px; }
  .doc-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; padding: 16px; }
  .modal-card { border-radius: 16px; }
  .modal-form { padding: 16px; gap: 14px; }
  .modal-header { padding: 16px; }
  .attendee-row { flex-direction: column; gap: 8px; align-items: stretch; }
  .attendee-row .flex-1,
  .attendee-row .flex-2 { flex: none; }
  .btn-remove-row { align-self: flex-end; }
}

@media (max-width: 640px) {
  .header-text h3 { font-size: 18px; }
  .doc-grid { grid-template-columns: 1fr; }
  .doc-thumbnail { height: 150px; }
  .categories-checkbox-grid { grid-template-columns: repeat(2, 1fr); }
  .pagination-footer { flex-direction: column; gap: 12px; align-items: center; text-align: center; }
}

</style>
