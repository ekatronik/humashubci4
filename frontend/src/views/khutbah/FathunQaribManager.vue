<template>
  <div class="sermon-container">
    <div class="header-actions">
      <div class="search-bar-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="search-icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input type="text" v-model="searchQuery" placeholder="Cari nama khatib, imam, muazzin..." class="search-input">
      </div>
      <button @click="openModal(null)" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Khatib</span>
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
              <th>Tanggal</th>
              <th>Nama Khatib</th>
              <th>Imam</th>
              <th>Muazzin</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in paginatedSermons" :key="item.id">
              <td>{{ (currentPage - 1) * pageSize + index + 1 }}</td>
              <td>
                <span class="date-badge">{{ formatDate(item.date) }}</span>
              </td>
              <td>
                <strong>{{ item.khatib }}</strong>
              </td>
              <td>
                <span v-if="item.imam">{{ item.imam }}</span>
                <span v-else class="text-muted">-</span>
              </td>
              <td>
                <span v-if="item.muazzin">{{ item.muazzin }}</span>
                <span v-else class="text-muted">-</span>
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
              <td colspan="6" class="text-center py-8 text-slate-400">Tidak ada jadwal khutbah ditemukan.</td>
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

    <!-- Modal Form (Add / Edit) -->
    <div class="modal-overlay" v-if="modal.isOpen" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ modal.isEdit ? 'Edit Jadwal Khatib' : 'Tambah Khatib Masjid Fathun Qarib' }}</h3>
          <button @click="closeModal" class="modal-close">&times;</button>
        </div>
        <form @submit.prevent="saveSermon" class="modal-body">
          <div class="form-group">
            <label for="date">Tanggal (Hari Jumat saja)</label>
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
              placeholder="Contoh: Prof. Dr. H. Mujiburrahman, M.Ag" 
              class="form-control"
            >
          </div>

          <div class="form-group">
            <label for="imam">Nama Imam</label>
            <input 
              type="text" 
              id="imam" 
              v-model="modal.form.imam" 
              placeholder="Contoh: Tgk. H. Syarifuddin" 
              class="form-control"
            >
          </div>

          <div class="form-group">
            <label for="muazzin">Nama Muazzin</label>
            <input 
              type="text" 
              id="muazzin" 
              v-model="modal.form.muazzin" 
              placeholder="Contoh: Tgk. Bukhari" 
              class="form-control"
            >
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-cancel">Batal</button>
            <button type="submit" class="btn-submit" :disabled="modal.submitting || dateError">
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
  form: {
    date: '',
    khatib: '',
    imam: '',
    muazzin: ''
  }
})

const dateError = ref(false)

const validateDateChange = () => {
  if (!modal.value.form.date) {
    dateError.value = false
    return
  }
  const selectedDate = new Date(modal.value.form.date)
  // getDay() returns 0 for Sunday, 1 for Monday, ..., 5 for Friday, 6 for Saturday
  if (selectedDate.getDay() !== 5) {
    dateError.value = true
  } else {
    dateError.value = false
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
    (item.khatib && item.khatib.toLowerCase().includes(query)) ||
    (item.imam && item.imam.toLowerCase().includes(query)) ||
    (item.muazzin && item.muazzin.toLowerCase().includes(query))
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
      params: { mosque_type: 'fathun_qarib' }
    })
    if (res.data.status === 'success') {
      sermons.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil data jadwal khutbah:', err)
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
  if (item) {
    modal.value.isEdit = true
    modal.value.id = item.id
    modal.value.form = {
      date: item.date,
      khatib: item.khatib,
      imam: item.imam || '',
      muazzin: item.muazzin || ''
    }
  } else {
    modal.value.isEdit = false
    modal.value.id = null
    // Set default to next Friday
    modal.value.form = {
      date: getNextFridayString(),
      khatib: '',
      imam: '',
      muazzin: ''
    }
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
  // Final Friday validation
  const selectedDate = new Date(modal.value.form.date)
  if (selectedDate.getDay() !== 5) {
    dateError.value = true
    return
  }

  modal.value.submitting = true
  alert.value.show = false

  const payload = {
    ...modal.value.form,
    mosque_type: 'fathun_qarib'
  }

  try {
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
  } catch (err) {
    console.error(err)
    alert.value = {
      show: true,
      type: 'alert-error',
      message: err.response?.data?.message || 'Gagal menyimpan jadwal khutbah.'
    }
  } finally {
    modal.value.submitting = false
  }
}

const confirmDelete = async (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus jadwal khutbah tanggal ${formatDate(item.date)} dengan Khatib ${item.khatib}?`)) {
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
.error-text {
  font-size: 12px;
  color: #ef4444;
  font-weight: 500;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
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
