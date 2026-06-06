import { defineStore } from 'pinia'
import axios from 'axios'

// Axios Request Interceptor untuk menangani base URL dinamis pada subfolder cPanel
axios.interceptors.request.use((config) => {
  if (config.url && config.url.startsWith('/api')) {
    // Lewati jika dalam mode development (menggunakan proxy dev server)
    if (import.meta.env.DEV) {
      return config
    }
    
    // Cari prefix path di browser URL dengan aman (mengabaikan client-side routes)
    const pathname = window.location.pathname
    let prefix = ''
    
    const routeKeywords = [
      '/dashboard',
      '/login',
      '/berita',
      '/kliping',
      '/dokumentasi',
      '/rss',
      '/pencarian'
    ]
    
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
    
    config.url = prefix + config.url
  }
  return config
}, (error) => {
  return Promise.reject(error)
})

// Axios Response Interceptor untuk menangani error 401 (Unauthorized / Token Kadaluarsa) dan 403 (Forbidden)
axios.interceptors.response.use((response) => {
  return response
}, async (error) => {
  if (error.response && error.response.status === 401) {
    localStorage.removeItem('hh_token')
    localStorage.removeItem('hh_user')
    delete axios.defaults.headers.common['Authorization']
    window.location.href = '/login'
  } else if (error.response && error.response.status === 403) {
    // Jika 403 Forbidden, perbarui permissions di frontend secara background
    try {
      // Hindari loop tak terbatas jika request ke profile sendiri yang 403
      if (error.config && error.config.url && !error.config.url.includes('/api/admin/profile')) {
        const authStore = useAuthStore()
        const res = await authStore.refreshProfile()
        if (res.success && res.data) {
          // Redirect ke dashboard jika route saat ini memerlukan permission yang sekarang tidak dimiliki
          try {
            const routerModule = await import('../router')
            const router = routerModule.default
            const currentRoute = router.currentRoute.value
            if (currentRoute.meta && currentRoute.meta.requiredPermission) {
              const requiredPermission = currentRoute.meta.requiredPermission
              if (!res.data.permissions.includes(requiredPermission)) {
                router.push('/dashboard')
              }
            }
          } catch (routerErr) {
            console.error('Gagal memproses redirect setelah 403:', routerErr)
          }
        }
      }
    } catch (e) {
      console.error('Gagal me-refresh profil pasca 403:', e)
    }
  }
  return Promise.reject(error)
})


export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('hh_token') || null,
    user: JSON.parse(localStorage.getItem('hh_user')) || null,
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    currentUser: (state) => state.user
  },

  actions: {
    async login(username, password) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post('/api/auth/login', { username, password })
        const { token, user } = response.data

        // Simpan ke state
        this.token = token
        this.user = user

        // Simpan ke localStorage agar tidak ter-reset saat di-refresh
        localStorage.setItem('hh_token', token)
        localStorage.setItem('hh_user', JSON.stringify(user))

        // Set default header Authorization untuk Axios
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

        return { success: true }
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal login. Periksa koneksi internet Anda.'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    logout() {
      // Reset state
      this.token = null
      this.user = null
      
      // Hapus dari localStorage
      localStorage.removeItem('hh_token')
      localStorage.removeItem('hh_user')

      // Hapus header Authorization
      delete axios.defaults.headers.common['Authorization']
    },

    async refreshProfile() {
      try {
        const res = await axios.get('/api/admin/profile')
        if (res.data.status === 'success') {
          this.user = res.data.data
          localStorage.setItem('hh_user', JSON.stringify(res.data.data))
          return { success: true, data: res.data.data }
        }
        return { success: false }
      } catch (err) {
        console.error('Gagal me-refresh profil dari server:', err)
        if (err.response && err.response.status === 401) {
          this.logout()
        }
        return { success: false, error: err }
      }
    },

    initializeAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }
    }
  }
})
