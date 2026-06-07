import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    name: 'Portal',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/berita',
    name: 'PortalNews',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/kliping',
    name: 'PortalClippings',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/kliping/:slug',
    name: 'PortalClippingDetail',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/dokumentasi',
    name: 'PortalDocumentation',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/dokumentasi/:slug',
    name: 'PortalDocDetail',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/rss',
    name: 'PortalRss',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/pencarian',
    name: 'PortalSearch',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/akreditasi',
    name: 'PortalAccreditation',
    component: () => import('../views/Portal.vue')
  },
  {
    path: '/dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { requiresAuth: true },
    children: [
      // ── Halaman Utama ─────────────────────────────────────
      {
        path: '',
        name: 'DashboardHome',
        component: () => import('../views/DashboardHome.vue')
      },

      // ── Modul Berita Online ───────────────────────────────
      {
        path: 'news/overview',
        name: 'NewsDashboard',
        component: () => import('../views/news/NewsDashboard.vue'),
        meta: { requiredPermission: 'news' }
      },
      {
        path: 'news',
        name: 'NewsList',
        component: () => import('../views/news/NewsList.vue'),
        meta: { requiredPermission: 'news' }
      },
      {
        path: 'news/reports',
        name: 'NewsReports',
        component: () => import('../views/reports/ReportDashboard.vue'),
        props: { defaultModule: 'news' },
        meta: { requiredPermission: 'news' }
      },

      // ── Modul Kliping Berita ──────────────────────────────
      {
        path: 'clippings/overview',
        name: 'ClippingDashboard',
        component: () => import('../views/clippings/ClippingDashboard.vue'),
        meta: { requiredPermission: 'clippings' }
      },
      {
        path: 'clippings',
        name: 'ClippingList',
        component: () => import('../views/clippings/ClippingList.vue'),
        meta: { requiredPermission: 'clippings' }
      },
      {
        path: 'clippings/:id',
        name: 'ClippingView',
        component: () => import('../views/clippings/ClippingView.vue'),
        props: true,
        meta: { requiredPermission: 'clippings' }
      },
      {
        path: 'clippings/reports',
        name: 'ClippingReports',
        component: () => import('../views/reports/ReportDashboard.vue'),
        props: { defaultModule: 'clippings' },
        meta: { requiredPermission: 'clippings' }
      },

      // ── Modul Dokumentasi ─────────────────────────────────
      {
        path: 'documentation/overview',
        name: 'DocDashboard',
        component: () => import('../views/documentation/DocDashboard.vue'),
        meta: { requiredPermission: 'documentation' }
      },
      {
        path: 'documentation',
        name: 'DocumentationList',
        component: () => import('../views/documentation/DocumentationList.vue'),
        meta: { requiredPermission: 'documentation' }
      },
      {
        path: 'documentation/:id',
        name: 'DocumentationView',
        component: () => import('../views/documentation/DocumentationView.vue'),
        props: true,
        meta: { requiredPermission: 'documentation' }
      },
      {
        path: 'documentation/reports',
        name: 'DocReports',
        component: () => import('../views/reports/ReportDashboard.vue'),
        props: { defaultModule: 'documentation' },
        meta: { requiredPermission: 'documentation' }
      },

      // ── Referensi Data (Taxonomy) ─────────────────────────
      {
        path: 'taxonomy',
        name: 'TaxonomyManager',
        component: () => import('../views/taxonomy/TaxonomyManager.vue'),
        meta: { requiredPermission: 'taxonomy' }
      },

      // ── Sindikasi Berita RSS ──────────────────────────────
      {
        path: 'rss-feed',
        name: 'RssFeedReader',
        component: () => import('../views/rss/RssFeedReader.vue'),
        meta: { requiredPermission: 'rss' }
      },
      {
        path: 'rss-manager',
        name: 'RssManager',
        component: () => import('../views/rss/RssManager.vue'),
        meta: { requiredPermission: 'rss' }
      },

      // ── Sub-modul / Update & Backup ───────────────────────
      {
        path: 'system-manager',
        name: 'SystemManager',
        component: () => import('../views/system/SystemManager.vue'),
        meta: { requiredPermission: 'system' }
      },

      // ── Master Menu (App Launcher) ───────────────────────
      {
        path: 'master-menu',
        name: 'MenuManager',
        component: () => import('../views/system/MenuManager.vue'),
        meta: { requiredPermission: 'system' }
      },

      // ── Manajemen User ────────────────────────────────────
      {
        path: 'users',
        name: 'UserManager',
        component: () => import('../views/users/UserManager.vue'),
        meta: { requiredPermission: 'users' }
      },

      // ── Log Audit ─────────────────────────────────────────
      {
        path: 'activity-logs',
        name: 'ActivityList',
        component: () => import('../views/activity/ActivityList.vue'),
        meta: { requiredPermission: 'activity' }
      },

      // ── Modul Akreditasi (Prodi & Kampus) ─────────────────
      {
        path: 'accreditation/overview',
        name: 'AccreditationOverview',
        component: () => import('../views/accreditation/AccreditationOverview.vue'),
        meta: { requiredPermission: 'accreditation' }
      },
      {
        path: 'accreditation/campus',
        name: 'CampusAccreditation',
        component: () => import('../views/accreditation/CampusAccreditation.vue'),
        meta: { requiredPermission: 'accreditation' }
      },
      {
        path: 'accreditation/faculties',
        name: 'FacultyManager',
        component: () => import('../views/accreditation/FacultyManager.vue'),
        meta: { requiredPermission: 'accreditation' }
      },
      {
        path: 'accreditation/study-programs',
        name: 'ProdiManager',
        component: () => import('../views/accreditation/ProdiManager.vue'),
        meta: { requiredPermission: 'accreditation' }
      },
      {
        path: 'accreditation/reports',
        name: 'AccreditationReports',
        component: () => import('../views/accreditation/AccreditationReports.vue'),
        meta: { requiredPermission: 'accreditation' }
      },

      // ── Modul Khutbah Jumat ───────────────────────────────
      {
        path: 'khutbah/fathun-qarib',
        name: 'FathunQaribManager',
        component: () => import('../views/khutbah/FathunQaribManager.vue'),
        meta: { requiredPermission: 'khutbah' }
      },
      {
        path: 'khutbah/other-mosques',
        name: 'OtherMosquesManager',
        component: () => import('../views/khutbah/OtherMosquesManager.vue'),
        meta: { requiredPermission: 'khutbah' }
      },

      // ── Laporan Global (Legacy — redirect ke news) ─────────
      {
        path: 'reports',
        redirect: '/dashboard/news/reports'
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})
// Route guard untuk proteksi halaman admin dan pengecekan role permission secara real-time
router.beforeEach(async (to, from) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return '/login'
  } else if (to.meta.guest && authStore.isAuthenticated) {
    return '/dashboard'
  }

  // Sinkronisasikan profil dari server pada setiap perpindahan halaman terproteksi
  if (to.meta.requiresAuth && authStore.isAuthenticated) {
    await authStore.refreshProfile()
  }

  // Jika halaman membutuhkan permission tertentu, verifikasi dari data user paling mutakhir
  if (authStore.isAuthenticated) {
    const requiredPermission = to.meta.requiredPermission
    if (requiredPermission) {
      const userPermissions = authStore.user?.permissions || []
      if (!userPermissions.includes(requiredPermission)) {
        // Alihkan paksa ke Overview Dashboard jika tidak memiliki izin
        return '/dashboard'
      }
    }
  }
})

export default router
