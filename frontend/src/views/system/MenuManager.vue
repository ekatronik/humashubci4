<template>
  <div class="menu-manager">
    <!-- Header -->
    <div class="manager-header">
      <div class="header-info">
        <h3>🧩 Master Menu</h3>
        <p>Kelola menu aplikasi yang tampil di App Launcher halaman depan. Seret untuk mengubah urutan.</p>
      </div>
      <button class="btn-add" @click="openAddModal">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Tambah Menu
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Memuat data menu...</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="menus.length === 0" class="empty-state">
      <div class="empty-icon">📋</div>
      <h4>Belum ada menu</h4>
      <p>Tambahkan menu baru untuk ditampilkan di App Launcher halaman depan.</p>
      <button class="btn-add" @click="openAddModal">Tambah Menu Pertama</button>
    </div>

    <!-- Menu List (Draggable) -->
    <div v-else class="menu-list-container">
      <div class="list-header">
        <span class="col-drag"></span>
        <span class="col-icon">Icon</span>
        <span class="col-name">Nama Menu</span>
        <span class="col-url">Link / URL</span>
        <span class="col-status">Status</span>
        <span class="col-actions">Aksi</span>
      </div>

      <draggable
        v-model="menus"
        item-key="id"
        handle=".drag-handle"
        ghost-class="drag-ghost"
        animation="250"
        @end="onDragEnd"
      >
        <template #item="{ element, index }">
          <div class="menu-row" :class="{ inactive: !element.is_active || element.is_active === '0' }">
            <div class="col-drag drag-handle" title="Seret untuk mengubah urutan">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
              </svg>
            </div>
            <div class="col-icon">
              <span class="menu-icon-preview">{{ element.icon }}</span>
            </div>
            <div class="col-name">
              <span class="menu-name-text">{{ element.name }}</span>
              <span class="sort-badge">#{{ index + 1 }}</span>
            </div>
            <div class="col-url">
              <a :href="element.url" target="_blank" class="url-link" :title="element.url">{{ element.url }}</a>
            </div>
            <div class="col-status">
              <span :class="['status-badge', element.is_active == 1 ? 'active' : 'disabled']">
                {{ element.is_active == 1 ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
            <div class="col-actions">
              <button class="btn-icon btn-edit" @click="openEditModal(element)" title="Edit">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
              </button>
              <button class="btn-icon btn-delete" @click="confirmDelete(element)" title="Hapus">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
              </button>
            </div>
          </div>
        </template>
      </draggable>
    </div>

    <!-- Toast -->
    <Transition name="toast">
      <div v-if="toast.show" :class="['toast-msg', toast.type]">
        <span>{{ toast.text }}</span>
      </div>
    </Transition>

    <!-- Modal Add / Edit -->
    <Transition name="modal">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-box">
          <div class="modal-header">
            <h4>{{ editingMenu ? 'Edit Menu' : 'Tambah Menu Baru' }}</h4>
            <button class="btn-close-modal" @click="closeModal">✕</button>
          </div>
          <form @submit.prevent="saveMenu" class="modal-body">
            <div class="form-group">
              <label>Icon (Emoji)</label>
              <div class="icon-input-group">
                <span class="icon-preview-big">{{ formData.icon || '🔗' }}</span>
                <input type="text" v-model="formData.icon" placeholder="Contoh: 📰" maxlength="4" class="icon-input" />
              </div>
              <div class="emoji-suggestions">
                <button type="button" v-for="emoji in emojiSuggestions" :key="emoji" @click="formData.icon = emoji" class="emoji-btn" :class="{ selected: formData.icon === emoji }">{{ emoji }}</button>
              </div>
            </div>
            <div class="form-group">
              <label>Nama Menu <span class="required">*</span></label>
              <input type="text" v-model="formData.name" placeholder="Contoh: Berita Online" required />
            </div>
            <div class="form-group">
              <label>Link / URL <span class="required">*</span></label>
              <input type="text" v-model="formData.url" placeholder="Contoh: /berita atau https://example.com" required />
              <small class="helper-text">Gunakan path relatif (misal: /berita) untuk halaman internal, atau URL lengkap untuk link eksternal.</small>
            </div>
            <div class="form-group">
              <label class="toggle-label">
                <input type="checkbox" v-model="formData.is_active" :true-value="1" :false-value="0" />
                <span class="toggle-switch"></span>
                <span>{{ formData.is_active == 1 ? 'Aktif (Tampil di App Launcher)' : 'Nonaktif (Tersembunyi)' }}</span>
              </label>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn-cancel" @click="closeModal">Batal</button>
              <button type="submit" class="btn-save" :disabled="saving">
                <span v-if="saving" class="spinner-mini"></span>
                {{ editingMenu ? 'Simpan Perubahan' : 'Tambah Menu' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Modal Konfirmasi Hapus -->
    <Transition name="modal">
      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
        <div class="modal-box modal-sm">
          <div class="modal-header danger">
            <h4>Hapus Menu</h4>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus menu <strong>{{ deletingMenu?.name }}</strong>?</p>
            <p class="text-muted">Aksi ini tidak dapat dibatalkan.</p>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" @click="showDeleteConfirm = false">Batal</button>
            <button class="btn-danger" @click="deleteMenu" :disabled="saving">
              <span v-if="saving" class="spinner-mini"></span>
              Hapus
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import draggable from 'vuedraggable'

const menus = ref([])
const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const editingMenu = ref(null)
const deletingMenu = ref(null)
const toast = ref({ show: false, text: '', type: 'success' })

const emojiSuggestions = ['📰', '📡', '🎓', '✂️', '📸', '🏫', '📊', '🔗', '🌐', '📋', '📁', '⚡', '🎯', '💡', '🏅']

const formData = ref({
  name: '',
  url: '',
  icon: '🔗',
  is_active: 1,
})

const showToast = (text, type = 'success') => {
  toast.value = { show: true, text, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

const fetchMenus = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/menus')
    if (res.data.status === 'success') {
      menus.value = res.data.data
    }
  } catch (err) {
    showToast('Gagal memuat data menu.', 'error')
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  editingMenu.value = null
  formData.value = { name: '', url: '', icon: '🔗', is_active: 1 }
  showModal.value = true
}

const openEditModal = (menu) => {
  editingMenu.value = menu
  formData.value = {
    name: menu.name,
    url: menu.url,
    icon: menu.icon,
    is_active: parseInt(menu.is_active),
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingMenu.value = null
}

const saveMenu = async () => {
  saving.value = true
  try {
    if (editingMenu.value) {
      await axios.put(`/api/admin/menus/${editingMenu.value.id}`, formData.value)
      showToast('Menu berhasil diperbarui.')
    } else {
      await axios.post('/api/admin/menus', formData.value)
      showToast('Menu berhasil ditambahkan.')
    }
    closeModal()
    await fetchMenus()
  } catch (err) {
    showToast(err.response?.data?.message || 'Gagal menyimpan menu.', 'error')
  } finally {
    saving.value = false
  }
}

const confirmDelete = (menu) => {
  deletingMenu.value = menu
  showDeleteConfirm.value = true
}

const deleteMenu = async () => {
  saving.value = true
  try {
    await axios.delete(`/api/admin/menus/${deletingMenu.value.id}`)
    showToast('Menu berhasil dihapus.')
    showDeleteConfirm.value = false
    await fetchMenus()
  } catch (err) {
    showToast('Gagal menghapus menu.', 'error')
  } finally {
    saving.value = false
  }
}

const onDragEnd = async () => {
  const order = menus.value.map(m => m.id)
  try {
    await axios.post('/api/admin/menus/reorder', { order })
    showToast('Urutan menu berhasil disimpan.')
  } catch (err) {
    showToast('Gagal menyimpan urutan.', 'error')
    await fetchMenus()
  }
}

onMounted(fetchMenus)
</script>

<style scoped>
.menu-manager {
  font-family: 'Inter', sans-serif;
}

/* ─── Header ───────────────────────────── */
.manager-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 28px;
  gap: 20px;
  flex-wrap: wrap;
}
.header-info h3 {
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 6px;
}
.header-info p {
  font-size: 13.5px;
  color: #64748b;
  margin: 0;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
  white-space: nowrap;
}
.btn-add svg {
  width: 16px;
  height: 16px;
}
.btn-add:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(34, 197, 94, 0.4);
}

/* ─── Loading ──────────────────────────── */
.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  padding: 60px 20px;
  color: #64748b;
  font-size: 14px;
}
.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #e2e8f0;
  border-top-color: #22c55e;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Empty State ──────────────────────── */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 16px;
  border: 2px dashed #e2e8f0;
}
.empty-icon { font-size: 48px; margin-bottom: 16px; }
.empty-state h4 { font-size: 17px; font-weight: 700; color: #0f172a; margin: 0 0 8px; }
.empty-state p { font-size: 13.5px; color: #64748b; margin: 0 0 24px; }

/* ─── Menu List ────────────────────────── */
.menu-list-container {
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.list-header {
  display: grid;
  grid-template-columns: 44px 56px 1fr 1.2fr 90px 90px;
  align-items: center;
  padding: 12px 16px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  color: #94a3b8;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.menu-row {
  display: grid;
  grid-template-columns: 44px 56px 1fr 1.2fr 90px 90px;
  align-items: center;
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  transition: all 0.2s;
  background: white;
}
.menu-row:last-child { border-bottom: none; }
.menu-row:hover { background: #f8fafc; }
.menu-row.inactive { opacity: 0.5; }

.drag-handle {
  cursor: grab;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}
.drag-handle:active { cursor: grabbing; }
.drag-handle svg { width: 18px; height: 18px; }
.drag-handle:hover { color: #22c55e; }

.drag-ghost {
  opacity: 0.4;
  background: #ecfdf5 !important;
  border: 2px dashed #22c55e !important;
  border-radius: 12px;
}

.menu-icon-preview {
  font-size: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: #f8fafc;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

.col-name {
  display: flex;
  align-items: center;
  gap: 10px;
}
.menu-name-text {
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
}
.sort-badge {
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  background: #f1f5f9;
  padding: 2px 8px;
  border-radius: 20px;
}

.url-link {
  font-size: 12.5px;
  color: #3b82f6;
  text-decoration: none;
  word-break: break-all;
  max-width: 220px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: block;
}
.url-link:hover { text-decoration: underline; }

.status-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 20px;
  text-align: center;
  display: inline-block;
}
.status-badge.active {
  background: #ecfdf5;
  color: #15803d;
}
.status-badge.disabled {
  background: #fef2f2;
  color: #dc2626;
}

.col-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-icon svg { width: 15px; height: 15px; }
.btn-edit { color: #3b82f6; }
.btn-edit:hover { background: #eff6ff; border-color: #93c5fd; }
.btn-delete { color: #ef4444; }
.btn-delete:hover { background: #fef2f2; border-color: #fca5a5; }

/* ─── Toast ────────────────────────────── */
.toast-msg {
  position: fixed;
  bottom: 32px;
  right: 32px;
  padding: 14px 24px;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 600;
  color: white;
  z-index: 10000;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}
.toast-msg.success { background: linear-gradient(135deg, #22c55e, #15803d); }
.toast-msg.error   { background: linear-gradient(135deg, #ef4444, #b91c1c); }

.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { transform: translateY(20px); opacity: 0; }
.toast-leave-to   { transform: translateY(20px); opacity: 0; }

/* ─── Modal ────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9000;
  padding: 24px;
}
.modal-box {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}
.modal-sm { max-width: 420px; }

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-header h4 {
  font-size: 17px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}
.modal-header.danger h4 { color: #dc2626; }

.btn-close-modal {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.btn-close-modal:hover { background: #e2e8f0; color: #0f172a; }

.modal-body {
  padding: 24px;
}
.modal-body p {
  font-size: 14px;
  color: #475569;
  margin: 0 0 8px;
}
.text-muted { color: #94a3b8 !important; font-size: 12.5px !important; }

.form-group {
  margin-bottom: 20px;
}
.form-group label {
  display: block;
  font-size: 12.5px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.required { color: #ef4444; }

.form-group input[type="text"] {
  width: 100%;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  font-size: 14px;
  color: #0f172a;
  background: #f8fafc;
  outline: none;
  transition: all 0.2s;
  box-sizing: border-box;
}
.form-group input[type="text"]:focus {
  border-color: #22c55e;
  background: white;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

.icon-input-group {
  display: flex;
  align-items: center;
  gap: 12px;
}
.icon-preview-big {
  font-size: 36px;
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  border-radius: 14px;
  border: 1.5px solid #e2e8f0;
  flex-shrink: 0;
}
.icon-input {
  max-width: 80px;
  text-align: center;
  font-size: 20px !important;
}

.emoji-suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
}
.emoji-btn {
  font-size: 20px;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.emoji-btn:hover { border-color: #22c55e; background: #ecfdf5; transform: scale(1.1); }
.emoji-btn.selected { border-color: #22c55e; background: #ecfdf5; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15); }

.helper-text {
  display: block;
  font-size: 11.5px;
  color: #94a3b8;
  margin-top: 6px;
}

.toggle-label {
  display: flex !important;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  font-size: 14px !important;
  font-weight: 500 !important;
  text-transform: none !important;
  letter-spacing: 0 !important;
  color: #334155 !important;
}
.toggle-label input[type="checkbox"] { display: none; }
.toggle-switch {
  width: 44px;
  height: 24px;
  border-radius: 12px;
  background: #cbd5e1;
  position: relative;
  transition: all 0.25s;
  flex-shrink: 0;
}
.toggle-switch::after {
  content: '';
  position: absolute;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: white;
  top: 3px;
  left: 3px;
  transition: all 0.25s;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}
.toggle-label input:checked + .toggle-switch {
  background: linear-gradient(135deg, #22c55e, #15803d);
}
.toggle-label input:checked + .toggle-switch::after {
  left: 23px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 8px;
}
.btn-cancel {
  padding: 10px 20px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: white;
  color: #475569;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-cancel:hover { background: #f8fafc; border-color: #cbd5e1; }

.btn-save {
  padding: 10px 24px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #22c55e, #15803d);
  color: white;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
}
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(34, 197, 94, 0.4); }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

.btn-danger {
  padding: 10px 24px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #ef4444, #b91c1c);
  color: white;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-danger:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner-mini {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from { opacity: 0; }
.modal-enter-from .modal-box { transform: scale(0.95) translateY(10px); }
.modal-leave-to { opacity: 0; }
.modal-leave-to .modal-box { transform: scale(0.95) translateY(10px); }

/* ─── Responsive ───────────────────────── */
@media (max-width: 768px) {
  .list-header { display: none; }
  .menu-row {
    grid-template-columns: 40px 48px 1fr auto;
    gap: 8px;
  }
  .col-url { display: none; }
  .col-status { display: none; }
}
</style>
