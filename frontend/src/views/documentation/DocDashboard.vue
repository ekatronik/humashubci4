<template>
  <div class="mod-overview">
    <!-- Header -->
    <div class="overview-header">
      <div class="overview-title-block">
        <div class="module-icon-lg doc-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
          </svg>
        </div>
        <div>
          <h2>Dokumentasi Kegiatan</h2>
          <p>Ringkasan dan statistik modul dokumentasi humas</p>
        </div>
      </div>
      <router-link to="/dashboard/documentation" class="btn-primary-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
        Lihat Daftar
      </router-link>
    </div>

    <!-- Stat Cards -->
    <div v-if="loading" class="stat-loading">
      <div class="loader"></div><span>Memuat data...</span>
    </div>
    <div v-else class="stat-grid">
      <div class="stat-card s-emerald">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg></div>
        <div class="stat-info"><span class="stat-val">{{ stats.documentation.total }}</span><span class="stat-lbl">Total Kegiatan</span></div>
      </div>
      <div class="stat-card s-cyan">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg></div>
        <div class="stat-info"><span class="stat-val">{{ stats.documentation.attendees }}</span><span class="stat-lbl">Total Peserta</span></div>
      </div>
      <div class="stat-card s-amber">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253M3.284 14.253A8.959 8.959 0 0 1 3 12c0-1.085.184-2.126.516-3.096" /></svg></div>
        <div class="stat-info"><span class="stat-val">{{ stats.documentation.external }}</span><span class="stat-lbl">Kegiatan Eksternal</span></div>
      </div>
      <div class="stat-card s-green">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></div>
        <div class="stat-info"><router-link to="/dashboard/documentation/reports" class="stat-link">Lihat Laporan →</router-link><span class="stat-lbl">Analitik & Grafik</span></div>
      </div>
    </div>

    <!-- Latest Events Table -->
    <div class="latest-card" v-if="!loading">
      <div class="latest-header">
        <h4>Kegiatan Terbaru</h4>
        <router-link to="/dashboard/documentation" class="view-all">Lihat semua →</router-link>
      </div>
      <div v-if="stats.documentation.latest.length === 0" class="empty-state">Belum ada data dokumentasi.</div>
      <table v-else class="latest-table">
        <thead>
          <tr><th>Nama Kegiatan</th><th>Cakupan</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
          <tr v-for="item in stats.documentation.latest" :key="item.id">
            <td class="td-title">{{ item.event_name }}</td>
            <td><span :class="['badge-loc', getBadgeClass(item.location_type)]">{{ item.location_type }}</span></td>
            <td>{{ formatDate(item.event_date) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const stats   = ref({ documentation: { total: 0, attendees: 0, external: 0, latest: [] } })

const formatDate = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

const getBadgeClass = (type) => {
  if (type === 'Internasional') return 'b-red'
  if (type === 'Nasional')      return 'b-amber'
  if (type === 'Lokal Daerah')  return 'b-blue'
  return 'b-slate'
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/admin/dashboard-stats')
    if (res.data.status === 'success') stats.value = res.data.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>

<style scoped>
.mod-overview { display: flex; flex-direction: column; gap: 24px; animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.overview-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
.overview-title-block { display: flex; align-items: center; gap: 16px; }
.module-icon-lg { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.module-icon-lg svg { width: 28px; height: 28px; }
.doc-icon { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
.overview-title-block h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
.overview-title-block p  { font-size: 13px; color: #64748b; margin-top: 2px; }
.btn-primary-sm { display: flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 12px; background: #0f172a; color: white; font-size: 13px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
.btn-primary-sm svg { width: 16px; height: 16px; }
.btn-primary-sm:hover { background: #1e293b; }

.stat-loading { display: flex; align-items: center; gap: 12px; padding: 24px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; color: #64748b; }
.loader { width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top-color: #0f172a; border-radius: 50%; animation: spin 0.8s infinite linear; }
@keyframes spin { to { transform: rotate(360deg); } }

.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
@media (max-width: 960px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

.stat-card { display: flex; align-items: center; gap: 16px; padding: 20px 22px; border-radius: 16px; border: 1px solid transparent; transition: transform 0.2s, box-shadow 0.2s; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon svg { width: 22px; height: 22px; }
.stat-info { display: flex; flex-direction: column; }
.stat-val { font-size: 28px; font-weight: 800; line-height: 1; }
.stat-lbl { font-size: 12px; font-weight: 500; margin-top: 3px; }
.stat-link { font-size: 13px; font-weight: 700; text-decoration: none; }

.s-emerald { background: #ecfdf5; border-color: #a7f3d0; }
.s-emerald .stat-icon { background: #d1fae5; color: #065f46; }
.s-emerald .stat-val  { color: #059669; }
.s-emerald .stat-lbl  { color: #34d399; }

.s-cyan { background: #ecfeff; border-color: #a5f3fc; }
.s-cyan .stat-icon { background: #cffafe; color: #0e7490; }
.s-cyan .stat-val  { color: #0891b2; }
.s-cyan .stat-lbl  { color: #22d3ee; }

.s-amber { background: #fffbeb; border-color: #fde68a; }
.s-amber .stat-icon { background: #fef3c7; color: #b45309; }
.s-amber .stat-val  { color: #b45309; }
.s-amber .stat-lbl  { color: #f59e0b; }

.s-green { background: #f0fdf4; border-color: #bbf7d0; }
.s-green .stat-icon { background: #dcfce7; color: #15803d; }
.s-green .stat-val  { color: #16a34a; }
.s-green .stat-lbl  { color: #4ade80; }
.s-green .stat-link { color: #16a34a; }
.s-green .stat-link:hover { text-decoration: underline; }

.latest-card { background: white; border-radius: 18px; border: 1px solid #e2e8f0; overflow: hidden; }
.latest-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #f1f5f9; }
.latest-header h4 { font-size: 15px; font-weight: 800; color: #0f172a; margin: 0; }
.view-all { font-size: 13px; font-weight: 600; color: #6366f1; text-decoration: none; }
.view-all:hover { text-decoration: underline; }
.empty-state { padding: 40px; text-align: center; color: #94a3b8; font-size: 14px; }

.latest-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.latest-table th { padding: 10px 20px; background: #f8fafc; color: #64748b; font-weight: 700; text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.05em; }
.latest-table td { padding: 13px 20px; border-top: 1px solid #f1f5f9; color: #374151; vertical-align: middle; }
.latest-table tr:hover td { background: #fafafa; }
.td-title { font-weight: 600; color: #0f172a; max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.badge-loc { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
.b-red   { background: #fee2e2; color: #b91c1c; }
.b-amber { background: #fef3c7; color: #b45309; }
.b-blue  { background: #dbeafe; color: #1d4ed8; }
.b-slate { background: #f1f5f9; color: #475569; }

@media (max-width: 1024px) {
  .overview-header { flex-direction: column; align-items: flex-start; }
  .btn-primary-sm { align-self: flex-start; }
}

@media (max-width: 640px) {
  .stat-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 12px; }
  .stat-card { padding: 14px 16px; }
  .stat-val { font-size: 22px; }
  .overview-title-block h2 { font-size: 18px; }
  .latest-table th, .latest-table td { padding: 10px 12px; }
  .td-title { max-width: 140px; }
}
</style>

