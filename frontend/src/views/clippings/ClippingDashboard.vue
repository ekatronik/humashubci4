<template>
  <div class="mod-overview">
    <!-- Header -->
    <div class="overview-header">
      <div class="overview-title-block">
        <div class="module-icon-lg clip-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
          </svg>
        </div>
        <div>
          <h2>Kliping Berita</h2>
          <p>Ringkasan dan statistik modul kliping surat kabar humas</p>
        </div>
      </div>
      <router-link to="/dashboard/clippings" class="btn-primary-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
        Lihat Daftar
      </router-link>
    </div>

    <!-- Stat Cards -->
    <div v-if="loading" class="stat-loading">
      <div class="loader"></div><span>Memuat data...</span>
    </div>
    <div v-else class="stat-grid">
      <div class="stat-card s-violet">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" /></svg></div>
        <div class="stat-info"><span class="stat-val">{{ stats.clippings.total }}</span><span class="stat-lbl">Total Kliping</span></div>
      </div>
      <div class="stat-card s-amber">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m0-3-3-3m0 0-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" /></svg></div>
        <div class="stat-info"><span class="stat-val">{{ stats.clippings.borrowable }}</span><span class="stat-lbl">Dapat Dipinjam</span></div>
      </div>
      <div class="stat-card s-slate">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" /></svg></div>
        <div class="stat-info">
          <span class="stat-val">{{ stats.clippings.total - stats.clippings.borrowable }}</span>
          <span class="stat-lbl">Tidak Dipinjamkan</span>
        </div>
      </div>
      <div class="stat-card s-emerald">
        <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></div>
        <div class="stat-info"><router-link to="/dashboard/clippings/reports" class="stat-link">Lihat Laporan →</router-link><span class="stat-lbl">Analitik & Grafik</span></div>
      </div>
    </div>

    <!-- Latest Clippings Table -->
    <div class="latest-card" v-if="!loading">
      <div class="latest-header">
        <h4>Kliping Terbaru</h4>
        <router-link to="/dashboard/clippings" class="view-all">Lihat semua →</router-link>
      </div>
      <div v-if="stats.clippings.latest.length === 0" class="empty-state">Belum ada data kliping.</div>
      <table v-else class="latest-table">
        <thead>
          <tr><th>Judul Kliping</th><th>Media</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
          <tr v-for="item in stats.clippings.latest" :key="item.id">
            <td class="td-title">{{ item.title }}</td>
            <td>{{ item.media_name || '—' }}</td>
            <td>{{ formatDate(item.clipping_date) }}</td>
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
const stats   = ref({ clippings: { total: 0, borrowable: 0, latest: [] } })

const formatDate = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
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
.clip-icon { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #6d28d9; }
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

.s-violet { background: #f5f3ff; border-color: #ddd6fe; }
.s-violet .stat-icon { background: #ede9fe; color: #6d28d9; }
.s-violet .stat-val  { color: #6d28d9; }
.s-violet .stat-lbl  { color: #a78bfa; }

.s-amber { background: #fffbeb; border-color: #fde68a; }
.s-amber .stat-icon { background: #fef3c7; color: #b45309; }
.s-amber .stat-val  { color: #b45309; }
.s-amber .stat-lbl  { color: #f59e0b; }

.s-slate { background: #f8fafc; border-color: #e2e8f0; }
.s-slate .stat-icon { background: #f1f5f9; color: #475569; }
.s-slate .stat-val  { color: #334155; }
.s-slate .stat-lbl  { color: #94a3b8; }

.s-emerald { background: #ecfdf5; border-color: #a7f3d0; }
.s-emerald .stat-icon { background: #d1fae5; color: #065f46; }
.s-emerald .stat-val  { color: #059669; }
.s-emerald .stat-lbl  { color: #34d399; }
.s-emerald .stat-link { color: #059669; }
.s-emerald .stat-link:hover { text-decoration: underline; }

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
.td-title { font-weight: 600; color: #0f172a; max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

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

