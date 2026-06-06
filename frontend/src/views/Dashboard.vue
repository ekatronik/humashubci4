<template>
  <div class="dashboard-layout">
    <!-- Backdrop overlay for mobile sidebar -->
    <div class="sidebar-backdrop" v-if="mobileSidebarOpen" @click="mobileSidebarOpen = false"></div>

    <!-- ─── SIDEBAR ──────────────────────────────────────────── -->
    <aside :class="['sidebar', { collapsed: sidebarCollapsed, 'mobile-open': mobileSidebarOpen }]">
      <div class="brand">
        <div class="brand-icon" :class="{ 'has-img': settings.app_logo }">
          <img v-if="settings.app_logo" :src="getFileUrl(settings.app_logo)" alt="Logo" class="sidebar-logo-img">
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
          </svg>
        </div>
        <span class="brand-name" v-show="!sidebarCollapsed">{{ settings.app_name || 'Humas Hub' }}</span>
        <button class="btn-collapse" @click="sidebarCollapsed = !sidebarCollapsed" title="Perkecil sidebar">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
          </svg>
        </button>
      </div>

      <nav class="nav-menu">
        <!-- ── MENU UTAMA ───────────────────────────── -->
        <div class="nav-group-label">MENU UTAMA</div>
        <router-link to="/dashboard" class="nav-item" active-class="active" exact-active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
          </svg>
          <span>Overview</span>
        </router-link>

        <!-- ── BERITA ONLINE ────────────────────────── -->
        <div v-if="authStore.user?.permissions?.includes('news')" class="nav-accordion" :class="{ open: expandedModules.news && !sidebarCollapsed }">
          <div class="nav-accordion-header" @click="toggleModule('news')">
            <div class="nav-accordion-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
              </svg>
              <span>Berita Online</span>
            </div>
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
          <div class="nav-accordion-content" v-show="expandedModules.news && !sidebarCollapsed">
            <router-link to="/dashboard/news/overview" class="nav-sub-item" active-class="active">
              <span>Overview</span>
            </router-link>
            <router-link to="/dashboard/news" class="nav-sub-item" active-class="active" exact-active-class="active">
              <span>Daftar Berita</span>
            </router-link>
            <router-link to="/dashboard/news/reports" class="nav-sub-item" active-class="active">
              <span>Laporan</span>
            </router-link>
          </div>
        </div>

        <!-- ── KLIPING BERITA ──────────────────────── -->
        <div v-if="authStore.user?.permissions?.includes('clippings')" class="nav-accordion" :class="{ open: expandedModules.clippings && !sidebarCollapsed }">
          <div class="nav-accordion-header" @click="toggleModule('clippings')">
            <div class="nav-accordion-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
              </svg>
              <span>Kliping Berita</span>
            </div>
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
          <div class="nav-accordion-content" v-show="expandedModules.clippings && !sidebarCollapsed">
            <router-link to="/dashboard/clippings/overview" class="nav-sub-item" active-class="active">
              <span>Overview</span>
            </router-link>
            <router-link to="/dashboard/clippings" class="nav-sub-item" active-class="active" exact-active-class="active">
              <span>Daftar Kliping</span>
            </router-link>
            <router-link to="/dashboard/clippings/reports" class="nav-sub-item" active-class="active">
              <span>Laporan</span>
            </router-link>
          </div>
        </div>

        <!-- ── DOKUMENTASI ─────────────────────────── -->
        <div v-if="authStore.user?.permissions?.includes('documentation')" class="nav-accordion" :class="{ open: expandedModules.documentation && !sidebarCollapsed }">
          <div class="nav-accordion-header" @click="toggleModule('documentation')">
            <div class="nav-accordion-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
              </svg>
              <span>Dokumentasi</span>
            </div>
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
          <div class="nav-accordion-content" v-show="expandedModules.documentation && !sidebarCollapsed">
            <router-link to="/dashboard/documentation/overview" class="nav-sub-item" active-class="active">
              <span>Overview</span>
            </router-link>
            <router-link to="/dashboard/documentation" class="nav-sub-item" active-class="active" exact-active-class="active">
              <span>Daftar Dokumen</span>
            </router-link>
            <router-link to="/dashboard/documentation/reports" class="nav-sub-item" active-class="active">
              <span>Laporan</span>
            </router-link>
          </div>
        </div>

        <!-- ── AKREDITASI ──────────────────────────── -->
        <div v-if="authStore.user?.permissions?.includes('accreditation')" class="nav-accordion" :class="{ open: expandedModules.accreditation && !sidebarCollapsed }">
          <div class="nav-accordion-header" @click="toggleModule('accreditation')">
            <div class="nav-accordion-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
              </svg>
              <span>Akreditasi</span>
            </div>
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
          <div class="nav-accordion-content" v-show="expandedModules.accreditation && !sidebarCollapsed">
            <router-link to="/dashboard/accreditation/overview" class="nav-sub-item" active-class="active">
              <span>Overview</span>
            </router-link>
            <router-link to="/dashboard/accreditation/campus" class="nav-sub-item" active-class="active">
              <span>Akreditasi Kampus</span>
            </router-link>
            <router-link to="/dashboard/accreditation/faculties" class="nav-sub-item" active-class="active">
              <span>Data Fakultas</span>
            </router-link>
            <router-link to="/dashboard/accreditation/study-programs" class="nav-sub-item" active-class="active">
              <span>Daftar Prodi</span>
            </router-link>
            <router-link to="/dashboard/accreditation/reports" class="nav-sub-item" active-class="active">
              <span>Laporan</span>
            </router-link>
          </div>
        </div>

        <!-- ── KHUTBAH JUMAT ───────────────────────── -->
        <div v-if="authStore.user?.permissions?.includes('khutbah')" class="nav-accordion" :class="{ open: expandedModules.khutbah && !sidebarCollapsed }">
          <div class="nav-accordion-header" @click="toggleModule('khutbah')">
            <div class="nav-accordion-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.339a.75.75 0 0 0-.292-.588L12 4.257l-8.208 5.494a.75.75 0 0 0-.292.588V21h17.083Z" />
              </svg>
              <span>Khutbah Jumat</span>
            </div>
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
          <div class="nav-accordion-content" v-show="expandedModules.khutbah && !sidebarCollapsed">
            <router-link to="/dashboard/khutbah/fathun-qarib" class="nav-sub-item" active-class="active">
              <span>Masjid Fathun Qarib</span>
            </router-link>
            <router-link to="/dashboard/khutbah/other-mosques" class="nav-sub-item" active-class="active">
              <span>Mesjid Lain</span>
            </router-link>
          </div>
        </div>

        <!-- ── SINDIKASI RSS ───────────────────────── -->
        <div v-if="authStore.user?.permissions?.includes('rss')" class="nav-accordion" :class="{ open: expandedModules.rss && !sidebarCollapsed }">
          <div class="nav-accordion-header" @click="toggleModule('rss')">
            <div class="nav-accordion-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 19.5v-.75a7.5 7.5 0 0 0-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
              </svg>
              <span>Sindikasi RSS</span>
            </div>
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
          <div class="nav-accordion-content" v-show="expandedModules.rss && !sidebarCollapsed">
            <router-link to="/dashboard/rss-feed" class="nav-sub-item" active-class="active">
              <span>Web Feed Reader</span>
            </router-link>
            <router-link to="/dashboard/rss-manager" class="nav-sub-item" active-class="active">
              <span>Pengaturan RSS</span>
            </router-link>
          </div>
        </div>

        <!-- ── SISTEM ───────────────────────────────── -->
        <div v-if="authStore.user?.permissions?.some(p => ['taxonomy', 'system', 'users', 'activity'].includes(p))" class="nav-group-label">SISTEM</div>
        <router-link v-if="authStore.user?.permissions?.includes('taxonomy')" to="/dashboard/taxonomy" class="nav-item" active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
          </svg>
          <span>Referensi Data</span>
        </router-link>
        <router-link v-if="authStore.user?.permissions?.includes('system')" to="/dashboard/system-manager" class="nav-item" active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
          </svg>
          <span>Update & Backup</span>
        </router-link>
        <router-link v-if="authStore.user?.permissions?.includes('users')" to="/dashboard/users" class="nav-item" active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
          <span>Manajemen User</span>
        </router-link>
        <router-link v-if="authStore.user?.permissions?.includes('activity')" to="/dashboard/activity-logs" class="nav-item" active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375M9 18h3.375m1.875-12h7.5M12 3h7.5M12 21h7.5M3 5.625c0-.621.504-1.125 1.125-1.125h3.375c.621 0 1.125.504 1.125 1.125v12.75c0 .621-.504 1.125-1.125 1.125H4.125C3.504 19.5 3 18.996 3 18.375V5.625Z" />
          </svg>
          <span>Log Audit</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="user-mini">
          <div class="user-mini-avatar">{{ userInitial }}</div>
          <div class="user-mini-info">
            <span class="user-mini-name">{{ authStore.user?.full_name }}</span>
            <span class="user-mini-role">{{ roleName }}</span>
          </div>
        </div>
        <button @click="handleLogout" class="btn-logout" title="Keluar">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
          </svg>
        </button>
      </div>
    </aside>

    <!-- ─── MAIN CONTENT ─────────────────────────────────────── -->
    <main class="main-content">
      <!-- Top Navbar -->
      <header class="top-nav">
        <!-- Hamburger Menu Button for Mobile -->
        <button class="btn-hamburger" @click="mobileSidebarOpen = !mobileSidebarOpen" title="Buka Menu">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>

        <div class="page-title">
          <h2>{{ pageTitle }}</h2>
          <p>Selamat datang kembali, <strong>{{ authStore.user?.full_name }}</strong></p>
        </div>
        <div class="top-nav-right">
          <div class="breadcrumb-tag" v-if="moduleBadge">
            <span :class="['module-dot', moduleBadge.color]"></span>
            {{ moduleBadge.label }}
          </div>
          <div class="user-avatar">{{ userInitial }}</div>
        </div>
      </header>

      <!-- Dynamic Router Outlet -->
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const router    = useRouter()
const route     = useRoute()
const authStore = useAuthStore()

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

// Get File/Upload URL with dev/prod environment awareness
const getFileUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http') || path.startsWith('blob:') || path.startsWith('data:')) {
    return path
  }
  
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
  
  const cleanPath = path.startsWith('/') ? path.substring(1) : path
  return `${apiBase}/${cleanPath}`
}

// App settings
const settings = ref({
  app_name: '',
  app_logo: ''
})

const fetchSettings = async () => {
  try {
    const res = await axios.get('/api/public/settings')
    if (res.data.status === 'success') {
      settings.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil pengaturan publik:', err)
  }
}

const expandedModules = ref({
  news: false,
  clippings: false,
  documentation: false,
  rss: false,
  accreditation: false,
  khutbah: false
})

const toggleModule = (moduleName) => {
  if (sidebarCollapsed.value) {
    sidebarCollapsed.value = false
  }
  expandedModules.value[moduleName] = !expandedModules.value[moduleName]
}

const autoExpandActiveModule = () => {
  const path = route.path
  if (path.includes('/news')) {
    expandedModules.value.news = true
  } else if (path.includes('/clippings')) {
    expandedModules.value.clippings = true
  } else if (path.includes('/documentation')) {
    expandedModules.value.documentation = true
  } else if (path.includes('/rss')) {
    expandedModules.value.rss = true
  } else if (path.includes('/accreditation')) {
    expandedModules.value.accreditation = true
  } else if (path.includes('/khutbah')) {
    expandedModules.value.khutbah = true
  }
}

onMounted(async () => {
  fetchSettings()
  autoExpandActiveModule()
  
  // Ambil profil terbaru dari server untuk sinkronisasi permissions
  if (authStore.isAuthenticated) {
    await authStore.refreshProfile()
  }
})

watch(() => route.path, () => {
  autoExpandActiveModule()
  mobileSidebarOpen.value = false
})

const userInitial = computed(() => {
  const name = authStore.user?.full_name || ''
  return name.charAt(0).toUpperCase()
})

const roleName = computed(() => {
  const roleId = authStore.user?.role_id
  if (roleId === 1) return 'Super Admin'
  if (roleId === 2) return 'Pranata Humas'
  if (roleId === 3) return 'Operator'
  return 'User'
})

const pageTitles = {
  'DashboardHome':    'Overview Dashboard',
  'NewsDashboard':    'Dashboard Berita Online',
  'NewsList':         'Daftar Berita Online',
  'NewsReports':      'Laporan Berita Online',
  'ClippingDashboard':'Dashboard Kliping Berita',
  'ClippingList':     'Daftar Kliping Berita',
  'ClippingReports':  'Laporan Kliping Berita',
  'ClippingView':     'Detail Kliping Berita',
  'DocDashboard':     'Dashboard Dokumentasi',
  'DocumentationList':'Daftar Dokumentasi Kegiatan',
  'DocReports':       'Laporan Dokumentasi',
  'TaxonomyManager':  'Referensi Data',
  'UserManager':      'Manajemen User',
  'ActivityList':     'Log Aktivitas & Audit',
  'RssFeedReader':    'Web Feed Reader RSS',
  'RssManager':       'Pengaturan & Sumber RSS',
  'SystemManager':    'Manajemen & Update Sistem',
  'AccreditationOverview': 'Overview Akreditasi',
  'CampusAccreditation':   'Akreditasi Kampus / Institusi',
  'FacultyManager':        'Manajemen Data Fakultas',
  'ProdiManager':          'Manajemen Program Studi',
  'AccreditationReports':  'Laporan Analisis Akreditasi',
  'FathunQaribManager':    'Jadwal Khatib Masjid Fathun Qarib',
  'OtherMosquesManager':   'Jadwal Khutbah Mesjid Lain',
}
const pageTitle = computed(() => pageTitles[route.name] || 'Humas Hub')

// ── Badge modul di top-nav ──────────────────────────────────
const moduleBadgeMap = {
  'NewsDashboard':    { label: 'Berita Online',   color: 'dot-blue' },
  'NewsList':         { label: 'Berita Online',   color: 'dot-blue' },
  'NewsReports':      { label: 'Berita Online',   color: 'dot-blue' },
  'ClippingDashboard':{ label: 'Kliping Berita',  color: 'dot-violet' },
  'ClippingList':     { label: 'Kliping Berita',  color: 'dot-violet' },
  'ClippingReports':  { label: 'Kliping Berita',  color: 'dot-violet' },
  'ClippingView':     { label: 'Kliping Berita',  color: 'dot-violet' },
  'DocDashboard':     { label: 'Dokumentasi',     color: 'dot-emerald' },
  'DocumentationList':{ label: 'Dokumentasi',     color: 'dot-emerald' },
  'DocReports':       { label: 'Dokumentasi',     color: 'dot-emerald' },
  'TaxonomyManager':  { label: 'Referensi Data',  color: 'dot-amber' },
  'UserManager':      { label: 'Sistem',           color: 'dot-slate' },
  'ActivityList':     { label: 'Sistem',           color: 'dot-slate' },
  'RssFeedReader':    { label: 'Sindikasi RSS',    color: 'dot-amber' },
  'RssManager':       { label: 'Sindikasi RSS',    color: 'dot-amber' },
  'SystemManager':    { label: 'Sistem',           color: 'dot-slate' },
  'AccreditationOverview': { label: 'Akreditasi',   color: 'dot-emerald' },
  'CampusAccreditation':   { label: 'Akreditasi',   color: 'dot-emerald' },
  'FacultyManager':        { label: 'Akreditasi',   color: 'dot-emerald' },
  'ProdiManager':          { label: 'Akreditasi',   color: 'dot-emerald' },
  'AccreditationReports':  { label: 'Akreditasi',   color: 'dot-emerald' },
  'FathunQaribManager':    { label: 'Khutbah Jumat', color: 'dot-blue' },
  'OtherMosquesManager':   { label: 'Khutbah Jumat', color: 'dot-blue' },
}
const moduleBadge = computed(() => moduleBadgeMap[route.name] || null)

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  width: 100vw;
  background: #f1f5f9;
  color: #1e293b;
  font-family: 'Inter', sans-serif;
  overflow-x: hidden;
}

/* ─── SIDEBAR ───────────────────────────────────────────── */
.sidebar {
  width: 258px;
  background: #0f172a;
  color: #cbd5e1;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  border-right: 1px solid rgba(255,255,255,0.05);
  transition: width 0.3s ease;
  overflow: hidden;
}
.sidebar.collapsed { width: 72px; }
.sidebar.collapsed .brand-name,
.sidebar.collapsed span,
.sidebar.collapsed .nav-group-label,
.sidebar.collapsed .chevron-icon,
.sidebar.collapsed .user-mini-info { display: none; }
.sidebar.collapsed .brand { justify-content: center; padding: 20px 16px; }
.sidebar.collapsed .btn-collapse { display: none; }
.sidebar.collapsed .nav-item { justify-content: center; padding: 12px 0; }
.sidebar.collapsed .sidebar-footer { flex-direction: column; gap: 10px; align-items: center; }
.sidebar.collapsed .user-mini { flex-direction: column; }
.sidebar.collapsed .nav-accordion-header { justify-content: center; padding: 12px 0; }
.sidebar.collapsed .nav-accordion-content { display: none !important; }

/* Accordion */
.nav-accordion {
  display: flex;
  flex-direction: column;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 4px;
  background: rgba(255, 255, 255, 0.01);
  border: 1px solid rgba(255, 255, 255, 0.02);
}
.nav-accordion-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
  user-select: none;
  border-radius: 10px;
}
.nav-accordion-header:hover {
  color: #cbd5e1;
  background: rgba(255, 255, 255, 0.04);
}
.nav-accordion-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13.5px;
  font-weight: 500;
}
.nav-accordion-title svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  transition: all 0.18s;
}
.chevron-icon {
  width: 14px;
  height: 14px;
  transition: transform 0.2s ease;
  color: #475569;
}
.nav-accordion.open .chevron-icon {
  transform: rotate(180deg);
  color: #22c55e;
}
.nav-accordion.open .nav-accordion-header {
  color: #cbd5e1;
  background: rgba(255, 255, 255, 0.02);
}
.nav-accordion-content {
  display: flex;
  flex-direction: column;
  padding: 4px 6px 8px 12px;
  gap: 2px;
  border-left: 1.5px solid rgba(34, 197, 94, 0.15);
  margin-left: 20px;
  animation: slideDown 0.18s ease-out;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
.nav-sub-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  font-size: 12.5px;
  font-weight: 500;
  color: #64748b;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.15s;
}
.nav-sub-item:hover {
  color: #cbd5e1;
  background: rgba(255, 255, 255, 0.03);
}
.nav-sub-item.active {
  color: white;
  font-weight: 600;
  background: rgba(34, 197, 94, 0.12);
  border-left: 2px solid #22c55e;
  padding-left: 10px;
}

/* Brand */
.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  min-height: 72px;
}
.brand-icon {
  width: 36px; height: 36px; flex-shrink: 0;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}
.brand-icon svg { width: 20px; height: 20px; }
.brand-icon.has-img {
  background: transparent;
  padding: 0;
  overflow: hidden;
}
.sidebar-logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.brand-name { font-size: 18px; font-weight: 800; color: white; white-space: nowrap; letter-spacing: -0.5px; flex: 1; }
.btn-collapse {
  width: 28px; height: 28px; border-radius: 8px; border: none;
  background: rgba(255,255,255,0.05); color: #64748b;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.2s; flex-shrink: 0;
}
.btn-collapse svg { width: 14px; height: 14px; }
.btn-collapse:hover { background: rgba(255,255,255,0.1); color: white; }

/* Nav */
.nav-menu {
  display: flex; flex-direction: column; gap: 2px;
  padding: 16px 12px; flex: 1;
  overflow-y: auto; overflow-x: hidden;
}
.nav-menu::-webkit-scrollbar { width: 4px; }
.nav-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }

.nav-group-label {
  font-size: 9.5px; font-weight: 700; letter-spacing: 1.2px;
  color: #334155; padding: 14px 8px 4px;
  text-transform: uppercase; white-space: nowrap;
}

.nav-module-group {
  position: relative;
  margin-left: 4px;
  padding-left: 12px;
  border-left: 1.5px solid rgba(255,255,255,0.06);
  display: flex; flex-direction: column; gap: 2px;
  margin-bottom: 4px;
}

.nav-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 12px; border-radius: 10px;
  font-size: 13.5px; font-weight: 500; color: #64748b;
  text-decoration: none; transition: all 0.18s;
  white-space: nowrap; overflow: hidden;
}
.nav-item svg { width: 18px; height: 18px; flex-shrink: 0; transition: all 0.18s; }
.nav-item:hover { color: #cbd5e1; background: rgba(255,255,255,0.04); }
.nav-item.active {
  color: white; font-weight: 600;
  background: linear-gradient(to right, rgba(34,197,94,0.22), rgba(21,128,61,0.12));
  border-left: 2px solid #22c55e;
  padding-left: 10px;
}
.nav-item.active svg { color: #22c55e; }

/* Footer */
.sidebar-footer {
  padding: 16px 14px;
  border-top: 1px solid rgba(255,255,255,0.06);
  display: flex; align-items: center; gap: 10px;
}
.user-mini { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
.user-mini-avatar {
  width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white; font-size: 14px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.user-mini-info { display: flex; flex-direction: column; min-width: 0; }
.user-mini-name { font-size: 12.5px; font-weight: 700; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-mini-role { font-size: 10.5px; font-weight: 600; color: #22c55e; }
.btn-logout {
  width: 34px; height: 34px; border-radius: 10px; border: none;
  background: rgba(239,68,68,0.08); color: #f87171;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.2s; flex-shrink: 0;
}
.btn-logout svg { width: 16px; height: 16px; }
.btn-logout:hover { background: rgba(239,68,68,0.18); color: #ef4444; }

/* ─── MAIN CONTENT ──────────────────────────────────────── */
.main-content {
  flex: 1; display: flex; flex-direction: column;
  padding: 32px 40px; overflow-y: auto;
  min-width: 0;
}

/* Top Navbar */
.top-nav {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 32px;
}
.page-title h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
.page-title p  { font-size: 13px; color: #64748b; margin-top: 3px; }
.page-title p strong { color: #0f172a; }

.top-nav-right { display: flex; align-items: center; gap: 14px; }

.breadcrumb-tag {
  display: flex; align-items: center; gap: 8px;
  padding: 6px 14px; border-radius: 20px;
  background: white; border: 1px solid #e2e8f0;
  font-size: 12.5px; font-weight: 600; color: #475569;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.module-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.dot-blue   { background: #3b82f6; }
.dot-violet { background: #8b5cf6; }
.dot-emerald{ background: #10b981; }
.dot-amber  { background: #f59e0b; }
.dot-slate  { background: #64748b; }

.user-avatar {
  width: 40px; height: 40px; border-radius: 12px;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white; font-size: 15px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 12px rgba(34,197,94,0.25);
  flex-shrink: 0;
}

/* Backdrop overlay for mobile */
.sidebar-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(4px);
  z-index: 999;
}

/* Hamburger button */
.btn-hamburger {
  display: none;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #0f172a;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  transition: all 0.2s;
  flex-shrink: 0;
}
.btn-hamburger svg {
  width: 20px;
  height: 20px;
}
.btn-hamburger:hover {
  background: #f8fafc;
  color: #22c55e;
  border-color: #cbd5e1;
}

@media (max-width: 1024px) {
  .btn-hamburger { display: flex; }
  .top-nav { gap: 14px; justify-content: flex-start; margin-bottom: 24px; }
  .top-nav-right { margin-left: auto; }
  .main-content { padding: 24px 16px; }
  .page-title h2 { font-size: 18px; }
  .page-title p { display: none; }
  
  .sidebar {
    position: fixed;
    left: -258px;
    top: 0;
    bottom: 0;
    z-index: 1000;
    transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  }
  .sidebar.mobile-open {
    left: 0;
  }
}
</style>
