<template>
  <div class="activity-container">
    <!-- Header -->
    <div class="page-action-header">
      <div class="header-text">
        <h3>Log Audit &amp; Aktivitas</h3>
        <p>Catatan riwayat aktivitas operasional administrator sistem Humas Hub</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-card">
      <div class="search-box">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.608 10.608Z" />
        </svg>
        <input v-model="searchQuery" @input="debounceFetch" type="text" placeholder="Cari aktivitas, operator, atau IP..." />
      </div>
      <div class="filter-controls">
        <div class="filter-group">
          <select v-model="filterModule" @change="fetchLogs">
            <option value="">Semua Modul</option>
            <option v-for="m in modulesList" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>
        <div class="filter-group">
          <select v-model="filterUser" @change="fetchLogs">
            <option value="">Semua Operator</option>
            <option v-for="u in usersList" :key="u.user_id" :value="u.user_id">{{ u.full_name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Log Table -->
    <div class="table-card">
      <div v-if="loading" class="table-loading">
        <div class="loader"></div>
        <span>Mengambil catatan log...</span>
      </div>

      <div v-else-if="items.length === 0" class="table-empty">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375M9 18h3.375m1.875-12h7.5M12 3h7.5M12 21h7.5M3 5.625c0-.621.504-1.125 1.125-1.125h3.375c.621 0 1.125.504 1.125 1.125v12.75c0 .621-.504 1.125-1.125 1.125H4.125C3.504 19.5 3 18.996 3 18.375V5.625Z" />
        </svg>
        <p>Tidak ditemukan data log aktivitas.</p>
      </div>

      <div v-else class="table-responsive">
        <table class="activity-table">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Operator</th>
              <th>Modul</th>
              <th>Aktivitas</th>
              <th>IP &amp; Device</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td class="col-time">{{ formatDateTime(item.created_at) }}</td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-sm">
                    {{ item.user_fullname ? item.user_fullname.charAt(0).toUpperCase() : '?' }}
                  </div>
                  <div class="user-meta">
                    <span class="user-fullname">{{ item.user_fullname || 'Sistem' }}</span>
                    <span class="user-username">@{{ item.username || 'system' }}</span>
                  </div>
                </div>
              </td>
              <td>
                <span class="module-badge" :class="'module-' + slugify(item.module)">
                  {{ item.module }}
                </span>
              </td>
              <td class="col-activity">
                <span class="activity-text">{{ item.activity }}</span>
                <span v-if="item.target_id" class="target-badge">ID: {{ item.target_id }}</span>
              </td>
              <td>
                <div class="device-cell">
                  <span class="ip-address">{{ item.ip_address }}</span>
                  <div class="user-agent-tooltip" :title="item.user_agent">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="items.length > 0" class="pagination-footer">
        <span class="pagination-info">
          Menampilkan <strong>{{ (currentPage - 1) * limitSize + 1 }}</strong> hingga
          <strong>{{ Math.min(currentPage * limitSize, totalItems) }}</strong> dari
          <strong>{{ totalItems }}</strong> data
        </span>
        <div class="pagination-buttons">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="btn-page">Sebelumnya</button>
          <span class="page-indicator">Hal {{ currentPage }} dari {{ totalPages }}</span>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="btn-page">Berikutnya</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const items       = ref([])
const modulesList = ref([])
const usersList   = ref([])

const searchQuery  = ref('')
const filterModule = ref('')
const filterUser   = ref('')
const currentPage  = ref(1)
const totalPages   = ref(1)
const totalItems   = ref(0)
const limitSize    = ref(20)

const loading = ref(false)

const fetchLogs = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/activity-logs', {
      params: {
        search:    searchQuery.value,
        module:    filterModule.value,
        user_id:   filterUser.value,
        page:      currentPage.value,
        limit:     limitSize.value,
      }
    })
    if (res.data.status === 'success') {
      const d = res.data.data
      items.value       = d.items
      totalItems.value  = d.total_items
      totalPages.value  = d.total_pages
      
      // Update data filter dinamis jika ada
      if (d.filters) {
        modulesList.value = d.filters.modules || []
        usersList.value   = d.filters.users || []
      }
    }
  } catch (err) {
    console.error('Gagal memuat log aktivitas:', err)
  } finally {
    loading.value = false
  }
}

let debTimer = null
const debounceFetch = () => {
  clearTimeout(debTimer)
  debTimer = setTimeout(() => { currentPage.value = 1; fetchLogs() }, 400)
}

const changePage = (p) => {
  if (p >= 1 && p <= totalPages.value) { currentPage.value = p; fetchLogs() }
}

const formatDateTime = (dtStr) => {
  if (!dtStr) return '—'
  const dt = new Date(dtStr)
  const d = String(dt.getDate()).padStart(2, '0')
  const m = String(dt.getMonth() + 1).padStart(2, '0')
  const y = dt.getFullYear()
  
  const h = String(dt.getHours()).padStart(2, '0')
  const i = String(dt.getMinutes()).padStart(2, '0')
  const s = String(dt.getSeconds()).padStart(2, '0')
  
  return `${d}-${m}-${y} ${h}:${i}:${s}`
}

const slugify = (str) => str ? str.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '') : ''

onMounted(() => {
  fetchLogs()
})
</script>

<style scoped>
.activity-container {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.page-action-header { display: flex; justify-content: space-between; align-items: center; }
.header-text h3 { font-size: 22px; font-weight: 800; color: #0f172a; }
.header-text p  { font-size: 13.5px; color: #64748b; margin-top: 4px; }

/* Filter */
.filter-card {
  display: flex; justify-content: space-between; align-items: center;
  gap: 20px; padding: 20px; background: white; border-radius: 16px;
  border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}
.search-box { position: relative; display: flex; align-items: center; flex: 1; max-width: 420px; }
.search-icon { position: absolute; left: 16px; width: 20px; height: 20px; color: #94a3b8; }
.search-box input {
  width: 100%; height: 46px; padding: 0 16px 0 48px;
  background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;
  font-family: inherit; font-size: 14px; color: #1e293b; outline: none; transition: all 0.2s;
}
.search-box input:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.filter-controls { display: flex; gap: 14px; }
.filter-group select {
  height: 46px; padding: 0 36px 0 16px; background: #f8fafc; border: 1.5px solid #e2e8f0;
  border-radius: 12px; font-family: inherit; font-size: 13.5px; color: #475569;
  outline: none; cursor: pointer; transition: all 0.2s; appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat; background-position: right 14px center; background-size: 16px;
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
.loader { width: 32px; height: 32px; border: 3.5px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s infinite linear; }
@keyframes spin { to { transform: rotate(360deg); } }

.table-responsive { overflow-x: auto; }
.activity-table { width: 100%; border-collapse: collapse; text-align: left; }
.activity-table th {
  padding: 16px 24px; background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;
  font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;
}
.activity-table td { padding: 18px 24px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
.activity-table tr:hover td { background: #f8fafc; }

.col-time { font-family: monospace; font-size: 13px !important; color: #64748b !important; white-space: nowrap; }

/* User cell */
.user-cell { display: flex; align-items: center; gap: 12px; }
.user-avatar-sm {
  width: 32px; height: 32px; background: #e2e8f0; color: #475569; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
  flex-shrink: 0; border: 1.5px solid #e2e8f0;
}
.user-meta { display: flex; flex-direction: column; }
.user-fullname { font-size: 13.5px; font-weight: 700; color: #1e293b; }
.user-username { font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: 1px; }

/* Module badges */
.module-badge {
  display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 8px;
  font-size: 11.5px; font-weight: 700;
}
.module-berita-online   { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
.module-kliping         { background: #f5f3ff; color: #7c3aed; border: 1px solid #ede9fe; }
.module-fotovideo       { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
.module-manajemen-user  { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }

.col-activity { font-weight: 500; color: #1e293b; }
.activity-text { display: inline-block; line-height: 1.4; vertical-align: middle; }
.target-badge {
  display: inline-block; margin-left: 8px; padding: 2px 6px; background: #f1f5f9;
  border-radius: 6px; font-size: 11px; font-family: monospace; color: #475569; font-weight: 600;
  vertical-align: middle;
}

/* Device Cell & User Agent Tooltip */
.device-cell { display: flex; align-items: center; gap: 8px; }
.ip-address { font-family: monospace; font-size: 12.5px; color: #64748b; font-weight: 500; }
.user-agent-tooltip {
  display: flex; align-items: center; justify-content: center; cursor: pointer; color: #94a3b8; transition: color 0.2s;
}
.user-agent-tooltip:hover { color: #6366f1; }
.user-agent-tooltip svg { width: 15px; height: 15px; }

/* Pagination */
.pagination-footer { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; }
.pagination-info { font-size: 13px; color: #64748b; }
.pagination-info strong { color: #0f172a; }
.pagination-buttons { display: flex; align-items: center; gap: 12px; }
.btn-page { padding: 8px 16px; background: white; border: 1.5px solid #e2e8f0; border-radius: 8px; font-family: inherit; font-size: 13px; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.2s; }
.btn-page:hover:not(:disabled) { border-color: #6366f1; color: #4338ca; }
.btn-page:disabled { opacity: 0.5; cursor: not-allowed; }
.page-indicator { font-size: 13px; font-weight: 600; color: #334155; }

@media (max-width: 768px) {
  .filter-card { flex-direction: column; align-items: stretch; gap: 12px; }
  .filter-controls { flex-direction: column; gap: 10px; }
  .search-box { max-width: none; }
  .filter-group select { width: 100%; }
  .activity-table th, .activity-table td { padding: 12px 14px; }
}

@media (max-width: 640px) {
  .header-text h3 { font-size: 18px; }
  .col-time { font-size: 11px !important; }
  .user-fullname { font-size: 12.5px; }
  .activity-text { font-size: 13px; }
  .pagination-footer { flex-direction: column; gap: 12px; align-items: center; text-align: center; }
}

</style>
