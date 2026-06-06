<template>
  <div class="report-container">
    <!-- Header -->
    <div class="page-action-header">
      <div class="header-text">
        <h3>Laporan &amp; Analitik {{ moduleTitle }}</h3>
        <p>{{ moduleDescription }}</p>
      </div>
    </div>

    <!-- Loader state -->
    <div v-if="loading" class="chart-loading-card">
      <div class="loader"></div>
      <span>Mempersiapkan data laporan...</span>
    </div>

    <!-- Dashboard Content -->
    <template v-else>
      <!-- Year Filter Control -->
      <div class="year-filter-bar">
        <span class="year-filter-label">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
          Filter Tahun:
        </span>
        <div class="year-pills">
          <button
            :class="['year-pill', { active: selectedYear === null }]"
            @click="selectYear(null)"
          >Semua Tahun</button>
          <button
            v-for="yr in availableYears"
            :key="yr"
            :class="['year-pill', { active: selectedYear === yr }]"
            @click="selectYear(yr)"
          >{{ yr }}</button>
        </div>
      </div>

      <div class="reports-dashboard-grid">
        <!-- Full-width: Grafik Bulanan -->
        <div class="chart-card full-width-card">
          <div class="chart-header">
            <div class="chart-title-block">
              <h4>Tren Perkembangan Bulanan</h4>
              <span class="badge-active" v-if="selectedYear">{{ selectedYear }}</span>
              <span class="badge" v-else>Semua Tahun</span>
            </div>
            <span class="data-count-label">{{ monthlyPeriodLabel }}</span>
          </div>
          <div class="chart-body chart-body-lg">
            <canvas ref="monthlyChartCanvas"></canvas>
          </div>
        </div>

        <!-- Row 2: Tahunan -->
        <div class="chart-card">
          <div class="chart-header">
            <h4>Tren Perkembangan Tahunan</h4>
            <span class="badge">Klik bar untuk filter</span>
          </div>
          <div class="chart-body">
            <canvas ref="yearlyChartCanvas"></canvas>
          </div>
        </div>

        <!-- Row 2: Distribusi Kategori -->
        <div class="chart-card">
          <div class="chart-header">
            <h4>Distribusi Berdasarkan Kategori</h4>
            <span class="badge">Top Kategori</span>
          </div>
          <div class="chart-body">
            <canvas ref="categoryChartCanvas"></canvas>
          </div>
        </div>

        <!-- Row 3: Media / Cakupan — full width -->
        <div class="chart-card full-width-card">
          <div class="chart-header">
            <h4>{{ activeTab === 'documentation' ? 'Cakupan Wilayah Dokumentasi' : 'Penyebaran Media Publikasi' }}</h4>
            <span class="badge">Distribusi Sumber</span>
          </div>
          <div class="chart-body doughnut-container">
            <canvas ref="mediaChartCanvas"></canvas>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  defaultModule: {
    type: String,
    default: 'news',
    validator: (v) => ['news', 'clippings', 'documentation'].includes(v)
  }
})

const activeTab    = ref(props.defaultModule)
const loading      = ref(true)
const reportData   = ref(null)
const selectedYear = ref(null)  // null = semua tahun

// ── Daftar tahun tersedia ───────────────────────────────────
const availableYears = computed(() => {
  if (!reportData.value) return []
  const d = reportData.value[activeTab.value]
  if (!d) return []
  return d.yearly.map(y => y.period).sort((a, b) => b - a)
})

// ── Data bulanan dari backend (sudah difilter per tahun) ───
const filteredMonthlyData = computed(() => {
  if (!reportData.value) return []
  const d = reportData.value[activeTab.value]
  if (!d) return []
  return d.monthly
})

const monthlyPeriodLabel = computed(() => {
  if (!reportData.value) return '0'
  if (selectedYear.value) {
    const activeMonths = filteredMonthlyData.value.filter(d => parseInt(d.total) > 0).length
    return `${activeMonths} bulan aktif`
  }
  const years = [...new Set(filteredMonthlyData.value.map(d => d.period.split('-')[0]))]
  return `${years.length} Tahun`
})

// ── Deteksi perpindahan antar halaman laporan ───────────────
watch(() => props.defaultModule, async (newModule) => {
  if (activeTab.value === newModule) return
  destroyCharts()
  activeTab.value    = newModule
  reportData.value   = null
  selectedYear.value = null
  await fetchReportData()
})

// ── Update chart saat filter tahun berubah → re-fetch backend
watch(selectedYear, async () => {
  if (monthlyChartInstance) { monthlyChartInstance.destroy(); monthlyChartInstance = null }
  if (yearlyChartInstance)  yearlyChartInstance.update()  // refresh highlight bar
  await fetchReportData()   // ambil data bulanan sesuai tahun dari server
})

const moduleTitles = {
  news: 'Berita Online',
  clippings: 'Kliping Berita',
  documentation: 'Dokumentasi Kegiatan'
}
const moduleDescriptions = {
  news: 'Visualisasi tren data rilis humas dan liputan wartawan di media online',
  clippings: 'Visualisasi tren data kliping surat kabar dan statistik media cetak',
  documentation: 'Visualisasi tren data dokumentasi foto/video dan kehadiran peserta kegiatan'
}

const moduleTitle       = computed(() => moduleTitles[activeTab.value] || 'Humas Hub')
const moduleDescription = computed(() => moduleDescriptions[activeTab.value] || '')

// Canvas refs
const monthlyChartCanvas  = ref(null)
const yearlyChartCanvas   = ref(null)
const categoryChartCanvas = ref(null)
const mediaChartCanvas    = ref(null)

// Chart instances
let monthlyChartInstance  = null
let yearlyChartInstance   = null
let categoryChartInstance = null
let mediaChartInstance    = null

// ── Tema warna per modul ────────────────────────────────────
const getThemeColors = () => {
  if (activeTab.value === 'clippings')
    return { color: 'rgba(124,58,237,1)', bg: 'rgba(124,58,237,0.15)', grad: 'rgba(124,58,237,0.4)' }
  if (activeTab.value === 'documentation')
    return { color: 'rgba(16,185,129,1)', bg: 'rgba(16,185,129,0.15)', grad: 'rgba(16,185,129,0.4)' }
  return { color: 'rgba(99,102,241,1)', bg: 'rgba(99,102,241,0.15)', grad: 'rgba(99,102,241,0.4)' }
}

// ── Ambil Data dari API ─────────────────────────────────────
const fetchReportData = async () => {
  loading.value = true
  try {
    const params = { module: activeTab.value }
    if (selectedYear.value) params.year = selectedYear.value
    const res = await axios.get('/api/admin/reports', { params })
    if (res.data.status === 'success') {
      reportData.value = res.data.data
      loading.value    = false
      await nextTick()
      renderCharts()
    }
  } catch (err) {
    console.error('Gagal mengambil laporan statistik:', err)
    loading.value = false
  }
}

// ── Pilih / hapus filter tahun ──────────────────────────────
const selectYear = (yr) => { selectedYear.value = yr }

// ── Render grafik bulanan ───────────────────────────────────
const renderMonthlyChart = () => {
  if (!monthlyChartCanvas.value) return
  const { color, grad } = getThemeColors()
  const ctx      = monthlyChartCanvas.value.getContext('2d')
  const gradient = ctx.createLinearGradient(0, 0, 0, 360)
  gradient.addColorStop(0, grad)
  gradient.addColorStop(1, 'rgba(255,255,255,0.02)')

  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
  let datasets = []
  let labels = months

  const data = filteredMonthlyData.value
  if (!data || data.length === 0) {
    labels = ['Belum ada data']
    datasets = [{
      label: 'Jumlah Publikasi',
      data: [0],
      borderColor: color,
      borderWidth: 3,
      backgroundColor: gradient,
      fill: true,
      tension: 0.35,
      pointBackgroundColor: color,
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 7
    }]
  } else if (selectedYear.value) {
    // ─── SINGLE YEAR MODE ───
    const values = Array(12).fill(0)
    data.forEach(d => {
      const [yr, mo] = d.period.split('-')
      const monthIdx = parseInt(mo) - 1
      if (monthIdx >= 0 && monthIdx < 12) {
        values[monthIdx] = parseInt(d.total)
      }
    })

    datasets = [{
      label: `Tahun ${selectedYear.value}`,
      data: values,
      borderColor: color,
      borderWidth: 3,
      backgroundColor: gradient,
      fill: true,
      tension: 0.35,
      pointBackgroundColor: color,
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 7
    }]
  } else {
    // ─── SEMUA TAHUN MODE (MULTI-LINE) ───
    const dataByYear = {}
    data.forEach(d => {
      const [yr, mo] = d.period.split('-')
      if (!dataByYear[yr]) {
        dataByYear[yr] = Array(12).fill(0)
      }
      const monthIdx = parseInt(mo) - 1
      if (monthIdx >= 0 && monthIdx < 12) {
        dataByYear[yr][monthIdx] = parseInt(d.total)
      }
    })

    const years = Object.keys(dataByYear).sort((a, b) => parseInt(a) - parseInt(b))

    const colorPalette = [
      'rgba(124, 58, 237, 1)',   // Violet (Clippings)
      'rgba(16, 185, 129, 1)',   // Emerald (Documentation)
      'rgba(99, 102, 241, 1)',   // Indigo (News)
      'rgba(245, 158, 11, 1)',   // Amber
      'rgba(239, 68, 68, 1)',    // Red
      'rgba(6, 182, 212, 1)',    // Cyan
      'rgba(236, 72, 153, 1)',   // Pink
      'rgba(249, 115, 22, 1)',   // Orange
    ]

    datasets = years.map((yr, index) => {
      let lineClr = colorPalette[index % colorPalette.length]
      if (index === 0) {
        lineClr = color
      }
      return {
        label: `Tahun ${yr}`,
        data: dataByYear[yr],
        borderColor: lineClr,
        borderWidth: 3,
        backgroundColor: lineClr.replace('1)', '0.08)'),
        fill: false,
        tension: 0.35,
        pointBackgroundColor: lineClr,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 7
      }
    })
  }

  monthlyChartInstance = new Chart(monthlyChartCanvas.value, {
    type: 'line',
    data: {
      labels: labels,
      datasets: datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: datasets.length > 1,
          position: 'top',
          align: 'end',
          labels: {
            boxWidth: 12,
            padding: 16,
            font: { family: 'Inter', size: 12, weight: '600' },
            color: '#475569'
          }
        },
        tooltip: {
          padding: 12,
          backgroundColor: '#0f172a',
          titleColor: '#fff',
          bodyColor: '#cbd5e1',
          cornerRadius: 10,
          displayColors: datasets.length > 1,
          callbacks: {
            label: (item) => {
              if (datasets.length > 1) {
                return ` ${item.dataset.label}: ${item.raw} entri`
              }
              return ` Total: ${item.raw} entri`
            }
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' } },
        y: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b', stepSize: 1 } }
      }
    }
  })
}

// ── Destroy semua chart ─────────────────────────────────────
const destroyCharts = () => {
  if (monthlyChartInstance)  { monthlyChartInstance.destroy();  monthlyChartInstance  = null }
  if (yearlyChartInstance)   { yearlyChartInstance.destroy();   yearlyChartInstance   = null }
  if (categoryChartInstance) { categoryChartInstance.destroy(); categoryChartInstance = null }
  if (mediaChartInstance)    { mediaChartInstance.destroy();    mediaChartInstance    = null }
}

// ── Render semua chart ──────────────────────────────────────
const renderCharts = () => {
  if (!reportData.value) return
  const currentData = reportData.value[activeTab.value]
  if (!currentData) return

  const { color, bg } = getThemeColors()

  renderMonthlyChart()

  // Grafik Tahunan — klik bar → filter tahun
  if (yearlyChartCanvas.value) {
    const labels = currentData.yearly.map(d => d.period)
    const values = currentData.yearly.map(d => d.total)

    yearlyChartInstance = new Chart(yearlyChartCanvas.value, {
      type: 'bar',
      data: {
        labels: labels.length > 0 ? labels : ['Belum ada data'],
        datasets: [{
          label: 'Total Pertahun',
          data:  values.length > 0 ? values : [0],
          backgroundColor: labels.map(yr => (selectedYear.value && yr == selectedYear.value) ? color : bg),
          borderColor: color,
          borderWidth: 2,
          borderRadius: 8,
          barThickness: 36
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cursor: 'pointer',
        onClick: (_, elements) => {
          if (elements.length > 0) {
            const yr = currentData.yearly[elements[0].index]?.period
            if (yr) selectYear(selectedYear.value === yr ? null : yr)
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            padding: 12, backgroundColor: '#0f172a', titleColor: '#fff',
            bodyColor: '#cbd5e1', cornerRadius: 10, displayColors: false,
            callbacks: {
              title: (items) => `Tahun ${items[0].label}`,
              label: (item) => `Total: ${item.raw} entri`
            }
          }
        },
        scales: {
          x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' } },
          y: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b', stepSize: 1 } }
        }
      }
    })
  }

  // Grafik Kategori (horizontal bar)
  if (categoryChartCanvas.value) {
    const labels = currentData.categories.map(d => d.name)
    const values = currentData.categories.map(d => d.total)

    categoryChartInstance = new Chart(categoryChartCanvas.value, {
      type: 'bar',
      data: {
        labels: labels.length > 0 ? labels : ['Belum ada data'],
        datasets: [{ data: values.length > 0 ? values : [0], backgroundColor: color, borderRadius: 6, barThickness: 16 }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { padding: 12, backgroundColor: '#0f172a', cornerRadius: 10,
            callbacks: { label: (item) => `Total: ${item.raw} entri` } }
        },
        scales: {
          x: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b', stepSize: 1 } },
          y: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 }, color: '#475569' } }
        }
      }
    })
  }

  // Grafik Doughnut Media/Cakupan
  if (mediaChartCanvas.value) {
    const src    = activeTab.value === 'documentation' ? currentData.locations : currentData.media
    const labels = src.map(d => d.name)
    const values = src.map(d => d.total)
    const palette = ['#6366f1','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899','#0ea5e9','#f97316']

    mediaChartInstance = new Chart(mediaChartCanvas.value, {
      type: 'doughnut',
      data: {
        labels: labels.length > 0 ? labels : ['Belum ada data'],
        datasets: [{
          data: values.length > 0 ? values : [0],
          backgroundColor: labels.length > 0 ? palette.slice(0, labels.length) : ['#cbd5e1'],
          borderWidth: 3,
          borderColor: '#fff',
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
          legend: {
            position: 'right',
            labels: { boxWidth: 12, padding: 16, font: { family: 'Inter', size: 12, weight: '500' }, color: '#475569' }
          },
          tooltip: { padding: 12, backgroundColor: '#0f172a', cornerRadius: 10,
            callbacks: { label: (item) => ` ${item.label}: ${item.raw} entri` } }
        }
      }
    })
  }
}

onMounted(() => { fetchReportData() })
onBeforeUnmount(() => { destroyCharts() })
</script>

<style scoped>
.report-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.page-action-header { display: flex; justify-content: space-between; align-items: center; }
.header-text h3 { font-size: 22px; font-weight: 800; color: #0f172a; }
.header-text p  { font-size: 13.5px; color: #64748b; margin-top: 4px; }

/* ── Year Filter ─────────────────────────────────────────── */
.year-filter-bar {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 14px 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.year-filter-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #475569;
  white-space: nowrap;
  flex-shrink: 0;
}
.year-filter-label svg { width: 16px; height: 16px; }
.year-pills { display: flex; flex-wrap: wrap; gap: 8px; }
.year-pill {
  padding: 5px 16px;
  border-radius: 20px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s;
  font-family: inherit;
}
.year-pill:hover { border-color: #94a3b8; color: #334155; background: #f1f5f9; }
.year-pill.active {
  background: #0f172a;
  color: white;
  border-color: #0f172a;
  box-shadow: 0 4px 12px rgba(15,23,42,0.2);
}

/* ── Loader ──────────────────────────────────────────────── */
.chart-loading-card {
  padding: 100px 40px;
  background: white;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  color: #64748b;
}
.loader { width: 32px; height: 32px; border: 3.5px solid #e2e8f0; border-top-color: #0f172a; border-radius: 50%; animation: spin 0.8s infinite linear; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Grid ────────────────────────────────────────────────── */
.reports-dashboard-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
}
.chart-card {
  background: white;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.full-width-card { grid-column: 1 / -1; }

/* ── Chart Header ────────────────────────────────────────── */
.chart-header { display: flex; justify-content: space-between; align-items: center; }
.chart-title-block { display: flex; align-items: center; gap: 10px; }
.chart-header h4 { font-size: 15.5px; font-weight: 800; color: #0f172a; margin: 0; }
.badge { font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 6px; background: #f1f5f9; color: #475569; }
.badge-active { font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 6px; background: #0f172a; color: #fff; }
.data-count-label { font-size: 12px; font-weight: 600; color: #94a3b8; }

/* ── Chart Body ──────────────────────────────────────────── */
.chart-body { position: relative; height: 280px; width: 100%; }
.chart-body-lg { height: 320px; }
.doughnut-container { height: 300px; display: flex; align-items: center; justify-content: center; }

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 1024px) {
  .reports-dashboard-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .page-action-header { flex-direction: column; align-items: flex-start; gap: 8px; }
  .header-text h3 { font-size: 18px; }
  .year-filter-bar { padding: 12px 14px; gap: 10px; }
  .chart-card { padding: 16px; border-radius: 14px; gap: 14px; }
  .chart-body { height: 220px; }
  .chart-body-lg { height: 260px; }
  .doughnut-container { height: 260px; }
  .chart-header h4 { font-size: 14px; }
}
</style>
