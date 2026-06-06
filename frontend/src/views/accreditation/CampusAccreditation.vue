<template>
  <div class="campus-container">
    <div class="card">
      <div class="card-header">
        <h3>Pengaturan Akreditasi Institusi / Kampus</h3>
        <p>Kelola profil akreditasi universitas saat ini secara lengkap via link Google Drive.</p>
      </div>

      <form @submit.prevent="handleSubmit" class="card-body">
        <!-- Alert Notification -->
        <div v-if="alert.show" :class="['alert', alert.type]">
          <span>{{ alert.message }}</span>
          <button type="button" @click="alert.show = false" class="alert-close">&times;</button>
        </div>

        <!-- Section 1: Profil Institusi -->
        <div class="form-section">
          <div class="form-group">
            <label for="nama_institusi">Nama Lengkap Institusi / Universitas</label>
            <input 
              type="text" 
              id="nama_institusi" 
              v-model="form.nama_institusi" 
              placeholder="Contoh: UIN Ar-Raniry Banda Aceh" 
              required
              class="form-control"
            >
          </div>
        </div>

        <hr class="divider">

        <!-- Section 2: Satu Periode Akreditasi Aktif -->
        <div class="single-period-card">
          <div class="period-header">
            <span class="period-tag">Akreditasi Saat Ini (Aktif / Terbaru)</span>
          </div>
          <div class="period-body">
            <div class="form-grid">
              <div class="form-group">
                <label>Peringkat Akreditasi</label>
                <select v-model="form.akreditasi_sekarang" class="form-control">
                  <option :value="null">-- Pilih Peringkat --</option>
                  <option value="Unggul">Unggul</option>
                  <option value="Baik Sekali">Baik Sekali</option>
                  <option value="Baik">Baik</option>
                </select>
              </div>

              <div class="form-group">
                <label>Masa Berlaku SK</label>
                <input type="date" v-model="form.masa_berlaku" class="form-control">
                
                <!-- Countdown Badge -->
                <div 
                  v-if="getCountdown(form.masa_berlaku)" 
                  :class="['countdown-badge', { 'warning': getCountdown(form.masa_berlaku).isWarning, 'critical': getCountdown(form.masa_berlaku).isCritical }]"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="clock-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  <span>{{ getCountdown(form.masa_berlaku).text }}</span>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Link Folder Google Drive (SK &amp; Sertifikat)</label>
              <input 
                type="url" 
                v-model="form.sertifikat_berlaku" 
                placeholder="https://drive.google.com/drive/folders/..." 
                class="form-control"
              >
            </div>
          </div>
        </div>

        <div class="card-footer">
          <button type="submit" class="btn-save" :disabled="submitting">
            <span v-if="submitting">Menyimpan...</span>
            <span v-else>Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const form = ref({
  nama_institusi: 'UIN Ar-Raniry Banda Aceh',
  akreditasi_sekarang: null,
  masa_berlaku: '',
  sertifikat_berlaku: '',
})

const submitting = ref(false)
const alert = ref({ show: false, type: '', message: '' })

// Countdown helper logic
const getCountdown = (dateString) => {
  if (!dateString) return null
  const now = new Date()
  now.setHours(0, 0, 0, 0)
  
  const expiry = new Date(dateString)
  expiry.setHours(0, 0, 0, 0)
  
  const diffTime = expiry - now
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays <= 0) {
    return { text: 'Expired / Kadaluarsa', isWarning: true, isCritical: true }
  }
  
  if (diffDays <= 30) {
    return { text: `${diffDays} hari lagi`, isWarning: true, isCritical: true }
  }
  
  const diffMonths = Math.ceil(diffDays / 30)
  const isWarning = diffMonths <= 3
  return { text: `${diffMonths} bulan lagi`, isWarning, isCritical: false }
}

const handleSubmit = async () => {
  submitting.value = true
  alert.value.show = false

  try {
    const params = new URLSearchParams()
    params.append('nama_institusi', form.value.nama_institusi)

    if (form.value.akreditasi_sekarang) params.append('akreditasi_sekarang', form.value.akreditasi_sekarang)
    if (form.value.masa_berlaku) params.append('masa_berlaku', form.value.masa_berlaku)
    if (form.value.sertifikat_berlaku) params.append('sertifikat_berlaku', form.value.sertifikat_berlaku)

    const res = await axios.post('/api/admin/accreditation/campus', params)

    if (res.data.status === 'success') {
      alert.value = { show: true, type: 'alert-success', message: 'Akreditasi institusi berhasil disimpan.' }
      
      const data = res.data.data
      form.value = {
        nama_institusi: data.nama_institusi,
        akreditasi_sekarang: data.akreditasi_sekarang,
        masa_berlaku: data.masa_berlaku || '',
        sertifikat_berlaku: data.sertifikat_berlaku || '',
      }
    }
  } catch (err) {
    console.error(err)
    alert.value = { 
      show: true, 
      type: 'alert-error', 
      message: err.response?.data?.message || 'Gagal menyimpan akreditasi institusi.' 
    }
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/admin/accreditation/campus')
    if (res.data.status === 'success') {
      const data = res.data.data
      form.value = {
        nama_institusi: data.nama_institusi,
        akreditasi_sekarang: data.akreditasi_sekarang,
        masa_berlaku: data.masa_berlaku || '',
        sertifikat_berlaku: data.sertifikat_berlaku || '',
      }
    }
  } catch (err) {
    console.error('Gagal memuat akreditasi institusi:', err)
  }
})
</script>

<style scoped>
.campus-container {
  display: flex;
  flex-direction: column;
}
.card {
  background: white;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
  overflow: hidden;
}
.card-header {
  padding: 32px 40px;
  border-bottom: 1px solid #e2e8f0;
}
.card-header h3 {
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
}
.card-header p {
  font-size: 13px;
  color: #64748b;
  margin-top: 4px;
}

.card-body {
  padding: 40px;
  display: flex;
  flex-direction: column;
  gap: 28px;
}
.form-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}
.form-control {
  width: 100%;
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-size: 14px;
  color: #0f172a;
  background: #f8fafc;
  transition: all 0.2s;
  box-sizing: border-box;
}
.form-control:focus {
  outline: none;
  border-color: #10b981;
  background: white;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.divider {
  border: none;
  border-top: 1px solid #e2e8f0;
  margin: 0;
}

/* Single Period Layout */
.single-period-card {
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  border-color: rgba(16, 185, 129, 0.3);
  background: rgba(16, 185, 129, 0.005);
}
.period-header {
  padding: 16px 20px;
  background: linear-gradient(135deg, #047857, #10b981);
  color: white;
}
.period-tag {
  font-size: 12.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.period-body {
  padding: 32px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
}

/* Countdown Badge */
.countdown-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  align-self: flex-start;
  margin-top: 2px;
}
.countdown-badge.warning {
  background: #fffbeb;
  color: #d97706;
  border-color: #fde68a;
  animation: pulseWarning 2s infinite ease-in-out;
}
.countdown-badge.critical {
  background: #fef2f2;
  color: #dc2626;
  border-color: #fca5a5;
}
@keyframes pulseWarning {
  0% { transform: scale(1); }
  50% { transform: scale(1.02); }
  100% { transform: scale(1); }
}
.clock-icon {
  width: 14px;
  height: 14px;
}

.card-footer {
  padding: 24px 40px 0;
  display: flex;
  justify-content: flex-end;
}
.btn-save {
  padding: 12px 32px;
  border-radius: 10px;
  border: none;
  background: #10b981;
  color: white;
  font-weight: 700;
  font-size: 14.5px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
  transition: all 0.2s;
}
.btn-save:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
}
.btn-save:disabled {
  background: #94a3b8;
  box-shadow: none;
  cursor: not-allowed;
}

/* Alert Notification classes */
.alert {
  padding: 14px 20px;
  border-radius: 12px;
  font-size: 13.5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.alert-success {
  background: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
}
.alert-error {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}
.alert-close {
  background: transparent;
  border: none;
  font-size: 20px;
  color: inherit;
  cursor: pointer;
}
</style>
