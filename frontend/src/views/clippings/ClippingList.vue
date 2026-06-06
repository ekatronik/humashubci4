<template>
  <div class="clipping-container">
    <!-- Header Page Actions -->
    <div class="page-action-header">
      <div class="header-text">
        <h3>Kliping Surat Kabar</h3>
        <p>Manajemen arsip kliping media cetak UIN Ar-Raniry</p>
      </div>
      <button @click="openCreateModal" class="btn-create">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Kliping</span>
      </button>
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

          <!-- Media Cetak -->
          <div class="filter-group">
            <label class="filter-label">Media Cetak</label>
            <select v-model="filterMedia" @change="fetchClippings" class="filter-select">
              <option value="">Semua Media</option>
              <option v-for="m in mediaCetakList" :key="m.id" :value="m.id">{{ m.media_name }}</option>
            </select>
          </div>

          <!-- Kategori -->
          <div class="filter-group">
            <label class="filter-label">Kategori</label>
            <select v-model="filterCategory" @change="fetchClippings" class="filter-select">
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
                <svg class="search-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                </svg>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari judul..."
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
        <span>Mengambil data kliping...</span>
      </div>

      <div v-else-if="items.length === 0" class="table-empty">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
        </svg>
        <p>Tidak ditemukan data kliping yang sesuai.</p>
      </div>

      <div v-else class="table-wrapper">
        <table class="datatable">
          <thead>
            <tr>
              <th class="col-no-header">NO</th>
              <th>Informasi Kliping</th>
              <th>Media Cetak</th>
              <th>Kategori</th>
              <th class="actions-header">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="item.id">
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
                    {{ formatDate(item.clipping_date) }}
                  </span>
                </div>
                <p v-if="item.summary" class="summary-snippet">{{ truncate(item.summary, 60) }}</p>
              </td>
              <td><span class="media-tag">{{ item.media_name || 'Tanpa Media' }}</span></td>
              <td class="col-categories">
                <div class="categories-list">
                  <span v-for="cat in item.categories" :key="cat.id" class="category-pill">{{ cat.name }}</span>
                  <span v-if="item.categories.length === 0" class="no-category">Tanpa Kategori</span>
                </div>
              </td>
              <td class="col-actions">
                <router-link :to="`/dashboard/clippings/${item.id}`" class="btn-action btn-view" title="Lihat Detail &amp; PDF">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </router-link>
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
            Menampilkan seluruh {{ totalItems }} data kliping.
          </span>
          <span v-else>
            Menampilkan {{ (currentPage - 1) * (parseInt(limitSize) || 10) + 1 }} - {{ Math.min(currentPage * (parseInt(limitSize) || 10), totalItems) }} dari {{ totalItems }} data kliping.
          </span>
        </div>
      </div>
    </div>

    <!-- Form Modal (Create / Edit) -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-header">
            <h4>{{ isEditMode ? 'Edit Kliping' : 'Tambah Kliping Baru' }}</h4>
            <button @click="closeModal" class="btn-close">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveClipping" class="modal-form">
            <!-- Judul -->
            <div class="form-group">
              <label>Judul Kliping *</label>
              <input v-model="form.title" type="text" placeholder="Masukkan judul berita kliping..." required />
            </div>

            <!-- Tanggal & Media -->
            <div class="form-row">
              <div class="form-group flex-1">
                <label>Tanggal Kliping *</label>
                <input v-model="form.clipping_date" type="date" required />
              </div>
              <div class="form-group flex-1">
                <label>Media / Surat Kabar</label>
                <select v-model="form.media_id">
                  <option :value="null">Pilih Media Cetak</option>
                  <option v-for="m in mediaCetakList" :key="m.id" :value="m.id">{{ m.media_name }}</option>
                </select>
              </div>
            </div>

            <!-- Ringkasan -->
            <div class="form-group">
              <label>Ringkasan / Abstrak</label>
              <textarea v-model="form.summary" rows="3" placeholder="Ringkasan isi berita (opsional)..."></textarea>
            </div>

            <!-- Lokasi Penyimpanan -->
            <div class="section-label">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
              </svg>
              <span>Lokasi Penyimpanan Fisik</span>
            </div>
            <div class="form-row">
              <div class="form-group flex-1">
                <label>Gedung</label>
                <input v-model="form.storage_building" type="text" placeholder="Nama gedung..." />
              </div>
              <div class="form-group flex-1">
                <label>Ruangan</label>
                <input v-model="form.storage_room" type="text" placeholder="Nama ruangan..." />
              </div>
              <div class="form-group flex-1">
                <label>Rak</label>
                <input v-model="form.storage_rack" type="text" placeholder="Nomor rak..." />
              </div>
              <div class="form-group flex-1">
                <label>Folder / Map</label>
                <input v-model="form.storage_folder" type="text" placeholder="Nomor folder..." />
              </div>
            </div>

            <!-- Kategori -->
            <div class="form-group">
              <label>Kategori (Bisa Pilih Lebih dari Satu)</label>
              <div class="categories-checkbox-grid">
                <label v-for="cat in categoryList" :key="cat.id" class="checkbox-label">
                  <input type="checkbox" :value="cat.id" v-model="form.category_ids" />
                  <span class="custom-checkbox"></span>
                  <span class="checkbox-text">{{ cat.name }}</span>
                </label>
              </div>
            </div>

            <!-- Upload PDF -->
            <div class="form-group">
              <label>Unggah Dokumen PDF (Maks. 10MB)</label>
              <div class="file-upload-zone" :class="{ 'has-file': selectedFile || form.file_path }">
                <input type="file" ref="fileInput" @change="handleFileChange" accept="application/pdf" class="hidden-file-input" />
                <div class="upload-content" @click="triggerFileInput">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="upload-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  <div class="upload-text" v-if="!selectedFile && !form.file_path">
                    <strong>Pilih berkas PDF</strong> atau klik untuk menelusuri file
                  </div>
                  <div class="upload-text" v-else-if="selectedFile">
                    <strong>Berkas terpilih:</strong> {{ selectedFile.name }} ({{ formatBytes(selectedFile.size) }})
                  </div>
                  <div class="upload-text" v-else>
                    <strong>Arsip berkas saat ini:</strong> {{ getFileNameOnly(form.file_path) }}
                  </div>
                </div>
                <button type="button" class="btn-clear-file" v-if="selectedFile || form.file_path" @click="clearSelectedFile">
                  Hapus Berkas
                </button>
              </div>
            </div>

            <!-- Status Peminjaman -->
            <div class="form-group">
              <label class="toggle-label">
                <span>Dapat Dipinjam</span>
                <div class="toggle-switch" :class="{ active: form.is_borrowable }" @click="form.is_borrowable = !form.is_borrowable">
                  <div class="toggle-thumb"></div>
                </div>
              </label>
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
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

// State data
const items        = ref([])
const mediaList    = ref([])
const categoryList = ref([])

// Filter media cetak saja untuk form dropdown
const mediaCetakList = computed(() =>
  mediaList.value.filter(m => m.media_type === 'cetak' || !m.media_type || m.media_type === 'print')
)

// State query & pagination
const searchQuery     = ref('')
const filterMedia     = ref('')
const filterCategory  = ref('')
const filterStartDate = ref('')
const filterEndDate   = ref('')
const filterQuarter   = ref('')
const currentPage     = ref(1)
const totalPages      = ref(1)
const totalItems      = ref(0)
const limitSize       = ref(10)

// UI States
const loading    = ref(false)
const saving     = ref(false)
const showModal  = ref(false)
const isEditMode = ref(false)

// Form data
const form = ref({
  id: null,
  title: '',
  clipping_date: '',
  media_id: null,
  summary: '',
  file_path: '',
  storage_building: '',
  storage_room: '',
  storage_rack: '',
  storage_folder: '',
  category_ids: [],
  is_borrowable: true,
})

// File Upload States & Helpers
const selectedFile = ref(null)
const fileInput = ref(null)

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file && file.type === 'application/pdf') {
    selectedFile.value = file
  } else {
    alert('Hanya berkas PDF yang diperbolehkan.')
    if (fileInput.value) fileInput.value.value = ''
  }
}

const triggerFileInput = () => {
  if (fileInput.value) fileInput.value.click()
}

const clearSelectedFile = () => {
  selectedFile.value = null
  form.value.file_path = ''
  if (fileInput.value) fileInput.value.value = ''
}

const formatBytes = (bytes, decimals = 2) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}

const getFileNameOnly = (path) => {
  if (!path) return ''
  return path.substring(path.lastIndexOf('/') + 1)
}

// ── Fetch kliping dari API ──────────────────────────────────
const fetchClippings = async () => {
  loading.value = true
  try {
    const params = {
      search:      searchQuery.value,
      media_id:    filterMedia.value,
      category_id: filterCategory.value,
      start_date:  filterStartDate.value,
      end_date:    filterEndDate.value,
      quarter:     filterQuarter.value,
      page:        currentPage.value,
      limit:       limitSize.value === 'all' ? 1000 : limitSize.value,
    }
    
    const res = await axios.get('/api/admin/clippings', { params })
    if (res.data.status === 'success') {
      const d = res.data.data
      items.value = d.items
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
    console.error(err)
    alert('Gagal mengambil data kliping')
  } finally {
    loading.value = false
  }
}

// ── Fetch metadata dropdown ─────────────────────────────────
const fetchMetadata = async () => {
  try {
    const [mRes, cRes] = await Promise.all([
      axios.get('/api/admin/media'),
      axios.get('/api/admin/categories'),
    ])
    if (mRes.data.status === 'success') mediaList.value    = mRes.data.data
    if (cRes.data.status === 'success') categoryList.value = cRes.data.data
  } catch (err) {
    console.error('Gagal memuat metadata:', err)
  }
}

// ── Debounce ────────────────────────────────────────────────
let debTimer = null
const debounceFetch = () => {
  clearTimeout(debTimer)
  debTimer = setTimeout(() => { currentPage.value = 1; fetchClippings() }, 400)
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
  fetchClippings()
}

const onDateRangeChange = () => {
  filterQuarter.value = '' // Clear quarter selector since user edited manually
  currentPage.value = 1
  fetchClippings()
}

const resetFilters = () => {
  searchQuery.value     = ''
  filterMedia.value     = ''
  filterCategory.value  = ''
  filterStartDate.value = ''
  filterEndDate.value   = ''
  filterQuarter.value   = ''
  currentPage.value     = 1
  fetchClippings()
}

// ── Paginasi ────────────────────────────────────────────────
const changePage = (p) => {
  if (p >= 1 && p <= totalPages.value) { currentPage.value = p; fetchClippings() }
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
  fetchClippings()
}

const truncate = (str, n) => str && str.length > n ? str.substring(0, n) + '...' : str

// ── Modal ───────────────────────────────────────────────────
const resetForm = () => ({
  id: null,
  title: '',
  clipping_date: new Date().toISOString().split('T')[0],
  media_id: null,
  summary: '',
  file_path: '',
  storage_building: '',
  storage_room: '',
  storage_rack: '',
  storage_folder: '',
  category_ids: [],
  is_borrowable: true,
})

const openCreateModal = () => {
  isEditMode.value = false
  form.value = resetForm()
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
  showModal.value = true
}

const openEditModal = (item) => {
  isEditMode.value = true
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
  form.value = {
    id:               item.id,
    title:            item.title,
    clipping_date:    item.clipping_date,
    media_id:         item.media_id,
    summary:          item.summary || '',
    file_path:        item.file_path || '',
    storage_building: item.storage_building || '',
    storage_room:     item.storage_room || '',
    storage_rack:     item.storage_rack || '',
    storage_folder:   item.storage_folder || '',
    category_ids:     item.categories.map(c => c.id),
    is_borrowable:    !!item.is_borrowable,
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

// ── CRUD ────────────────────────────────────────────────────
const saveClipping = async () => {
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('title', form.value.title.trim())
    formData.append('clipping_date', form.value.clipping_date)
    formData.append('media_id', form.value.media_id !== null ? form.value.media_id : '')
    formData.append('summary', form.value.summary || '')
    formData.append('storage_building', form.value.storage_building || '')
    formData.append('storage_room', form.value.storage_room || '')
    formData.append('storage_rack', form.value.storage_rack || '')
    formData.append('storage_folder', form.value.storage_folder || '')
    formData.append('is_borrowable', form.value.is_borrowable ? '1' : '0')
    formData.append('category_ids', JSON.stringify(form.value.category_ids || []))
    formData.append('clear_file', form.value.file_path === '' ? '1' : '0')

    if (selectedFile.value) {
      formData.append('file', selectedFile.value)
    }

    let res
    if (isEditMode.value) {
      // POST ke /api/admin/clippings/:id untuk parsing multipart di PHP
      res = await axios.post(`/api/admin/clippings/${form.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      res = await axios.post('/api/admin/clippings', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    if (res.data.status === 'success') {
      closeModal()
      fetchClippings()
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan kliping.')
  } finally {
    saving.value = false
  }
}

const handleDelete = async (id) => {
  if (!confirm('Yakin ingin menghapus data kliping ini? Tindakan tidak dapat dibatalkan.')) return
  try {
    const res = await axios.delete(`/api/admin/clippings/${id}`)
    if (res.data.status === 'success') fetchClippings()
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus kliping.')
  }
}

onMounted(() => { fetchMetadata(); fetchClippings() })
</script>

<style scoped>
.clipping-container {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Header */
.page-action-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.header-text h3 { font-size: 22px; font-weight: 800; color: #0f172a; }
.header-text p  { font-size: 13.5px; color: #64748b; margin-top: 4px; }

.btn-create {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25);
  transition: all 0.2s;
}
.btn-create svg { width: 18px; height: 18px; }
.btn-create:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35); }

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
    grid-template-columns: 1.8fr 1.1fr 1.1fr 1fr;
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
  border-color: #f59e0b;
  background: white;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
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

.date-input-native:focus {
  box-shadow: none !important;
}

/* Customize date picker icon / native appearance */
.date-input-native::-webkit-calendar-picker-indicator {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: auto;
  height: auto;
  color: transparent;
  background: transparent;
  cursor: pointer;
}

.date-range-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #cbd5e1;
  flex-shrink: 0;
}

.date-range-arrow svg {
  width: 14px;
  height: 14px;
}

/* Dropdown select options */
.filter-select {
  height: 48px;
  padding: 0 36px 0 16px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-family: inherit;
  font-size: 13.5px;
  font-weight: 600;
  color: #475569;
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
  border-color: #f59e0b;
  background-color: white;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
}

/* Keyword Search */
.search-actions-row {
  display: flex;
  gap: 12px;
}

.search-input-container {
  position: relative;
  flex: 1;
  min-width: 0;
}

.search-input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #94a3b8;
}

.search-input-field {
  width: 100%;
  height: 48px;
  padding: 0 16px 0 38px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-family: inherit;
  font-size: 13.5px;
  font-weight: 600;
  color: #1e293b;
  outline: none;
  transition: all 0.2s;
}

.search-input-field:focus {
  border-color: #f59e0b;
  background: white;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
}

.btn-filter-reset {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 0 20px;
  height: 48px;
  border-radius: 12px;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  border: 1.5px solid #e2e8f0;
  font-family: inherit;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-filter-reset:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.btn-filter-reset svg {
  width: 16px;
  height: 16px;
}

/* Table Card */
.table-card {
  background: white; border-radius: 18px; border: 1px solid #e2e8f0;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02); overflow: hidden;
}

.table-loading, .table-empty {
  padding: 80px 40px; display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 16px; color: #64748b;
}
.table-empty svg { width: 48px; height: 48px; color: #cbd5e1; }

.loader {
  width: 32px; height: 32px; border: 3.5px solid #e2e8f0;
  border-top-color: #f59e0b; border-radius: 50%; animation: spin 0.8s infinite linear;
}
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
.datatable tr:hover td { background: #fffbeb; }

.col-title { max-width: 260px; }
.title-text { font-weight: 700; color: #0f172a; line-height: 1.4; margin-bottom: 4px; }
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
.pointer-edit {
  cursor: pointer;
  transition: all 0.2s ease;
}
.pointer-edit:hover {
  color: #d97706;
  text-decoration: underline;
}
.summary-snippet { font-size: 12px; color: #64748b; margin: 0; }

.col-date { font-family: monospace; font-weight: 600; color: #64748b; font-size: 13px; white-space: nowrap; }
.media-tag { font-weight: 600; color: #475569; font-size: 13.5px; }

.scale-badge {
  padding: 3px 10px; background: #fef3c7; color: #b45309;
  border-radius: 8px; font-size: 12px; font-weight: 600; white-space: nowrap;
}
.no-data { color: #cbd5e1; font-size: 13px; }

.col-storage { font-size: 12.5px; color: #64748b; }
.storage-info { line-height: 1.6; }

.col-categories { max-width: 200px; }
.categories-list { display: flex; flex-wrap: wrap; gap: 5px; }
.category-pill {
  padding: 3px 8px; background: #f1f5f9; border: 1px solid #cbd5e1;
  color: #334155; border-radius: 6px; font-size: 11.5px; font-weight: 600;
}
.no-category { font-size: 12px; color: #94a3b8; font-style: italic; }

.borrow-badge { padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.borrow-yes { background: #f0fdf4; color: #16a34a; }
.borrow-no  { background: #fef2f2; color: #ef4444; }

/* Actions */
.col-actions { display: flex; gap: 8px; justify-content: flex-end; }
.btn-action { width: 36px; height: 36px; border-radius: 10px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.btn-action svg { width: 18px; height: 18px; }
.btn-view { background: #faf5ff; color: #8b5cf6; border: 1px solid #e9d5ff; }
.btn-view:hover { background: #f3e8ff; color: #7c3aed; }
.btn-edit { background: #f1f5f9; color: #475569; }
.btn-edit:hover { background: #e2e8f0; color: #0f172a; }
.btn-delete { background: #fef2f2; color: #ef4444; }
.btn-delete:hover { background: #fee2e2; color: #b91c1c; }

/* File Uploader Zone */
.file-upload-zone {
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  background: #f8fafc;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: all 0.20s;
  position: relative;
}
.file-upload-zone:hover {
  border-color: #f59e0b;
  background: #fffbeb;
}
.file-upload-zone.has-file {
  border-style: solid;
  border-color: #f59e0b;
  background: #fffbeb;
}
.hidden-file-input {
  display: none;
}
.upload-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  width: 100%;
}
.upload-icon {
  width: 32px;
  height: 32px;
  color: #94a3b8;
  transition: color 0.20s;
}
.file-upload-zone:hover .upload-icon,
.file-upload-zone.has-file .upload-icon {
  color: #d97706;
}
.upload-text {
  font-size: 13px;
  color: #64748b;
  text-align: center;
  line-height: 1.5;
}
.upload-text strong {
  color: #334155;
}
.btn-clear-file {
  padding: 6px 14px;
  background: #fee2e2;
  color: #ef4444;
  border: none;
  border-radius: 8px;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s;
  z-index: 10;
}
.btn-clear-file:hover {
  background: #fecaca;
  color: #dc2626;
}

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
  background: #f59e0b;
  color: white;
  border-color: #f59e0b;
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

/* Modal */
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(15,23,42,0.4); backdrop-filter: blur(8px);
  z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal-card {
  width: 100%; max-width: 780px; background: white; border-radius: 20px;
  border: 1.5px solid rgba(255,255,255,0.08); box-shadow: 0 25px 50px rgba(0,0,0,0.2);
  display: flex; flex-direction: column; max-height: 90vh; overflow: hidden;
  animation: scaleIn 0.3s ease-out;
}
@keyframes scaleIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }

.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px; border-bottom: 1px solid #e2e8f0; }
.modal-header h4 { font-size: 18px; font-weight: 800; color: #0f172a; }
.btn-close { border: none; background: transparent; color: #94a3b8; cursor: pointer; transition: all 0.2s; }
.btn-close svg { width: 22px; height: 22px; }
.btn-close:hover { color: #0f172a; }

.modal-form { padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 18px; }
.form-row { display: flex; gap: 14px; }
.flex-1 { flex: 1; }

.modal-form label { font-size: 12.5px; font-weight: 700; color: #475569; margin-bottom: 6px; display: block; }
.modal-form input[type="text"],
.modal-form input[type="date"],
.modal-form select,
.modal-form textarea {
  width: 100%; padding: 0 16px; height: 48px;
  background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px;
  font-family: inherit; font-size: 14px; color: #1e293b; outline: none; transition: all 0.2s;
}
.modal-form textarea { height: auto; padding: 12px 16px; resize: vertical; }
.modal-form input:focus, .modal-form select:focus, .modal-form textarea:focus {
  border-color: #f59e0b; background: white; box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
}

/* Section Label */
.section-label {
  display: flex; align-items: center; gap: 8px;
  font-size: 12.5px; font-weight: 700; color: #475569;
  padding: 10px 14px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;
}
.section-label svg { width: 16px; height: 16px; color: #f59e0b; }

/* Category Checkbox Grid */
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
  font-weight: 500 !important;
  color: #475569 !important;
  font-size: 13px !important;
  margin-bottom: 0 !important;
  text-align: left !important;
}
.checkbox-label input { display: none; }
.custom-checkbox { width: 20px; height: 20px; border: 1.5px solid #cbd5e1; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: white; transition: all 0.2s; flex-shrink: 0; }
.checkbox-label input:checked + .custom-checkbox { background: #f59e0b; border-color: #f59e0b; }
.checkbox-label input:checked + .custom-checkbox::after { content: ''; width: 6px; height: 10px; border: solid white; border-width: 0 2.5px 2.5px 0; transform: rotate(45deg) translate(-0.5px, -1px); }

/* Toggle Switch */
.toggle-label { display: flex; align-items: center; justify-content: space-between; cursor: pointer; }
.toggle-switch { width: 48px; height: 26px; border-radius: 999px; background: #e2e8f0; position: relative; transition: all 0.25s; cursor: pointer; }
.toggle-switch.active { background: #f59e0b; }
.toggle-thumb { position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: all 0.25s; }
.toggle-switch.active .toggle-thumb { left: 25px; }

/* Modal Footer */
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
.btn-cancel { padding: 12px 24px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: white; font-family: inherit; font-size: 14px; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.2s; }
.btn-cancel:hover { background: #f8fafc; }
.btn-save { padding: 12px 28px; border-radius: 10px; border: none; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 15px rgba(245,158,11,0.2); transition: all 0.2s; display: flex; align-items: center; justify-content: center; min-width: 120px; }
.btn-save:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,158,11,0.3); }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }
.loader-sm { width: 20px; height: 20px; border-width: 2.5px; }

/* Transition */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .filter-card { padding: 16px; }
  .filter-grid-layout { gap: 14px; }
  .filters-row { grid-template-columns: 1fr !important; gap: 14px; }
  .date-inputs-wrapper { flex-direction: row; gap: 8px; }
  .filter-select { width: 100%; }
  .search-actions-row { flex-direction: column; gap: 10px; }
  .btn-filter-reset { width: 100%; justify-content: center; }
  .form-row { flex-direction: column; gap: 14px; }
  .modal-card { border-radius: 16px; }
  .modal-form { padding: 16px; gap: 14px; }
  .modal-header { padding: 16px; }
  .modal-footer { margin-top: 0; }
}

@media (max-width: 640px) {
  .header-text h3 { font-size: 18px; }
  .categories-checkbox-grid { grid-template-columns: repeat(2, 1fr); }
  .pagination-footer { flex-direction: column; gap: 12px; align-items: center; text-align: center; }
  .datatable td, .datatable th { padding: 12px 14px; }
  .col-actions { gap: 6px; }
}

</style>
