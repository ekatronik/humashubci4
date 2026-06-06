<template>
  <div class="taxonomy-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-left">
        <h2>Referensi Data</h2>
        <p>Kelola data kategori dan nama media yang digunakan bersama di seluruh modul</p>
      </div>
    </div>

    <!-- Two Panel Grid -->
    <div class="panels-grid">

      <!-- ── PANEL KATEGORI ─────────────────────────────────── -->
      <div class="panel-card">
        <div class="panel-header">
          <div class="panel-title-block">
            <div class="panel-icon cat-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
              </svg>
            </div>
            <div>
              <h3>Kategori</h3>
              <span class="panel-count">{{ categories.length }} entri</span>
            </div>
          </div>
        </div>

        <!-- Add Form -->
        <div class="add-form">
          <div class="input-group">
            <input
              v-model="newCategoryName"
              type="text"
              placeholder="Nama kategori baru..."
              class="text-input"
              @keyup.enter="addCategory"
              :disabled="catSaving"
            />
            <button class="btn-add" @click="addCategory" :disabled="catSaving || !newCategoryName.trim()">
              <span v-if="catSaving" class="spin-sm"></span>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
              Tambah
            </button>
          </div>
          <p v-if="catError" class="form-error">{{ catError }}</p>
        </div>

        <!-- List -->
        <div class="list-body">
          <div v-if="catLoading" class="list-loading">
            <div class="loader"></div><span>Memuat...</span>
          </div>
          <div v-else-if="categories.length === 0" class="empty-list">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5" /></svg>
            Belum ada kategori.
          </div>
          <div v-else class="list-items">
            <div v-for="cat in categories" :key="cat.id" class="list-item">
              <!-- View Mode -->
              <template v-if="editingCatId !== cat.id">
                <span class="item-name">{{ cat.name }}</span>
                <div class="item-actions">
                  <button class="btn-edit" @click="startEditCat(cat)" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                  </button>
                  <button class="btn-del" @click="deleteCategory(cat)" title="Hapus">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                  </button>
                </div>
              </template>
              <!-- Edit Mode -->
              <template v-else>
                <input v-model="editCatName" class="edit-input" @keyup.enter="saveCat(cat.id)" @keyup.escape="cancelEditCat" autofocus />
                <div class="item-actions">
                  <button class="btn-save" @click="saveCat(cat.id)" :disabled="!editCatName.trim()">Simpan</button>
                  <button class="btn-cancel" @click="cancelEditCat">Batal</button>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- ── PANEL MEDIA ─────────────────────────────────────── -->
      <div class="panel-card">
        <div class="panel-header">
          <div class="panel-title-block">
            <div class="panel-icon med-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
              </svg>
            </div>
            <div>
              <h3>Nama Media</h3>
              <span class="panel-count">{{ media.length }} entri</span>
            </div>
          </div>
        </div>

        <!-- Media Form (Add / Edit) -->
        <div class="add-form media-form">
          <h4 class="form-section-title">{{ editingMedId ? 'Edit Media' : 'Tambah Media Baru' }}</h4>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Nama Media</label>
              <input
                v-model="newMediaName"
                type="text"
                placeholder="Contoh: Serambi Indonesia"
                class="text-input-full"
                :disabled="medSaving"
              />
            </div>
            
            <div class="form-row-2">
              <div class="form-group">
                <label class="form-label">Jenis Media</label>
                <select v-model="newMediaType" class="select-input-full" :disabled="medSaving">
                  <option value="online">Online</option>
                  <option value="cetak">Cetak</option>
                </select>
              </div>
              
              <div class="form-group">
                <label class="form-label">Skala Jangkauan</label>
                <select v-model="newMediaScale" class="select-input-full" :disabled="medSaving">
                  <option value="Media Lokal">Media Lokal</option>
                  <option value="Media Nasional">Media Nasional</option>
                  <option value="Media Internasional">Media Internasional</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Logo Media (200x200)</label>
              <div class="logo-upload-wrapper">
                <div class="logo-preview-box" v-if="newMediaLogoUrl">
                  <img :src="newMediaLogoUrl" alt="Preview Logo" class="logo-preview-img" />
                  <button type="button" class="btn-remove-logo" @click="clearLogoSelection" title="Hapus Logo" :disabled="medSaving">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
                <div class="logo-upload-btn-wrapper">
                  <input
                    type="file"
                    ref="mediaLogoInput"
                    accept="image/*"
                    class="file-input-hidden"
                    id="media-logo-file"
                    @change="handleLogoChange"
                    :disabled="medSaving"
                  />
                  <label for="media-logo-file" class="btn-file-select">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    Pilih Berkas Logo
                  </label>
                </div>
              </div>
            </div>

            <div class="form-actions-row">
              <button class="btn-submit-media" @click="saveMediaForm" :disabled="medSaving || !newMediaName.trim()">
                <span v-if="medSaving" class="spin-sm"></span>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" /></svg>
                {{ editingMedId ? 'Simpan Perubahan' : 'Simpan Media' }}
              </button>
              <button class="btn-cancel-media" v-if="editingMedId" @click="cancelEditMed" :disabled="medSaving">
                Batal
              </button>
            </div>
          </div>
          <p v-if="medError" class="form-error">{{ medError }}</p>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
          <button :class="['ftab', { active: mediaFilter === '' }]" @click="mediaFilter = ''">Semua</button>
          <button :class="['ftab', { active: mediaFilter === 'online' }]" @click="mediaFilter = 'online'">Online</button>
          <button :class="['ftab', { active: mediaFilter === 'cetak' }]" @click="mediaFilter = 'cetak'">Cetak</button>
        </div>

        <!-- List -->
        <div class="list-body">
          <div v-if="medLoading" class="list-loading">
            <div class="loader"></div><span>Memuat...</span>
          </div>
          <div v-else-if="filteredMedia.length === 0" class="empty-list">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5" /></svg>
            Belum ada data media.
          </div>
          <div v-else class="list-items">
            <div v-for="med in filteredMedia" :key="med.id" :class="['list-item', { 'item-is-editing': editingMedId === med.id }]">
              <div class="item-left">
                <!-- Media Logo Thumbnail -->
                <div class="media-logo-thumb-wrapper">
                  <img v-if="med.media_logo" :src="getMediaLogoUrl(med.media_logo)" alt="Logo" class="media-logo-thumb-img" />
                  <div v-else class="media-logo-thumb-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0z" /></svg>
                  </div>
                </div>

                <div class="media-info-block">
                  <span class="item-name">{{ med.media_name }}</span>
                  <div class="media-badges-row">
                    <span :class="['badge-type', med.media_type === 'online' ? 'bt-online' : 'bt-cetak']">{{ med.media_type }}</span>
                    <span class="badge-scale" v-if="med.media_scale">{{ med.media_scale }}</span>
                  </div>
                </div>
              </div>
              
              <div class="item-actions">
                <button class="btn-edit" @click="startEditMed(med)" title="Edit" :disabled="medSaving">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                </button>
                <button class="btn-del" @click="deleteMedia(med)" title="Hapus" :disabled="medSaving">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// ─── STATE ────────────────────────────────────────────────
const categories     = ref([])
const media          = ref([])
const catLoading     = ref(true)
const medLoading     = ref(true)
const catSaving      = ref(false)
const medSaving      = ref(false)
const catError       = ref('')
const medError       = ref('')
const newCategoryName = ref('')
const newMediaName   = ref('')
const newMediaType   = ref('online')
const newMediaScale  = ref('Media Lokal')
const newMediaLogoFile = ref(null)
const newMediaLogoUrl = ref(null)
const mediaLogoInput = ref(null)
const shouldClearLogo = ref(false)

const editingCatId   = ref(null)
const editingMedId   = ref(null)
const editCatName    = ref('')
const mediaFilter    = ref('')
const toast          = ref({ show: false, message: '', type: 'success' })

// ─── COMPUTED ──────────────────────────────────────────────
const filteredMedia = computed(() => {
  if (!mediaFilter.value) return media.value
  return media.value.filter(m => m.media_type === mediaFilter.value)
})

// ─── TOAST HELPER ─────────────────────────────────────────
const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// ─── FETCH DATA ───────────────────────────────────────────
const fetchCategories = async () => {
  catLoading.value = true
  try {
    const res = await axios.get('/api/admin/categories')
    categories.value = res.data.data || []
  } catch (e) { console.error(e) }
  finally { catLoading.value = false }
}

const fetchMedia = async () => {
  medLoading.value = true
  try {
    const res = await axios.get('/api/admin/media')
    media.value = res.data.data || []
  } catch (e) { console.error(e) }
  finally { medLoading.value = false }
}

// ─── CATEGORY CRUD ────────────────────────────────────────
const addCategory = async () => {
  catError.value = ''
  if (!newCategoryName.value.trim()) return
  catSaving.value = true
  try {
    await axios.post('/api/admin/categories', { name: newCategoryName.value.trim() })
    newCategoryName.value = ''
    showToast('Kategori berhasil ditambahkan.')
    await fetchCategories()
  } catch (e) {
    catError.value = e.response?.data?.message || 'Gagal menambahkan kategori.'
  } finally { catSaving.value = false }
}

const startEditCat  = (cat) => { editingCatId.value = cat.id; editCatName.value = cat.name }
const cancelEditCat = ()    => { editingCatId.value = null; editCatName.value = '' }

const saveCat = async (id) => {
  if (!editCatName.value.trim()) return
  try {
    await axios.put(`/api/admin/categories/${id}`, { name: editCatName.value.trim() })
    cancelEditCat()
    showToast('Kategori berhasil diperbarui.')
    await fetchCategories()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal menyimpan perubahan.', 'error')
  }
}

const deleteCategory = async (cat) => {
  if (!confirm(`Hapus kategori "${cat.name}"? Tindakan ini tidak dapat dibatalkan.`)) return
  try {
    await axios.delete(`/api/admin/categories/${cat.id}`)
    showToast('Kategori berhasil dihapus.')
    await fetchCategories()
  } catch (e) {
    showToast('Gagal menghapus kategori.', 'error')
  }
}

// ─── MEDIA CRUD ───────────────────────────────────────────
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

const handleLogoChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  newMediaLogoFile.value = file
  newMediaLogoUrl.value = URL.createObjectURL(file)
}

const clearLogoSelection = () => {
  newMediaLogoFile.value = null
  newMediaLogoUrl.value = null
  shouldClearLogo.value = true
  if (mediaLogoInput.value) {
    mediaLogoInput.value.value = ''
  }
}

const startEditMed = (med) => {
  editingMedId.value = med.id
  newMediaName.value = med.media_name
  newMediaType.value = med.media_type
  newMediaScale.value = med.media_scale || 'Media Lokal'
  newMediaLogoFile.value = null
  newMediaLogoUrl.value = med.media_logo ? getMediaLogoUrl(med.media_logo) : null
  shouldClearLogo.value = false
}

const cancelEditMed = () => {
  editingMedId.value = null
  newMediaName.value = ''
  newMediaType.value = 'online'
  newMediaScale.value = 'Media Lokal'
  newMediaLogoFile.value = null
  newMediaLogoUrl.value = null
  shouldClearLogo.value = false
  if (mediaLogoInput.value) {
    mediaLogoInput.value.value = ''
  }
}

const saveMediaForm = async () => {
  medError.value = ''
  if (!newMediaName.value.trim()) return
  medSaving.value = true

  try {
    const formData = new FormData()
    formData.append('media_name', newMediaName.value.trim())
    formData.append('media_type', newMediaType.value)
    formData.append('media_scale', newMediaScale.value)
    
    if (newMediaLogoFile.value) {
      formData.append('logo', newMediaLogoFile.value)
    }

    if (editingMedId.value) {
      if (shouldClearLogo.value) {
        formData.append('clear_logo', '1')
      }
      await axios.post(`/api/admin/media/${editingMedId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      showToast('Media berhasil diperbarui.')
    } else {
      await axios.post('/api/admin/media', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      showToast('Media berhasil ditambahkan.')
    }

    cancelEditMed()
    await fetchMedia()
  } catch (e) {
    medError.value = e.response?.data?.message || 'Gagal menyimpan media.'
  } finally {
    medSaving.value = false
  }
}

const deleteMedia = async (med) => {
  if (!confirm(`Hapus media "${med.media_name}"? Tindakan ini tidak dapat dibatalkan.`)) return
  try {
    await axios.delete(`/api/admin/media/${med.id}`)
    showToast('Media berhasil dihapus.')
    if (editingMedId.value === med.id) {
      cancelEditMed()
    }
    await fetchMedia()
  } catch (e) {
    showToast('Gagal menghapus media.', 'error')
  }
}

// ─── INIT ─────────────────────────────────────────────────
onMounted(() => {
  fetchCategories()
  fetchMedia()
})
</script>

<style scoped>
.taxonomy-page { display: flex; flex-direction: column; gap: 28px; animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; }
.page-header h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
.page-header p  { font-size: 13.5px; color: #64748b; margin-top: 4px; }

/* Two-panel grid */
.panels-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 860px) { .panels-grid { grid-template-columns: 1fr; } }

/* Panel Card */
.panel-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }

.panel-header { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; }
.panel-title-block { display: flex; align-items: center; gap: 14px; }
.panel-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.panel-icon svg { width: 22px; height: 22px; }
.cat-icon { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #6d28d9; }
.med-icon { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }
.panel-title-block h3 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }
.panel-count { font-size: 11.5px; font-weight: 600; color: #94a3b8; margin-top: 2px; display: block; }

/* Add Form */
.add-form { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.input-group { display: flex; gap: 8px; flex-wrap: wrap; }
.text-input {
  flex: 1; min-width: 0;
  padding: 10px 14px; border-radius: 10px;
  border: 1.5px solid #e2e8f0; font-size: 13.5px;
  font-family: inherit; outline: none; color: #0f172a;
  transition: border-color 0.2s;
  background: white;
}
.text-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.08); }
.text-input:disabled { opacity: 0.6; }
.select-input {
  padding: 10px 12px; border-radius: 10px;
  border: 1.5px solid #e2e8f0; font-size: 13px;
  font-family: inherit; outline: none; color: #0f172a;
  background: white; cursor: pointer;
  transition: border-color 0.2s;
}
.select-input:focus { border-color: #6366f1; }
.btn-add {
  display: flex; align-items: center; gap: 6px;
  padding: 10px 16px; border-radius: 10px;
  border: none; background: #0f172a; color: white;
  font-size: 13px; font-weight: 600; font-family: inherit;
  cursor: pointer; transition: background 0.2s;
  white-space: nowrap;
}
.btn-add svg { width: 16px; height: 16px; }
.btn-add:hover:not(:disabled) { background: #1e293b; }
.btn-add:disabled { opacity: 0.5; cursor: not-allowed; }
.spin-sm { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }
.form-error { color: #ef4444; font-size: 12px; margin-top: 6px; }

/* Filter Tabs */
.filter-tabs { display: flex; gap: 4px; padding: 10px 20px; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.ftab { padding: 5px 14px; border-radius: 20px; border: 1.5px solid transparent; font-size: 12px; font-weight: 600; cursor: pointer; background: transparent; color: #64748b; font-family: inherit; transition: all 0.2s; }
.ftab:hover { background: #f1f5f9; color: #0f172a; }
.ftab.active { background: #0f172a; color: white; border-color: #0f172a; }

/* List */
.list-body { flex: 1; overflow-y: auto; max-height: 480px; }
.list-loading { display: flex; align-items: center; gap: 12px; padding: 32px 24px; color: #94a3b8; font-size: 13px; }
.loader { width: 20px; height: 20px; border: 2.5px solid #e2e8f0; border-top-color: #0f172a; border-radius: 50%; animation: spin 0.8s linear infinite; }
.empty-list { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 24px; color: #94a3b8; font-size: 13px; }
.empty-list svg { width: 36px; height: 36px; opacity: 0.4; }

.list-items { display: flex; flex-direction: column; }
.list-item {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  padding: 14px 20px; border-bottom: 1px solid #f8fafc;
  transition: background 0.15s;
}
.list-item:last-child { border-bottom: none; }
.list-item:hover { background: #fafbff; }

.item-left { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; }
.item-name { font-size: 13.5px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }

.item-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.btn-edit, .btn-del { width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.btn-edit svg, .btn-del svg { width: 15px; height: 15px; }
.btn-edit { background: #f1f5f9; color: #475569; }
.btn-edit:hover { background: #e0e7ff; color: #4338ca; }
.btn-del  { background: #f1f5f9; color: #475569; }
.btn-del:hover  { background: #fee2e2; color: #dc2626; }

.btn-save, .btn-cancel {
  padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;
  border: none; cursor: pointer; font-family: inherit; transition: all 0.2s;
}
.btn-save   { background: #0f172a; color: white; }
.btn-save:hover   { background: #1e293b; }
.btn-save:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-cancel { background: #f1f5f9; color: #475569; }
.btn-cancel:hover { background: #e2e8f0; }

.edit-input {
  flex: 1; padding: 7px 12px; border-radius: 8px;
  border: 1.5px solid #6366f1; font-size: 13px;
  font-family: inherit; outline: none; color: #0f172a;
}
.badge-type { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; flex-shrink: 0; }
.bt-online { background: #dbeafe; color: #1d4ed8; }
.bt-cetak  { background: #fef3c7; color: #b45309; }

/* Media Form Layout */
.media-form { display: flex; flex-direction: column; gap: 16px; }
.form-section-title { font-size: 14px; font-weight: 800; color: #1e293b; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 0.5px; }
.form-grid { display: flex; flex-direction: column; gap: 14px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; }
.text-input-full, .select-input-full {
  width: 100%; padding: 10px 14px; border-radius: 10px;
  border: 1.5px solid #e2e8f0; font-size: 13px; font-family: inherit;
  outline: none; color: #0f172a; background: white; transition: border-color 0.2s;
}
.text-input-full:focus, .select-input-full:focus { border-color: #6366f1; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* Logo Upload & Preview Styles */
.logo-upload-wrapper { display: flex; align-items: center; gap: 16px; }
.logo-preview-box { position: relative; width: 64px; height: 64px; border-radius: 12px; border: 1.5px solid #e2e8f0; overflow: hidden; background: #fafafa; display: flex; align-items: center; justify-content: center; padding: 4px; flex-shrink: 0; }
.logo-preview-img { width: 100%; height: 100%; object-fit: contain; }
.btn-remove-logo { position: absolute; top: 2px; right: 2px; width: 18px; height: 18px; border-radius: 50%; background: rgba(220, 38, 38, 0.9); color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; transition: transform 0.15s; }
.btn-remove-logo:hover { transform: scale(1.1); }
.btn-remove-logo svg { width: 12px; height: 12px; stroke-width: 2.5; }

.logo-upload-btn-wrapper { flex: 1; }
.file-input-hidden { display: none; }
.btn-file-select {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
  border-radius: 10px; border: 1.5px dashed #cbd5e1; background: #f8fafc;
  color: #475569; font-size: 12.5px; font-weight: 600; cursor: pointer;
  transition: all 0.2s;
}
.btn-file-select:hover { border-color: #6366f1; background: #f5f3ff; color: #6366f1; }
.btn-file-select svg { width: 15px; height: 15px; }

/* Form Actions */
.form-actions-row { display: flex; gap: 10px; margin-top: 6px; }
.btn-submit-media {
  flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 10px 16px; border-radius: 10px; border: none; background: #0f172a; color: white;
  font-size: 13px; font-weight: 700; cursor: pointer; transition: background 0.2s;
}
.btn-submit-media:hover:not(:disabled) { background: #1e293b; }
.btn-submit-media:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-submit-media svg { width: 15px; height: 15px; }
.btn-cancel-media {
  padding: 10px 16px; border-radius: 10px; border: 1.5px solid #cbd5e1;
  background: white; color: #475569; font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
}
.btn-cancel-media:hover { background: #f1f5f9; color: #0f172a; }

/* List Item Enhancements */
.item-is-editing { background: #f5f3ff !important; border-left: 3px solid #6366f1; padding-left: 17px !important; }
.media-logo-thumb-wrapper { width: 42px; height: 42px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; background: #fafafa; overflow: hidden; padding: 2px; flex-shrink: 0; }
.media-logo-thumb-img { width: 100%; height: 100%; object-fit: contain; }
.media-logo-thumb-placeholder { color: #cbd5e1; display: flex; align-items: center; justify-content: center; }
.media-logo-thumb-placeholder svg { width: 20px; height: 20px; }

.media-info-block { display: flex; flex-direction: column; gap: 4px; min-width: 0; flex: 1; }
.media-badges-row { display: flex; align-items: center; gap: 6px; }
.badge-scale { font-size: 10px; font-weight: 700; background: #f1f5f9; color: #475569; padding: 1px 6px; border-radius: 4px; text-transform: uppercase; }

/* Toast */
.toast {
  position: fixed; bottom: 28px; right: 28px; z-index: 9999;
  display: flex; align-items: center; gap: 10px;
  padding: 14px 20px; border-radius: 14px; font-size: 13.5px;
  font-weight: 600; box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  min-width: 260px;
}
.toast svg { width: 18px; height: 18px; flex-shrink: 0; }
.toast.success { background: #0f172a; color: white; }
.toast.error   { background: #dc2626; color: white; }
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(12px); }

@media (max-width: 768px) {
  .panels-grid { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
  .page-header h2 { font-size: 18px; }
  .form-row-2 { grid-template-columns: 1fr; gap: 10px; }
  .logo-upload-wrapper { flex-direction: column; align-items: flex-start; gap: 10px; }
  .logo-upload-btn-wrapper { width: 100%; }
  .btn-file-select { width: 100%; justify-content: center; }
  .toast { min-width: auto; max-width: calc(100vw - 32px); right: 16px; bottom: 16px; }
}
</style>

