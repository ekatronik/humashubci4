<template>
  <div class="user-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-left">
        <h2>Manajemen User & Hak Akses</h2>
        <p>Kelola data akun pengguna, status keaktifan, dan konfigurasi hak akses modul</p>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs-nav">
      <button 
        :class="['tab-btn', { active: activeTab === 'users' }]" 
        @click="activeTab = 'users'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="tab-icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
        Daftar Pengguna
      </button>
      <button 
        :class="['tab-btn', { active: activeTab === 'roles' }]" 
        @click="selectFirstRole"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="tab-icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
        </svg>
        Manajemen Hak Akses / Role
      </button>
    </div>

    <!-- Grid Layout: Tab Manajemen User -->
    <div v-if="activeTab === 'users'" class="panels-grid">
      <!-- PANEL KIRI: FORM ENTRI/EDIT -->
      <div class="panel-card form-panel">
        <div class="panel-header">
          <div class="panel-title-block">
            <div class="panel-icon user-form-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
              </svg>
            </div>
            <div>
              <h3>{{ editingUserId ? 'Edit Akun Pengguna' : 'Tambah Pengguna Baru' }}</h3>
              <span class="panel-count">{{ editingUserId ? 'Mengubah data user' : 'Registrasi user baru' }}</span>
            </div>
          </div>
        </div>

        <div class="add-form user-form">
          <div class="form-grid">
            <!-- Nama Lengkap -->
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input
                v-model="form.full_name"
                type="text"
                placeholder="Contoh: Ahmad Wijaya"
                class="text-input-full"
                :disabled="saving"
              />
            </div>

            <!-- Username -->
            <div class="form-group">
              <label class="form-label">Username</label>
              <input
                v-model="form.username"
                type="text"
                placeholder="Contoh: ahmad_w"
                class="text-input-full"
                :disabled="saving"
              />
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="form-label">
                Password 
                <span v-if="editingUserId" class="password-note">(Kosongkan jika tidak diubah)</span>
              </label>
              <input
                v-model="form.password"
                type="password"
                placeholder="Masukkan password..."
                class="text-input-full"
                :disabled="saving"
              />
            </div>

            <!-- Level / Role -->
            <div class="form-group">
              <label class="form-label">Level / Hak Akses</label>
              <select v-model="form.role_id" class="select-input-full" :disabled="saving">
                <option :value="null" disabled>Pilih Level...</option>
                <option 
                  v-for="role in availableRoles" 
                  :key="role.id" 
                  :value="parseInt(role.id)"
                  :disabled="isRoleRestricted(role)"
                >
                  {{ role.role_name }} {{ isRoleRestricted(role) ? '(Terbatas)' : '' }}
                </option>
              </select>
              <p v-if="isPranataHumas && form.role_id && isRoleRestrictedById(form.role_id)" class="form-error">
                Anda tidak memiliki izin memilih level ini.
              </p>
            </div>

            <!-- Status -->
            <div class="form-group">
              <label class="form-label">Status Akun</label>
              <select v-model="form.status" class="select-input-full" :disabled="saving">
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
              </select>
            </div>

            <!-- Error message if any -->
            <p v-if="errorMsg" class="form-error">{{ errorMsg }}</p>

            <div class="form-actions-row">
              <button 
                class="btn-submit-user" 
                @click="saveUserForm" 
                :disabled="saving || !isFormValid"
              >
                <span v-if="saving" class="spin-sm"></span>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
                {{ editingUserId ? 'Simpan Perubahan' : 'Simpan User' }}
              </button>
              <button 
                class="btn-cancel-user" 
                v-if="editingUserId" 
                @click="cancelEditUser" 
                :disabled="saving"
              >
                Batal
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- PANEL KANAN: DAFTAR USER -->
      <div class="panel-card list-panel">
        <div class="panel-header">
          <div class="panel-title-block">
            <div class="panel-icon user-list-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
            </div>
            <div>
              <h3>Daftar Pengguna</h3>
              <span class="panel-count">{{ users.length }} akun terdaftar</span>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="search-bar">
          <div class="search-input-wrapper">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
            </svg>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Cari nama atau username..." 
              class="search-input" 
            />
          </div>
        </div>

        <!-- List Body -->
        <div class="list-body">
          <div v-if="loading" class="list-loading">
            <div class="loader"></div><span>Memuat...</span>
          </div>
          <div v-else-if="filteredUsers.length === 0" class="empty-list">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Belum ada pengguna atau tidak ditemukan.
          </div>
          <div v-else class="list-items">
            <div 
              v-for="user in filteredUsers" 
              :key="user.id" 
              :class="['list-item', { 'item-is-editing': editingUserId === user.id }]"
            >
              <div class="item-left">
                <!-- Avatar Circle -->
                <div class="user-avatar-circle" :style="getAvatarStyle(user)">
                  {{ getUserInitial(user) }}
                </div>
                
                <div class="user-info-block">
                  <div class="user-name-row">
                    <span class="user-full-name">{{ user.full_name }}</span>
                    <!-- Me badge for current logged in user -->
                    <span v-if="isMe(user)" class="badge-me">Saya</span>
                  </div>
                  <span class="user-username">@{{ user.username }}</span>
                  <div class="user-badges-row">
                    <span :class="['badge-role', getRoleBadgeClass(user)]">
                      {{ user.role_name || 'User' }}
                    </span>
                    <span :class="['badge-status', user.status === 'active' ? 'bs-active' : 'bs-inactive']">
                      {{ user.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="item-actions">
                <!-- Edit button if permitted -->
                <button 
                  v-if="canEditUser(user)" 
                  class="btn-edit" 
                  @click="startEditUser(user)" 
                  title="Edit Pengguna" 
                  :disabled="saving"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                  </svg>
                </button>
                
                <!-- Locked icon if not permitted -->
                <div 
                  v-else 
                  class="action-locked" 
                  title="Anda tidak memiliki izin mengelola akun tingkat ini"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid Layout: Tab Manajemen Role -->
    <div v-if="activeTab === 'roles'" class="panels-grid">
      <!-- PANEL KIRI: DAFTAR ROLE -->
      <div class="panel-card list-panel">
        <div class="panel-header">
          <div class="panel-title-block">
            <div class="panel-icon user-list-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
            </div>
            <div>
              <h3>Pilih Role / Level</h3>
              <span class="panel-count">{{ availableRoles.length }} role terdaftar</span>
            </div>
          </div>
        </div>

        <div class="list-body">
          <div class="list-items">
            <div 
              v-for="role in availableRoles" 
              :key="role.id" 
              :class="['list-item', 'role-item-clickable', { 'item-is-editing': selectedRoleId === role.id }]"
              @click="selectRole(role)"
            >
              <div class="item-left">
                <div class="user-avatar-circle" :style="getRoleAvatarStyle(role)">
                  {{ getRoleInitial(role) }}
                </div>
                <div class="user-info-block">
                  <span class="user-full-name">{{ role.role_name }}</span>
                  <span class="user-username">ID: {{ role.id }} • {{ role.permissions?.length || 0 }} Hak Akses</span>
                </div>
              </div>
              <div class="item-actions">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="chevron-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PANEL KANAN: SETTING PERMISSIONS -->
      <div class="panel-card form-panel">
        <div class="panel-header">
          <div class="panel-title-block">
            <div class="panel-icon user-form-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
              </svg>
            </div>
            <div>
              <h3>Pengaturan Hak Akses</h3>
              <span class="panel-count" v-if="selectedRole">Role: {{ selectedRole.role_name }}</span>
            </div>
          </div>
        </div>

        <div class="permissions-body">
          <div v-if="!selectedRoleId" class="empty-permissions">
            Silakan pilih role di panel kiri terlebih dahulu.
          </div>
          <div v-else class="permissions-content">
            
            <!-- Warning for Super Admin -->
            <div v-if="selectedRole?.role_name === 'Super Admin'" class="alert-info-box">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="alert-icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
              </svg>
              <div>
                <strong>Hak Akses Super Admin Bersifat Mutlak</strong>
                <p>Seluruh modul diaktifkan secara default dan tidak dapat dimodifikasi demi keamanan akses sistem.</p>
              </div>
            </div>

            <!-- List of Modules to Check/Uncheck -->
            <div class="modules-list">
              <div 
                v-for="mod in availableModules" 
                :key="mod.id" 
                :class="['module-row', { 'module-checked': rolePermissions.includes(mod.id), 'row-disabled': selectedRole?.role_name === 'Super Admin' }]"
                @click="toggleModule(mod.id)"
              >
                <div class="module-check-wrapper">
                  <input 
                    type="checkbox" 
                    :checked="rolePermissions.includes(mod.id)" 
                    :disabled="selectedRole?.role_name === 'Super Admin'"
                    class="module-checkbox-input"
                    @click.stop
                    @change="toggleModule(mod.id)"
                  />
                  <div class="custom-checkbox"></div>
                </div>
                <div class="module-info">
                  <div class="module-title">{{ mod.name }}</div>
                  <div class="module-desc">{{ mod.description }}</div>
                </div>
              </div>
            </div>

            <div class="form-actions-row permissions-actions" v-if="selectedRole?.role_name !== 'Super Admin'">
              <button 
                class="btn-submit-user" 
                @click="saveRolePermissions" 
                :disabled="savingPermissions"
              >
                <span v-if="savingPermissions" class="spin-sm"></span>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
                Simpan Hak Akses
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()

// State
const users = ref([])
const availableRoles = ref([])
const loading = ref(true)
const saving = ref(false)
const searchQuery = ref('')
const errorMsg = ref('')

const editingUserId = ref(null)
const form = ref({
  full_name: '',
  username: '',
  password: '',
  role_id: null,
  status: 'active'
})

const toast = ref({ show: false, message: '', type: 'success' })

// Dynamic RBAC State
const activeTab = ref('users')
const selectedRoleId = ref(null)
const rolePermissions = ref([])
const savingPermissions = ref(false)

const availableModules = [
  { id: 'news', name: 'Berita Online', description: 'Pengelolaan berita online dan import artikel massal via CSV' },
  { id: 'clippings', name: 'Kliping Berita', description: 'Manajemen kliping koran digital, cetak, dan upload media' },
  { id: 'documentation', name: 'Dokumentasi Kegiatan', description: 'Koleksi dokumentasi foto, gambar dan video kegiatan universitas' },
  { id: 'rss', name: 'Sindikasi RSS', description: 'Sinkronisasi otomatis berita eksternal via RSS Feed dan setting sumber feed' },
  { id: 'taxonomy', name: 'Referensi Data', description: 'Pengaturan kategori berita, media cetak, dan parameter taksonomi' },
  { id: 'system', name: 'Update & Backup', description: 'Pembuatan cadangan (backup) database & file sistem, serta update patch' },
  { id: 'users', name: 'Manajemen User', description: 'Registrasi akun, edit status keaktifan user, dan kontrol hak akses role' },
  { id: 'activity', name: 'Log Audit', description: 'Pencatatan log riwayat aktivitas dan operasi pengguna untuk audit keamanan' }
]

const selectedRole = computed(() => {
  return availableRoles.value.find(r => r.id === selectedRoleId.value) || null
})

const selectRole = (role) => {
  selectedRoleId.value = role.id
  rolePermissions.value = [...(role.permissions || [])]
}

const selectFirstRole = () => {
  activeTab.value = 'roles'
  if (availableRoles.value.length > 0) {
    if (!selectedRoleId.value) {
      const nonSuper = availableRoles.value.find(r => r.role_name !== 'Super Admin')
      if (nonSuper) {
        selectRole(nonSuper)
      } else {
        selectRole(availableRoles.value[0])
      }
    }
  }
}

const toggleModule = (moduleId) => {
  if (selectedRole.value?.role_name === 'Super Admin') return
  const idx = rolePermissions.value.indexOf(moduleId)
  if (idx > -1) {
    rolePermissions.value.splice(idx, 1)
  } else {
    rolePermissions.value.push(moduleId)
  }
}

const saveRolePermissions = async () => {
  if (!selectedRoleId.value) return
  if (selectedRole.value?.role_name === 'Super Admin') {
    showToast('Hak akses Super Admin tidak dapat diubah.', 'error')
    return
  }

  savingPermissions.value = true
  try {
    const res = await axios.put(`/api/admin/roles/${selectedRoleId.value}/permissions`, {
      permissions: rolePermissions.value
    })
    
    if (res.data.status === 'success') {
      showToast('Hak akses role berhasil diperbarui!')
      
      const roleIndex = availableRoles.value.findIndex(r => r.id === selectedRoleId.value)
      if (roleIndex !== -1) {
        availableRoles.value[roleIndex].permissions = [...rolePermissions.value]
      }
      
      if (authStore.user && authStore.user.role_id === selectedRoleId.value) {
        authStore.user.permissions = [...rolePermissions.value]
        localStorage.setItem('hh_user', JSON.stringify(authStore.user))
      }
    }
  } catch (e) {
    console.error(e)
    const msg = e.response?.data?.message || 'Gagal memperbarui hak akses.'
    showToast(msg, 'error')
  } finally {
    savingPermissions.value = false
  }
}

const getRoleInitial = (role) => {
  return role.role_name ? role.role_name.charAt(0).toUpperCase() : 'R'
}

const getRoleAvatarStyle = (role) => {
  const name = role.role_name
  if (name === 'Super Admin') {
    return 'background: linear-gradient(135deg, #8b5cf6, #5b21b6); color: white;'
  } else if (name === 'Pranata Humas') {
    return 'background: linear-gradient(135deg, #10b981, #065f46); color: white;'
  } else if (name === 'Operator Kliping') {
    return 'background: linear-gradient(135deg, #3b82f6, #1e3a8a); color: white;'
  } else if (name === 'Operator Berita Online') {
    return 'background: linear-gradient(135deg, #f59e0b, #b45309); color: white;'
  } else if (name === 'Operator Foto/Video') {
    return 'background: linear-gradient(135deg, #ec4899, #be185d); color: white;'
  }
  return 'background: linear-gradient(135deg, #64748b, #334155); color: white;'
}

// Toast helper
const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// Current logged in user info
const isPranataHumas = computed(() => {
  return authStore.user?.role_id === 2
})

// Check if a role option is restricted for logged in user
const isRoleRestricted = (role) => {
  if (isPranataHumas.value) {
    return role.role_name === 'Super Admin' || role.role_name === 'Pranata Humas'
  }
  return false
}

const isRoleRestrictedById = (roleId) => {
  const role = availableRoles.value.find(r => parseInt(r.id) === parseInt(roleId))
  return role ? isRoleRestricted(role) : false
}

// Fetch users
const fetchUsers = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/users')
    users.value = res.data.data || []
  } catch (e) {
    console.error(e)
    showToast('Gagal memuat daftar pengguna.', 'error')
  } finally {
    loading.value = false
  }
}

// Fetch roles
const fetchRoles = async () => {
  try {
    const res = await axios.get('/api/admin/roles')
    availableRoles.value = res.data.data || []
  } catch (e) {
    console.error(e)
  }
}

// Computed filter users
const filteredUsers = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return users.value
  return users.value.filter(u => 
    (u.full_name && u.full_name.toLowerCase().includes(query)) || 
    (u.username && u.username.toLowerCase().includes(query))
  )
})

// Form validity
const isFormValid = computed(() => {
  if (!form.value.full_name.trim()) return false
  if (!form.value.username.trim()) return false
  if (!editingUserId.value && !form.value.password) return false // Password required for new user
  if (!form.value.role_id) return false
  
  // Restriction check
  if (isPranataHumas.value && isRoleRestrictedById(form.value.role_id)) {
    return false
  }
  
  return true
})

// Can edit a specific user in the list
const canEditUser = (user) => {
  if (isPranataHumas.value) {
    return user.role_name !== 'Super Admin' && user.role_name !== 'Pranata Humas'
  }
  return true
}

const isMe = (user) => {
  return authStore.user?.id === user.id
}

// Edit operations
const startEditUser = (user) => {
  editingUserId.value = user.id
  form.value = {
    full_name: user.full_name,
    username: user.username,
    password: '', // blank by default on edit
    role_id: parseInt(user.role_id),
    status: user.status
  }
  errorMsg.value = ''
}

const cancelEditUser = () => {
  editingUserId.value = null
  form.value = {
    full_name: '',
    username: '',
    password: '',
    role_id: null,
    status: 'active'
  }
  errorMsg.value = ''
}

// Save form
const saveUserForm = async () => {
  if (!isFormValid.value) return
  saving.value = true
  errorMsg.value = ''

  try {
    const payload = {
      full_name: form.value.full_name.trim(),
      username: form.value.username.trim(),
      role_id: form.value.role_id,
      status: form.value.status
    }

    if (form.value.password) {
      payload.password = form.value.password
    }

    if (editingUserId.value) {
      await axios.post(`/api/admin/users/${editingUserId.value}`, payload)
      showToast('Data pengguna berhasil diperbarui.')
    } else {
      await axios.post('/api/admin/users', payload)
      showToast('Pengguna baru berhasil ditambahkan.')
    }

    cancelEditUser()
    await fetchUsers()
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Gagal menyimpan data pengguna.'
    showToast(errorMsg.value, 'error')
  } finally {
    saving.value = false
  }
}

// UI Helpers
const getUserInitial = (user) => {
  return user.full_name ? user.full_name.charAt(0).toUpperCase() : 'U'
}

const getAvatarStyle = (user) => {
  const name = user.role_name
  if (name === 'Super Admin') {
    return 'background: linear-gradient(135deg, #8b5cf6, #5b21b6); color: white;'
  } else if (name === 'Pranata Humas') {
    return 'background: linear-gradient(135deg, #10b981, #065f46); color: white;'
  } else if (name === 'Operator Kliping') {
    return 'background: linear-gradient(135deg, #3b82f6, #1e3a8a); color: white;'
  } else if (name === 'Operator Berita Online') {
    return 'background: linear-gradient(135deg, #f59e0b, #b45309); color: white;'
  } else if (name === 'Operator Foto/Video') {
    return 'background: linear-gradient(135deg, #ec4899, #be185d); color: white;'
  }
  return 'background: linear-gradient(135deg, #64748b, #334155); color: white;'
}

const getRoleBadgeClass = (user) => {
  const name = user.role_name
  if (name === 'Super Admin') return 'br-super'
  if (name === 'Pranata Humas') return 'br-pranata'
  if (name === 'Operator Kliping') return 'br-op-kliping'
  if (name === 'Operator Berita Online') return 'br-op-berita'
  if (name === 'Operator Foto/Video') return 'br-op-dokumentasi'
  return 'br-default'
}

onMounted(() => {
  fetchUsers()
  fetchRoles()
})
</script>

<style scoped>
.user-page { display: flex; flex-direction: column; gap: 28px; animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; }
.page-header h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
.page-header p  { font-size: 13.5px; color: #64748b; margin-top: 4px; }

/* Two-panel grid */
.panels-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 24px; }
@media (max-width: 960px) { .panels-grid { grid-template-columns: 1fr; } }

/* Panel Card */
.panel-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }

.panel-header { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; }
.panel-title-block { display: flex; align-items: center; gap: 14px; }
.panel-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.panel-icon svg { width: 22px; height: 22px; }
.user-form-icon { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #6d28d9; }
.user-list-icon { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }
.panel-title-block h3 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }
.panel-count { font-size: 11.5px; font-weight: 600; color: #94a3b8; margin-top: 2px; display: block; }

/* Add Form */
.add-form { padding: 24px; background: #fafafa; border-bottom: 1px solid #f1f5f9; }
.form-grid { display: flex; flex-direction: column; gap: 18px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; display: flex; align-items: center; justify-content: space-between; }
.password-note { font-size: 10px; font-weight: 500; color: #94a3b8; text-transform: none; }
.text-input-full, .select-input-full {
  width: 100%; padding: 10px 14px; border-radius: 10px;
  border: 1.5px solid #e2e8f0; font-size: 13.5px; font-family: inherit;
  outline: none; color: #0f172a; background: white; transition: border-color 0.2s, box-shadow 0.2s;
}
.text-input-full:focus, .select-input-full:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.08); }
.text-input-full:disabled, .select-input-full:disabled { opacity: 0.6; cursor: not-allowed; background: #f1f5f9; }

.form-actions-row { display: flex; gap: 10px; margin-top: 6px; }
.btn-submit-user {
  flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 11px 16px; border-radius: 10px; border: none; background: #0f172a; color: white;
  font-size: 13.5px; font-weight: 700; cursor: pointer; transition: background 0.2s;
}
.btn-submit-user:hover:not(:disabled) { background: #1e293b; }
.btn-submit-user:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-submit-user svg { width: 16px; height: 16px; }
.btn-cancel-user {
  padding: 11px 16px; border-radius: 10px; border: 1.5px solid #cbd5e1;
  background: white; color: #475569; font-size: 13.5px; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
}
.btn-cancel-user:hover { background: #f1f5f9; color: #0f172a; }

.spin-sm { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }
.form-error { color: #ef4444; font-size: 12px; margin-top: 4px; font-weight: 500; }

/* Search bar */
.search-bar { padding: 14px 20px; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.search-input-wrapper { position: relative; width: 100%; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8; }
.search-input {
  width: 100%; padding: 9px 14px 9px 38px; border-radius: 10px;
  border: 1.5px solid #e2e8f0; font-size: 13.5px; font-family: inherit;
  outline: none; color: #0f172a; background: white; transition: border-color 0.2s;
}
.search-input:focus { border-color: #6366f1; }

/* List items */
.list-body { flex: 1; overflow-y: auto; max-height: 520px; }
.list-loading { display: flex; align-items: center; gap: 12px; padding: 32px 24px; color: #94a3b8; font-size: 13.5px; }
.loader { width: 20px; height: 20px; border: 2.5px solid #e2e8f0; border-top-color: #0f172a; border-radius: 50%; animation: spin 0.8s linear infinite; }
.empty-list { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 64px 24px; color: #94a3b8; font-size: 13.5px; }
.empty-list svg { width: 40px; height: 40px; opacity: 0.3; }

.list-items { display: flex; flex-direction: column; }
.list-item {
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  padding: 16px 24px; border-bottom: 1px solid #f8fafc;
  transition: background 0.15s;
}
.list-item:last-child { border-bottom: none; }
.list-item:hover { background: #fafbff; }
.item-is-editing { background: #f5f3ff !important; border-left: 3px solid #6366f1; padding-left: 21px !important; }

.item-left { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }

/* Avatar Circle */
.user-avatar-circle {
  width: 44px; height: 44px; border-radius: 50%;
  font-size: 16px; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

.user-info-block { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
.user-name-row { display: flex; align-items: center; gap: 8px; }
.user-full-name { font-size: 14.5px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.badge-me { font-size: 10px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; text-transform: uppercase; }
.user-username { font-size: 12px; color: #64748b; font-weight: 500; }

.user-badges-row { display: flex; align-items: center; gap: 6px; margin-top: 4px; }
.badge-role { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; }
.br-super { background: #f3e8ff; color: #6b21a8; }
.br-pranata { background: #d1fae5; color: #065f46; }
.br-operator { background: #dbeafe; color: #1e40af; }
.br-default { background: #f1f5f9; color: #475569; }

.badge-status { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; }
.bs-active { background: #ecfdf5; color: #10b981; }
.bs-inactive { background: #f1f5f9; color: #64748b; }

.item-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.btn-edit {
  width: 34px; height: 34px; border-radius: 10px; border: none;
  background: #f1f5f9; color: #475569; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.btn-edit:hover { background: #e0e7ff; color: #4338ca; }
.btn-edit svg { width: 16px; height: 16px; }

.action-locked {
  width: 34px; height: 34px; border-radius: 10px;
  background: #f8fafc; color: #cbd5e1;
  display: flex; align-items: center; justify-content: center;
  cursor: not-allowed;
}
.action-locked svg { width: 16px; height: 16px; }

/* Toast */
.toast {
  position: fixed; bottom: 28px; right: 28px; z-index: 9999;
  display: flex; align-items: center; gap: 10px;
  padding: 14px 20px; border-radius: 14px; font-size: 13.5px;
  font-weight: 600; box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  min-width: 260px;
}
.toast svg { width: 18px; height: 18px; flex-shrink: 0; }
.toast.success { background: #0f172a; color: white; }
.toast.error   { background: #dc2626; color: white; }
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(12px); }

@media (max-width: 640px) {
  .page-header h2 { font-size: 18px; }
  .toast { min-width: auto; max-width: calc(100vw - 32px); right: 16px; bottom: 16px; }
}

/* Tabs Navigation */
.tabs-nav {
  display: flex;
  gap: 8px;
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 24px;
  padding-bottom: 0px;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  color: #64748b;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: -2px;
}

.tab-btn:hover {
  color: #0f172a;
}

.tab-btn.active {
  color: #6366f1;
  border-bottom-color: #6366f1;
}

.tab-icon {
  width: 18px;
  height: 18px;
}

/* Specific operator styles */
.br-op-kliping { background: #dbeafe; color: #1e40af; }
.br-op-berita { background: #fef3c7; color: #b45309; }
.br-op-dokumentasi { background: #fce7f3; color: #be185d; }

/* Clickable list item */
.role-item-clickable {
  cursor: pointer;
}

.role-item-clickable:hover {
  background: #fafbff;
}

.chevron-icon {
  width: 16px;
  height: 16px;
  color: #94a3b8;
  transition: transform 0.2s;
}

.item-is-editing .chevron-icon {
  color: #6366f1;
  transform: translateX(3px);
}

/* Permissions layout */
.permissions-body {
  padding: 24px;
  background: #fafafa;
  border-bottom: 1px solid #f1f5f9;
  flex: 1;
}

.empty-permissions {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #94a3b8;
  font-size: 14px;
}

.alert-info-box {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #e0f2fe;
  color: #0369a1;
  border-radius: 12px;
  font-size: 13px;
  line-height: 1.5;
  margin-bottom: 24px;
}

.alert-icon {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}

.alert-info-box p {
  margin-top: 4px;
  color: #075985;
}

.modules-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.module-row {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.module-row:hover:not(.row-disabled) {
  border-color: #cbd5e1;
  background: #f8fafc;
}

.module-row.module-checked {
  border-color: #6366f1;
  background: #f5f3ff;
}

.row-disabled {
  cursor: not-allowed;
  opacity: 0.8;
}

.module-check-wrapper {
  position: relative;
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  margin-top: 2px;
}

.module-checkbox-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.custom-checkbox {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: white;
  border: 2px solid #cbd5e1;
  border-radius: 6px;
  transition: all 0.2s;
}

.module-row.module-checked .custom-checkbox {
  background-color: #6366f1;
  border-color: #6366f1;
}

.custom-checkbox:after {
  content: "";
  position: absolute;
  display: none;
  left: 6px;
  top: 2px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.module-row.module-checked .custom-checkbox:after {
  display: block;
}

.row-disabled .custom-checkbox {
  background-color: #f1f5f9;
  border-color: #cbd5e1;
}

.module-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.module-title {
  font-size: 14.5px;
  font-weight: 700;
  color: #0f172a;
}

.module-desc {
  font-size: 12.5px;
  color: #64748b;
  line-height: 1.4;
}

.permissions-actions {
  margin-top: 20px;
  border-top: 1px solid #f1f5f9;
  padding-top: 20px;
}
</style>
