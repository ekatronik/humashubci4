<template>
  <div class="login-wrapper">
    <!-- Background dynamic mesh blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="login-card">
      <!-- Brand Logo / Header -->
      <div class="brand-header">
        <div class="brand-logo">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
          </svg>
        </div>
        <h1 class="brand-title">Humas Hub</h1>
        <p class="brand-sub">Sistem Manajemen Berita & Dokumentasi</p>
      </div>

      <!-- Error Message Banner -->
      <transition name="fade">
        <div v-if="errorMsg" class="alert-banner">
          <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <span class="alert-text">{{ errorMsg }}</span>
        </div>
      </transition>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-container">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            <input 
              v-model="username" 
              type="text" 
              id="username" 
              placeholder="Masukkan username" 
              required 
              :disabled="authStore.loading"
            />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-container">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <input 
              v-model="password" 
              type="password" 
              id="password" 
              placeholder="••••••••" 
              required 
              :disabled="authStore.loading"
            />
          </div>
        </div>

        <button type="submit" class="btn-submit" :disabled="authStore.loading">
          <span v-if="!authStore.loading">Masuk Sekarang</span>
          <div v-else class="spinner"></div>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const errorMsg = ref('')

const handleLogin = async () => {
  errorMsg.value = ''
  
  if (!username.value || !password.value) {
    errorMsg.value = 'Silakan isi username dan password Anda.'
    return
  }

  const result = await authStore.login(username.value, password.value)

  if (result.success) {
    router.push('/dashboard')
  } else {
    errorMsg.value = result.error
  }
}
</script>

<style scoped>
.login-wrapper {
  position: relative;
  min-height: 100vh;
  width: 100vw;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0f172a; /* Slate 900 */
  overflow: hidden;
  font-family: 'Inter', sans-serif;
  color: #f8fafc;
}

/* Background blob animations */
.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  opacity: 0.45;
  z-index: 0;
  animation: float 20s infinite alternate ease-in-out;
}
.blob-1 {
  top: -10%;
  left: -10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, #22c55e 0%, #15803d 100%);
  animation-duration: 25s;
}
.blob-2 {
  bottom: -15%;
  right: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, #10b981 0%, #064e3b 100%);
  animation-duration: 22s;
  animation-delay: 2s;
}
.blob-3 {
  top: 40%;
  left: 50%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, #059669 0%, #111827 100%);
  animation-duration: 18s;
  animation-delay: 5s;
}

@keyframes float {
  0% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(60px, 40px) scale(1.1); }
  100% { transform: translate(-40px, -60px) scale(0.9); }
}

/* Glassmorphism Card */
.login-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 440px;
  padding: 48px 40px;
  background: rgba(30, 41, 59, 0.7); /* Slate 800 with opacity */
  border: 1.5px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
  transition: all 0.3s ease;
}

.login-card:hover {
  border-color: rgba(34, 197, 94, 0.25);
  box-shadow: 0 20px 60px rgba(34, 197, 94, 0.1);
}

/* Brand Header */
.brand-header {
  text-align: center;
  margin-bottom: 32px;
}

.brand-logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white;
  margin-bottom: 16px;
  box-shadow: 0 8px 24px rgba(34, 197, 94, 0.4);
}

.brand-logo svg {
  width: 32px;
  height: 32px;
}

.brand-title {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.5px;
  background: linear-gradient(to right, #ffffff, #e2e8f0);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 6px;
}

.brand-sub {
  font-size: 14px;
  color: #94a3b8;
}

/* Alert Banner */
.alert-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: rgba(239, 68, 68, 0.15); /* Red 500 */
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 12px;
  margin-bottom: 24px;
  color: #fca5a5;
  font-size: 13.5px;
  font-weight: 500;
}

.alert-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  color: #ef4444;
}

/* Form Styling */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 12.5px;
  font-weight: 600;
  color: #cbd5e1;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 16px;
  width: 20px;
  height: 20px;
  color: #64748b;
  transition: all 0.2s;
}

.input-container input {
  width: 100%;
  height: 52px;
  padding: 0 16px 0 48px;
  background: rgba(15, 23, 42, 0.6);
  border: 1.5px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  font-family: inherit;
  font-size: 14.5px;
  color: white;
  outline: none;
  transition: all 0.2s;
}

.input-container input:focus {
  border-color: #22c55e;
  background: rgba(15, 23, 42, 0.9);
  box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
}

.input-container input:focus + .input-icon {
  color: #22c55e;
}

/* Button Submit */
.btn-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 54px;
  border-radius: 14px;
  border: none;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(34, 197, 94, 0.35);
  transition: all 0.2s;
  margin-top: 10px;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 24px rgba(34, 197, 94, 0.45);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(1px);
}

.btn-submit:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* Loading Spinner */
.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s infinite linear;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@media (max-width: 480px) {
  .login-card {
    padding: 36px 24px;
    border: none;
    background: transparent;
    backdrop-filter: none;
    box-shadow: none;
  }
  .login-card:hover {
    box-shadow: none;
  }
}
</style>
