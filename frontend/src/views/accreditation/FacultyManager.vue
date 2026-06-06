<template>
  <div class="faculty-container">
    <div class="header-actions">
      <div class="search-bar-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="search-icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input type="text" v-model="searchQuery" placeholder="Cari nama fakultas atau singkatan..." class="search-input">
      </div>
      <button @click="openModal(null)" class="btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Fakultas</span>
      </button>
    </div>

    <!-- Alert -->
    <div v-if="alert.show" :class="['alert', alert.type]">
      <span>{{ alert.message }}</span>
      <button type="button" @click="alert.show = false" class="alert-close">&times;</button>
    </div>

    <!-- Table / Grid -->
    <div class="table-card">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Nama Fakultas</th>
              <th>Singkatan</th>
              <th>Website Resmi</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="fac in filteredFaculties" :key="fac.id">
              <td>
                <div class="fullname-cell">
                  <strong>{{ fac.nama_fakultas }}</strong>
                </div>
              </td>
              <td>
                <span class="abbr-badge">{{ fac.singkatan }}</span>
              </td>
              <td>
                <a v-if="fac.website" :href="fac.website" target="_blank" class="web-link">
                  <span>{{ fac.website }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                  </svg>
                </a>
                <span v-else class="text-muted">-</span>
              </td>
              <td>
                <div class="action-buttons justify-end">
                  <button @click="openModal(fac)" class="btn-action edit" title="Edit Fakultas">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                  </button>
                  <button @click="confirmDelete(fac)" class="btn-action delete" title="Hapus Fakultas">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredFaculties.length === 0">
              <td colspan="4" class="text-center py-8 text-slate-400">Tidak ada data fakultas ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form (Add / Edit) -->
    <div class="modal-overlay" v-if="modal.isOpen" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ modal.isEdit ? 'Edit Fakultas' : 'Tambah Fakultas Baru' }}</h3>
          <button @click="closeModal" class="modal-close">&times;</button>
        </div>
        <form @submit.prevent="saveFaculty" class="modal-body">
          <div class="form-group">
            <label for="nama_fakultas">Nama Lengkap Fakultas</label>
            <input 
              type="text" 
              id="nama_fakultas" 
              v-model="modal.form.nama_fakultas" 
              required 
              placeholder="Contoh: Fakultas Sains dan Teknologi" 
              class="form-control"
            >
          </div>

          <div class="form-group">
            <label for="singkatan">Singkatan / Akronim</label>
            <input 
              type="text" 
              id="singkatan" 
              v-model="modal.form.singkatan" 
              required 
              placeholder="Contoh: FST" 
              class="form-control"
            >
          </div>

          <div class="form-group">
            <label for="website">Alamat Website</label>
            <input 
              type="url" 
              id="website" 
              v-model="modal.form.website" 
              placeholder="Contoh: https://fst.ar-raniry.ac.id" 
              class="form-control"
            >
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-cancel">Batal</button>
            <button type="submit" class="btn-submit" :disabled="modal.submitting">
              {{ modal.submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const faculties = ref([])
const searchQuery = ref('')
const alert = ref({ show: false, type: '', message: '' })

const modal = ref({
  isOpen: false,
  isEdit: false,
  submitting: false,
  id: null,
  form: {
    nama_fakultas: '',
    singkatan: '',
    website: ''
  }
})

const filteredFaculties = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  if (!query) return faculties.value
  return faculties.value.filter(fac => 
    fac.nama_fakultas.toLowerCase().includes(query) ||
    fac.singkatan.toLowerCase().includes(query)
  )
})

const loadFaculties = async () => {
  try {
    const res = await axios.get('/api/admin/accreditation/faculties')
    if (res.data.status === 'success') {
      faculties.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil data fakultas:', err)
  }
}

const openModal = (fac = null) => {
  alert.value.show = false
  if (fac) {
    modal.value.isEdit = true
    modal.value.id = fac.id
    modal.value.form = {
      nama_fakultas: fac.nama_fakultas,
      singkatan: fac.singkatan,
      website: fac.website || ''
    }
  } else {
    modal.value.isEdit = false
    modal.value.id = null
    modal.value.form = {
      nama_fakultas: '',
      singkatan: '',
      website: ''
    }
  }
  modal.value.isOpen = true
}

const closeModal = () => {
  modal.value.isOpen = false
}

const saveFaculty = async () => {
  modal.value.submitting = true
  alert.value.show = false

  try {
    let res
    if (modal.value.isEdit) {
      res = await axios.put(`/api/admin/accreditation/faculties/${modal.value.id}`, modal.value.form)
    } else {
      res = await axios.post('/api/admin/accreditation/faculties', modal.value.form)
    }

    if (res.data.status === 'success') {
      alert.value = {
        show: true,
        type: 'alert-success',
        message: modal.value.isEdit ? 'Data fakultas berhasil diperbarui.' : 'Fakultas baru berhasil ditambahkan.'
      }
      closeModal()
      await loadFaculties()
    }
  } catch (err) {
    console.error(err)
    alert.value = {
      show: true,
      type: 'alert-error',
      message: err.response?.data?.message || 'Gagal menyimpan data fakultas.'
    }
  } finally {
    modal.value.submitting = false
  }
}

const confirmDelete = async (fac) => {
  if (confirm(`Apakah Anda yakin ingin menghapus fakultas "${fac.singkatan}"? Seluruh program studi di dalam fakultas ini juga akan terhapus secara permanen.`)) {
    alert.value.show = false
    try {
      const res = await axios.delete(`/api/admin/accreditation/faculties/${fac.id}`)
      if (res.data.status === 'success') {
        alert.value = {
          show: true,
          type: 'alert-success',
          message: `Fakultas ${fac.singkatan} berhasil dihapus.`
        }
        await loadFaculties()
      }
    } catch (err) {
      console.error(err)
      alert.value = {
        show: true,
        type: 'alert-error',
        message: 'Gagal menghapus fakultas.'
      }
    }
  }
}

onMounted(() => {
  loadFaculties()
})
</script>

<style scoped>
.faculty-container {
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
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
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
}
.btn-add svg {
  width: 16px;
  height: 16px;
}
.btn-add:hover {
  background: #059669;
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
.fullname-cell {
  font-size: 14px;
}
.abbr-badge {
  background: #eff6ff;
  color: #1d4ed8;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
}
.web-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.web-link svg {
  width: 14px;
  height: 14px;
}
.web-link:hover {
  text-decoration: underline;
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
  border-color: #10b981;
  background: white;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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
</style>
