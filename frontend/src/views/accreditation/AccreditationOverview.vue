<template>
  <div class="overview-container">
    <!-- Stat Cards -->
    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon faculty-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21h15Z" />
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Total Fakultas</span>
          <span class="stat-value">{{ stats.total_faculties }}</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon prodi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Total Program Studi</span>
          <span class="stat-value">{{ stats.total_prodis }}</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon unggul-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Z" />
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Akreditasi Unggul</span>
          <span class="stat-value">{{ stats.total_unggul }}</span>
        </div>
      </div>
    </section>

    <!-- Campus Accreditation Premium Widget (Simplified) -->
    <section class="campus-section">
      <div class="premium-card">
        <div class="premium-header">
          <div class="header-main">
            <span class="badge-tag">Sertifikasi Institusi</span>
            <h2>{{ stats.campus.nama_institusi || 'UIN Ar-Raniry Banda Aceh' }}</h2>
          </div>
          <div class="current-grade" :class="gradeClass(stats.campus.akreditasi_sekarang)">
            {{ stats.campus.akreditasi_sekarang || 'Belum Diatur' }}
          </div>
        </div>

        <div class="premium-body">
          <div class="meta-row-grid">
            <div class="meta-item">
              <span class="meta-label">Status Saat Ini</span>
              <span class="meta-val highlight">{{ stats.campus.akreditasi_sekarang || '-' }}</span>
            </div>
            
            <div class="meta-item" v-if="stats.campus.masa_berlaku">
              <span class="meta-label">Masa Berlaku SK</span>
              <span class="meta-val">{{ formatDate(stats.campus.masa_berlaku) }}</span>
              
              <!-- Countdown Badge -->
              <div 
                v-if="getCountdown(stats.campus.masa_berlaku)" 
                :class="['countdown-badge', { 'warning': getCountdown(stats.campus.masa_berlaku).isWarning, 'critical': getCountdown(stats.campus.masa_berlaku).isCritical }]"
              >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="clock-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ getCountdown(stats.campus.masa_berlaku).text }}</span>
              </div>
            </div>

            <div class="meta-item" v-if="stats.campus.sertifikat_berlaku">
              <span class="meta-label">Folder Google Drive (SK &amp; Sertifikat)</span>
              <a :href="stats.campus.sertifikat_berlaku" target="_blank" class="download-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m5.579 13.94A9.004 9.004 0 0 1 12 3.019M2.22 14.22h19.56" />
                </svg>
                <span>Buka Google Drive SK</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Study Programs Table -->
    <section class="recent-section">
      <div class="table-card">
        <div class="card-header">
          <h3>Program Studi Terbaru yang Ditambahkan</h3>
          <router-link to="/dashboard/accreditation/study-programs" class="btn-view-all">Kelola Prodi →</router-link>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Nama Program Studi</th>
                <th>Fakultas</th>
                <th>Jenjang</th>
                <th>Akreditasi Sekarang</th>
                <th>Masa Berlaku / Countdown</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in stats.recent_prodis" :key="item.id">
                <td>
                  <div class="prodi-name-cell">
                    <strong>{{ item.nama_prodi }}</strong>
                    <a v-if="item.website" :href="item.website" target="_blank" class="web-link">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                      </svg>
                    </a>
                  </div>
                </td>
                <td>
                  <span class="faculty-badge" :title="item.nama_fakultas">{{ item.singkatan_fakultas }}</span>
                </td>
                <td>
                  <span class="jenjang-tag" :class="jenjangClass(item.jenjang)">{{ item.jenjang }}</span>
                </td>
                <td>
                  <span class="status-badge" :class="gradeClass(item.akreditasi_sekarang)">
                    {{ item.akreditasi_sekarang }}
                  </span>
                </td>
                <td>
                  <div class="accred-cell" v-if="item.masa_berlaku">
                    <span class="date-text">{{ formatDateShort(item.masa_berlaku) }}</span>
                    <span 
                      v-if="getCountdown(item.masa_berlaku)" 
                      :class="['countdown-text', { 'warning': getCountdown(item.masa_berlaku).isWarning, 'critical': getCountdown(item.masa_berlaku).isCritical }]"
                    >
                      {{ getCountdown(item.masa_berlaku).text }}
                    </span>
                  </div>
                  <span class="text-muted" v-else>-</span>
                </td>
                <td>
                  <a v-if="item.sertifikat_berlaku" :href="item.sertifikat_berlaku" target="_blank" class="btn-table-action" title="Buka Folder Google Drive">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m5.579 13.94A9.004 9.004 0 0 1 12 3.019M2.22 14.22h19.56" />
                    </svg>
                  </a>
                  <span v-else class="text-muted text-xs">-</span>
                </td>
              </tr>
              <tr v-if="!stats.recent_prodis || stats.recent_prodis.length === 0">
                <td colspan="6" class="text-center py-8 text-slate-400">Belum ada data program studi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const stats = ref({
  total_faculties: 0,
  total_prodis: 0,
  total_unggul: 0,
  campus: {
    nama_institusi: 'UIN Ar-Raniry Banda Aceh',
    akreditasi_sekarang: null,
    masa_berlaku: null,
    sertifikat_berlaku: null,
  },
  recent_prodis: []
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

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    const options = { year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(dateString).toLocaleDateString('id-ID', options)
  } catch (e) {
    return dateString
  }
}

const formatDateShort = (dateString) => {
  if (!dateString) return ''
  try {
    return new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
  } catch (e) {
    return dateString
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/admin/accreditation/stats')
    if (res.data.status === 'success') {
      stats.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil data akreditasi:', err)
  }
})
</script>

<style scoped>
.overview-container {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 24px;
}
.stat-card {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 24px;
  background: white;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01);
  transition: all 0.2s ease;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
}
.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}
.stat-icon svg {
  width: 24px;
  height: 24px;
}
.faculty-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.prodi-icon { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
.unggul-icon { background: linear-gradient(135deg, #10b981, #047857); }

.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: 13px; font-weight: 500; color: #64748b; }
.stat-value { font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px; }

/* Premium Card Widget */
.premium-card {
  background: white;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 8px 30px rgba(0,0,0,0.015);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.premium-header {
  padding: 32px 40px;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}
.header-main h2 {
  font-size: 24px;
  font-weight: 800;
  margin-top: 8px;
  letter-spacing: -0.5px;
}
.badge-tag {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #10b981;
  letter-spacing: 1px;
}
.current-grade {
  font-size: 22px;
  font-weight: 900;
  padding: 10px 24px;
  border-radius: 30px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  text-transform: uppercase;
}
.grade-unggul { background: #10b981; color: white; }
.grade-baik-sekali { background: #3b82f6; color: white; }
.grade-baik { background: #f59e0b; color: white; }
.grade-none { background: #64748b; color: white; }

.premium-body {
  padding: 40px;
}
.meta-row-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
}
.meta-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.meta-label {
  font-size: 12px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.meta-val {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
}
.meta-val.highlight {
  font-size: 20px;
  color: #0f172a;
}
.download-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #10b981;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
  align-self: flex-start;
}
.download-link svg {
  width: 18px;
  height: 18px;
}
.download-link:hover {
  color: #059669;
  transform: translateX(2px);
}

.countdown-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 700;
  background: #eff6ff;
  color: #1d4ed8;
  width: max-content;
  margin-top: 2px;
}
.countdown-badge.warning {
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fde68a;
  animation: pulseWarning 2s infinite ease-in-out;
}
.countdown-badge.critical {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fca5a5;
}
.clock-icon {
  width: 12px;
  height: 12px;
}

/* Recent Study Programs Table */
.table-card {
  background: white;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 15px rgba(0,0,0,0.01);
  overflow: hidden;
}
.card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}
.card-header h3 {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}
.btn-view-all {
  font-size: 13px;
  font-weight: 600;
  color: #10b981;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-view-all:hover {
  color: #059669;
  transform: translateX(3px);
}
.table-wrapper {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
th {
  background: #f8fafc;
  padding: 14px 20px;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  border-bottom: 1px solid #e2e8f0;
}
td {
  padding: 16px 20px;
  font-size: 13.5px;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}
tr:last-child td {
  border-bottom: none;
}
.prodi-name-cell {
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
.faculty-badge {
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
  color: #2563eb;
}
.jenjang-tag.jenjang-red {
  background: #fef2f2;
  color: #dc2626;
}
.status-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}

.accred-cell {
  display: flex;
  flex-direction: column;
  gap: 3px;
}
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

.btn-table-action {
  color: #64748b;
  display: inline-flex;
  padding: 6px;
  border-radius: 6px;
  background: #f8fafc;
  transition: all 0.15s;
}
.btn-table-action svg {
  width: 16px;
  height: 16px;
}
.btn-table-action:hover {
  background: #eff6ff;
  color: #2563eb;
}
.text-muted { color: #94a3b8; }
.text-xs { font-size: 11px; }
.py-8 { padding-top: 32px; padding-bottom: 32px; }
.text-center { text-align: center; }
</style>
