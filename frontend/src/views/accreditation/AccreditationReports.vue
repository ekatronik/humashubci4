<template>
  <div class="report-container">
    <!-- Loader state -->
    <div v-if="loading" class="chart-loading-card">
      <div class="loader"></div>
      <span>Mempersiapkan data laporan akreditasi...</span>
    </div>

    <!-- Dashboard Content -->
    <template v-else>
      <!-- Stat Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon faculty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21h15Z" />
            </svg>
          </div>
          <div class="stat-info">
            <span class="stat-label">Total Fakultas</span>
            <span class="stat-value">{{ reportData.total_faculties }}</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon prodi-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
            </svg>
          </div>
          <div class="stat-info">
            <span class="stat-label">Total Program Studi</span>
            <span class="stat-value">{{ reportData.total_prodis }}</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon unggul-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Zm-3 3h.008v.008h-.008v-.008Zm0-3h.008v.008h-.008v-.008Z" />
            </svg>
          </div>
          <div class="stat-info">
            <span class="stat-label">Akreditasi Unggul</span>
            <span class="stat-value">{{ reportData.total_unggul }}</span>
          </div>
        </div>
      </div>

      <div class="reports-dashboard-grid">
        <!-- Chart 1: Akreditasi Ratio (Doughnut) -->
        <div class="chart-card">
          <div class="chart-header">
            <h4>Rasio Peringkat Akreditasi Prodi</h4>
          </div>
          <div class="chart-body doughnut-container">
            <canvas ref="accredChartCanvas"></canvas>
          </div>
        </div>

        <!-- Chart 2: Jenjang Ratio (Pie) -->
        <div class="chart-card">
          <div class="chart-header">
            <h4>Distribusi Jenjang Studi</h4>
          </div>
          <div class="chart-body doughnut-container">
            <canvas ref="jenjangChartCanvas"></canvas>
          </div>
        </div>

        <!-- Chart 3: Prodis per Faculty (Bar Chart) - Full Width -->
        <div class="chart-card full-width-card">
          <div class="chart-header">
            <h4>Jumlah Program Studi Per Fakultas</h4>
          </div>
          <div class="chart-body chart-body-lg">
            <canvas ref="facultyChartCanvas"></canvas>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const loading = ref(true)
const reportData = ref(null)

const accredChartCanvas = ref(null)
const jenjangChartCanvas = ref(null)
const facultyChartCanvas = ref(null)

let accredChartInstance = null
let jenjangChartInstance = null
let facultyChartInstance = null

const fetchReportData = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/accreditation/reports')
    if (res.data.status === 'success') {
      reportData.value = res.data.data
      loading.value = false
      await nextTick()
      renderCharts()
    }
  } catch (err) {
    console.error('Gagal mengambil laporan akreditasi:', err)
    loading.value = false
  }
}

const destroyCharts = () => {
  if (accredChartInstance) { accredChartInstance.destroy(); accredChartInstance = null }
  if (jenjangChartInstance) { jenjangChartInstance.destroy(); jenjangChartInstance = null }
  if (facultyChartInstance) { facultyChartInstance.destroy(); facultyChartInstance = null }
}

const renderCharts = () => {
  if (!reportData.value) return
  const data = reportData.value

  // Chart 1: Accreditation Ratio
  if (accredChartCanvas.value) {
    const rawLabels = Object.keys(data.accreditation_ratio)
    const values = Object.values(data.accreditation_ratio)
    const total = values.reduce((sum, v) => sum + v, 0)
    
    const labels = rawLabels.map((label, idx) => {
      const val = values[idx]
      const percentage = total > 0 ? ((val / total) * 100).toFixed(0) : 0
      return `${label} - ${val} Prodi (${percentage}%)`
    })
    
    const colors = ['#10b981', '#3b82f6', '#f59e0b'] // Green (Unggul), Blue (Baik Sekali), Amber (Baik)

    accredChartInstance = new Chart(accredChartCanvas.value, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
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
            cornerRadius: 10,
            callbacks: {
              label: (item) => ` ${rawLabels[item.dataIndex]}: ${item.raw} Prodi`
            }
          }
        }
      }
    })
  }

  // Chart 2: Jenjang Ratio
  if (jenjangChartCanvas.value) {
    const rawLabels = Object.keys(data.jenjang_ratio)
    const values = Object.values(data.jenjang_ratio)
    const total = values.reduce((sum, v) => sum + v, 0)

    const labels = rawLabels.map((label, idx) => {
      const val = values[idx]
      const percentage = total > 0 ? ((val / total) * 100).toFixed(0) : 0
      return `${label} - ${val} Prodi (${percentage}%)`
    })

    const colors = ['#ef4444', '#8b5cf6', '#0ea5e9', '#64748b', '#10b981'] // Red, Violet, Light Blue, Slate, Emerald

    jenjangChartInstance = new Chart(jenjangChartCanvas.value, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
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
            cornerRadius: 10,
            callbacks: {
              label: (item) => ` ${rawLabels[item.dataIndex]}: ${item.raw} Prodi`
            }
          }
        }
      }
    })
  }

  // Chart 3: Prodis per Faculty
  if (facultyChartCanvas.value) {
    const labels = data.prodi_per_faculty.map(f => f.singkatan)
    const names = data.prodi_per_faculty.map(f => f.nama_fakultas)
    const values = data.prodi_per_faculty.map(f => f.count)

    // Inline plugin to draw numbers at the top of each bar
    const topLabelsPlugin = {
      id: 'topLabels',
      afterDatasetsDraw(chart) {
        const { ctx } = chart
        ctx.save()
        ctx.font = 'bold 11.5px Inter'
        ctx.fillStyle = '#475569'
        ctx.textAlign = 'center'
        ctx.textBaseline = 'bottom'
        
        chart.data.datasets.forEach((dataset, i) => {
          const meta = chart.getDatasetMeta(i)
          meta.data.forEach((bar, index) => {
            const dataVal = dataset.data[index]
            if (dataVal > 0) {
              ctx.fillText(dataVal, bar.x, bar.y - 6)
            }
          })
        })
        ctx.restore()
      }
    }

    facultyChartInstance = new Chart(facultyChartCanvas.value, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Jumlah Prodi',
          data: values,
          backgroundColor: 'rgba(59, 130, 246, 0.8)',
          borderColor: '#2563eb',
          borderWidth: 2,
          borderRadius: 8,
          barThickness: 36
        }]
      },
      plugins: [topLabelsPlugin],
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            padding: 12,
            backgroundColor: '#0f172a',
            cornerRadius: 10,
            callbacks: {
              title: (items) => {
                const idx = items[0].dataIndex
                return names[idx] || items[0].label
              },
              label: (item) => ` Total: ${item.raw} Prodi`
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              font: { family: 'Inter', size: 11, weight: '600' },
              color: '#64748b'
            }
          },
          y: {
            grid: { color: '#f1f5f9' },
            beginAtZero: true,
            grace: '10%',
            ticks: {
              font: { family: 'Inter', size: 11 },
              color: '#64748b',
              stepSize: 1
            }
          }
        }
      }
    })
  }
}

onMounted(() => {
  fetchReportData()
})

onBeforeUnmount(() => {
  destroyCharts()
})
</script>

<style scoped>
.report-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 24px;
}
.stat-card {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 24px;
  background: white;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01);
  transition: all 0.2s ease;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
}
.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}
.stat-icon svg {
  width: 24px;
  height: 24px;
}
.faculty-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.prodi-icon { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
.unggul-icon { background: linear-gradient(135deg, #10b981, #047857); }

.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: 13px; font-weight: 500; color: #64748b; }
.stat-value { font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px; }

/* Loader */
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
.loader {
  width: 32px;
  height: 32px;
  border: 3.5px solid #e2e8f0;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s infinite linear;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Grid */
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
.full-width-card {
  grid-column: 1 / -1;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.chart-header h4 {
  font-size: 15px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}
.badge {
  font-size: 10.5px;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 6px;
  background: #f1f5f9;
  color: #475569;
}

.chart-body {
  position: relative;
  height: 280px;
  width: 100%;
}
.chart-body-lg {
  height: 320px;
}
.doughnut-container {
  height: 280px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 1024px) {
  .reports-dashboard-grid {
    grid-template-columns: 1fr;
  }
}
</style>
