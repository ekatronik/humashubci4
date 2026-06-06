<template>
  <div class="sermon-container">
    <div class="header-actions">
      <div class="search-bar-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="search-icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input type="text" v-model="searchQuery" placeholder="Cari masjid atau khatib..." class="search-input">
      </div>
      <button @click="openModal(null)" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Jadwal Baru</span>
      </button>
    </div>

    <!-- Alert Notification -->
    <div v-if="alert.show" :class="['alert', alert.type]">
      <span>{{ alert.message }}</span>
      <button type="button" @click="alert.show = false" class="alert-close">&times;</button>
    </div>

    <!-- Table Card -->
    <div class="table-card">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th style="width: 60px;">No</th>
              <th>Nama Masjid</th>
              <th>Tanggal</th>
              <th>Nama Khatib</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in paginatedSermons" :key="item.id">
              <td>{{ (currentPage - 1) * pageSize + index + 1 }}</td>
              <td>
                <strong>{{ item.mosque_name }}</strong>
              </td>
              <td>
                <span class="date-badge">{{ formatDate(item.date) }}</span>
              </td>
              <td>
                {{ item.khatib }}
              </td>
              <td>
                <div class="action-buttons justify-end">
                  <button @click="openModal(item)" class="btn-action edit" title="Edit Jadwal">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                  </button>
                  <button @click="confirmDelete(item)" class="btn-action delete" title="Hapus Jadwal">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredSermons.length === 0">
              <td colspan="5" class="text-center py-8 text-slate-400">Tidak ada jadwal khutbah ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="filteredSermons.length > 0" class="pagination-container">
        <span class="pagination-info">
          Menampilkan {{ (currentPage - 1) * pageSize + 1 }} - {{ Math.min(currentPage * pageSize, filteredSermons.length) }} dari {{ filteredSermons.length }} jadwal
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

    <!-- Modal Form (Add / Edit / Bulk) -->
    <div class="modal-overlay" v-if="modal.isOpen" @click.self="closeModal">
      <div class="modal-card" :class="{ 'modal-large': modal.mode === 'bulk' }">
        <div class="modal-header">
          <h3>{{ modal.isEdit ? 'Edit Jadwal Khatib' : 'Tambah Jadwal Mesjid Lain' }}</h3>
          <button @click="closeModal" class="modal-close">&times;</button>
        </div>
        
        <!-- Mode Switcher (only for new entry) -->
        <div class="mode-tabs" v-if="!modal.isEdit">
          <button 
            type="button" 
            class="tab-btn" 
            :class="{ active: modal.mode === 'single' }" 
            @click="setMode('single')"
          >
            Input Tunggal
          </button>
          <button 
            type="button" 
            class="tab-btn" 
            :class="{ active: modal.mode === 'bulk' }" 
            @click="setMode('bulk')"
          >
            Input Masal (Jadwal Beruntun)
          </button>
        </div>

        <form @submit.prevent="saveSermon" class="modal-body">
          
          <!-- Shared Field: Mosque Name -->
          <div class="form-group">
            <label for="mosque_name">Nama Masjid</label>
            <input 
              type="text" 
              id="mosque_name" 
              v-model="modal.form.mosque_name" 
              required 
              placeholder="Contoh: Masjid Raya Baiturrahman" 
              class="form-control"
            >
          </div>

          <!-- SINGLE MODE FIELDS -->
          <template v-if="modal.mode === 'single'">
            <div class="form-group">
              <label for="date">Tanggal (Hari Jumat)</label>
              <input 
                type="date" 
                id="date" 
                v-model="modal.form.date" 
                required 
                @change="validateDateChange"
                class="form-control"
              >
              <span v-if="dateError" class="error-text">
                Tanggal yang dipilih bukan hari Jumat! Silakan pilih hari Jumat.
              </span>
            </div>

            <div class="form-group">
              <label for="khatib">Nama Khatib</label>
              <input 
                type="text" 
                id="khatib" 
                v-model="modal.form.khatib" 
                required 
                placeholder="Masukkan nama khatib" 
                class="form-control"
              >
            </div>
          </template>

          <!-- BULK MODE FIELDS -->
          <template v-else>
            <div class="bulk-setup-row">
              <div class="form-group flex-1">
                <label for="bulk_start_date">Tanggal Jumat Awal</label>
                <input 
                  type="date" 
                  id="bulk_start_date" 
                  v-model="bulkSetup.startDate" 
                  required 
                  @change="handleBulkSetupChange"
                  class="form-control"
                >
                <span v-if="bulkSetup.dateError" class="error-text">
                  Tanggal awal harus hari Jumat!
                </span>
              </div>
              <div class="form-group" style="width: 130px;">
                <label for="bulk_weeks">Jumlah Pekan</label>
                <select 
                  id="bulk_weeks" 
                  v-model.number="bulkSetup.weeks" 
                  @change="handleBulkSetupChange" 
                  class="form-control"
                >
                  <option v-for="w in [2,3,4,5,6,8,10,12]" :key="w" :value="w">{{ w }} Pekan</option>
                </select>
              </div>
            </div>

            <!-- Dynamic generated rows for Khatibs -->
            <div class="bulk-schedules-list" v-if="bulkSetup.startDate && !bulkSetup.dateError">
              <h4 class="section-title">Isi Nama Khatib untuk masing-masing pekan:</h4>
              <div class="bulk-schedule-item" v-for="(sched, index) in bulkSetup.schedules" :key="index">
                <div class="schedule-number-badge">Jumat ke-{{ index + 1 }}</div>
                <div class="schedule-date">{{ formatDate(sched.date) }}</div>
                <div class="schedule-input-wrapper">
                  <input 
                    type="text" 
                    v-model="sched.khatib" 
                    required 
                    :placeholder="`Nama Khatib Jumat ${index + 1}`" 
                    class="form-control"
                  >
                </div>
              </div>
            </div>
          </template>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-cancel">Batal</button>
            <button 
              type="submit" 
              class="btn-submit" 
              :disabled="modal.submitting || dateError || (modal.mode === 'bulk' && (bulkSetup.dateError || !bulkSetup.startDate || bulkSetup.schedules.length === 0))"
            >
              {{ modal.submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const sermons = ref([])
const searchQuery = ref('')
const alert = ref({ show: false, type: '', message: '' })

// Pagination State
const currentPage = ref(1)
const pageSize = ref(10)

const modal = ref({
  isOpen: false,
  isEdit: false,
  submitting: false,
  id: null,
  mode: 'single', // 'single' or 'bulk'
  form: {
    mosque_name: '',
    date: '',
    khatib: ''
  }
})

const dateError = ref(false)

const bulkSetup = ref({
  startDate: '',
  weeks: 4,
  dateError: false,
  schedules: [] // array of { date: 'YYYY-MM-DD', khatib: '' }
})

const validateDateChange = () => {
  if (!modal.value.form.date) {
    dateError.value = false
    return
  }
  const selectedDate = new Date(modal.value.form.date)
  if (selectedDate.getDay() !== 5) {
    dateError.value = true
  } else {
    dateError.value = false
  }
}

const handleBulkSetupChange = () => {
  if (!bulkSetup.value.startDate) {
    bulkSetup.value.dateError = false
    bulkSetup.value.schedules = []
    return
  }
  
  const selectedDate = new Date(bulkSetup.value.startDate)
  if (selectedDate.getDay() !== 5) {
    bulkSetup.value.dateError = true
    bulkSetup.value.schedules = []
    return
  }
  
  bulkSetup.value.dateError = false
  
  // Generate schedules
  const newSchedules = []
  const count = bulkSetup.value.weeks
  for (let i = 0; i < count; i++) {
    const d = new Date(selectedDate)
    d.setDate(selectedDate.getDate() + (i * 7))
    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    
    // Preserve existing khatib if index matches and still valid
    const existingKhatib = bulkSetup.value.schedules[i]?.khatib || ''
    newSchedules.push({
      date: `${yyyy}-${mm}-${dd}`,
      khatib: existingKhatib
    })
  }
  bulkSetup.value.schedules = newSchedules
}

const setMode = (newMode) => {
  if (modal.value.isEdit) return
  modal.value.mode = newMode
  if (newMode === 'bulk') {
    handleBulkSetupChange()
  }
}

// Reset page on search query change
watch(searchQuery, () => {
  currentPage.value = 1
})

const filteredSermons = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  if (!query) return sermons.value
  return sermons.value.filter(item => 
    (item.mosque_name && item.mosque_name.toLowerCase().includes(query)) ||
    (item.khatib && item.khatib.toLowerCase().includes(query))
  )
})

const totalPages = computed(() => {
  return Math.ceil(filteredSermons.value.length / pageSize.value) || 1
})

const paginatedSermons = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredSermons.value.slice(start, end)
})

const loadSermons = async () => {
  try {
    const res = await axios.get('/api/admin/khutbah/sermons', {
      params: { mosque_type: 'other' }
    })
    if (res.data.status === 'success') {
      sermons.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil data jadwal khutbah masjid lain:', err)
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const [year, month, day] = dateStr.split('-')
  return `${day}/${month}/${year}`
}

const openModal = (item = null) => {
  alert.value.show = false
  dateError.value = false
  bulkSetup.value.dateError = false
  bulkSetup.value.startDate = getNextFridayString()
  bulkSetup.value.weeks = 4
  bulkSetup.value.schedules = []
  
  if (item) {
    modal.value.isEdit = true
    modal.value.mode = 'single'
    modal.value.id = item.id
    modal.value.form = {
      mosque_name: item.mosque_name,
      date: item.date,
      khatib: item.khatib
    }
  } else {
    modal.value.isEdit = false
    modal.value.mode = 'single'
    modal.value.id = null
    modal.value.form = {
      mosque_name: '',
      date: getNextFridayString(),
      khatib: ''
    }
    // Pre-populate bulk
    handleBulkSetupChange()
  }
  modal.value.isOpen = true
}

const getNextFridayString = () => {
  const d = new Date()
  const day = d.getDay()
  const diff = (5 - day + 7) % 7
  const nextFriday = new Date(d.setDate(d.getDate() + (diff === 0 ? 7 : diff)))
  const yyyy = nextFriday.getFullYear()
  const mm = String(nextFriday.getMonth() + 1).padStart(2, '0')
  const dd = String(nextFriday.getDate()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}`
}

const closeModal = () => {
  modal.value.isOpen = false
}

const saveSermon = async () => {
  alert.value.show = false
  modal.value.submitting = true

  try {
    if (modal.value.mode === 'single') {
      // Validate date
      const selectedDate = new Date(modal.value.form.date)
      if (selectedDate.getDay() !== 5) {
        dateError.value = true
        modal.value.submitting = false
        return
      }

      const payload = {
        ...modal.value.form,
        mosque_type: 'other'
      }

      let res
      if (modal.value.isEdit) {
        res = await axios.post(`/api/admin/khutbah/sermons/${modal.value.id}`, payload)
      } else {
        res = await axios.post('/api/admin/khutbah/sermons', payload)
      }

      if (res.data.status === 'success') {
        alert.value = {
          show: true,
          type: 'alert-success',
          message: modal.value.isEdit ? 'Jadwal khutbah berhasil diperbarui.' : 'Jadwal khutbah baru berhasil ditambahkan.'
        }
        closeModal()
        await loadSermons()
      }
    } else {
      // Bulk Submit
      if (bulkSetup.value.dateError || !bulkSetup.value.startDate || bulkSetup.value.schedules.length === 0) {
        modal.value.submitting = false
        return
      }

      // Check if all filled
      const unfilled = bulkSetup.value.schedules.some(s => !s.khatib.trim())
      if (unfilled) {
        alert.value = {
          show: true,
          type: 'alert-error',
          message: 'Silakan isi seluruh nama Khatib pada kolom jadwal masal.'
        }
        modal.value.submitting = false
        return
      }

      // Prepare payload
      const payload = new URLSearchParams()
      payload.append('mosque_name', modal.value.form.mosque_name)
      payload.append('schedules', JSON.stringify(bulkSetup.value.schedules))

      const res = await axios.post('/api/admin/khutbah/sermons/bulk', payload)
      if (res.data.status === 'success') {
        alert.value = {
          show: true,
          type: 'alert-success',
          message: res.data.message || 'Jadwal masal berhasil ditambahkan.'
        }
        closeModal()
        await loadSermons()
      }
    }
  } catch (err) {
    console.error(err)
    alert.value = {
      show: true,
      type: 'alert-error',
      message: err.response?.data?.message || 'Gagal menyimpan data jadwal khutbah.'
    }
  } finally {
    modal.value.submitting = false
  }
}

const confirmDelete = async (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus jadwal khutbah di ${item.mosque_name} tanggal ${formatDate(item.date)}?`)) {
    alert.value.show = false
    try {
      const res = await axios.delete(`/api/admin/khutbah/sermons/${item.id}`)
      if (res.data.status === 'success') {
        alert.value = {
          show: true,
          type: 'alert-success',
          message: `Jadwal khutbah berhasil dihapus.`
        }
        await loadSermons()
      }
    } catch (err) {
      console.error(err)
      alert.value = {
        show: true,
        type: 'alert-error',
        message: 'Gagal menghapus jadwal khutbah.'
      }
    }
  }
}

onMounted(() => {
  loadSermons()
})
</script>

<style scoped>
.sermon-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Header actions bar */
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.search-bar-wrapper {
  position: relative;
  flex: 1;
  max-width: 400px;
  min-width: 280px;
}
.search-icon {
  width: 18px;
  height: 18px;
  color: #94a3b8;
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
}
.search-input {
  width: 100%;
  padding: 10px 16px 10px 42px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-size: 14px;
  background: white;
  color: #0f172a;
  outline: none;
  box-sizing: border-box;
  transition: all 0.2s;
}
.search-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
.btn-add {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 10px;
  border: none;
  background: #3b82f6;
  color: white;
  font-weight: 700;
  font-size: 13.5px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.18);
  transition: all 0.2s;
}
.btn-add svg {
  width: 16px;
  height: 16px;
}
.btn-add:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

/* Table Card */
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

.date-badge {
  background: #eff6ff;
  color: #1d4ed8;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
}

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

/* Pagination */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  flex-wrap: wrap;
  gap: 12px;
}
.pagination-info {
  font-size: 13px;
  color: #64748b;
}
.pagination-buttons {
  display: flex;
  gap: 4px;
}
.btn-page {
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  background: white;
  color: #334155;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-page:hover:not(:disabled) {
  background: #f1f5f9;
  border-color: #94a3b8;
}
.btn-page.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}
.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Mode Switcher Tabs */
.mode-tabs {
  display: flex;
  background: #f1f5f9;
  padding: 4px;
  margin: 16px 24px 0;
  border-radius: 10px;
  gap: 4px;
}
.tab-btn {
  flex: 1;
  padding: 8px 12px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.tab-btn:hover {
  color: #334155;
}
.tab-btn.active {
  background: white;
  color: #3b82f6;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

/* Modal Form Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(4px);
  z-index: 1001;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.modal-card {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  transition: max-width 0.3s ease;
}
.modal-card.modal-large {
  max-width: 650px;
}
.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-height: 70vh;
  overflow-y: auto;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}
.form-control {
  width: 100%;
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  font-size: 13.5px;
  color: #0f172a;
  background: #f8fafc;
  outline: none;
  box-sizing: border-box;
}
.form-control:focus {
  border-color: #3b82f6;
  background: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.flex-1 {
  flex: 1;
}
.bulk-setup-row {
  display: flex;
  gap: 16px;
}
.error-text {
  font-size: 12px;
  color: #ef4444;
  font-weight: 500;
}

/* Bulk list styles */
.bulk-schedules-list {
  border-top: 1px dashed #cbd5e1;
  padding-top: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.section-title {
  font-size: 13px;
  font-weight: 700;
  color: #475569;
  margin: 0 0 4px 0;
}
.bulk-schedule-item {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}
.schedule-number-badge {
  background: #e2e8f0;
  color: #475569;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
}
.schedule-date {
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
  white-space: nowrap;
  width: 90px;
}
.schedule-input-wrapper {
  flex: 1;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
  border-top: 1px solid #e2e8f0;
  padding-top: 16px;
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
.btn-cancel:hover {
  background: #f8fafc;
}
.btn-submit {
  padding: 10px 24px;
  border-radius: 8px;
  border: none;
  background: #3b82f6;
  color: white;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
}
.btn-submit:hover:not(:disabled) {
  background: #2563eb;
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
</style>
