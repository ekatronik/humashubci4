<template>
  <div class="prodi-container">
    <!-- Header with Search & Add -->
    <div class="header-actions">
      <div class="filters-bar">
        <!-- Search Input -->
        <div class="search-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="search-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input type="text" v-model="filters.search" placeholder="Cari nama prodi..." class="search-input" @input="loadProdis">
        </div>

        <!-- Faculty Filter -->
        <select v-model="filters.faculty_id" class="filter-select" @change="loadProdis">
          <option value="">Semua Fakultas</option>
          <option v-for="fac in faculties" :key="fac.id" :value="fac.id">{{ fac.singkatan }}</option>
        </select>

        <!-- Jenjang Filter -->
        <select v-model="filters.jenjang" class="filter-select" @change="loadProdis">
          <option value="">Semua Jenjang</option>
          <option value="D4">D4</option>
          <option value="S1">S1</option>
          <option value="S2">S2</option>
          <option value="S3">S3</option>
          <option value="Profesi">Profesi</option>
        </select>

        <!-- Akreditasi Filter -->
        <select v-model="filters.akreditasi" class="filter-select" @change="loadProdis">
          <option value="">Semua Akreditasi</option>
          <option value="Unggul">Unggul</option>
          <option value="Baik Sekali">Baik Sekali</option>
          <option value="Baik">Baik</option>
        </select>
      </div>

      <div class="action-buttons-group">
        <button @click="openImportModal" class="btn-import-csv">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
          </svg>
          <span>Impor CSV</span>
        </button>
        <button @click="openModal(null)" class="btn-add">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <span>Tambah Prodi</span>
        </button>
      </div>
    </div>

    <!-- Alert Notification -->
    <div v-if="alert.show" :class="['alert', alert.type]">
      <span>{{ alert.message }}</span>
      <button type="button" @click="alert.show = false" class="alert-close">&times;</button>
    </div>

    <!-- Data Table -->
    <div class="table-card">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th style="width: 50px;">No</th>
              <th>Program Studi</th>
              <th>Fakultas</th>
              <th>Jenjang</th>
              <th>Akreditasi Saat Ini</th>
              <th>Masa Berlaku / Countdown</th>
              <th>Jalur Masuk</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in paginatedProdis" :key="item.id">
              <td>{{ (currentPage - 1) * pageSize + index + 1 }}</td>
              <td>
                <div class="prodi-title">
                  <strong>{{ item.nama_prodi }}</strong>
                  <a v-if="item.website" :href="item.website" target="_blank" class="web-link" title="Kunjungi website prodi">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                  </a>
                </div>
              </td>
              <td>
                <span class="fac-badge" :title="item.nama_fakultas">{{ item.singkatan_fakultas }}</span>
              </td>
              <td>
                <span class="jenjang-tag" :class="jenjangClass(item.jenjang)">{{ item.jenjang }}</span>
              </td>
              <td>
                <div class="accred-cell">
                  <span class="status-badge" :class="gradeClass(item.akreditasi_sekarang)">
                    {{ item.akreditasi_sekarang }}
                  </span>
                </div>
              </td>
              <td>
                <div class="accred-cell" v-if="item.masa_berlaku">
                  <span class="date-text">{{ formatDateShort(item.masa_berlaku) }}</span>
                  <!-- Countdown with warning highlight -->
                  <span 
                    v-if="getCountdown(item.masa_berlaku)" 
                    :class="['countdown-text', { 'warning': getCountdown(item.masa_berlaku).isWarning, 'critical': getCountdown(item.masa_berlaku).isCritical }]"
                  >
                    {{ getCountdown(item.masa_berlaku).text }}
                  </span>
                </div>
                <span v-else class="text-muted">-</span>
              </td>
              <td>
                <div class="paths-wrapper">
                  <span v-for="path in parseJalur(item.jalur_masuk)" :key="path" class="path-badge" :class="pathClass(path)">
                    {{ path }}
                  </span>
                  <span v-if="parseJalur(item.jalur_masuk).length === 0" class="text-muted text-xs">-</span>
                </div>
              </td>
              <td>
                <div class="action-buttons justify-end">
                  <a v-if="item.sertifikat_berlaku" :href="item.sertifikat_berlaku" target="_blank" class="btn-action view" title="Buka Folder Google Drive (SK &amp; Sertifikat)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m5.579 13.94A9.004 9.004 0 0 1 12 3.019M2.22 14.22h19.56" />
                    </svg>
                  </a>
                  <button @click="openModal(item)" class="btn-action edit" title="Edit Program Studi">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                  </button>
                  <button @click="confirmDelete(item)" class="btn-action delete" title="Hapus Program Studi">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="prodis.length === 0">
              <td colspan="8" class="text-center py-8 text-slate-400">Tidak ada program studi ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <div v-if="prodis.length > 0" class="pagination-container">
        <span class="pagination-info">
          Menampilkan {{ (currentPage - 1) * pageSize + 1 }} - {{ Math.min(currentPage * pageSize, prodis.length) }} dari {{ prodis.length }} prodi
        </span>
        <div class="pagination-buttons">
          <button 
            type="button" 
            class="btn-page" 
            :disabled="currentPage === 1" 
            @click="currentPage = 1"
            title="Halaman Pertama"
          >
            &laquo;
          </button>
          <button 
            type="button" 
            class="btn-page" 
            :disabled="currentPage === 1" 
            @click="currentPage--"
            title="Halaman Sebelumnya"
          >
            &lsaquo;
          </button>
          
          <button 
            v-for="page in totalPages" 
            :key="page" 
            type="button" 
            :class="['btn-page', { 'active': currentPage === page }]"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
          
          <button 
            type="button" 
            class="btn-page" 
            :disabled="currentPage === totalPages" 
            @click="currentPage++"
            title="Halaman Berikutnya"
          >
            &rsaquo;
          </button>
          <button 
            type="button" 
            class="btn-page" 
            :disabled="currentPage === totalPages" 
            @click="currentPage = totalPages"
            title="Halaman Terakhir"
          >
            &raquo;
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Form (Add / Edit) -->
    <div class="modal-overlay" v-if="modal.isOpen" @click.self="closeModal">
      <form @submit.prevent="saveProdi" class="modal-card">
        <div class="modal-header">
          <h3>{{ modal.isEdit ? 'Edit Program Studi' : 'Tambah Program Studi Baru' }}</h3>
          <button @click="closeModal" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
          <!-- General Details Group -->
          <div class="modal-section-grid">
            <div class="form-group">
              <label for="nama_prodi">Nama Program Studi</label>
              <input type="text" id="nama_prodi" v-model="modal.form.nama_prodi" required placeholder="Nama prodi lengkap..." class="form-control">
            </div>

            <div class="form-group">
              <label for="faculty_id">Fakultas</label>
              <select id="faculty_id" v-model="modal.form.faculty_id" required class="form-control">
                <option value="">-- Pilih Fakultas --</option>
                <option v-for="fac in faculties" :key="fac.id" :value="fac.id">{{ fac.nama_fakultas }} ({{ fac.singkatan }})</option>
              </select>
            </div>
          </div>

          <div class="modal-section-grid">
            <div class="form-group">
              <label for="jenjang">Jenjang Studi</label>
              <select id="jenjang" v-model="modal.form.jenjang" required class="form-control">
                <option value="D4">D4</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
                <option value="Profesi">Profesi</option>
              </select>
            </div>

            <div class="form-group">
              <label for="website">Website Resmi Prodi</label>
              <input type="url" id="website" v-model="modal.form.website" placeholder="https://prodi.ar-raniry.ac.id" class="form-control">
            </div>
          </div>

          <!-- Admission Paths selection card -->
          <div class="form-group">
            <label>Jalur Masuk Program Studi</label>
            <div class="paths-checkbox-card">
              <div v-for="path in admissionPathsList" :key="path" class="checkbox-option">
                <label class="checkbox-label">
                  <input type="checkbox" :value="path" v-model="modal.form.jalur_masuk_array">
                  <span>{{ path }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Accreditation Fields directly inside modal -->
          <div class="modal-section-grid">
            <div class="form-group">
              <label for="akreditasi_sekarang">Peringkat Akreditasi</label>
              <select id="akreditasi_sekarang" v-model="modal.form.akreditasi_sekarang" class="form-control">
                <option value="Unggul">Unggul</option>
                <option value="Baik Sekali">Baik Sekali</option>
                <option value="Baik">Baik</option>
              </select>
            </div>

            <div class="form-group">
              <label for="masa_berlaku">Masa Berlaku Akreditasi</label>
              <input type="date" id="masa_berlaku" v-model="modal.form.masa_berlaku" class="form-control">
              <div 
                v-if="getCountdown(modal.form.masa_berlaku)" 
                :class="['modal-countdown', { 'warning': getCountdown(modal.form.masa_berlaku).isWarning, 'critical': getCountdown(modal.form.masa_berlaku).isCritical }]"
              >
                {{ getCountdown(modal.form.masa_berlaku).text }}
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="sertifikat_berlaku">Link Folder Google Drive (SK &amp; Sertifikat)</label>
            <input 
              type="url" 
              id="sertifikat_berlaku"
              v-model="modal.form.sertifikat_berlaku" 
              placeholder="https://drive.google.com/drive/folders/..." 
              class="form-control"
            >
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" @click="closeModal" class="btn-cancel">Batal</button>
          <button type="submit" class="btn-submit" :disabled="modal.submitting">
            {{ modal.submitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </div>

    <!-- CSV Import Modal -->
    <div class="modal-overlay" v-if="importModal.isOpen" @click.self="closeImportModal">
      <div class="modal-card import-card" style="height: auto; max-height: 90vh;">
        <div class="modal-header">
          <h3>Impor Program Studi via CSV</h3>
          <button @click="closeImportModal" class="modal-close">&times;</button>
        </div>
        
        <div class="modal-body" style="max-height: calc(90vh - 150px); overflow-y: auto;">
          <div class="import-instructions">
            <p>Anda dapat mengunggah berkas CSV untuk menambahkan data program studi secara massal.</p>
            
            <div class="instructions-card">
              <h4>Petunjuk Pengisian CSV:</h4>
              <ul>
                <li>Unduh dan gunakan template CSV yang disediakan agar struktur kolom sesuai.</li>
                <li><strong>Nama Program Studi</strong>, <strong>Fakultas (Singkatan)</strong>, dan <strong>Jenjang</strong> wajib diisi.</li>
                <li>Singkatan fakultas harus cocok dengan singkatan fakultas yang terdaftar di sistem (contoh: <code>FTK</code>, <code>FEBI</code>, <code>Pasca</code>).</li>
                <li>Jenjang studi yang didukung: <code>D4</code>, <code>S1</code>, <code>S2</code>, <code>S3</code>, <code>Profesi</code>.</li>
                <li>Peringkat akreditasi yang didukung: <code>Unggul</code>, <code>Baik Sekali</code>, <code>Baik</code>.</li>
                <li>Format tanggal masa berlaku: <code>DD/MM/YYYY</code> (contoh: <code>26/11/2030</code>).</li>
                <li>Jalur masuk dapat diisi beberapa opsi dipisahkan dengan koma (contoh: <code>SNBT, UTBK, SPAN</code>).</li>
              </ul>
            </div>
            
            <div class="template-download-wrapper">
              <button @click="downloadTemplate" class="btn-download-template" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh Template CSV</span>
              </button>
            </div>
          </div>
          
          <hr class="divider">
          
          <!-- Upload area -->
          <div class="form-group">
            <label for="csv_file_input">Pilih File CSV</label>
            <div class="file-upload-area" :class="{ 'has-file': importModal.file }">
              <input type="file" id="csv_file_input" accept=".csv" @change="handleFileChange" class="hidden-file-input">
              <label for="csv_file_input" class="file-upload-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="upload-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                </svg>
                <div v-if="!importModal.file" class="upload-text">
                  <strong>Klik untuk mencari berkas</strong> atau seret ke sini
                  <span class="file-types">Hanya file format .csv</span>
                </div>
                <div v-else class="upload-text">
                  <strong class="text-green-600">Terpilih: {{ importModal.file.name }}</strong>
                  <span class="file-size">{{ (importModal.file.size / 1024).toFixed(2) }} KB</span>
                </div>
              </label>
            </div>
          </div>
          
          <!-- Import Results Stats -->
          <div v-if="importModal.result" class="import-results-card">
            <h4>Hasil Impor CSV:</h4>
            <div class="stats-grid">
              <div class="stat-box success">
                <span class="value">{{ importModal.result.inserted }}</span>
                <span class="label">Berhasil</span>
              </div>
              <div class="stat-box warning">
                <span class="value">{{ importModal.result.skipped }}</span>
                <span class="label">Dilewati (Duplikat)</span>
              </div>
              <div class="stat-box danger">
                <span class="value">{{ importModal.result.errors }}</span>
                <span class="label">Gagal/Error</span>
              </div>
            </div>
            <p class="summary-msg">{{ importModal.result.message }}</p>
            
            <div v-if="importModal.result.details && importModal.result.details.length > 0" class="error-details-list">
              <h5>Detail Logs:</h5>
              <ul>
                <li v-for="(detail, idx) in importModal.result.details" :key="idx" :class="{ 'log-err': detail.includes('Fakultas') || detail.includes('kosong') || detail.includes('kurang'), 'log-dup': detail.includes('dilewati') }">
                  {{ detail }}
                </li>
              </ul>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" @click="closeImportModal" class="btn-cancel" :disabled="importModal.submitting">Batal</button>
          <button type="button" @click="uploadCsv" class="btn-submit" :disabled="!importModal.file || importModal.submitting">
            {{ importModal.submitting ? 'Mengimpor...' : 'Impor Sekarang' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const prodis = ref([])
const faculties = ref([])
const alert = ref({ show: false, type: '', message: '' })

const importModal = ref({
  isOpen: false,
  submitting: false,
  file: null,
  result: null,
})

const admissionPathsList = ['SNBT', 'UTBK', 'SPAN', 'UM-PTKIN', 'Portofolio UTBK', 'Portofolio UM-PTKIN', 'Reguler UINAR']

const filters = ref({
  search: '',
  faculty_id: '',
  jenjang: '',
  akreditasi: ''
})

const modal = ref({
  isOpen: false,
  isEdit: false,
  submitting: false,
  id: null,
  form: {
    nama_prodi: '',
    faculty_id: '',
    jenjang: 'S1',
    website: '',
    jalur_masuk_array: [],
    akreditasi_sekarang: 'Baik',
    masa_berlaku: '',
    sertifikat_berlaku: '',
  }
})

const currentPage = ref(1)
const pageSize = ref(10)

const totalPages = computed(() => {
  return Math.ceil(prodis.value.length / pageSize.value) || 1
})

const paginatedProdis = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return prodis.value.slice(start, end)
})

// Countdown Calculation Helper
const getCountdown = (dateString) => {
  if (!dateString) return null
  const now = new Date()
  now.setHours(0, 0, 0, 0)
  
  const expiry = new Date(dateString)
  expiry.setHours(0, 0, 0, 0)
  
  const diffTime = expiry - now
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays <= 0) {
    return { text: 'Expired / Kadaluarsa', isWarning: true, isCritical: true }
  }
  
  if (diffDays <= 30) {
    return { text: `${diffDays} hari lagi`, isWarning: true, isCritical: true }
  }
  
  const diffMonths = Math.ceil(diffDays / 30)
  const isWarning = diffMonths <= 3
  return { text: `${diffMonths} bulan lagi`, isWarning, isCritical: false }
}

const loadProdis = async () => {
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id
    if (filters.value.jenjang) params.jenjang = filters.value.jenjang
    if (filters.value.akreditasi) params.akreditasi_sekarang = filters.value.akreditasi

    const res = await axios.get('/api/admin/accreditation/study-programs', { params })
    if (res.data.status === 'success') {
      prodis.value = res.data.data
      currentPage.value = 1
    }
  } catch (err) {
    console.error('Gagal mengambil data program studi:', err)
  }
}

const loadFaculties = async () => {
  try {
    const res = await axios.get('/api/admin/accreditation/faculties')
    if (res.data.status === 'success') {
      faculties.value = res.data.data
    }
  } catch (err) {
    console.error(err)
  }
}

const parseJalur = (jsonString) => {
  if (!jsonString) return []
  try {
    const parsed = JSON.parse(jsonString)
    return Array.isArray(parsed) ? parsed : [jsonString]
  } catch (e) {
    return [jsonString]
  }
}

const pathClass = (path) => {
  if (!path) return ''
  const p = path.toUpperCase().trim()
  if (p === 'UTBK' || p === 'SNBT' || p === 'PORTOFOLIO UTBK') {
    return 'path-blue'
  }
  if (p === 'SPAN' || p === 'UM-PTKIN' || p === 'PORTOFOLIO UM-PTKIN') {
    return 'path-green'
  }
  if (p === 'REGULER UINAR') {
    return 'path-yellow'
  }
  return ''
}

const formatDateShort = (dateString) => {
  if (!dateString) return ''
  try {
    return new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
  } catch (e) {
    return dateString
  }
}

const gradeClass = (grade) => {
  if (!grade) return 'grade-none'
  const g = grade.toLowerCase()
  if (g.includes('unggul')) return 'grade-unggul'
  if (g.includes('baik sekali')) return 'grade-baik-sekali'
  if (g.includes('baik')) return 'grade-baik'
  return 'grade-none'
}

const jenjangClass = (jenjang) => {
  if (!jenjang) return 'jenjang-blue'
  const j = jenjang.toUpperCase().trim()
  if (j === 'S2' || j === 'S3') {
    return 'jenjang-red'
  }
  return 'jenjang-blue'
}

const openModal = (item = null) => {
  alert.value.show = false
  if (item) {
    modal.value.isEdit = true
    modal.value.id = item.id
    modal.value.form = {
      nama_prodi: item.nama_prodi,
      faculty_id: item.faculty_id,
      jenjang: item.jenjang,
      website: item.website || '',
      jalur_masuk_array: parseJalur(item.jalur_masuk),
      akreditasi_sekarang: item.akreditasi_sekarang,
      masa_berlaku: item.masa_berlaku || '',
      sertifikat_berlaku: item.sertifikat_berlaku || '',
    }
  } else {
    modal.value.isEdit = false
    modal.value.id = null
    modal.value.form = {
      nama_prodi: '',
      faculty_id: '',
      jenjang: 'S1',
      website: '',
      jalur_masuk_array: [],
      akreditasi_sekarang: 'Baik',
      masa_berlaku: '',
      sertifikat_berlaku: '',
    }
  }
  
  modal.value.isOpen = true
}

const closeModal = () => {
  modal.value.isOpen = false
}

const openImportModal = () => {
  importModal.value = {
    isOpen: true,
    submitting: false,
    file: null,
    result: null,
  }
}

const closeImportModal = () => {
  importModal.value.isOpen = false
  importModal.value.file = null
  importModal.value.result = null
}

const downloadTemplate = async () => {
  try {
    const res = await axios.get('/api/admin/accreditation/study-programs/template', {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'template_prodi_akreditasi.csv')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (err) {
    console.error('Gagal mengunduh template CSV:', err)
    alert.value = {
      show: true,
      type: 'alert-error',
      message: 'Gagal mengunduh template CSV.'
    }
  }
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    importModal.value.file = file
    importModal.value.result = null
  }
}

const uploadCsv = async () => {
  if (!importModal.value.file) return
  
  importModal.value.submitting = true
  importModal.value.result = null
  
  const formData = new FormData()
  formData.append('csv_file', importModal.value.file)
  
  try {
    const res = await axios.post('/api/admin/accreditation/study-programs/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    
    if (res.data.status === 'success') {
      importModal.value.result = res.data.data
      alert.value = {
        show: true,
        type: 'alert-success',
        message: 'Impor data program studi via CSV berhasil diselesaikan.'
      }
      await loadProdis()
    }
  } catch (err) {
    console.error(err)
    alert.value = {
      show: true,
      type: 'alert-error',
      message: err.response?.data?.message || 'Gagal mengimpor data CSV.'
    }
  } finally {
    importModal.value.submitting = false
  }
}

const saveProdi = async () => {
  modal.value.submitting = true
  alert.value.show = false

  try {
    const params = new URLSearchParams()
    params.append('nama_prodi', modal.value.form.nama_prodi)
    params.append('faculty_id', modal.value.form.faculty_id)
    params.append('jenjang', modal.value.form.jenjang)
    params.append('website', modal.value.form.website)
    params.append('jalur_masuk', JSON.stringify(modal.value.form.jalur_masuk_array))

    params.append('akreditasi_sekarang', modal.value.form.akreditasi_sekarang)
    if (modal.value.form.masa_berlaku) params.append('masa_berlaku', modal.value.form.masa_berlaku)
    if (modal.value.form.sertifikat_berlaku) params.append('sertifikat_berlaku', modal.value.form.sertifikat_berlaku)

    let res
    if (modal.value.isEdit) {
      res = await axios.post(`/api/admin/accreditation/study-programs/${modal.value.id}`, params)
    } else {
      res = await axios.post('/api/admin/accreditation/study-programs', params)
    }

    if (res.data.status === 'success') {
      alert.value = {
        show: true,
        type: 'alert-success',
        message: modal.value.isEdit ? 'Program studi berhasil diperbarui.' : 'Program studi baru berhasil ditambahkan.'
      }
      closeModal()
      await loadProdis()
    }
  } catch (err) {
    console.error(err)
    alert.value = {
      show: true,
      type: 'alert-error',
      message: err.response?.data?.message || 'Gagal menyimpan program studi.'
    }
  } finally {
    modal.value.submitting = false
  }
}

const confirmDelete = async (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus program studi "${item.nama_prodi}" secara permanen?`)) {
    alert.value.show = false
    try {
      const res = await axios.delete(`/api/admin/accreditation/study-programs/${item.id}`)
      if (res.data.status === 'success') {
        alert.value = {
          show: true,
          type: 'alert-success',
          message: `Program studi ${item.nama_prodi} berhasil dihapus.`
        }
        await loadProdis()
      }
    } catch (err) {
      console.error(err)
      alert.value = {
        show: true,
        type: 'alert-error',
        message: 'Gagal menghapus program studi.'
      }
    }
  }
}

onMounted(() => {
  loadProdis()
  loadFaculties()
})
</script>

<style scoped>
.prodi-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Header bar with filters */
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.filters-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  flex: 1;
}
.search-wrapper {
  position: relative;
  min-width: 240px;
  flex: 1;
  max-width: 320px;
}
.search-icon {
  width: 16px;
  height: 16px;
  color: #94a3b8;
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
}
.search-input {
  width: 100%;
  padding: 8px 12px 8px 36px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  font-size: 13.5px;
  outline: none;
  background: white;
  box-sizing: border-box;
}
.search-input:focus {
  border-color: #10b981;
}
.filter-select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  font-size: 13.5px;
  background: white;
  color: #475569;
  outline: none;
  cursor: pointer;
}
.btn-add {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 10px;
  border: none;
  background: #10b981;
  color: white;
  font-weight: 700;
  font-size: 13.5px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.18);
  transition: all 0.2s;
  white-space: nowrap;
}
.btn-add svg {
  width: 16px;
  height: 16px;
}
.btn-add:hover {
  background: #059669;
  transform: translateY(-1px);
}

/* Table */
.table-card {
  background: white;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
  overflow: hidden;
}
.table-wrapper {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th {
  background: #f8fafc;
  padding: 14px 20px;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
}
td {
  padding: 16px 20px;
  font-size: 13.5px;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
  text-align: left;
}
tr:last-child td {
  border-bottom: none;
}
.prodi-title {
  display: flex;
  align-items: center;
  gap: 8px;
}
.web-link {
  color: #94a3b8;
  display: inline-flex;
  transition: color 0.15s;
}
.web-link svg {
  width: 14px;
  height: 14px;
}
.web-link:hover {
  color: #3b82f6;
}
.fac-badge {
  background: #f1f5f9;
  color: #475569;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}
.jenjang-tag {
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 11.5px;
  font-weight: 700;
}
.jenjang-tag.jenjang-blue {
  background: #eff6ff;
  color: #1d4ed8;
}
.jenjang-tag.jenjang-red {
  background: #fef2f2;
  color: #dc2626;
}
.accred-cell {
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.status-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
  text-align: center;
  width: max-content;
}
.grade-unggul { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.grade-baik-sekali { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.grade-baik { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
.grade-none { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }

.date-text {
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
}
.countdown-text {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  background: #eff6ff;
  color: #1d4ed8;
  display: inline-block;
  width: max-content;
}
.countdown-text.warning {
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fde68a;
}
.countdown-text.critical {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fca5a5;
}

/* Pathways */
.paths-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  max-width: 250px;
}
.path-badge {
  background: #f8fafc;
  color: #64748b;
  border: 1px solid #e2e8f0;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 10.5px;
  font-weight: 600;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}
.justify-end {
  justify-content: flex-end;
}
.text-right {
  text-align: right;
}
.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-action svg {
  width: 16px;
  height: 16px;
}
.btn-action.view {
  background: #f0fdf4;
  color: #16a34a;
}
.btn-action.view:hover {
  background: #dcfce7;
  color: #15803d;
}
.btn-action.edit {
  background: #f1f5f9;
  color: #475569;
}
.btn-action.edit:hover {
  background: #e0f2fe;
  color: #0369a1;
}
.btn-action.delete {
  background: #fef2f2;
  color: #ef4444;
}
.btn-action.delete:hover {
  background: #fee2e2;
  color: #b91c1c;
}

/* Modal Form Custom Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(4px);
  z-index: 1001;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  overflow-y: auto;
}
.modal-card {
  width: 100%;
  max-width: 780px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  height: min(680px, 90vh);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}
.modal-header h3 {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
}
.modal-close {
  background: transparent;
  border: none;
  font-size: 24px;
  color: #64748b;
  cursor: pointer;
}
.modal-body {
  padding: 24px;
  padding-bottom: 32px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  flex: 1;
  min-height: 0;
  overflow-y: auto;
}
.modal-body::-webkit-scrollbar {
  width: 6px;
}
.modal-body::-webkit-scrollbar-track {
  background: #f1f5f9;
}
.modal-body::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}
.modal-body::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
.modal-section-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
@media (max-width: 640px) {
  .modal-section-grid { grid-template-columns: 1fr; }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-group label {
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
}
.form-control {
  width: 100%;
  padding: 9px 12px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  font-size: 13.5px;
  color: #0f172a;
  background: #f8fafc;
  outline: none;
  box-sizing: border-box;
}
.form-control:focus {
  border-color: #10b981;
  background: white;
}

/* Pathways checkboxes UI */
.paths-checkbox-card {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  gap: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
}
.checkbox-option {
  display: flex;
  align-items: center;
}
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: #475569;
  cursor: pointer;
}

.divider {
  border: none;
  border-top: 1px solid #e2e8f0;
  margin: 8px 0;
}
.section-subtitle {
  font-size: 13px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Single Period Layout */
.single-period-card {
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  border-color: rgba(16, 185, 129, 0.3);
  background: rgba(16, 185, 129, 0.005);
}
.col-head {
  padding: 10px 14px;
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  background: #f1f5f9;
  border-bottom: 1px solid #e2e8f0;
}
.single-period-card .period-header {
  padding: 16px 20px;
  background: linear-gradient(135deg, #047857, #10b981);
  color: white;
}
.period-tag {
  font-size: 12.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.period-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
}

.modal-countdown {
  font-size: 11px;
  font-weight: 700;
  color: #1d4ed8;
  padding: 4px 8px;
  background: #eff6ff;
  border-radius: 6px;
  margin-top: 4px;
  width: max-content;
}
.modal-countdown.warning {
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fde68a;
}
.modal-countdown.critical {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fca5a5;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  flex-shrink: 0;
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
  background: white;
}
.btn-cancel {
  padding: 10px 20px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
}
.btn-submit {
  padding: 10px 24px;
  border-radius: 8px;
  border: none;
  background: #10b981;
  color: white;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
}
.btn-submit:hover:not(:disabled) {
  background: #059669;
}
.btn-submit:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

/* Alert */
.alert {
  padding: 12px 18px;
  border-radius: 10px;
  font-size: 13px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.alert-success {
  background: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
}
.alert-error {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}
.alert-close {
  background: transparent;
  border: none;
  font-size: 18px;
  color: inherit;
  cursor: pointer;
}
.text-muted { color: #94a3b8; }
.py-8 { padding-top: 32px; padding-bottom: 32px; }
.text-center { text-align: center; }

/* CSV Import Styles */
.action-buttons-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-import-csv {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: white;
  color: #475569;
  font-weight: 700;
  font-size: 13.5px;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.btn-import-csv svg {
  width: 16px;
  height: 16px;
}
.btn-import-csv:hover {
  background: #f8fafc;
  border-color: #94a3b8;
  transform: translateY(-1px);
}
.import-instructions p {
  font-size: 13px;
  color: #475569;
  margin-bottom: 12px;
  line-height: 1.5;
}
.instructions-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 12px 16px;
  margin-bottom: 16px;
}
.instructions-card h4 {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.instructions-card ul {
  padding-left: 20px;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.instructions-card li {
  font-size: 11.5px;
  color: #475569;
  line-height: 1.4;
}
.instructions-card code {
  background: #e2e8f0;
  color: #0f172a;
  padding: 1px 4px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 11px;
}
.template-download-wrapper {
  margin-bottom: 16px;
}
.btn-download-template {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-download-template svg {
  width: 14px;
  height: 14px;
}
.btn-download-template:hover {
  background: #dbeafe;
}
.file-upload-area {
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  background: #f8fafc;
  cursor: pointer;
  transition: all 0.2s;
}
.file-upload-area:hover {
  border-color: #10b981;
  background: #f0fdf4;
}
.file-upload-area.has-file {
  border-color: #10b981;
  border-style: solid;
  background: #f0fdf4;
}
.hidden-file-input {
  display: none;
}
.file-upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  width: 100%;
}
.upload-icon {
  width: 32px;
  height: 32px;
  color: #64748b;
}
.file-upload-area.has-file .upload-icon {
  color: #10b981;
}
.upload-text {
  font-size: 12.5px;
  color: #475569;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.file-types {
  font-size: 10.5px;
  color: #94a3b8;
}
.file-size {
  font-size: 10.5px;
  color: #64748b;
  font-weight: 600;
}
.text-green-600 {
  color: #16a34a;
}
.import-results-card {
  margin-top: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
}
.import-results-card h4 {
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 12px;
}
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
  margin-bottom: 12px;
}
.stat-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10px;
  border-radius: 8px;
  text-align: center;
}
.stat-box.success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; }
.stat-box.warning { background: #fffbeb; border: 1px solid #fde68a; color: #d97706; }
.stat-box.danger { background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; }
.stat-box .value { font-size: 18px; font-weight: 800; }
.stat-box .label { font-size: 10.5px; font-weight: 700; margin-top: 2px; }
.summary-msg {
  font-size: 12px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 12px;
}
.error-details-list h5 {
  font-size: 11.5px;
  font-weight: 700;
  color: #475569;
  margin-bottom: 6px;
}
.error-details-list ul {
  padding-left: 16px;
  margin: 0;
  max-height: 120px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.error-details-list li {
  font-size: 11px;
  line-height: 1.3;
}
.log-err { color: #dc2626; }
.log-dup { color: #d97706; }

/* Custom Badge Colors for Admission Paths */
.path-badge.path-blue {
  background: #eff6ff;
  color: #1d4ed8;
  border-color: #bfdbfe;
}
.path-badge.path-green {
  background: #ecfdf5;
  color: #047857;
  border-color: #a7f3d0;
}
.path-badge.path-yellow {
  background: #fffbeb;
  color: #b45309;
  border-color: #fde68a;
}

/* Pagination Styling */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  flex-wrap: wrap;
  gap: 12px;
}
.pagination-info {
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
}
.pagination-buttons {
  display: flex;
  gap: 6px;
  align-items: center;
}
.btn-page {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
  padding: 0 6px;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  background: white;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-page:hover:not(:disabled) {
  border-color: #10b981;
  color: #10b981;
  background: #f0fdf4;
}
.btn-page.active {
  background: #10b981;
  color: white;
  border-color: #10b981;
  box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
}
.btn-page:disabled {
  background: #f1f5f9;
  color: #cbd5e1;
  border-color: #e2e8f0;
  cursor: not-allowed;
}
</style>
