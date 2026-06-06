<template>
  <div class="clipping-view-container">
    <!-- Back Header -->
    <div class="view-header">
      <router-link to="/dashboard/clippings" class="btn-back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        <span>Kembali ke Daftar Kliping</span>
      </router-link>
      <div style="flex: 1"></div>
      <button @click="editClipping" class="btn-edit">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
        </svg>
        <span>Edit Kliping</span>
      </button>
    </div>

    <!-- Error/Loading states -->
    <div v-if="loading" class="view-loading-card">
      <div class="loader"></div>
      <span>Memuat berkas kliping...</span>
    </div>

    <div v-else-if="error" class="view-error-card">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
      </svg>
      <p>{{ error }}</p>
      <router-link to="/dashboard/clippings" class="btn-error-back">Kembali</router-link>
    </div>

    <!-- Split Screen Content -->
    <div v-else class="split-layout">
      <!-- SISI KIRI: Metadata Detail -->
      <div class="details-panel">
        <!-- Card 1: Metadata & Title -->
        <div class="metadata-card">
          <h2 class="clipping-title">{{ clipping.title }}</h2>
          
          <div class="meta-row">
            <span class="meta-item">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
              <span>{{ formatDate(clipping.clipping_date) }}</span>
            </span>
            <span class="meta-item" v-if="clipping.media_scale">
              <span class="scale-badge">{{ clipping.media_scale }}</span>
            </span>
          </div>

          <div class="categories-list" v-if="clipping.categories && clipping.categories.length > 0">
            <span v-for="cat in clipping.categories" :key="cat.id" class="category-pill">{{ cat.name }}</span>
          </div>

          <!-- Media Logo Block (Now placed below categories) -->
          <div class="media-logo-block-bottom" v-if="clipping.media_name">
            <div class="media-logo-display-wrapper">
              <img v-if="clipping.media_logo" :src="getMediaLogoUrl(clipping.media_logo)" alt="Logo Media" class="media-logo-display" />
              <div v-else class="media-logo-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                </svg>
              </div>
            </div>
            <span class="media-tag-below">{{ clipping.media_name }}</span>
          </div>
        </div>

        <!-- Card 2: Ringkasan / Abstrak (Now right under title card) -->
        <div class="summary-card">
          <h5>Abstrak / Ringkasan</h5>
          <div class="summary-text" v-if="clipping.summary">
            {{ clipping.summary }}
          </div>
          <div class="summary-empty" v-else>
            Tidak ada ringkasan atau deskripsi abstrak yang diinput untuk kliping ini.
          </div>
        </div>

        <!-- Card 3: Lokasi Penyimpanan Fisik (Compact Style) -->
        <div class="storage-card">
          <div class="storage-compact-list">
            <div class="storage-compact-item" v-if="clipping.storage_building">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
              </svg>
              <span>{{ clipping.storage_building }}</span>
            </div>
            <div class="storage-compact-item" v-if="clipping.storage_room">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
              </svg>
              <span>{{ clipping.storage_room }}</span>
            </div>
            <div class="storage-compact-item" v-if="clipping.storage_rack">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.025 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
              </svg>
              <span>{{ clipping.storage_rack }}</span>
            </div>
            <div class="storage-compact-item" v-if="clipping.storage_folder">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.625-1.812A9.238 9.238 0 0 0 12 11.25c-1.08 0-2.112.186-3.078.525L3 13.5M3 13.5v5.25a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18.75V13.5M3 13.5l1.623-.541A9.227 9.227 0 0 1 7.875 12.75h8.25c.967 0 1.913.149 2.807.443L21 13.5" />
              </svg>
              <span>{{ clipping.storage_folder }}</span>
            </div>
          </div>
          
          <!-- Status Ketersediaan Fisik (inside storage-card at the bottom) -->
          <div class="borrow-status-in-storage">
            <span class="label">Status Ketersediaan Fisik:</span>
            <span class="borrow-badge" :class="clipping.is_borrowable ? 'borrow-yes' : 'borrow-no'">
              {{ clipping.is_borrowable ? 'Dapat Dipinjam' : 'Hanya Baca di Tempat' }}
            </span>
          </div>
        </div>

        <!-- Action Panel -->
        <div class="action-panel">
          <a v-if="clipping.file_path" :href="pdfUrl" download target="_blank" class="btn-download">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <span>Unduh Berkas PDF</span>
          </a>
        </div>
      </div>

      <!-- SISI KANAN: PDF Viewer -->
      <div class="viewer-panel">
        <div v-if="clipping.file_path" class="pdf-wrapper">
          <iframe :src="pdfUrl" class="pdf-iframe" title="PDF Viewer"></iframe>
        </div>
        <div v-else class="no-pdf-placeholder">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
          </svg>
          <h4>Belum ada berkas PDF diunggah</h4>
          <p>Kliping berita ini belum memiliki unggahan dokumen digital PDF pendukung.</p>
        </div>
      </div>
    </div>

    <!-- Form Modal (Edit Only) -->
    <transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-header">
            <h4>Edit Kliping</h4>
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
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const clipping = ref(null)
const loading = ref(true)
const error = ref(null)

const clippingId = computed(() => route.params.id)

const getPdfUrl = (filePath) => {
  if (!filePath) return ''
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
  return `${apiBase}/uploads/clippings/${filePath}`
}

const getMediaLogoUrl = (logoPath) => {
  if (!logoPath) return ''
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
  return `${apiBase}/uploads/media/${logoPath}`
}

const pdfUrl = computed(() => {
  if (!clipping.value || !clipping.value.file_path) return ''
  return getPdfUrl(clipping.value.file_path)
})

const fetchClipping = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await axios.get(`/api/admin/clippings/${clippingId.value}`)
    if (res.data.status === 'success') {
      clipping.value = res.data.data
    } else {
      error.value = 'Gagal mengambil informasi kliping.'
    }
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat memuat data.'
  } finally {
    loading.value = false
  }
}

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

const showModal = ref(false)
const saving = ref(false)

const mediaList = ref([])
const categoryList = ref([])

const mediaCetakList = computed(() =>
  mediaList.value.filter(m => m.media_type === 'cetak' || !m.media_type || m.media_type === 'print')
)

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

const fetchMetadata = async () => {
  try {
    const [mRes, cRes] = await Promise.all([
      axios.get('/api/admin/media'),
      axios.get('/api/admin/categories'),
    ])
    if (mRes.data.status === 'success') mediaList.value = mRes.data.data
    if (cRes.data.status === 'success') categoryList.value = cRes.data.data
  } catch (err) {
    console.error('Gagal memuat metadata:', err)
  }
}

const editClipping = () => {
  if (!clipping.value) return
  const item = clipping.value
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
    category_ids:     item.categories ? item.categories.map(c => c.id) : [],
    is_borrowable:    !!item.is_borrowable,
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

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

    const res = await axios.post(`/api/admin/clippings/${form.value.id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data.status === 'success') {
      closeModal()
      fetchClipping()
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan kliping.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchClipping()
  fetchMetadata()
})
</script>

<style scoped>
.clipping-view-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
  height: calc(100vh - 120px);
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

.view-header {
  display: flex;
  align-items: center;
}

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

.view-loading-card, .view-error-card {
  flex: 1;
  background: white;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  color: #64748b;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}
.view-error-card svg { width: 48px; height: 48px; color: #f87171; }
.btn-error-back {
  padding: 10px 24px;
  background: #f1f5f9;
  border: 1.5px solid #e2e8f0;
  color: #475569;
  font-weight: 600;
  text-decoration: none;
  border-radius: 10px;
  transition: all 0.2s;
}
.btn-error-back:hover { background: #e2e8f0; color: #0f172a; }

.loader {
  width: 32px; height: 32px; border: 3.5px solid #e2e8f0;
  border-top-color: #8b5cf6; border-radius: 50%; animation: spin 0.8s infinite linear;
}
@keyframes spin { to { transform: rotate(360deg); } }

.split-layout {
  display: flex;
  gap: 24px;
  flex: 1;
  min-height: 0; /* Important for inner scroll */
}

/* SISI KIRI: Detail Panel */
.details-panel {
  width: 380px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow-y: auto;
  padding-right: 4px;
}
.details-panel::-webkit-scrollbar { width: 4px; }
.details-panel::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 4px; }

.metadata-card, .storage-card, .summary-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.01);
}

.clipping-title {
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.4;
  margin-bottom: 12px;
}

/* Media Logo Block below categories */
.media-logo-block-bottom {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 8px;
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px dashed #e2e8f0;
}
.media-logo-display-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  background: #fafafa;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  overflow: hidden;
}
.media-logo-display {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.media-logo-placeholder {
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
}
.media-logo-placeholder svg {
  width: 32px;
  height: 32px;
}
.media-tag-below {
  font-size: 11.5px;
  font-weight: 800;
  color: #7c3aed;
  background: #f5f3ff;
  padding: 2px 8px;
  border-radius: 6px;
  text-align: center;
  word-break: break-all;
  max-width: 100px;
}

.meta-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 14px;
}
.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
}
.meta-item svg { width: 16px; height: 16px; color: #94a3b8; }

.scale-badge {
  padding: 3px 8px;
  background: #fef3c7;
  color: #b45309;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
}

.categories-list {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-bottom: 4px;
}
.category-pill {
  padding: 3px 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #475569;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 600;
}

/* Borrow status in storage card styles */
.borrow-status-in-storage {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px dashed #e2e8f0;
}
.borrow-status-in-storage .label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }

.borrow-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  text-align: center;
}
.borrow-yes { background: #f0fdf4; color: #16a34a; }
.borrow-no  { background: #fef2f2; color: #ef4444; }

/* Storage Card Compact List */
.storage-compact-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.storage-compact-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  color: #334155;
  font-weight: 500;
}
.storage-compact-item svg {
  width: 20px;
  height: 20px;
  color: #94a3b8;
  flex-shrink: 0;
}

/* Summary Card */
.summary-card h5 { font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
.summary-text { font-size: 13px; color: #475569; line-height: 1.6; text-align: justify; }
.summary-empty { font-size: 12.5px; color: #94a3b8; font-style: italic; }

/* Action Panel */
.action-panel {
  margin-bottom: 8px;
  display: flex;
  justify-content: flex-start;
}
.btn-download {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 18px;
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border-radius: 10px;
  font-size: 12.5px;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 3px 10px rgba(124, 58, 237, 0.2);
  transition: all 0.2s;
}
.btn-download:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3); }
.btn-download svg { width: 15px; height: 15px; }

/* SISI KANAN: PDF Viewer Panel */
.viewer-panel {
  flex: 1;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.01);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.pdf-wrapper {
  width: 100%;
  height: 100%;
}
.pdf-iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.no-pdf-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 40px;
  height: 100%;
  text-align: center;
  color: #64748b;
}
.no-pdf-placeholder svg { width: 56px; height: 56px; color: #cbd5e1; }
.no-pdf-placeholder h4 { font-size: 16px; font-weight: 700; color: #334155; }
.no-pdf-placeholder p { font-size: 13px; color: #94a3b8; max-width: 320px; }

@media (max-width: 1024px) {
  .clipping-view-container { height: auto; }
  .split-layout { flex-direction: column; }
  .details-panel { width: 100%; overflow-y: visible; }
  .viewer-panel { height: 500px; }
}

@media (max-width: 640px) {
  .viewer-panel { height: 380px; }
  .clipping-title { font-size: 16px; }
  .storage-grid { grid-template-columns: 1fr; gap: 8px; }
  .btn-back span { display: none; }
  .meta-row { flex-direction: column; gap: 6px; }
}

/* Scoped Modal Styles */
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
.modal-header h4 { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
.btn-close { border: none; background: transparent; color: #94a3b8; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.btn-close svg { width: 22px; height: 22px; }
.btn-close:hover { color: #0f172a; }

.modal-form { padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 18px; text-align: left; }
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
  box-sizing: border-box;
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

/* Modal Footer */
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
.btn-cancel { padding: 12px 24px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: white; font-family: inherit; font-size: 14px; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.2s; }
.btn-cancel:hover { background: #f8fafc; }
.btn-save { padding: 12px 28px; border-radius: 10px; border: none; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 15px rgba(245,158,11,0.2); transition: all 0.2s; display: flex; align-items: center; justify-content: center; min-width: 120px; }
.btn-save:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,158,11,0.3); }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }
.loader-sm { width: 20px; height: 20px; border: 2.5px solid #e2e8f0; border-top-color: #f59e0b; border-radius: 50%; animation: spin 0.8s infinite linear; }

/* Transition */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .form-row { flex-direction: column; gap: 14px; }
  .modal-card { border-radius: 16px; max-height: 95vh; }
  .modal-form { padding: 16px; }
  .modal-header { padding: 16px; }
  .modal-footer { margin-top: 0; }
}

@media (max-width: 640px) {
  .categories-checkbox-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
