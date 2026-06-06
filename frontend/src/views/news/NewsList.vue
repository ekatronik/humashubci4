<template>
  <div class="news-container">
    <!-- Header Page Actions -->
    <div class="page-action-header">
      <div class="header-text">
        <h3>Kelola Berita Online</h3>
        <p>Manajemen data rilis humas dan liputan media online UIN</p>
      </div>
      <div class="header-actions">
        <button @click="openImportModal" class="btn-import">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
          </svg>
          <span>Impor CSV</span>
        </button>
        <button @click="openCreateModal" class="btn-create">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <span>Tambah Berita</span>
        </button>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
      <div class="filter-grid-layout">
        <!-- Row 1: Filters -->
        <div class="filters-row">
          <!-- Rentang Waktu Berita -->
          <div class="filter-group date-range-group">
            <label class="filter-label">Rentang Waktu Berita</label>
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

          <!-- Media Portal -->
          <div class="filter-group">
            <label class="filter-label">Media Portal</label>
            <select v-model="filterMedia" @change="fetchNews" class="filter-select">
              <option value="">Semua Media Portal</option>
              <option v-for="m in mediaList" :key="m.id" :value="m.id">{{ m.media_name }}</option>
            </select>
          </div>

          <!-- Kategori -->
          <div class="filter-group">
            <label class="filter-label">Kategori</label>
            <select v-model="filterCategory" @change="fetchNews" class="filter-select">
              <option value="">Semua Kategori</option>
              <option v-for="c in categoryList" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Sumber -->
          <div class="filter-group">
            <label class="filter-label">Sumber</label>
            <select v-model="filterSource" @change="fetchNews" class="filter-select">
              <option value="">Semua Sumber</option>
              <option value="Rilis Humas">Rilis Humas</option>
              <option value="Liputan Wartawan">Liputan Wartawan</option>
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
                <svg class="search-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                </svg>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari judul berita atau tautan..."
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

    <!-- Data Table Card -->
    <div class="table-card">
      <div v-if="loading" class="table-loading">
        <div class="loader"></div>
        <span>Mengambil data berita...</span>
      </div>

      <div v-else-if="newsItems.length === 0" class="table-empty">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008h-.008v-.008Z" />
        </svg>
        <p>Tidak ditemukan data berita online yang sesuai.</p>
      </div>

      <div v-else class="table-wrapper">
        <table class="datatable">
          <thead>
            <tr>
              <th class="col-no-header">NO</th>
              <th>Informasi Berita</th>
              <th>Media</th>
              <th>Sumber</th>
              <th>Kategori</th>
              <th class="actions-header">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in newsItems" :key="item.id">
              <td class="col-no">
                {{ String((currentPage - 1) * (parseInt(limitSize) || 0) + index + 1).padStart(2, '0') }}
              </td>
              <td class="col-title">
                <div class="title-text pointer-edit" @click="openEditModal(item)" title="Edit data ini">
                  {{ item.title }}
                </div>
                <div class="title-sub-info">
                  <span class="date-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="inline-calendar-icon">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    {{ formatDate(item.news_date) }}
                  </span>
                  <a :href="item.news_link" target="_blank" class="link-badge">
                    <span>Lihat Tautan</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                  </a>
                </div>
              </td>
              <td>
                <span class="media-tag">{{ item.media_name || 'Tanpa Media' }}</span>
              </td>
              <td>
                <span class="source-tag" :class="item.source_type === 'Rilis Humas' ? 'source-rilis' : 'source-liputan'">
                  {{ item.source_type }}
                </span>
              </td>
              <td class="col-categories">
                <div class="categories-list">
                  <span v-for="cat in item.categories" :key="cat.id" class="category-pill">
                    {{ cat.name }}
                  </span>
                  <span v-if="item.categories.length === 0" class="no-category">
                    Tanpa Kategori
                  </span>
                </div>
              </td>
              <td class="col-actions">
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

      <!-- Pagination Footer -->
      <div v-if="newsItems.length > 0" class="pagination-footer">
        <div class="page-size-selector">
          <span>Tampilkan:</span>
          <select v-model="limitSize" @change="onLimitChange">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
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
            Menampilkan seluruh {{ totalItems }} data berita.
          </span>
          <span v-else>
            Menampilkan {{ (currentPage - 1) * (parseInt(limitSize) || 10) + 1 }} - {{ Math.min(currentPage * (parseInt(limitSize) || 10), totalItems) }} dari {{ totalItems }} data berita.
          </span>
        </div>
      </div>
    </div>

    <!-- Unified Form Modal (Create / Edit) -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay">
        <div class="modal-card">
          <div class="modal-header">
            <h4>{{ isEditMode ? 'Edit Berita Online' : 'Tambah Berita Online' }}</h4>
            <button @click="closeModal" class="btn-close">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveNews" class="modal-form">
            <div class="form-group">
              <label>Judul Berita *</label>
              <input v-model="form.title" type="text" placeholder="Masukkan judul berita utama..." required />
            </div>

            <div class="form-row">
              <div class="form-group flex-1">
                <label>Tanggal Berita *</label>
                <input v-model="form.news_date" type="date" required />
              </div>

              <div class="form-group flex-1">
                <label>Media Sumber *</label>
                <select v-model="form.media_id" required>
                  <option :value="null" disabled>Pilih Media Portal</option>
                  <option v-for="m in mediaList" :key="m.id" :value="m.id">
                    {{ m.media_name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group flex-1">
                <label>Tipe Sumber *</label>
                <select v-model="form.source_type" required>
                  <option value="Rilis Humas">Rilis Humas</option>
                  <option value="Liputan Wartawan">Liputan Wartawan</option>
                </select>
              </div>

              <div class="form-group flex-2">
                <label>Tautan / Link Berita *</label>
                <input v-model="form.news_link" type="url" placeholder="https://..." required />
              </div>
            </div>

            <!-- Multi Select Categories Checkbox Grid -->
            <div class="form-group">
              <label>Kategori Berita (Bisa Pilih Lebih dari Satu)</label>
              <div class="categories-checkbox-grid">
                <label v-for="cat in categoryList" :key="cat.id" class="checkbox-label">
                  <input 
                    type="checkbox" 
                    :value="cat.id" 
                    v-model="form.category_ids"
                  />
                  <span class="custom-checkbox"></span>
                  <span class="checkbox-text">{{ cat.name }}</span>
                </label>
              </div>
            </div>

            <!-- Preview Badges -->
            <div class="form-group">
              <label class="preview-lbl">Preview Badge Kategori Terpilih:</label>
              <div class="preview-badges-wrapper">
                <span v-for="id in form.category_ids" :key="id" class="preview-badge">
                  {{ getCategoryNameById(id) }}
                </span>
                <span v-if="form.category_ids.length === 0" class="no-preview">
                  Belum memilih kategori
                </span>
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

    <!-- CSV Import Modal -->
    <transition name="fade">
      <div v-if="showImportModal" class="modal-overlay" @click.self="closeImportModal">
        <div class="modal-card import-modal-card">
          <div class="modal-header">
            <div class="import-modal-title">
              <div class="import-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
              </div>
              <div>
                <h4>Impor Data Berita via CSV</h4>
                <p class="import-subtitle">Unggah file .csv untuk menambah data secara massal</p>
              </div>
            </div>
            <button @click="closeImportModal" class="btn-close">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="import-body">
            <!-- Format Guide -->
            <div class="format-guide">
              <div class="format-guide-header">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <span>Format Kolom CSV yang Dibutuhkan</span>
              </div>
              <div class="format-cols">
                <div class="format-col-item" v-for="col in csvColumns" :key="col.name">
                  <code>{{ col.name }}</code>
                  <span>{{ col.desc }}</span>
                </div>
              </div>
              <a href="#" @click.prevent="downloadTemplate" class="template-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Template CSV
              </a>
            </div>

            <!-- Drop Zone -->
            <div 
              class="drop-zone"
              :class="{ 'drag-over': isDragging, 'has-file': csvFile !== null }"
              @dragenter.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @dragover.prevent
              @drop.prevent="onFileDrop"
              @click="triggerFileInput"
            >
              <input 
                ref="fileInputRef" 
                type="file" 
                accept=".csv" 
                class="hidden-input"
                @change="onFileSelected"
              />

              <div v-if="!csvFile" class="drop-zone-prompt">
                <div class="drop-zone-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                </div>
                <p class="drop-title">Seret & Lepas file CSV di sini</p>
                <p class="drop-sub">atau <strong>klik untuk memilih file</strong> dari komputer</p>
                <span class="drop-hint">Hanya file .csv, maksimal 5MB</span>
              </div>

              <div v-else class="drop-zone-file">
                <div class="file-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                </div>
                <div class="file-info">
                  <p class="file-name">{{ csvFile.name }}</p>
                  <p class="file-size">{{ formatFileSize(csvFile.size) }}</p>
                </div>
                <button type="button" @click.stop="clearCsvFile" class="btn-remove-file" title="Ganti file">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Progress Bar (saat mengimpor) -->
            <div v-if="importing" class="import-progress">
              <div class="progress-bar-track">
                <div class="progress-bar-fill" :style="{ width: importProgress + '%' }"></div>
              </div>
              <p class="progress-text">Sedang memproses data... harap tunggu.</p>
            </div>

            <!-- Import Result (setelah selesai) -->
            <div v-if="importResult" class="import-result">
              <div class="result-grid">
                <div class="result-item result-success">
                  <span class="result-number">{{ importResult.inserted }}</span>
                  <span class="result-label">Berhasil Diimpor</span>
                </div>
                <div class="result-item result-skip">
                  <span class="result-number">{{ importResult.skipped }}</span>
                  <span class="result-label">Dilewati (Duplikat)</span>
                </div>
                <div class="result-item result-error">
                  <span class="result-number">{{ importResult.errors }}</span>
                  <span class="result-label">Baris Error</span>
                </div>
              </div>
              <p class="result-message">{{ importResult.message }}</p>
            </div>
          </div>

          <div class="import-footer">
            <button type="button" @click="closeImportModal" class="btn-cancel">Tutup</button>
            <button 
              type="button" 
              @click="submitImport" 
              class="btn-import-submit"
              :disabled="!csvFile || importing"
            >
              <div v-if="importing" class="loader loader-sm"></div>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
              </svg>
              <span>{{ importing ? 'Mengimpor...' : 'Mulai Import' }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// CSV Column definitions untuk format guide
const csvColumns = [
  { name: 'title',       desc: 'Judul berita (wajib)' },
  { name: 'news_date',   desc: 'Format YYYY-MM-DD (wajib)' },
  { name: 'news_link',   desc: 'URL berita lengkap (wajib)' },
  { name: 'media_name',  desc: 'Nama media portal (wajib)' },
  { name: 'source_type', desc: 'Rilis Humas / Liputan Wartawan' },
  { name: 'categories',  desc: 'Nama kategori, pisah dengan \';\'' },
]

// State Data lists
const newsItems       = ref([])
const mediaList       = ref([])
const categoryList    = ref([])

// State query parameters & pagination
const searchQuery     = ref('')
const filterMedia     = ref('')
const filterCategory  = ref('')
const filterStartDate = ref('')
const filterEndDate   = ref('')
const filterQuarter   = ref('')
const filterSource    = ref('')
const currentPage     = ref(1)
const totalPages      = ref(1)
const totalItems      = ref(0)
const limitSize       = ref(10)

// UI States
const loading    = ref(false)
const saving     = ref(false)
const showModal  = ref(false)
const isEditMode = ref(false)

// CSV Import States
const showImportModal = ref(false)
const csvFile         = ref(null)
const isDragging      = ref(false)
const importing       = ref(false)
const importProgress  = ref(0)
const importResult    = ref(null)
const fileInputRef    = ref(null)

// Unified Form Data
const form = ref({
  id: null,
  title: '',
  news_date: '',
  media_id: null,
  source_type: 'Rilis Humas',
  news_link: '',
  category_ids: []
})

// Fetch berita dari API backend CI4
const fetchNews = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/admin/news', {
      params: {
        search: searchQuery.value,
        media_id: filterMedia.value,
        category_id: filterCategory.value,
        start_date: filterStartDate.value,
        end_date: filterEndDate.value,
        quarter: filterQuarter.value,
        source_type: filterSource.value,
        page: currentPage.value,
        limit: limitSize.value
      }
    })
    
    if (response.data.status === 'success') {
      const pData = response.data.data
      newsItems.value  = pData.items
      totalItems.value = pData.total_items
      totalPages.value = pData.total_pages
    }
  } catch (err) {
    console.error('Gagal memuat berita online:', err)
  } finally {
    loading.value = false
  }
}

// Fetch metadata list untuk dropdown
const fetchMetadata = async () => {
  try {
    const mediaRes = await axios.get('/api/admin/media?type=online')
    if (mediaRes.data.status === 'success') {
      mediaList.value = mediaRes.data.data
    }

    const catRes = await axios.get('/api/admin/categories')
    if (catRes.data.status === 'success') {
      categoryList.value = catRes.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil metadata filter:', err)
  }
}

// Debounce input pencarian
let debounceTimeout = null
const debounceFetch = () => {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchNews()
  }, 400)
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
  fetchNews()
}

const onDateRangeChange = () => {
  filterQuarter.value = '' // Clear quarter selector since user edited manually
  currentPage.value = 1
  fetchNews()
}

const resetFilters = () => {
  searchQuery.value     = ''
  filterMedia.value     = ''
  filterCategory.value  = ''
  filterStartDate.value = ''
  filterEndDate.value   = ''
  filterQuarter.value   = ''
  filterSource.value    = ''
  currentPage.value     = 1
  fetchNews()
}

// Paginasi
const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchNews()
  }
}

// Format Tanggal DD-MM-YYYY
const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  const dt = new Date(dateStr)
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
  fetchNews()
}

// Ambil Nama Kategori berdasarkan ID (untuk preview badge)
const getCategoryNameById = (id) => {
  const cat = categoryList.value.find(c => c.id === id)
  return cat ? cat.name : ''
}

// Penanganan Modal
const openCreateModal = () => {
  isEditMode.value = false
  form.value = {
    id: null,
    title: '',
    news_date: new Date().toISOString().split('T')[0], // Hari ini
    media_id: mediaList.value.length > 0 ? mediaList.value[0].id : null,
    source_type: 'Rilis Humas',
    news_link: '',
    category_ids: []
  }
  showModal.value = true
}

const openEditModal = (item) => {
  isEditMode.value = true
  form.value = {
    id: item.id,
    title: item.title,
    news_date: item.news_date,
    media_id: item.media_id,
    source_type: item.source_type,
    news_link: item.news_link,
    category_ids: item.categories.map(c => c.id)
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// Simpan data (Tambah / Edit) ke backend CI4
const saveNews = async () => {
  saving.value = true
  try {
    let response;
    if (isEditMode.value) {
      response = await axios.put(`/api/admin/news/${form.value.id}`, form.value)
    } else {
      response = await axios.post('/api/admin/news', form.value)
    }

    if (response.data.status === 'success') {
      closeModal()
      fetchNews()
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan data berita.')
  } finally {
    saving.value = false
  }
}

// Hapus Berita
const handleDelete = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data berita online ini? Tindakan ini tidak dapat dibatalkan.')) {
    try {
      const response = await axios.delete(`/api/admin/news/${id}`)
      if (response.data.status === 'success') {
        fetchNews()
      }
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menghapus data berita.')
    }
  }
}

// CSV Import Logic
const openImportModal = () => {
  showImportModal.value = true
  csvFile.value         = null
  importResult.value    = null
  importProgress.value  = 0
}

const closeImportModal = () => {
  if (importing.value) return // blokir tutup saat proses berjalan
  showImportModal.value = false
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const onFileSelected = (event) => {
  const file = event.target.files?.[0]
  if (file && file.name.endsWith('.csv')) {
    csvFile.value      = file
    importResult.value = null
  } else if (file) {
    alert('Hanya file berformat .csv yang diperbolehkan.')
    event.target.value = ''
  }
}

const onFileDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files?.[0]
  if (file && file.name.endsWith('.csv')) {
    csvFile.value      = file
    importResult.value = null
  } else if (file) {
    alert('Hanya file berformat .csv yang diperbolehkan.')
  }
}

const clearCsvFile = () => {
  csvFile.value      = null
  importResult.value = null
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}

const downloadTemplate = () => {
  const cols = 'title,news_date,news_link,media_name,source_type,categories'
  const sample = 'Contoh Judul Berita UIN,2024-01-15,https://example.com/berita,Serambi Indonesia,Rilis Humas,Akademik;Penelitian'
  const csvContent = `${cols}\n${sample}`
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url  = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href     = url
  link.download = 'template_impor_berita.csv'
  link.click()
  URL.revokeObjectURL(url)
}

const submitImport = async () => {
  if (!csvFile.value) return

  importing.value      = true
  importProgress.value = 10
  importResult.value   = null

  // Simulasi progress awal sambil menunggu server
  const progressInterval = setInterval(() => {
    if (importProgress.value < 80) importProgress.value += 10
  }, 300)

  try {
    const formData = new FormData()
    formData.append('csv_file', csvFile.value)

    const response = await axios.post('/api/admin/news/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    importProgress.value = 100
    if (response.data.status === 'success') {
      importResult.value = response.data.data // { inserted, skipped, errors, message }
      fetchNews() // refresh tabel
    } else {
      alert(response.data.message || 'Gagal mengimpor CSV.')
    }
  } catch (err) {
    importProgress.value = 0
    alert(err.response?.data?.message || 'Terjadi kesalahan saat mengimpor file CSV.')
  } finally {
    clearInterval(progressInterval)
    importing.value = false
  }
}

onMounted(() => {
  fetchMetadata()
  fetchNews()
})
</script>

<style scoped>
.news-container {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Page Header */
.page-action-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.header-text h3 {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
}

.header-text p {
  font-size: 13.5px;
  color: #64748b;
  margin-top: 4px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-import {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #475569;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-import svg {
  width: 18px;
  height: 18px;
}

.btn-import:hover {
  border-color: #22c55e;
  color: #16a34a;
  background: #f0fdf4;
}

.btn-create {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(34, 197, 94, 0.25);
  transition: all 0.2s;
}

.btn-create svg {
  width: 18px;
  height: 18px;
}

.btn-create:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.35);
}

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

.search-input-icon {
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
  border-color: #cbd5e1;
  background: #f8fafc;
  color: #334155;
}

/* Datatable Card */
.table-card {
  background: white;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
  overflow: hidden;
}

.table-loading, .table-empty {
  padding: 80px 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  color: #64748b;
}

.table-empty svg {
  width: 48px;
  height: 48px;
  color: #cbd5e1;
}

.loader {
  width: 32px;
  height: 32px;
  border: 3.5px solid #e2e8f0;
  border-top-color: #22c55e;
  border-radius: 50%;
  animation: spin 0.8s infinite linear;
}

@keyframes spin { to { transform: rotate(360deg); } }

.table-wrapper {
  overflow-x: auto;
}

.datatable {
  width: 100%;
  border-collapse: collapse;
}

.datatable th {
  background: #f8fafc;
  padding: 16px 20px;
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #475569;
  border-bottom: 2px solid #e2e8f0;
}

.datatable td {
  padding: 20px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  vertical-align: middle;
}

.datatable tr:last-child td {
  border-bottom: none;
}

.datatable tr:hover td {
  background: #f8fafc;
}

/* Column styles */
.col-title {
  max-width: 300px;
}
.col-no {
  font-size: 18px;
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
.title-text {
  font-weight: 700;
  color: #0f172a;
  line-height: 1.4;
  margin-bottom: 6px;
}
.pointer-edit {
  cursor: pointer;
  transition: all 0.2s ease;
}
.pointer-edit:hover {
  color: #22c55e;
  text-decoration: underline;
}

.link-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: #eff6ff;
  color: #2563eb;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}

.link-badge svg {
  width: 12px;
  height: 12px;
}

.link-badge:hover {
  background: #dbeafe;
}

.col-date {
  font-family: monospace;
  font-weight: 600;
  color: #64748b;
  font-size: 13px;
}

.media-tag {
  font-weight: 600;
  color: #475569;
  font-size: 13.5px;
}

.source-tag {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
}

.source-rilis {
  background: #f0fdf4;
  color: #16a34a;
}

.source-liputan {
  background: #fffbeb;
  color: #d97706;
}

.col-categories {
  max-width: 220px;
}

.categories-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.category-pill {
  padding: 3px 8px;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  color: #334155;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 600;
}

.no-category {
  font-size: 12px;
  color: #94a3b8;
  font-style: italic;
}

/* Row actions */
.col-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn-action {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action svg {
  width: 18px;
  height: 18px;
}

.btn-edit {
  background: #f1f5f9;
  color: #475569;
}

.btn-edit:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.btn-delete {
  background: #fef2f2;
  color: #ef4444;
}

.btn-delete:hover {
  background: #fee2e2;
  color: #b91c1c;
}

/* Pagination Footer */
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
  background: #22c55e;
  color: white;
  border-color: #22c55e;
}
.pagination-info-text {
  font-size: 13.5px;
  color: #64748b;
  text-align: center;
}
.title-sub-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 4px;
  margin-bottom: 6px;
}
.date-info {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
}
.inline-calendar-icon {
  width: 14px;
  height: 14px;
  color: #94a3b8;
}

/* Premium Modal Layout */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.4); /* Slate 900 with alpha */
  backdrop-filter: blur(8px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-card {
  width: 100%;
  max-width: 680px;
  background: white;
  border-radius: 20px;
  border: 1.5px solid rgba(255,255,255,0.08);
  box-shadow: 0 25px 50px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow: hidden;
  animation: scaleIn 0.3s ease-out;
}

@keyframes scaleIn {
  from { transform: scale(0.95); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h4 {
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
}

.btn-close {
  border: none;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-close svg {
  width: 22px;
  height: 22px;
}

.btn-close:hover {
  color: #0f172a;
}

.modal-form {
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: flex;
  gap: 16px;
}

.flex-1 { flex: 1; }
.flex-2 { flex: 2; }

.modal-form label {
  font-size: 12.5px;
  font-weight: 700;
  color: #475569;
  margin-bottom: 6px;
  display: block;
}

.modal-form input[type="text"],
.modal-form input[type="url"],
.modal-form input[type="date"],
.modal-form select {
  width: 100%;
  height: 48px;
  padding: 0 16px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-family: inherit;
  font-size: 14px;
  color: #1e293b;
  outline: none;
  transition: all 0.2s;
}

.modal-form input:focus,
.modal-form select:focus {
  border-color: #22c55e;
  background: white;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

/* Category Checkbox Grid */
.categories-checkbox-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
}

.checkbox-label {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  gap: 10px !important;
  cursor: pointer;
  user-select: none;
  font-weight: 500 !important;
  color: #475569 !important;
  font-size: 13px !important;
  margin-bottom: 0 !important;
  text-align: left !important;
}

.checkbox-label input {
  display: none;
}

.custom-checkbox {
  width: 20px;
  height: 20px;
  border: 1.5px solid #cbd5e1;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  transition: all 0.2s;
  flex-shrink: 0;
}

.checkbox-label input:checked + .custom-checkbox {
  background: #22c55e;
  border-color: #22c55e;
}

.checkbox-label input:checked + .custom-checkbox::after {
  content: '';
  width: 6px;
  height: 10px;
  border: solid white;
  border-width: 0 2.5px 2.5px 0;
  transform: rotate(45deg) translate(-0.5px, -1px);
}

/* Preview badges area */
.preview-lbl {
  color: #64748b !important;
  font-size: 12px !important;
  font-weight: 600 !important;
}

.preview-badges-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.preview-badge {
  padding: 4px 10px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #16a34a;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
}

.no-preview {
  font-size: 12px;
  color: #94a3b8;
  font-style: italic;
}

/* Modal Footer buttons */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}

.btn-cancel {
  padding: 12px 24px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: white;
  font-family: inherit;
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f8fafc;
  color: #0f172a;
}

.btn-save {
  padding: 12px 28px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.3);
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loader-sm {
  width: 20px;
  height: 20px;
  border-width: 2.5px;
}

/* Transition utility */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* ===== CSV Import Modal Styles ===== */
.import-modal-card {
  max-width: 700px;
}

.import-modal-title {
  display: flex;
  align-items: center;
  gap: 14px;
}

.import-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border: 1px solid #bbf7d0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #16a34a;
  flex-shrink: 0;
}

.import-icon-wrap svg { width: 22px; height: 22px; }

.import-modal-title h4 {
  font-size: 17px;
  font-weight: 800;
  color: #0f172a;
}

.import-subtitle {
  font-size: 13px;
  color: #64748b;
  margin-top: 2px;
}

.import-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  overflow-y: auto;
  max-height: 65vh;
}

/* Format Guide */
.format-guide {
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 18px;
}

.format-guide-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 700;
  color: #475569;
  margin-bottom: 14px;
}

.format-guide-header svg { width: 16px; height: 16px; }

.format-cols {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
  gap: 10px;
  margin-bottom: 16px;
}

.format-col-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 10px 12px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
}

.format-col-item code {
  font-size: 12px;
  font-weight: 700;
  color: #7c3aed;
  font-family: 'Courier New', monospace;
}

.format-col-item span {
  font-size: 11.5px;
  color: #64748b;
}

.template-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
  color: #2563eb;
  text-decoration: none;
  transition: color 0.2s;
}

.template-link svg { width: 14px; height: 14px; }
.template-link:hover { color: #1d4ed8; }

/* Drop Zone */
.drop-zone {
  border: 2px dashed #cbd5e1;
  border-radius: 16px;
  padding: 40px 24px;
  text-align: center;
  cursor: pointer;
  transition: all 0.25s;
  background: #fafafa;
  position: relative;
}

.drop-zone:hover {
  border-color: #22c55e;
  background: #f0fdf4;
}

.drop-zone.drag-over {
  border-color: #22c55e;
  background: #dcfce7;
  transform: scale(1.01);
  box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
}

.drop-zone.has-file {
  border-style: solid;
  border-color: #22c55e;
  background: #f0fdf4;
  padding: 20px 24px;
}

.hidden-input {
  display: none;
}

.drop-zone-prompt {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.drop-zone-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: white;
  border: 1.5px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  margin-bottom: 4px;
}

.drop-zone-icon svg { width: 28px; height: 28px; }

.drop-title {
  font-size: 15px;
  font-weight: 700;
  color: #334155;
}

.drop-sub {
  font-size: 13px;
  color: #64748b;
}

.drop-hint {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 4px;
}

.drop-zone-file {
  display: flex;
  align-items: center;
  gap: 14px;
}

.file-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.file-icon svg { width: 24px; height: 24px; }

.file-info {
  flex: 1;
  text-align: left;
}

.file-name {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
}

.file-size {
  font-size: 12px;
  color: #64748b;
  margin-top: 3px;
}

.btn-remove-file {
  width: 32px;
  height: 32px;
  border: none;
  background: #fee2e2;
  color: #ef4444;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.btn-remove-file svg { width: 16px; height: 16px; }
.btn-remove-file:hover { background: #fecaca; }

/* Progress Bar */
.import-progress {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.progress-bar-track {
  height: 10px;
  background: #e2e8f0;
  border-radius: 999px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #22c55e, #16a34a);
  border-radius: 999px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 13px;
  color: #64748b;
  text-align: center;
}

/* Import Result */
.import-result {
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
}

.result-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}

.result-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 16px;
  border-radius: 12px;
}

.result-success { background: #f0fdf4; }
.result-skip    { background: #fffbeb; }
.result-error   { background: #fef2f2; }

.result-number {
  font-size: 28px;
  font-weight: 900;
  line-height: 1;
}

.result-success .result-number { color: #16a34a; }
.result-skip    .result-number { color: #d97706; }
.result-error   .result-number { color: #ef4444; }

.result-label {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-align: center;
}

.result-message {
  font-size: 13px;
  color: #475569;
  text-align: center;
  font-weight: 500;
}

/* Import Modal Footer */
.import-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid #e2e8f0;
}

.btn-import-submit {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
  transition: all 0.2s;
}

.btn-import-submit svg { width: 18px; height: 18px; }

.btn-import-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.3);
}

.btn-import-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .page-action-header { flex-direction: column; align-items: flex-start; }
  .header-actions { width: 100%; justify-content: flex-end; }
  .filter-card { padding: 16px; }
  .filter-grid-layout { gap: 14px; }
  .filters-row { grid-template-columns: 1fr !important; gap: 14px; }
  .date-inputs-wrapper { flex-direction: row; gap: 8px; }
  .filter-select { width: 100%; }
  .search-actions-row { flex-direction: column; gap: 10px; }
  .btn-filter-reset { width: 100%; justify-content: center; }
  .form-row { flex-direction: column; gap: 14px; }
  .modal-card { max-width: 100%; border-radius: 16px; }
  .modal-form { padding: 16px; gap: 14px; }
  .modal-header { padding: 16px; }
}

@media (max-width: 640px) {
  .header-text h3 { font-size: 18px; }
  .btn-create, .btn-import { padding: 10px 14px; font-size: 13px; }
  .result-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .result-stat-num { font-size: 24px; }
  .categories-checkbox-grid { grid-template-columns: repeat(2, 1fr); }
  .pagination-footer { flex-direction: column; gap: 12px; align-items: center; text-align: center; }
  .datatable td, .datatable th { padding: 12px 14px; }
}

</style>
