import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Inisialisasi token di Axios header jika sudah ada di localStorage sebelumnya
const authStore = useAuthStore(pinia)
authStore.initializeAuth()

app.mount('#app')
