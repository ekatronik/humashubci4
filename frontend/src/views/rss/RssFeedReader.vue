<template>
  <div class="rss-reader-page">
    <!-- Filters & Search Toolbar -->
    <div class="toolbar-section">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="search-icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
        </svg>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari artikel sindikasi berita..." 
          class="search-input"
          @input="handleSearchInput"
        >
      </div>

      <div class="filter-wrapper">
        <select v-model="selectedSourceId" @change="fetchItems(1)" class="source-select">
          <option value="">Semua Asal Website</option>
          <option v-for="src in activeSources" :key="src.id" :value="src.id">
            {{ src.site_name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-box">
      <div class="loader"></div>
      <span>Memuat artikel berita terbaru...</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="items.length === 0" class="empty-box">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
      </svg>
      <p>Tidak ada artikel berita yang cocok dengan kriteria pencarian.</p>
    </div>

    <!-- Grid Card Articles -->
    <div v-else class="articles-grid">
      <article v-for="item in items" :key="item.id" class="article-card">
        <!-- Thumbnail Cover -->
        <div class="card-cover">
          <img 
            v-if="item.image_url" 
            :src="item.image_url" 
            :alt="item.title"
            class="cover-img"
            @error="handleImageError"
          >
          <div v-else class="cover-placeholder" :class="getGradientClass(item.source_id)">
            <span class="placeholder-initials">{{ getInitials(item.site_name) }}</span>
            <span class="placeholder-icon">RSS Feed</span>
          </div>
          
          <!-- Source Badge on Image -->
          <span :class="['source-badge', getBadgeClass(item.source_id)]">
            {{ item.site_name }}
          </span>
        </div>

        <!-- Card Body -->
        <div class="card-body">
          <div class="meta-row">
            <span class="meta-date">{{ timeAgo(item.pub_date) }}</span>
            <span class="meta-dot"></span>
            <span class="meta-author" :title="'Ditulis oleh: ' + item.creator">Oleh: {{ item.creator }}</span>
          </div>

          <h3 class="article-title" :title="item.title">
            <a :href="item.link" target="_blank">{{ item.title }}</a>
          </h3>

          <p class="article-excerpt">
            {{ item.description }}
          </p>

          <div class="card-footer">
            <a :href="item.link" target="_blank" class="btn-read-more">
              Buka Berita Asli
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
            </a>
          </div>
        </div>
      </article>
    </div>

    <!-- Pagination -->
    <div class="pagination-section" v-if="totalPages > 1">
      <button 
        class="btn-pag" 
        :disabled="currentPage === 1" 
        @click="fetchItems(currentPage - 1)"
      >
        Sebelumnya
      </button>

      <div class="pag-pages">
        <button 
          v-for="p in totalPages" 
          :key="p" 
          :class="['page-num', { active: currentPage === p }]"
          @click="fetchItems(p)"
        >
          {{ p }}
        </button>
      </div>

      <button 
        class="btn-pag" 
        :disabled="currentPage === totalPages" 
        @click="fetchItems(currentPage + 1)"
      >
        Selanjutnya
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// ─── STATE ────────────────────────────────────────────────
const items            = ref([])
const activeSources    = ref([])
const loading          = ref(true)
const searchQuery      = ref('')
const selectedSourceId = ref('')
const currentPage      = ref(1)
const totalPages       = ref(1)
const totalItems       = ref(0)
const itemsPerPage     = 12

// Debounce timer for search
let searchDebounceTimer = null

// ─── UTILITIES ────────────────────────────────────────────
const timeAgo = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    const now = new Date()
    const diffMs = now - date
    const diffSec = Math.floor(diffMs / 1000)
    const diffMin = Math.floor(diffSec / 60)
    const diffHr = Math.floor(diffMin / 60)
    const diffDays = Math.floor(diffHr / 24)

    if (diffSec < 60) return 'Baru saja'
    if (diffMin < 60) return `${diffMin} menit yang lalu`
    if (diffHr < 24) return `${diffHr} jam yang lalu`
    if (diffDays === 1) return 'Kemarin'
    if (diffDays < 7) return `${diffDays} hari yang lalu`
    
    return date.toLocaleDateString('id-ID', { 
      day: 'numeric', 
      month: 'short', 
      year: 'numeric' 
    })
  } catch (e) {
    return dateString
  }
}

const getInitials = (siteName) => {
  if (!siteName) return 'RSS'
  return siteName.split(' ')
                 .map(word => word[0])
                 .join('')
                 .substring(0, 3)
                 .toUpperCase()
}

// Map source id to dynamic CSS colors
const getBadgeClass = (sourceId) => {
  const classes = [
    'badge-indigo', 'badge-violet', 'badge-emerald', 'badge-amber', 
    'badge-rose', 'badge-blue', 'badge-teal', 'badge-fuchsia'
  ]
  return classes[sourceId % classes.length]
}

const getGradientClass = (sourceId) => {
  const classes = [
    'grad-indigo', 'grad-purple', 'grad-teal', 'grad-amber', 
    'grad-rose', 'grad-blue', 'grad-emerald', 'grad-fuchsia'
  ]
  return classes[sourceId % classes.length]
}

// ─── IMAGE HANDLER ────────────────────────────────────────
const handleImageError = (e) => {
  // If image fails to load, replace it with a fallback placeholder container
  const imgElement = e.target
  const cardCover = imgElement.parentElement
  
  imgElement.style.display = 'none'
  
  const placeholder = document.createElement('div')
  placeholder.className = 'cover-placeholder fallback-cover'
  placeholder.innerHTML = '<span class="placeholder-icon">Visual Blocked</span>'
  cardCover.appendChild(placeholder)
}

// ─── FETCH ARTICLES ───────────────────────────────────────
const fetchItems = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const params = {
      search: searchQuery.value.trim(),
      source_id: selectedSourceId.value,
      page: page,
      limit: itemsPerPage
    }
    const res = await axios.get('/api/admin/rss/items', { params })
    const resData = res.data.data || {}
    items.value = resData.items || []
    totalPages.value = resData.total_pages || 1
    totalItems.value = resData.total_items || 0
  } catch (e) {
    console.error('Gagal memuat artikel RSS', e)
  } finally {
    loading.value = false
  }
}

const fetchActiveSources = async () => {
  try {
    const res = await axios.get('/api/admin/rss/sources')
    // Hanya tampilkan di dropdown jika berstatus aktif
    activeSources.value = (res.data.data || []).filter(s => Number(s.is_active) === 1)
  } catch (e) {
    console.error(e)
  }
}

// ─── SEARCH INPUT DEBOUNCE ───────────────────────────────
const handleSearchInput = () => {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    fetchItems(1)
  }, 400)
}

// ─── INIT ─────────────────────────────────────────────────
onMounted(() => {
  fetchActiveSources()
  fetchItems(1)
})
</script>

<style scoped>
.rss-reader-page {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Toolbar */
.toolbar-section {
  display: flex;
  gap: 16px;
  align-items: center;
  background: white;
  padding: 16px 20px;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 16px rgba(0,0,0,0.01);
}

.search-wrapper {
  position: relative;
  flex: 1;
  min-width: 0;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #94a3b8;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 14px 10px 42px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  font-size: 13.5px;
  outline: none;
  font-family: inherit;
  color: #0f172a;
  background: white;
  transition: all 0.2s;
}

.search-input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.08);
}

.filter-wrapper {
  flex-shrink: 0;
}

.source-select {
  padding: 10px 32px 10px 14px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  font-size: 13.5px;
  font-family: inherit;
  outline: none;
  color: #0f172a;
  background: white url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") no-repeat right 10px center;
  background-size: 20px;
  cursor: pointer;
  appearance: none;
  min-width: 200px;
  transition: border-color 0.2s;
}

.source-select:focus {
  border-color: #4f46e5;
}

/* Articles Grid */
.articles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
  gap: 24px;
}

.article-card {
  background: white;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 20px rgba(0,0,0,0.015);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.article-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(79, 70, 229, 0.08);
  border-color: #dbeafe;
}

.card-cover {
  position: relative;
  width: 100%;
  padding-top: 56.25%; /* 16:9 Aspect Ratio */
  overflow: hidden;
  background: #f1f5f9;
}

.cover-img {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.article-card:hover .cover-img {
  transform: scale(1.04);
}

/* Cover Placeholders */
.cover-placeholder {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  gap: 8px;
}

.placeholder-initials {
  font-size: 24px;
  font-weight: 900;
  letter-spacing: 1px;
}

.placeholder-icon {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  background: rgba(0,0,0,0.15);
  padding: 3px 8px;
  border-radius: 4px;
}

/* Fallback Cover Image error */
.fallback-cover {
  background: linear-gradient(135deg, #64748b, #475569) !important;
}

/* Curated Gradients for Placeholders */
.grad-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.grad-purple { background: linear-gradient(135deg, #a855f7, #7c3aed); }
.grad-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }
.grad-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
.grad-rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }
.grad-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.grad-emerald { background: linear-gradient(135deg, #10b981, #059669); }
.grad-fuchsia { background: linear-gradient(135deg, #d946ef, #c026d3); }

/* Source badges */
.source-badge {
  position: absolute;
  top: 14px;
  left: 14px;
  z-index: 10;
  font-size: 10.5px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 6px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.12);
  color: white;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.badge-indigo { background: #6366f1; }
.badge-violet { background: #8b5cf6; }
.badge-emerald { background: #10b981; }
.badge-amber { background: #f59e0b; }
.badge-rose { background: #f43f5e; }
.badge-blue { background: #3b82f6; }
.badge-teal { background: #14b8a6; }
.badge-fuchsia { background: #d946ef; }

/* Card Body */
.card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.meta-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11.5px;
  color: #64748b;
  margin-bottom: 10px;
  font-weight: 500;
}

.meta-dot {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: #cbd5e1;
}

.meta-author {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 140px;
}

.article-title {
  font-size: 15px;
  font-weight: 800;
  line-height: 1.45;
  margin: 0 0 10px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 42px;
}

.article-title a {
  color: #0f172a;
  text-decoration: none;
  transition: color 0.15s;
}

.article-title a:hover {
  color: #4f46e5;
}

.article-card:hover .article-title a {
  color: #4f46e5;
}

.article-excerpt {
  font-size: 12.5px;
  color: #64748b;
  line-height: 1.6;
  margin: 0 0 20px 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}

.card-footer {
  border-top: 1px solid #f1f5f9;
  padding-top: 14px;
  margin-top: auto;
}

.btn-read-more {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
  color: #4f46e5;
  text-decoration: none;
  transition: gap 0.2s;
}

.btn-read-more:hover {
  gap: 10px;
  color: #3730a3;
}

.btn-read-more svg {
  width: 14px;
  height: 14px;
}

/* Pagination */
.pagination-section {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 16px;
}

.btn-pag {
  padding: 8px 16px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #475569;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-pag:hover:not(:disabled) {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #94a3b8;
}

.btn-pag:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pag-pages {
  display: flex;
  gap: 6px;
}

.page-num {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.page-num:hover {
  border-color: #cbd5e1;
  color: #0f172a;
}

.page-num.active {
  background: #0f172a;
  color: white;
  border-color: #0f172a;
}

/* Loading Box / Empty Box */
.loading-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 24px;
  color: #94a3b8;
  gap: 12px;
  font-size: 13.5px;
}

.loader {
  width: 28px;
  height: 28px;
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
  padding: 80px 24px;
  color: #94a3b8;
  gap: 10px;
}

.empty-box svg {
  width: 48px;
  height: 48px;
  opacity: 0.4;
}

.empty-box p {
  font-size: 14px;
  margin: 0;
}

@media (max-width: 640px) {
  .toolbar-section {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
    padding: 14px;
  }
  .source-select {
    width: 100%;
    min-width: auto;
  }
  .articles-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}
</style>
