<template>
  <div class="rss-manager-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-left">
        <h2>Sindikasi & Pengaturan RSS</h2>
        <p>Kelola sumber feed berita RSS dari situs fakultas/unit dan atur jadwal sinkronisasi otomatis</p>
      </div>
      <div class="header-actions">
        <button class="btn-sync-now" @click="runManualSync" :disabled="syncing">
          <span v-if="syncing" class="spin-sm"></span>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          {{ syncing ? 'Menyinkronkan...' : 'Sinkronisasi Manual' }}
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="tabs-container">
      <button :class="['tab-btn', { active: activeTab === 'sources' }]" @click="activeTab = 'sources'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
        </svg>
        Sumber RSS Feed
      </button>
      <button :class="['tab-btn', { active: activeTab === 'settings' }]" @click="activeTab = 'settings'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        Pengaturan & Cron Job
      </button>
    </div>

    <!-- Active Tab Content -->
    <div class="tab-content">
      
      <!-- ─── TAB 1: DAFTAR SUMBER RSS ────────────────────────── -->
      <div v-if="activeTab === 'sources'" class="sources-tab">
        <div class="card-section">
          <div class="card-header-row">
            <h3>Daftar Sumber Feed</h3>
            <button class="btn-primary" @click="openAddModal">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              Tambah Sumber Feed
            </button>
          </div>

          <!-- Loading state -->
          <div v-if="loadingSources" class="loading-box">
            <div class="loader"></div>
            <span>Memuat data sumber RSS...</span>
          </div>

          <!-- Empty state -->
          <div v-else-if="sources.length === 0" class="empty-box">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
            </svg>
            <p>Belum ada sumber feed RSS terdaftar.</p>
          </div>

          <!-- Table sources -->
          <div v-else class="table-wrapper">
            <table class="styled-table">
              <thead>
                <tr>
                  <th>Situs</th>
                  <th>URL Feed</th>
                  <th class="text-center">Status</th>
                  <th>Terakhir Sync</th>
                  <th class="text-center">Artikel</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="src in sources" :key="src.id">
                  <td class="font-semibold text-slate-800">
                    <a :href="src.site_url" target="_blank" class="site-link">{{ src.site_name }} ↗</a>
                  </td>
                  <td><code class="url-code">{{ src.feed_url }}</code></td>
                  <td class="text-center">
                    <button 
                      :class="['badge-status-toggle', src.is_active ? 'active' : 'inactive']"
                      @click="toggleSourceStatus(src)"
                      title="Ubah Status Aktif"
                    >
                      {{ src.is_active ? 'Aktif' : 'Nonaktif' }}
                    </button>
                  </td>
                  <td class="text-slate-500 font-medium">
                    {{ formatDate(src.last_synced_at) }}
                  </td>
                  <td class="text-center font-bold text-indigo-600">
                    {{ src.item_count }}
                  </td>
                  <td class="text-right">
                    <div class="row-actions">
                      <button class="btn-action-edit" @click="openEditModal(src)" title="Edit Sumber">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                      </button>
                      <button class="btn-action-del" @click="deleteSource(src)" title="Hapus Sumber">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ─── TAB 2: PENGATURAN & CRON JOB ─────────────────────── -->
      <div v-if="activeTab === 'settings'" class="settings-tab">
        <div class="grid-split">
          
          <!-- Column 1: Config Auto Cleanup -->
          <div class="card-section">
            <h3>Pembersihan Data Otomatis</h3>
            <p class="description-text">Konfigurasi ini dijalankan otomatis oleh sistem setiap kali sinkronisasi feed RSS berhasil dilakukan.</p>

            <form @submit.prevent="saveSettings" class="settings-form">
              <div class="form-group flex-row-align">
                <label class="toggle-switch">
                  <input type="checkbox" v-model="settings.rss_cleanup_enabled" :true-value="1" :false-value="0">
                  <span class="toggle-slider"></span>
                </label>
                <div class="toggle-label-block">
                  <span class="toggle-title">Pembersihan Otomatis Aktif</span>
                  <span class="toggle-desc">Hapus berita RSS yang sudah usang dari database secara periodik.</span>
                </div>
              </div>

              <div class="form-group" v-if="settings.rss_cleanup_enabled === 1">
                <label class="field-label">Masa Simpan Artikel (Hari)</label>
                <div class="input-inline-group">
                  <input 
                    type="number" 
                    v-model="settings.rss_cleanup_days" 
                    min="1" 
                    max="365"
                    class="number-input"
                    required
                  >
                  <span class="unit-label">Hari</span>
                </div>
                <p class="field-help">Artikel RSS yang memiliki tanggal publikasi lebih tua dari jumlah hari di atas akan otomatis dihapus.</p>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn-submit" :disabled="savingSettings">
                  <span v-if="savingSettings" class="spin-sm"></span>
                  Simpan Pengaturan
                </button>
              </div>
            </form>
          </div>

          <!-- Column 2: Cron Job Instructions -->
          <div class="card-section">
            <h3>Instruksi Otomatisasi (Cron Job)</h3>
            <p class="description-text">Agar data website fakultas terus terbarui secara otomatis di latar belakang, atur Cron Job di server / cPanel hosting Anda.</p>

            <div class="cron-status-indicator" :class="cronRunStatusClass">
              <div class="status-dot"></div>
              <div class="status-meta">
                <span class="status-text">Status Cron: <strong>{{ cronRunStatusText }}</strong></span>
                <span class="status-time" v-if="settings.rss_last_cron_run">Eksekusi Terakhir: {{ formatDate(settings.rss_last_cron_run) }}</span>
                <span class="status-time" v-else>Belum pernah terdeteksi berjalan</span>
              </div>
            </div>

            <div class="cron-command-block">
              <span class="block-title">Perintah Cron Job (Setiap 15 Menit)</span>
              <div class="code-box">
                <code>*/15 * * * * php {{ serverPath }}/spark rss:sync >/dev/null 2>&1</code>
                <button class="btn-copy" @click="copyCronCommand" title="Salin Perintah">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5A3.375 3.375 0 0 0 6.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 0 0-9-9Z" />
                  </svg>
                </button>
              </div>
              <p class="field-help mt-2">Catatan: Sesuaikan path <code>php</code> dan path folder project (<code>{{ serverPath }}</code>) sesuai dengan konfigurasi absolut di server hosting Anda.</p>
            </div>

            <!-- Detail Cron Log -->
            <div class="cron-log-card" v-if="settings.rss_last_cron_status">
              <span class="log-title">Hasil Sinkronisasi Terakhir</span>
              <p class="log-summary">{{ settings.rss_last_cron_status.summary }}</p>
              
              <div class="log-details-list" v-if="settings.rss_last_cron_status.details && settings.rss_last_cron_status.details.length > 0">
                <span class="details-heading">Detail Per-Situs:</span>
                <div v-for="(log, idx) in settings.rss_last_cron_status.details" :key="idx" class="log-item">
                  <span :class="['log-dot', log.includes('Sukses') ? 'success' : 'failed']"></span>
                  <span class="log-text">{{ log }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form (Tambah / Edit Sumber) -->
    <transition name="fade">
      <div class="modal-backdrop" v-if="modalOpen" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-header">
            <h4>{{ editMode ? 'Edit Sumber Feed RSS' : 'Tambah Sumber Feed RSS Baru' }}</h4>
            <button class="btn-close-modal" @click="closeModal">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <form @submit.prevent="saveSourceForm" class="modal-form">
            <div class="form-group">
              <label class="modal-label">Nama Website / Fakultas</label>
              <input 
                v-model="sourceForm.site_name" 
                type="text" 
                placeholder="Contoh: FEBI UIN Ar-Raniry" 
                class="modal-input"
                required
              >
            </div>
            
            <div class="form-group">
              <label class="modal-label">URL Website Utama</label>
              <input 
                v-model="sourceForm.site_url" 
                type="url" 
                placeholder="Contoh: https://febi.ar-raniry.ac.id" 
                class="modal-input"
                required
              >
            </div>

            <div class="form-group">
              <label class="modal-label">URL Feed RSS (XML)</label>
              <input 
                v-model="sourceForm.feed_url" 
                type="url" 
                placeholder="Contoh: https://febi.ar-raniry.ac.id/feed/" 
                class="modal-input"
                required
              >
              <p class="field-help">WordPress secara default meletakkan feed RSS pada akhiran path <code>/feed/</code></p>
            </div>

            <div class="form-group flex-row-align">
              <label class="toggle-switch">
                <input type="checkbox" v-model="sourceForm.is_active" :true-value="1" :false-value="0">
                <span class="toggle-slider"></span>
              </label>
              <div class="toggle-label-block">
                <span class="toggle-title">Aktifkan Sumber Feed</span>
                <span class="toggle-desc">Matikan jika website ini sedang maintenance atau lambat diakses.</span>
              </div>
            </div>

            <p v-if="modalError" class="modal-error-message">{{ modalError }}</p>

            <div class="modal-footer">
              <button type="button" class="btn-cancel" @click="closeModal" :disabled="savingSource">Batal</button>
              <button type="submit" class="btn-submit-primary" :disabled="savingSource">
                <span v-if="savingSource" class="spin-sm"></span>
                {{ editMode ? 'Simpan Perubahan' : 'Tambahkan Sumber' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// ─── STATE ────────────────────────────────────────────────
const activeTab       = ref('sources')
const sources         = ref([])
const loadingSources  = ref(true)
const modalOpen       = ref(false)
const editMode        = ref(false)
const savingSource    = ref(false)
const modalError      = ref('')
const syncing         = ref(false)
const savingSettings  = ref(false)
const serverPath      = ref('C:\\xampp\\htdocs\\humashubci\\backend') // default fallback path

const settings = ref({
  rss_cleanup_days: 14,
  rss_cleanup_enabled: 1,
  rss_last_cron_run: null,
  rss_last_cron_status: null
})

const sourceForm = ref({
  id: null,
  site_name: '',
  site_url: '',
  feed_url: '',
  is_active: 1
})

const toast = ref({ show: false, message: '', type: 'success' })

// ─── TOAST HELPER ─────────────────────────────────────────
const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3500)
}

// ─── FORMAT DATE ──────────────────────────────────────────
const formatDate = (dateString) => {
  if (!dateString) return 'Belum pernah'
  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return dateString
    return date.toLocaleString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (e) {
    return dateString
  }
}

// ─── COMPUTED ──────────────────────────────────────────────
const cronRunStatusClass = computed(() => {
  if (!settings.value.rss_last_cron_run) return 'inactive'
  
  // Deteksi apakah cron berjalan dalam 2 jam terakhir
  const lastRun = new Date(settings.value.rss_last_cron_run)
  const now = new Date()
  const diffMs = now - lastRun
  const diffHours = diffMs / (1000 * 60 * 60)
  
  return diffHours <= 2 ? 'active' : 'warning'
})

const cronRunStatusText = computed(() => {
  if (!settings.value.rss_last_cron_run) return 'Tidak Terdeteksi'
  
  const lastRun = new Date(settings.value.rss_last_cron_run)
  const now = new Date()
  const diffMs = now - lastRun
  const diffHours = diffMs / (1000 * 60 * 60)
  
  if (diffHours <= 2) {
    return 'Berjalan (Aktif)'
  } else {
    return 'Terlambat (Kemungkinan Mati)'
  }
})

// ─── FETCH DATA ───────────────────────────────────────────
const fetchSources = async () => {
  loadingSources.value = true
  try {
    const res = await axios.get('/api/admin/rss/sources')
    sources.value = (res.data.data || []).map(src => ({
      ...src,
      is_active: Number(src.is_active)
    }))
  } catch (e) {
    console.error('Gagal memuat sumber RSS', e)
    showToast('Gagal memuat daftar sumber feed RSS.', 'error')
  } finally {
    loadingSources.value = false
  }
}

const fetchSettings = async () => {
  try {
    const res = await axios.get('/api/admin/rss/settings')
    const data = res.data.data || {}
    settings.value.rss_cleanup_days = data.rss_cleanup_days ?? 14
    settings.value.rss_cleanup_enabled = data.rss_cleanup_enabled ?? 1
    settings.value.rss_last_cron_run = data.rss_last_cron_run ?? null
    settings.value.rss_last_cron_status = data.rss_last_cron_status ?? null
  } catch (e) {
    console.error('Gagal memuat pengaturan RSS', e)
  }
}

const fetchServerPath = () => {
  // Ambil server path untuk mempermudah instruksi cron secara dinamis
  if (window.location.hostname === 'localhost') {
    serverPath.value = 'c:/xampp/htdocs/humashubci/backend'
  } else {
    serverPath.value = '/home/username/public_html/backend' // path Linux default hosting
  }
}

// ─── SOURCE ACTIONS ───────────────────────────────────────
const toggleSourceStatus = async (src) => {
  const newStatus = Number(src.is_active) === 1 ? 0 : 1
  try {
    await axios.put(`/api/admin/rss/sources/${src.id}`, {
      site_name: src.site_name,
      site_url: src.site_url,
      feed_url: src.feed_url,
      is_active: newStatus
    })
    src.is_active = newStatus
    showToast(`Sumber "${src.site_name}" telah ${newStatus ? 'diaktifkan' : 'dinonaktifkan'}.`)
  } catch (e) {
    showToast('Gagal mengubah status sumber.', 'error')
  }
}

const openAddModal = () => {
  editMode.value = false
  modalError.value = ''
  sourceForm.value = {
    id: null,
    site_name: '',
    site_url: '',
    feed_url: '',
    is_active: 1
  }
  modalOpen.value = true
}

const openEditModal = (src) => {
  editMode.value = true
  modalError.value = ''
  sourceForm.value = {
    id: src.id,
    site_name: src.site_name,
    site_url: src.site_url,
    feed_url: src.feed_url,
    is_active: src.is_active
  }
  modalOpen.value = true
}

const closeModal = () => {
  modalOpen.value = false
}

const saveSourceForm = async () => {
  modalError.value = ''
  savingSource.value = true
  
  try {
    const payload = {
      site_name: sourceForm.value.site_name.trim(),
      site_url: sourceForm.value.site_url.trim(),
      feed_url: sourceForm.value.feed_url.trim(),
      is_active: sourceForm.value.is_active
    }

    if (editMode.value) {
      await axios.put(`/api/admin/rss/sources/${sourceForm.value.id}`, payload)
      showToast('Sumber RSS berhasil diperbarui.')
    } else {
      await axios.post('/api/admin/rss/sources', payload)
      showToast('Sumber RSS berhasil ditambahkan.')
    }
    
    closeModal()
    await fetchSources()
  } catch (e) {
    modalError.value = e.response?.data?.message || 'Gagal menyimpan sumber feed RSS.'
  } finally {
    savingSource.value = false
  }
}

const deleteSource = async (src) => {
  if (!confirm(`Hapus sumber "${src.site_name}"? Semua artikel berita yang berkaitan dengan situs ini akan otomatis terhapus.`)) return
  
  try {
    await axios.delete(`/api/admin/rss/sources/${src.id}`)
    showToast('Sumber RSS berhasil dihapus.')
    await fetchSources()
  } catch (e) {
    showToast('Gagal menghapus sumber RSS.', 'error')
  }
}

// ─── MANUAL SYNC ──────────────────────────────────────────
const runManualSync = async () => {
  syncing.value = true
  showToast('Memulai sinkronisasi feed RSS...', 'success')
  
  try {
    const res = await axios.post('/api/admin/rss/sync')
    const syncData = res.data.data || {}
    
    // Tampilkan hasil sukses
    showToast(`Pembaruan Selesai: +${syncData.total_inserted} artikel baru.`, 'success')
    
    // Tarik ulang sources untuk memperbarui item count & last_synced_at
    await fetchSources()
    await fetchSettings()
  } catch (e) {
    console.error('Sinkronisasi manual gagal', e)
    showToast('Sebagian atau seluruh sumber feed gagal disinkronkan. Periksa log detail di Tab 2.', 'error')
    await fetchSources()
    await fetchSettings()
  } finally {
    syncing.value = false
  }
}

// ─── SETTINGS ACTIONS ─────────────────────────────────────
const saveSettings = async () => {
  savingSettings.value = true
  try {
    await axios.post('/api/admin/rss/settings', {
      rss_cleanup_days: settings.value.rss_cleanup_days,
      rss_cleanup_enabled: settings.value.rss_cleanup_enabled
    })
    showToast('Pengaturan RSS berhasil disimpan.')
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal menyimpan pengaturan.', 'error')
  } finally {
    savingSettings.value = false
  }
}

// ─── COPY CRON JOB ────────────────────────────────────────
const copyCronCommand = () => {
  const cronCmd = `*/15 * * * * php ${serverPath.value}/spark rss:sync >/dev/null 2>&1`
  navigator.clipboard.writeText(cronCmd)
    .then(() => showToast('Perintah cron disalin ke clipboard!'))
    .catch(() => showToast('Gagal menyalin perintah.', 'error'))
}

// ─── INIT ─────────────────────────────────────────────────
onMounted(() => {
  fetchSources()
  fetchSettings()
  fetchServerPath()
})
</script>

<style scoped>
.rss-manager-page {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.header-left h2 {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.header-left p {
  font-size: 13.5px;
  color: #64748b;
  margin-top: 4px;
}

.btn-sync-now {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #4f46e5, #4338ca);
  color: white;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
  transition: all 0.2s ease;
}

.btn-sync-now:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);
  background: linear-gradient(135deg, #4338ca, #3730a3);
}

.btn-sync-now:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Tabs */
.tabs-container {
  display: flex;
  gap: 6px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 2px;
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  border-bottom: 2px solid transparent;
  background: transparent;
  color: #64748b;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: -2px;
}

.tab-btn svg {
  width: 18px;
  height: 18px;
  transition: all 0.2s;
}

.tab-btn:hover {
  color: #0f172a;
}

.tab-btn.active {
  color: #4f46e5;
  border-bottom-color: #4f46e5;
}

.tab-btn.active svg {
  color: #4f46e5;
}

/* Card Section */
.card-section {
  background: white;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.02);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.card-section h3 {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
  border: none;
  background: #0f172a;
  color: white;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #1e293b;
}

.btn-primary svg {
  width: 14px;
  height: 14px;
}

/* Table */
.table-wrapper {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
}

.styled-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 13.5px;
}

.styled-table th {
  background: #f8fafc;
  color: #64748b;
  font-weight: 700;
  padding: 14px 18px;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
  border-bottom: 1.5px solid #cbd5e1;
}

.styled-table td {
  padding: 16px 18px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.styled-table tbody tr:hover {
  background: #fafbff;
}

.site-link {
  color: #0f172a;
  text-decoration: none;
  transition: color 0.15s;
}

.site-link:hover {
  color: #4f46e5;
  text-decoration: underline;
}

.url-code {
  background: #f1f5f9;
  color: #475569;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11.5px;
  font-family: 'Fira Code', monospace;
  word-break: break-all;
}

/* Status badge */
.badge-status-toggle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.badge-status-toggle.active {
  background: #dbeafe;
  color: #1d4ed8;
}

.badge-status-toggle.active:hover {
  background: #fee2e2;
  color: #dc2626;
}

.badge-status-toggle.inactive {
  background: #f1f5f9;
  color: #64748b;
}

.badge-status-toggle.inactive:hover {
  background: #dbeafe;
  color: #1d4ed8;
}

.row-actions {
  display: flex;
  justify-content: flex-end;
  gap: 6px;
}

.btn-action-edit, .btn-action-del {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}

.btn-action-edit svg, .btn-action-del svg {
  width: 15px;
  height: 15px;
}

.btn-action-edit {
  background: #f1f5f9;
  color: #475569;
}

.btn-action-edit:hover {
  background: #e0e7ff;
  color: #4f46e5;
}

.btn-action-del {
  background: #f1f5f9;
  color: #475569;
}

.btn-action-del:hover {
  background: #fee2e2;
  color: #ef4444;
}

/* Grid Layout Split for Settings */
.grid-split {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 24px;
}

@media (max-width: 960px) {
  .grid-split {
    grid-template-columns: 1fr;
  }
}

.description-text {
  font-size: 13.5px;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Form Styles */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.flex-row-align {
  flex-direction: row;
  align-items: center;
  gap: 14px;
  background: #fafafa;
  padding: 14px 18px;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
}

.field-label {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.field-help {
  font-size: 11.5px;
  color: #94a3b8;
  margin: 0;
}

/* Toggle Switch Styling */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  flex-shrink: 0;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #cbd5e1;
  transition: .2s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .2s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background-color: #22c55e;
}

input:checked + .toggle-slider:before {
  transform: translateX(20px);
}

.toggle-label-block {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.toggle-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #0f172a;
}

.toggle-desc {
  font-size: 11.5px;
  color: #64748b;
}

.input-inline-group {
  display: flex;
  align-items: center;
  max-width: 140px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
  transition: border-color 0.2s;
  background: white;
}

.input-inline-group:focus-within {
  border-color: #4f46e5;
}

.number-input {
  width: 100%;
  padding: 10px 14px;
  border: none;
  font-size: 14px;
  font-weight: 600;
  outline: none;
  font-family: inherit;
  color: #0f172a;
}

.unit-label {
  background: #f1f5f9;
  color: #475569;
  padding: 10px 14px;
  font-size: 12px;
  font-weight: 700;
  border-left: 1px solid #e2e8f0;
}

.btn-submit {
  padding: 12px 20px;
  border-radius: 10px;
  border: none;
  background: #0f172a;
  color: white;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s;
}

.btn-submit:hover:not(:disabled) {
  background: #1e293b;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Cron Job Instructions Card */
.cron-status-indicator {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fafafa;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
  padding: 14px 18px;
}

.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.cron-status-indicator.active .status-dot {
  background: #22c55e;
  box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
  animation: pulse 2s infinite;
}

.cron-status-indicator.warning .status-dot {
  background: #f59e0b;
  box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
}

.cron-status-indicator.inactive .status-dot {
  background: #94a3b8;
}

@keyframes pulse {
  0% { transform: scale(0.95); opacity: 0.9; }
  50% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.9; }
}

.status-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.status-text {
  font-size: 13.5px;
  color: #1e293b;
}

.status-time {
  font-size: 11px;
  color: #64748b;
}

.cron-command-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
}

.block-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #475569;
}

.code-box {
  background: #0f172a;
  color: #e2e8f0;
  padding: 12px 16px;
  border-radius: 12px;
  font-family: 'Fira Code', monospace;
  font-size: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  line-height: 1.5;
  box-shadow: inset 0 2px 8px rgba(0,0,0,0.3);
}

.code-box code {
  word-break: break-all;
  white-space: pre-wrap;
}

.btn-copy {
  background: rgba(255,255,255,0.06);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  color: #94a3b8;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.btn-copy:hover {
  background: rgba(255,255,255,0.12);
  color: white;
}

.btn-copy svg {
  width: 16px;
  height: 16px;
}

/* Cron Logs Card */
.cron-log-card {
  margin-top: 14px;
  background: #fafafa;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.log-title {
  font-size: 13px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.log-summary {
  font-size: 12.5px;
  color: #475569;
  margin: 0;
  line-height: 1.5;
  font-weight: 500;
}

.log-details-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  border-top: 1px solid #e2e8f0;
  padding-top: 10px;
}

.details-heading {
  font-size: 11.5px;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 2px;
}

.log-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.log-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.log-dot.success { background: #22c55e; }
.log-dot.failed  { background: #ef4444; }

.log-text {
  color: #334155;
}

/* Loading / Empty States */
.loading-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 24px;
  color: #94a3b8;
  gap: 12px;
  font-size: 13.5px;
}

.loader {
  width: 24px;
  height: 24px;
  border: 3px solid #e2e8f0;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 24px;
  color: #94a3b8;
  gap: 10px;
}

.empty-box svg {
  width: 44px;
  height: 44px;
  opacity: 0.4;
}

.empty-box p {
  font-size: 13.5px;
  margin: 0;
}

/* Modal Styling */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 16px;
}

.modal-card {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  border: 1px solid #e2e8f0;
  overflow: hidden;
  animation: zoomIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes zoomIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.modal-header {
  padding: 18px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h4 {
  font-size: 15px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.btn-close-modal {
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  transition: all 0.2s;
}

.btn-close-modal:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.btn-close-modal svg {
  width: 18px;
  height: 18px;
}

.modal-form {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.modal-label {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.modal-input {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  font-size: 13.5px;
  outline: none;
  font-family: inherit;
  color: #0f172a;
  background: white;
  transition: border-color 0.2s;
}

.modal-input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.08);
}

.modal-error-message {
  color: #ef4444;
  font-size: 12.5px;
  margin: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 6px;
  border-top: 1px solid #f1f5f9;
  padding-top: 18px;
}

.btn-cancel {
  padding: 10px 18px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #475569;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f1f5f9;
}

.btn-submit-primary {
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  background: #0f172a;
  color: white;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s;
}

.btn-submit-primary:hover:not(:disabled) {
  background: #1e293b;
}

.btn-submit-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 20000;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 20px;
  border-radius: 14px;
  font-size: 13.5px;
  font-weight: 600;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
  min-width: 260px;
}

.toast svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.toast.success {
  background: #0f172a;
  color: white;
}

.toast.error {
  background: #dc2626;
  color: white;
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.toast-enter-active, .toast-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.95);
}

.spin-sm {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@media (max-width: 640px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
  }
  .btn-sync-now {
    width: 100%;
    justify-content: center;
  }
  .styled-table th:nth-child(2), .styled-table td:nth-child(2),
  .styled-table th:nth-child(4), .styled-table td:nth-child(4) {
    display: none; /* Hide URL & Last Sync on very narrow screens */
  }
  .grid-split {
    grid-template-columns: 1fr;
  }
  .flex-row-align {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  .toast {
    max-width: calc(100vw - 32px);
    right: 16px;
    bottom: 16px;
  }
}
</style>
