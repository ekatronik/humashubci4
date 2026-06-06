<template>
  <div class="mod-overview">
    <!-- Header -->
    <div class="overview-header">
      <div class="overview-title-block">
        <div class="module-icon-lg news-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
          </svg>
        </div>
        <div>
          <h2>Berita Online</h2>
          <p>Ringkasan dan statistik modul berita online humas</p>
        </div>
      </div>
      <router-link to="/dashboard/news" class="btn-primary-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
        Lihat Daftar
      </router-link>
    </div>

    <!-- Stat Cards -->
    <div v-if="loading" class="stat-loading">
      <div class="loader"></div><span>Memuat data...</span>
    </div>
    <div v-else class="stat-grid">
      <div class="stat-card s-blue">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg></div>
        <div class="stat-info"><span class="stat-val">{{ stats.news.total }}</span><span class="stat-lbl">Total Berita</span></div>
      </div>
      <div class="stat-card s-indigo">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg></div>
        <div class="stat-info"><span class="stat-val">{{ stats.news.rilis_humas }}</span><span class="stat-lbl">Rilis Humas</span></div>
      </div>
      <div class="stat-card s-violet">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg></div>
        <div class="stat-info"><span class="stat-val">{{ stats.news.liputan }}</span><span class="stat-lbl">Liputan Wartawan</span></div>
      </div>
      <div class="stat-card s-emerald">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></div>
        <div class="stat-info"><router-link to="/dashboard/news/reports" class="stat-link">Lihat Laporan →</router-link><span class="stat-lbl">Analitik & Grafik</span></div>
      </div>
    </div>

    <!-- Latest News Table -->
    <div class="latest-card" v-if="!loading">
      <div class="latest-header">
        <h4>Berita Terbaru</h4>
        <router-link to="/dashboard/news" class="view-all">Lihat semua →</router-link>
      </div>
      <div v-if="stats.news.latest.length === 0" class="empty-state">Belum ada data berita.</div>
      <table v-else class="latest-table">
        <thead>
          <tr><th>Judul Berita</th><th>Media</th><th>Sumber</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
          <tr v-for="item in stats.news.latest" :key="item.id">
            <td class="td-title">{{ item.title }}</td>
            <td>{{ item.media_name || '—' }}</td>
            <td><span :class="['badge-source', item.source_type === 'Rilis Humas' ? 'badge-rilis' : 'badge-liputan']">{{ item.source_type }}</span></td>
            <td>{{ formatDate(item.news_date) }}</td>
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
const stats   = ref({ news: { total: 0, rilis_humas: 0, liputan: 0, latest: [] } })

const formatDate = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/admin/dashboard-stats')
    if (res.data.status === 'success') {
      stats.value = res.data.data
    }
  } catch (e) {
    console.error('Gagal memuat statistik:', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.mod-overview { display: flex; flex-direction: column; gap: 24px; animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.overview-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
.overview-title-block { display: flex; align-items: center; gap: 16px; }
.module-icon-lg { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.module-icon-lg svg { width: 28px; height: 28px; }
.news-icon { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }
.overview-title-block h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
.overview-title-block p  { font-size: 13px; color: #64748b; margin-top: 2px; }

.btn-primary-sm {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 18px; border-radius: 12px;
  background: #0f172a; color: white;
  font-size: 13px; font-weight: 600; text-decoration: none;
  transition: background 0.2s;
}
.btn-primary-sm svg { width: 16px; height: 16px; }
.btn-primary-sm:hover { background: #1e293b; }

/* Stat Cards */
.stat-loading { display: flex; align-items: center; gap: 12px; padding: 24px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; color: #64748b; }
.loader { width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top-color: #0f172a; border-radius: 50%; animation: spin 0.8s infinite linear; }
@keyframes spin { to { transform: rotate(360deg); } }

.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
@media (max-width: 960px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

.stat-card {
  display: flex; align-items: center; gap: 16px;
  padding: 20px 22px; border-radius: 16px;
  border: 1px solid transparent;
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon svg { width: 22px; height: 22px; }
.stat-info { display: flex; flex-direction: column; }
.stat-val { font-size: 28px; font-weight: 800; line-height: 1; }
.stat-lbl { font-size: 12px; font-weight: 500; margin-top: 3px; }
.stat-link { font-size: 13px; font-weight: 700; text-decoration: none; }

.s-blue   { background: #eff6ff; border-color: #bfdbfe; }
.s-blue   .stat-icon { background: #dbeafe; color: #1d4ed8; }
.s-blue   .stat-val  { color: #1d4ed8; }
.s-blue   .stat-lbl  { color: #60a5fa; }

.s-indigo { background: #eef2ff; border-color: #c7d2fe; }
.s-indigo .stat-icon { background: #e0e7ff; color: #4338ca; }
.s-indigo .stat-val  { color: #4338ca; }
.s-indigo .stat-lbl  { color: #818cf8; }

.s-violet { background: #f5f3ff; border-color: #ddd6fe; }
.s-violet .stat-icon { background: #ede9fe; color: #6d28d9; }
.s-violet .stat-val  { color: #6d28d9; }
.s-violet .stat-lbl  { color: #a78bfa; }

.s-emerald { background: #ecfdf5; border-color: #a7f3d0; }
.s-emerald .stat-icon { background: #d1fae5; color: #065f46; }
.s-emerald .stat-val  { color: #059669; }
.s-emerald .stat-lbl  { color: #34d399; }
.s-emerald .stat-link { color: #059669; }
.s-emerald .stat-link:hover { text-decoration: underline; }

/* Latest Table */
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
.td-title { font-weight: 600; color: #0f172a; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.badge-source { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
.badge-rilis   { background: #dbeafe; color: #1d4ed8; }
.badge-liputan { background: #f5f3ff; color: #6d28d9; }

@media (max-width: 1024px) {
  .overview-header { flex-direction: column; align-items: flex-start; }
  .btn-primary-sm { align-self: flex-start; }
}

@media (max-width: 640px) {
  .stat-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 12px; }
  .stat-card { padding: 14px 16px; }
  .stat-val { font-size: 22px; }
  .latest-table { font-size: 12px; }
  .latest-table th,
  .latest-table td { padding: 10px 12px; }
  .td-title { max-width: 140px; }
  .overview-title-block h2 { font-size: 18px; }
}
</style>
