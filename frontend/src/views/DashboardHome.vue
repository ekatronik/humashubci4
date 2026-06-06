<template>
  <div class="home-container">
    <!-- Welcome Banner -->
    <section class="welcome-banner">
      <div class="banner-content">
        <h1>Pusat Manajemen Informasi Humas</h1>
        <p>Selamat bekerja! Gunakan menu navigasi di samping untuk mengelola seluruh data Berita Online, Kliping, dan Dokumentasi Kegiatan UIN.</p>
      </div>
      <div class="banner-ill">🚀</div>
    </section>

    <!-- Stats Grid -->
    <section class="stats-grid">
      <div class="stat-card" v-if="authStore.user?.permissions?.includes('news')">
        <div class="stat-icon news-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Total Berita Online</span>
          <span class="stat-value">{{ stats.news_online }}</span>
        </div>
      </div>

      <div class="stat-card" v-if="authStore.user?.permissions?.includes('clippings')">
        <div class="stat-icon clipping-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Total Kliping</span>
          <span class="stat-value">{{ stats.clippings }}</span>
        </div>
      </div>

      <div class="stat-card" v-if="authStore.user?.permissions?.includes('documentation')">
        <div class="stat-icon doc-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Dokumentasi</span>
          <span class="stat-value">{{ stats.documentation }}</span>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

const stats = ref({
  news_online: 0,
  clippings: 0,
  documentation: 0
})

onMounted(async () => {
  try {
    const res = await axios.get('/api/admin/dashboard-stats')
    if (res.data.status === 'success') {
      stats.value = {
        news_online: res.data.data.news.total,
        clippings: res.data.data.clippings.total,
        documentation: res.data.data.documentation.total
      }
    }
  } catch (err) {
    console.error('Gagal mengambil data statistik:', err)
  }
})
</script>

<style scoped>
/* Welcome Banner */
.welcome-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #1e293b, #0f172a);
  border-radius: 20px;
  padding: 32px 40px;
  color: white;
  margin-bottom: 36px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.banner-content h1 {
  font-size: 26px;
  font-weight: 800;
  margin-bottom: 8px;
}

.banner-content p {
  font-size: 14px;
  color: #94a3b8;
  max-width: 600px;
  line-height: 1.5;
}

.banner-ill {
  font-size: 56px;
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
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
  transition: all 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
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

.news-icon { background: #3b82f6; }
.clipping-icon { background: #8b5cf6; }
.doc-icon { background: #10b981; }

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
  color: #0f172a;
  margin-top: 4px;
}

@media (max-width: 1024px) {
  .welcome-banner { padding: 24px 28px; margin-bottom: 24px; }
  .banner-content h1 { font-size: 20px; }
}

@media (max-width: 640px) {
  .welcome-banner {
    flex-direction: column;
    align-items: flex-start;
    padding: 20px;
    border-radius: 16px;
    gap: 12px;
    margin-bottom: 20px;
  }
  .banner-content h1 { font-size: 18px; }
  .banner-content p { font-size: 13px; }
  .banner-ill { display: none; }
  .stats-grid { grid-template-columns: 1fr; gap: 12px; }
  .stat-card { padding: 18px; border-radius: 14px; }
}
</style>
