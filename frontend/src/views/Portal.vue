<template>
  <div class="portal-root" :class="{ 'dark-mode': isDarkMode }">
    <!-- ─── NAV BAR ──────────────────────────────────────────── -->
    <header class="portal-nav">
      <div class="nav-container">
        <div class="brand-left-wrapper">
          <!-- Waffle Menu App Launcher (Google-style) -->
          <div class="waffle-menu-container">
            <button class="btn-waffle" @click.stop="toggleWaffleMenu" title="Menu Aplikasi">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="waffle-icon">
                <rect x="3" y="3" width="4" height="4" rx="1" />
                <rect x="10" y="3" width="4" height="4" rx="1" />
                <rect x="17" y="3" width="4" height="4" rx="1" />
                <rect x="3" y="10" width="4" height="4" rx="1" />
                <rect x="10" y="10" width="4" height="4" rx="1" />
                <rect x="17" y="10" width="4" height="4" rx="1" />
                <rect x="3" y="17" width="4" height="4" rx="1" />
                <rect x="10" y="17" width="4" height="4" rx="1" />
                <rect x="17" y="17" width="4" height="4" rx="1" />
              </svg>
            </button>

            <!-- Dropdown grid of shortcuts -->
            <transition name="waffle-fade">
              <div v-if="showWaffleMenu" class="waffle-dropdown" @click.stop>
                <div class="waffle-grid">
                  <div 
                    v-for="(menu, idx) in dynamicMenus" 
                    :key="menu.id" 
                    class="waffle-item" 
                    @click="clickWaffleMenu(menu)"
                  >
                    <div :class="['waffle-item-icon', getShortcutColor(idx)]">
                      {{ menu.icon || '🔗' }}
                    </div>
                    <span class="waffle-item-label">{{ menu.name }}</span>
                  </div>
                </div>
              </div>
            </transition>
          </div>

          <div class="nav-brand" @click="navigateToView('home')" style="cursor: pointer;">
            <div class="brand-logo" :class="{ 'has-img': settings.app_logo }">
              <img v-if="settings.app_logo" :src="getFileUrl(settings.app_logo)" alt="Logo" class="nav-logo-img">
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="nav-actions">
          <div class="live-clock">
            <span class="clock-icon">🕒</span>
            <span class="clock-text">{{ liveTime }}</span>
          </div>
          <button class="theme-toggle" @click="toggleTheme" :title="isDarkMode ? 'Mode Terang' : 'Mode Gelap'">
            <span>{{ isDarkMode ? '☀️' : '🌙' }}</span>
          </button>
          
          <!-- Dashboard button removed from frontend header -->
        </div>
      </div>
    </header>

    <!-- ─── HERO SEARCH & SHORTCUT BAR ───────────────────────── -->
    <section class="hero-search-section" v-if="currentView === 'home'">
      <div class="hero-bg-glow"></div>
      <div class="hero-content">
        <h1 class="hero-title">
          Cari <span class="highlight-text">Informasi Kampus</span>?
        </h1>
        <p class="hero-desc">
          Masukkan kata kunci di kolom pencarian, atau klik menu di bawah.
        </p>

        <!-- Search Bar Container -->
        <div class="search-engine-box">
          <div class="search-input-wrapper">
            <input 
              type="text" 
              v-model="searchQuery" 
              @keyup.enter="handleSearch('internal')"
              placeholder="Ketik kata kunci yang ingin dicari..." 
              class="search-input"
            />
            <!-- Tombol Mic -->
            <button 
              v-if="isSpeechSupported" 
              type="button" 
              class="btn-voice-search" 
              :class="{ active: isListening }"
              @click="toggleVoiceSearch" 
              title="Cari dengan suara"
            >
              <svg viewBox="0 0 24 24" class="mic-icon" fill="currentColor" width="18" height="18">
                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
              </svg>
            </button>
            <button class="btn-clear" v-if="searchQuery" @click="searchQuery = ''">✕</button>
          </div>
          
          <!-- Tombol Google (G) -->
          <button class="btn-google-search" @click="handleSearch('google_cse')" title="Cari di Seluruh Web Kampus (Google)">
            <svg class="google-g-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
            </svg>
            <span class="btn-google-text">Google</span>
          </button>
          
          <!-- Tombol Cari -->
          <button class="btn-search-trigger" @click="handleSearch('internal')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
            </svg>
            <span>Cari</span>
          </button>
        </div>

        <!-- Quick Links -->
        <div class="quick-links">
          <span class="ql-label">Sering dicari:</span>
          <a href="#" @click.prevent="quickSearch('Rektor')">Rektor Baru</a>
          <a href="#" @click.prevent="quickSearch('Fathun Qarib')">Masjid Fathun Qarib</a>
          <a href="#" @click.prevent="quickSearch('Kliping')">Arsip Kliping</a>
          <a href="#" @click.prevent="quickSearch('Internasional')">Mahasiswa Asing</a>
        </div>

        <!-- MSN-STYLE SHORTCUT BAR -->
        <div class="msn-shortcut-bar">
          <template v-if="dynamicMenus.length > 0">
            <div v-for="(menu, idx) in dynamicMenus" :key="menu.id" class="shortcut-item" @click="clickMenu(menu)">
              <div :class="['shortcut-icon', getShortcutColor(idx)]">{{ menu.icon || '🔗' }}</div>
              <span class="shortcut-label">{{ menu.name }}</span>
            </div>
          </template>
          <template v-else>
            <div class="shortcut-item" @click="navigateToView('news')">
              <div class="shortcut-icon bg-green">📰</div>
              <span class="shortcut-label">Berita Online</span>
            </div>
            <div class="shortcut-item" @click="navigateToView('clippings')">
              <div class="shortcut-icon bg-violet">✂️</div>
              <span class="shortcut-label">Kliping Koran</span>
            </div>
            <div class="shortcut-item" @click="navigateToView('documentation')">
              <div class="shortcut-icon bg-blue">📸</div>
              <span class="shortcut-label">Dokumentasi</span>
            </div>
            <div class="shortcut-item" @click="navigateToView('rss')">
              <div class="shortcut-icon bg-amber">📡</div>
              <span class="shortcut-label">Warta RSS</span>
            </div>
            <div class="shortcut-item" @click="navigateToView('accreditation')">
              <div class="shortcut-icon bg-emerald">🎓</div>
              <span class="shortcut-label">Akreditasi</span>
            </div>
          </template>
        </div>

      </div>

      <!-- Scroll Down Indicator -->
      <div class="scroll-down-indicator" @click="scrollToData">
        <span class="scroll-text">Pusat Data</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="scroll-arrow">
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
      </div>
    </section>

    <!-- ─── VIEW 1: HOME MAIN GRID ───────────────────────────── -->
    <main class="portal-main-grid" v-if="currentView === 'home'">
      
      <!-- ── BARIS 1: PUSAT DATA & GRAFIK (Aceh Studies Style) ── -->
      <section id="pusat-data" class="data-hub-section full-width scroll-reveal">
        <div class="section-header">
          <div class="section-title-wrapper">
            <span class="sec-icon">📊</span>
            <h2>Pusat Data & Visualisasi Akademik</h2>
          </div>
          <span class="sec-subtitle">Visualisasi Tren & Kinerja Universitas</span>
        </div>

        <!-- KPI Grid -->
        <div class="kpi-grid">
          <div v-for="(kpi, idx) in kpis" :key="idx" class="kpi-card">
            <div class="kpi-icon-wrapper" :class="kpi.icon">
              <span class="kpi-emoji" v-if="kpi.icon === 'students'">🎓</span>
              <span class="kpi-emoji" v-if="kpi.icon === 'lecturers'">👨‍🏫</span>
              <span class="kpi-emoji" v-if="kpi.icon === 'accreditation'">⭐</span>
              <span class="kpi-emoji" v-if="kpi.icon === 'publications'">📚</span>
            </div>
            <div class="kpi-details">
              <span class="kpi-val">{{ kpi.value }}</span>
              <span class="kpi-lbl">{{ kpi.label }}</span>
              <div class="kpi-trend" :class="kpi.trend">
                <span class="trend-indicator">{{ kpi.trend === 'up' ? '▲' : '▬' }}</span>
                <span class="trend-sub">{{ kpi.sub }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Dashboard Box -->
        <div class="charts-dashboard-container">
          <div class="charts-sidebar-tabs">
            <button 
              :class="['chart-tab-btn', { active: activeChartTab === 'studentTrend' }]"
              @click="switchChartTab('studentTrend')"
            >
              📈 Tren Penerimaan Mahasiswa
            </button>
            <button 
              :class="['chart-tab-btn', { active: activeChartTab === 'international' }]"
              @click="switchChartTab('international')"
            >
              🌏 Asal Negara Mahasiswa Asing
            </button>
            <button 
              :class="['chart-tab-btn', { active: activeChartTab === 'publications' }]"
              @click="switchChartTab('publications')"
            >
              📄 Publikasi Ilmiah Tahunan
            </button>
          </div>

          <div class="chart-canvas-wrapper">
            <div class="chart-loading-overlay" v-if="chartsLoading">
              <div class="spinner"></div>
              <span>Menyiapkan Grafik...</span>
            </div>
            <canvas ref="statsChartCanvas"></canvas>
          </div>
        </div>
      </section>

      <!-- ── BARIS 2: MSN-STYLE WIDGETS GRID ─────────────────── -->
      
      <!-- Widget 1: Khatib Jumat Highlight Fathun Qarib -->
      <section class="portal-widget khatib-widget flex-2 scroll-reveal">
        <div class="widget-header">
          <h3>🕌 Jadwal Khatib Jumat</h3>
          <span class="widget-date-badge">{{ khatibData.date || 'Jumat Ini' }}</span>
        </div>
        
        <div class="widget-content flex-column">
          <!-- Main Highlight: Masjid Fathun Qarib -->
          <div class="khatib-highlight-card" v-if="khatibData.main">
            <div class="card-glow"></div>
            <div class="badge-main-mosque">MASJID UTAMA UIN</div>
            <h4>{{ khatibData.main.mosque_name }}</h4>
            <div class="khatib-details-row">
              <div class="khatib-names">
                <span class="khatib-label">KHATIB UTAMA</span>
                <span class="khatib-name" style="margin-bottom: 0;">{{ khatibData.main.khatib }}</span>
              </div>
            </div>
            <div class="card-footer-info">
              <span><strong>Imam:</strong> {{ khatibData.main.imam }}</span>
              <span><strong>Muadzin:</strong> {{ khatibData.main.muadzin }}</span>
            </div>
          </div>

          <!-- Other Mosques Schedule -->
          <div class="other-khatibs-list">
            <span class="other-khatibs-title">Masjid Sekitar Kampus:</span>
            <div v-for="(mosque, idx) in khatibData.others" :key="idx" class="other-mosque-item">
              <div class="omi-info">
                <h5>{{ mosque.mosque_name }}</h5>
              </div>
              <div class="omi-khatib">
                <span class="omi-kh-name">{{ mosque.khatib }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Widget 2: Shalat & Cuaca Combo -->
      <div class="portal-widget-combo flex-1 scroll-reveal">
        
        <!-- Sub-Widget 2A: Jadwal Shalat -->
        <div class="portal-widget shalat-widget">
          <div class="widget-header">
            <h3>⏰ Jadwal Shalat</h3>
            <span class="widget-sub">Banda Aceh</span>
          </div>
          <div class="widget-content">
            <div class="shalat-next-alert" v-if="nextPrayer">
              <span class="sna-title">Mendekati Waktu:</span>
              <span class="sna-value">{{ nextPrayer.name }} pukul {{ nextPrayer.time }}</span>
              <span class="sna-countdown">Hitung mundur: {{ prayerCountdown }}</span>
            </div>

            <div class="shalat-grid">
              <div 
                v-for="(time, name) in formattedPrayers" 
                :key="name" 
                :class="['shalat-item', { 'next-prayer-active': name === (nextPrayer ? nextPrayer.name : '') }]"
              >
                <span class="sh-name">{{ name }}</span>
                <span class="sh-val">{{ time }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sub-Widget 2B: Cuaca Banda Aceh -->
        <div class="portal-widget weather-widget">
          <div class="widget-header">
            <h3>⛅ Cuaca Live</h3>
            <span class="widget-sub">Banda Aceh</span>
          </div>
          <div class="widget-content weather-content">
            <div class="weather-loading" v-if="weatherLoading">
              <div class="spinner-sm"></div>
              <span>Menghubungkan satelit...</span>
            </div>
            <div class="weather-data" v-else>
              <div class="weather-main-row">
                <div class="weather-icon-wrapper">
                  <!-- Animated Sun/Clouds/Rain SVG -->
                  <svg v-if="weatherInfo.icon === 'sunny'" class="weather-icon anim-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="5" fill="#f59e0b" />
                    <path d="M12 1v3M12 20v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M1 12h3M20 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" />
                  </svg>
                  <svg v-else-if="weatherInfo.icon === 'rainy'" class="weather-icon anim-rain" viewBox="0 0 24 24" fill="none">
                    <path d="M6 10a5 5 0 0 1 9.9-1c.1 0 .2 0 .3 0A3.5 3.5 0 1 1 15 16H7a4 4 0 0 1-1-6Z" fill="#94a3b8" />
                    <path d="M9 18v2M12 18v2M15 18v2" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" />
                  </svg>
                  <svg v-else class="weather-icon anim-float" viewBox="0 0 24 24" fill="none">
                    <path d="M6 11a5 5 0 0 1 9.9-1c.1 0 .2 0 .3 0A3.5 3.5 0 1 1 15 17H7a4 4 0 0 1-1-6Z" fill="#cbd5e1" />
                    <circle cx="18" cy="8" r="3" fill="#f59e0b" opacity="0.8" />
                  </svg>
                </div>
                <div class="weather-temp-block">
                  <span class="weather-temp">{{ weatherInfo.temp }}°C</span>
                  <span class="weather-status">{{ weatherInfo.status }}</span>
                </div>
              </div>
              <div class="weather-stats">
                <span>💨 {{ weatherInfo.wind }} km/j</span>
                <span>💧 Kelembaban: 82%</span>
              </div>
              <!-- Forecast -->
              <div class="weather-forecast">
                <div v-for="(day, idx) in weatherInfo.forecast" :key="idx" class="forecast-day">
                  <span class="fc-name">{{ day.name }}</span>
                  <span class="fc-icon">{{ day.emoji }}</span>
                  <span class="fc-temp">{{ day.min }}°/{{ day.max }}°</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Widget 3: SIM Flight Board & Kurs Currency -->
      <div class="portal-widget-combo flex-1 scroll-reveal">

        <!-- Sub-Widget 3A: Papan Penerbangan SIM (BTJ) -->
        <div class="portal-widget flight-widget">
          <div class="widget-header">
            <h3>✈️ Jadwal Penerbangan SIM (BTJ)</h3>
            <div class="flight-tabs">
              <button :class="{ active: flightTab === 'dep' }" @click="flightTab = 'dep'">DEP</button>
              <button :class="{ active: flightTab === 'arr' }" @click="flightTab = 'arr'">ARR</button>
            </div>
          </div>
          <div class="widget-content no-padding">
            <div class="flight-table">
              <div class="flight-thead">
                <span>Flight</span>
                <span>Dest/Origin</span>
                <span>Time</span>
                <span>Status</span>
              </div>
              <div class="flight-tbody">
                <div v-for="(fl, idx) in currentFlights" :key="idx" class="flight-row">
                  <span class="f-code">{{ fl.code }}</span>
                  <span class="f-dest">{{ fl.target }}</span>
                  <span class="f-time">{{ fl.time }}</span>
                  <span :class="['f-status', fl.statusClass]">{{ fl.status }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sub-Widget 3B: Kurs Mata Uang & Tren -->
        <div class="portal-widget currency-widget">
          <div class="widget-header">
            <h3>💲 Kurs Mata Uang (IDR)</h3>
            <select v-model="selectedCurrency" class="currency-select" @change="updateCurrencyChart">
              <option value="USD">USD (Dolar Amerika)</option>
              <option value="MYR">MYR (Ringgit Malaysia)</option>
              <option value="SAR">SAR (Riyal Arab Saudi)</option>
            </select>
          </div>
          <div class="widget-content flex-column">
            <div class="currency-value-bar">
              <span class="c-val-label">1 {{ selectedCurrency }} = </span>
              <span class="c-val-price">IDR {{ formattedExchangeRate }}</span>
            </div>
            <!-- Small Currency Trend Line Chart -->
            <div class="currency-chart-box">
              <canvas ref="currencyChartCanvas"></canvas>
            </div>
          </div>
        </div>

      </div>

      <!-- Widget 4: Galeri Foto & Video Dokumentasi Kegiatan -->
      <section class="portal-widget gallery-widget flex-2 scroll-reveal">
        <div class="widget-header">
          <h3>📸 Galeri Foto & Dokumentasi Kegiatan</h3>
          <div class="gallery-filters">
            <button :class="{ active: galleryFilter === 'all' }" @click="galleryFilter = 'all'">Semua</button>
            <button :class="{ active: galleryFilter === 'Internal Kampus' }" @click="galleryFilter = 'Internal Kampus'">Internal</button>
            <button :class="{ active: galleryFilter === 'Internasional' }" @click="galleryFilter = 'Internasional'">Internasional</button>
          </div>
        </div>
        <div class="widget-content">
          <div class="gallery-grid">
            <div v-for="doc in filteredDocs" :key="doc.id" class="gallery-card" @click="navigateToDetail('documentation', doc)">
              <div class="gc-img" :style="{ backgroundImage: 'url(' + (getImageUrl(doc.thumbnail_url) || 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=600&auto=format&fit=crop&q=60') + ')' }">
                <div class="gc-overlay">
                  <span class="gc-loc">📍 {{ doc.location_name }}</span>
                  <div class="gc-actions" @click.stop>
                    <a v-if="doc.photo_folder_link" :href="doc.photo_folder_link" target="_blank" class="gc-btn">📷 Foto</a>
                    <a v-if="doc.video_folder_link" :href="doc.video_folder_link" target="_blank" class="gc-btn bg-red">🎥 Video</a>
                  </div>
                </div>
              </div>
              <div class="gc-desc">
                <h5>{{ doc.event_name }}</h5>
                <span class="gc-date">{{ formatDate(doc.event_date) }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── BARIS 3: NEWS AGGREGATOR GRID (Google News Style) ── -->
      <section class="news-aggregator-section full-width scroll-reveal">
        <div class="section-header">
          <div class="section-title-wrapper">
            <span class="sec-icon">📰</span>
            <h2>Agregator Berita Kampus & Kliping</h2>
          </div>
          <span class="sec-subtitle">Kompilasi informasi harian terhangat seputar UIN Ar-Raniry</span>
        </div>

        <div class="news-aggregator-layout">
          
          <!-- Column 1: Headline Sorotan Utama (Span 2) -->
          <div class="headline-container">
            <div class="news-card headline-card" v-if="featuredNews" @click="openLink(featuredNews.news_link)">
              <div class="hc-image-placeholder">
                <div class="hc-overlay"></div>
                <span class="hc-tag">Headline Terkini</span>
                <div class="hc-meta">
                  <span class="hc-date">{{ formatDate(featuredNews.news_date) }}</span>
                  <span class="hc-media">{{ featuredNews.media_name || 'Rilis Humas' }}</span>
                </div>
              </div>
              <div class="hc-content">
                <h3>{{ featuredNews.title }}</h3>
                <p>Klik untuk membuka rilis berita resmi lengkap dan meliput detail informasi kegiatan kampus UIN Ar-Raniry lebih lanjut...</p>
                <div class="hc-action">
                  <span>Baca Selengkapnya</span>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Column 2: Feeds Grid (Humas, Kliping, RSS) -->
          <div class="feeds-grid-container">
            
            <!-- Feed A: Berita Online Humas -->
            <div class="feed-column">
              <div class="feed-header bg-green">
                <h4>🟢 Rilis Humas (Berita)</h4>
              </div>
              <div class="feed-list">
                <div v-for="ns in localNewsList" :key="ns.id" class="feed-item" @click="openLink(ns.news_link)">
                  <span class="fi-date">{{ formatDate(ns.news_date) }}</span>
                  <h5>{{ ns.title }}</h5>
                  <div class="fi-tags">
                    <span class="fi-tag bg-green-light">{{ ns.source_type }}</span>
                    <span v-for="cat in ns.categories" :key="cat" class="fi-tag">{{ cat }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Feed B: Kliping Media Pers -->
            <div class="feed-column">
              <div class="feed-header bg-violet">
                <h4>🟣 Kliping Koran / Pers</h4>
              </div>
              <div class="feed-list">
                <div v-for="cl in clippingList" :key="cl.id" class="feed-item" @click="navigateToDetail('clippings', cl)">
                  <span class="fi-date">{{ formatDate(cl.clipping_date) }}</span>
                  <h5>{{ cl.title }}</h5>
                  <p class="fi-desc" v-if="cl.summary">{{ truncate(cl.summary, 60) }}</p>
                  <div class="fi-tags">
                    <span class="fi-tag bg-violet-light">{{ cl.media_name }}</span>
                    <span class="fi-tag">{{ cl.storage_building }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Feed C: Warta Kampus (RSS Feeds) -->
            <div class="feed-column">
              <div class="feed-header bg-amber">
                <h4>🟡 Sindikasi Warta Kampus (RSS)</h4>
              </div>
              <div class="feed-list">
                <div v-for="rss in rssList" :key="rss.id" class="feed-item" @click="openLink(rss.link)">
                  <span class="fi-date">{{ formatDate(rss.pub_date) }}</span>
                  <h5>{{ rss.title }}</h5>
                  <div class="fi-tags">
                    <span class="fi-tag bg-amber-light">{{ rss.site_name }}</span>
                    <span class="fi-tag" v-if="rss.creator">{{ rss.creator }}</span>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>
      </section>

    </main>

    <!-- ─── VIEW 2: SUB-VIEW BERITA (Google News style) ──────── -->
    <main class="portal-main-container" v-else-if="currentView === 'news'">
      <div class="subview-header-bar">
        <button class="btn-back-home" @click="navigateToView('home')">
          <span>← Kembali ke Halaman Utama</span>
        </button>
        <h2>🟢 Portal Berita & Rilis Humas UIN</h2>
      </div>

      <!-- Laporan Distribusi Statistik -->
      <div class="reports-distribution-panel">
        <div class="rep-card">
          <h4>📊 Berita Berdasarkan Kategori</h4>
          <div class="rep-list">
            <div v-for="cat in mediaStatsData.categories" :key="cat.id" class="rep-item">
              <div class="rep-item-lbl">
                <span>{{ cat.name }}</span>
                <strong>{{ cat.count }} rilis</strong>
              </div>
              <div class="rep-bar-wrapper">
                <div class="rep-bar bg-green" :style="{ width: getPercentage(cat.count, statsTotals.news) + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="rep-card">
          <h4>📰 Berita Berdasarkan Sumber Media</h4>
          <div class="rep-list">
            <div v-for="med in mediaStatsData.media" :key="med.id" class="rep-item">
              <div class="rep-item-lbl">
                <span>{{ med.media_name }}</span>
                <strong>{{ med.count }} rilis</strong>
              </div>
              <div class="rep-bar-wrapper">
                <div class="rep-bar bg-blue" :style="{ width: getPercentage(med.count, statsTotals.news) + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Google News Etalase Berita -->
      <div class="etalase-section">
        <h3>📰 Etalase Berita (Media Pilihan)</h3>
        <p class="section-info-desc">Artikel terbaru yang disajikan langsung oleh masing-masing media mitra UIN Ar-Raniry.</p>
        
        <div class="etalase-grid">
          <div v-for="item in etalaseList" :key="item.media_id" class="etalase-card">
            <div class="ec-header">
              <div class="ec-media-title" @click="filterNewsByMedia(item.media_id)">
                <h4>{{ item.media_name }}</h4>
              </div>
              <button class="btn-follow" @click="toggleFollowMedia(item.media_id)">
                <span>{{ followedMedia.includes(item.media_id) ? '⭐ Diikuti' : '☆ Ikuti' }}</span>
              </button>
            </div>
            
            <div class="ec-news-list">
              <div v-for="n in item.news" :key="n.id" class="ec-news-item" @click="openLink(n.news_link)">
                <span class="ec-news-date">{{ formatDate(n.news_date) }}</span>
                <h5>{{ n.title }}</h5>
              </div>
              <div v-if="item.news.length === 0" class="ec-empty">Tidak ada berita terbaru.</div>
            </div>
            <button class="ec-footer" @click="filterNewsByMedia(item.media_id)">
              Lihat Semua dari {{ item.media_name }} →
            </button>
          </div>
        </div>
      </div>

      <!-- Tabel Berita dengan Filter -->
      <div class="table-card">
        <div class="table-card-header">
          <h3>📋 Daftar Lengkap Berita Online</h3>
          <div class="table-filters">
            <input type="text" v-model="newsFilters.search" placeholder="Cari judul..." @input="fetchNewsTable" class="filter-search" />
            <select v-model="newsFilters.categoryId" @change="fetchNewsTable" class="filter-select">
              <option value="">Semua Kategori</option>
              <option v-for="cat in categoriesList" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
            <select v-model="newsFilters.mediaId" @change="fetchNewsTable" class="filter-select">
              <option value="">Semua Media</option>
              <option v-for="med in mediaList" :key="med.id" :value="med.id">{{ med.media_name }}</option>
            </select>
            <button class="btn-reset-filters" @click="resetNewsFilters">Reset</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Judul Berita</th>
                <th>Media</th>
                <th>Tipe Sumber</th>
                <th>Kategori</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(n, idx) in newsTableData.items" :key="n.id">
                <td>{{ (newsTableData.page - 1) * newsTableData.limit + idx + 1 }}</td>
                <td>{{ formatDate(n.news_date) }}</td>
                <td class="font-bold text-slate">{{ n.title }}</td>
                <td><span class="badge badge-media">{{ n.media_name || 'Rilis Humas' }}</span></td>
                <td><span class="badge badge-source">{{ n.source_type }}</span></td>
                <td>
                  <div class="tags-row">
                    <span v-for="cat in n.categories" :key="cat" class="table-tag">{{ cat }}</span>
                  </div>
                </td>
                <td>
                  <button @click="openLink(n.news_link)" class="btn-action">Buka Berita ↗</button>
                </td>
              </tr>
              <tr v-if="newsTableData.items.length === 0">
                <td colspan="7" class="text-center py-4">Data berita tidak ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginasi -->
        <div class="table-pagination" v-if="newsTableData.total_pages > 1">
          <button :disabled="newsTableData.page === 1" @click="changeNewsPage(newsTableData.page - 1)">« Prev</button>
          <span>Halaman {{ newsTableData.page }} dari {{ newsTableData.total_pages }}</span>
          <button :disabled="newsTableData.page === newsTableData.total_pages" @click="changeNewsPage(newsTableData.page + 1)">Next »</button>
        </div>
      </div>
    </main>

    <!-- ─── VIEW 3: SUB-VIEW KLIPING ────────────────────────── -->
    <main class="portal-main-container" v-else-if="currentView === 'clippings'">
      <div class="subview-header-bar">
        <button class="btn-back-home" @click="navigateToView('home')">
          <span>← Kembali ke Halaman Utama</span>
        </button>
        <h2>🟣 Pusat Arsip Kliping Koran & Pers UIN</h2>
      </div>

      <!-- Filter Card -->
      <div class="filter-card clipping-filter-card">
        <div class="filter-grid-layout">
          <!-- Row 1: Filters -->
          <div class="filters-row">
            <!-- Rentang Waktu Berita -->
            <div class="filter-group date-range-group">
              <label class="filter-label">Rentang Waktu Berita</label>
              <div class="date-inputs-wrapper">
                <div class="date-input-field">
                  <span class="date-field-label">Mulai</span>
                  <input type="date" v-model="filterStartDate" @change="onClippingDateRangeChange" class="date-input-native" />
                </div>
                <div class="date-range-arrow">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                  </svg>
                </div>
                <div class="date-input-field">
                  <span class="date-field-label">Selesai</span>
                  <input type="date" v-model="filterEndDate" @change="onClippingDateRangeChange" class="date-input-native" />
                </div>
              </div>
            </div>

            <!-- Media Cetak -->
            <div class="filter-group">
              <label class="filter-label">Media Cetak</label>
              <select v-model="filterMedia" @change="fetchClippingsViewData" class="clipping-select">
                <option value="">Semua Media</option>
                <option v-for="m in mediaCetakList" :key="m.id" :value="m.id">{{ m.media_name }}</option>
              </select>
            </div>

            <!-- Kategori -->
            <div class="filter-group">
              <label class="filter-label">Kategori</label>
              <select v-model="filterCategory" @change="fetchClippingsViewData" class="clipping-select">
                <option value="">Semua Kategori</option>
                <option v-for="c in categoriesList" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <!-- Triwulan -->
            <div class="filter-group">
              <label class="filter-label">Triwulan (TW)</label>
              <select v-model="filterQuarter" @change="onClippingQuarterChange" class="clipping-select">
                <option value="">Pilih Triwulan</option>
                <option value="1">Tw 1 (Jan-Mar)</option>
                <option value="2">Tw 2 (Apr-Jun)</option>
                <option value="3">Tw 3 (Jul-Sep)</option>
                <option value="4">Tw 4 (Okt-Des)</option>
              </select>
            </div>
          </div>

          <!-- Row 2: Search & Reset -->
          <div class="search-row">
            <div class="filter-group keyword-search-group">
              <label class="filter-label">Cari Kata Kunci</label>
              <div class="search-actions-row">
                <div class="search-input-container">
                  <svg class="search-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                  </svg>
                  <input
                    v-model="clippingSearchQuery"
                    type="text"
                    placeholder="Cari judul..."
                    @input="debounceFetchClippings"
                    class="search-input-field"
                  />
                </div>
                <button @click="resetClippingFilters" class="btn-filter-reset" title="Reset Filter">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                  </svg>
                  <span>Reset Filter</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabel Kliping -->
      <div class="table-card">
        <div class="table-card-header">
          <h3>📋 Daftar Arsip Kliping Pers</h3>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Judul Kliping Koran</th>
                <th>Media Koran</th>
                <th>Kategori</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(cl, idx) in clippingsTableData.items" :key="cl.id">
                <td>{{ (clippingsTableData.page - 1) * clippingsTableData.limit + idx + 1 }}</td>
                <td>{{ formatDate(cl.clipping_date) }}</td>
                <td class="font-bold text-slate">{{ cl.title }}</td>
                <td><span class="badge badge-media-violet">{{ cl.media_name }}</span></td>
                <td>
                  <div class="tags-row" v-if="cl.categories && cl.categories.length > 0">
                    <span v-for="cat in cl.categories" :key="cat" class="table-tag bg-violet-light">{{ cat }}</span>
                  </div>
                  <span v-else class="text-slate-400 text-xs">—</span>
                </td>
                <td>
                  <button @click="navigateToDetail('clippings', cl)" class="btn-action-violet">Lihat Detail Arsip</button>
                </td>
              </tr>
              <tr v-if="clippingsTableData.items.length === 0">
                <td colspan="6" class="text-center py-4">Arsip kliping koran tidak ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="table-pagination" v-if="clippingsTableData.total_pages > 1">
          <button :disabled="clippingsTableData.page === 1" @click="changeClippingsPage(clippingsTableData.page - 1)">« Prev</button>
          <span>Halaman {{ clippingsTableData.page }} dari {{ clippingsTableData.total_pages }}</span>
          <button :disabled="clippingsTableData.page === clippingsTableData.total_pages" @click="changeClippingsPage(clippingsTableData.page + 1)">Next »</button>
        </div>
      </div>
    </main>

    <!-- ─── VIEW 4: DETAIL KLIPING ──────────────────────────── -->
    <main class="portal-main-container" v-else-if="currentView === 'clipping-detail'">
      <div class="subview-header-bar" v-if="clippingDetailData">
        <button class="btn-back-home" @click="navigateToView('clippings')">
          <span>← Kembali ke Daftar Kliping</span>
        </button>
        <h2>🟣 Rincian Arsip Kliping Koran</h2>
      </div>

      <div class="detail-container" v-if="clippingDetailData" :class="{ 'clipping-split-layout': clippingDetailData.file_path }">
        <!-- Visual Indeks Card -->
        <div class="index-card">
          <div class="index-card-header">
            <span class="ic-badge">KODE ARSIP: CLP-{{ clippingDetailData.id }}</span>
            <span :class="['badge-status', clippingDetailData.is_borrowable ? 'status-green' : 'status-red']">
              {{ clippingDetailData.is_borrowable ? 'Status: BISA DIPINJAM' : 'Status: HANYA BACA DI TEMPAT' }}
            </span>
          </div>
          
          <h3 class="ic-title">{{ clippingDetailData.title }}</h3>
          
          <div class="ic-meta-grid">
            <div class="ic-meta-item">
              <span class="ic-meta-lbl">Tanggal Terbit</span>
              <span class="ic-meta-val">{{ formatDate(clippingDetailData.clipping_date) }}</span>
            </div>
            <div class="ic-meta-item">
              <span class="ic-meta-lbl">Media Cetak</span>
              <span class="ic-meta-val">{{ clippingDetailData.media_name }}</span>
            </div>
            <div class="ic-meta-item">
              <span class="ic-meta-lbl">Kategori Arsip</span>
              <span class="ic-meta-val">{{ clippingDetailData.categories ? clippingDetailData.categories.join(', ') : 'Umum' }}</span>
            </div>
          </div>

          <div class="ic-media-logo-section" v-if="clippingDetailData.media_name">
            <span class="ic-meta-lbl">Penerbit Media</span>
            <div class="ic-media-logo-wrapper">
              <img v-if="clippingDetailData.media_logo" :src="getMediaLogoUrl(clippingDetailData.media_logo)" :alt="clippingDetailData.media_name" class="ic-media-logo" />
              <span v-else class="ic-media-name-fallback">{{ clippingDetailData.media_name }}</span>
            </div>
          </div>

          <div class="ic-section" v-if="clippingDetailData.summary">
            <h4>Ringkasan Konten Kliping</h4>
            <p>{{ clippingDetailData.summary }}</p>
          </div>

          <!-- LOKASI PENYIMPANAN FISIK -->
          <div class="ic-section location-info-box">
            <h4>📍 Posisi Penyimpanan Arsip Fisik</h4>
            <div class="loc-grid">
              <div class="loc-item">
                <span class="loc-lbl">Gedung</span>
                <span class="loc-val">{{ clippingDetailData.storage_building || 'Gedung Rektorat UIN' }}</span>
              </div>
              <div class="loc-item">
                <span class="loc-lbl">Ruangan</span>
                <span class="loc-val">Ruang {{ clippingDetailData.storage_room || 'Humas Lt. 1' }}</span>
              </div>
              <div class="loc-item">
                <span class="loc-lbl">Nomor Rak</span>
                <span class="loc-val">Rak {{ clippingDetailData.storage_rack || 'R-04' }}</span>
              </div>
              <div class="loc-item">
                <span class="loc-lbl">Nomor Map / Folder</span>
                <span class="loc-val">Folder {{ clippingDetailData.storage_folder || 'F-12' }}</span>
              </div>
            </div>
          </div>

          <!-- Aksi Unduh PDF -->
          <div class="ic-actions" v-if="clippingDetailData.file_path">
            <a :href="getPdfUrl(clippingDetailData.file_path)" target="_blank" class="btn-download-pdf">
              📥 Unduh / Buka Dokumen PDF Kliping
            </a>
          </div>
        </div>

        <!-- SISI KANAN: PDF Viewer (70% width) -->
        <div class="clipping-viewer-panel" v-if="clippingDetailData.file_path">
          <iframe :src="getPdfUrl(clippingDetailData.file_path)" class="clipping-pdf-iframe" title="PDF Viewer"></iframe>
        </div>
      </div>
    </main>

    <!-- ─── VIEW 5: SUB-VIEW DOKUMENTASI ────────────────────── -->
    <main class="portal-main-container" v-else-if="currentView === 'documentation'">
      <div class="subview-header-bar">
        <button class="btn-back-home" @click="navigateToView('home')">
          <span>← Kembali ke Halaman Utama</span>
        </button>
        <h2>🔵 Pusat Dokumentasi Kegiatan Rektorat</h2>
      </div>

      <!-- Filter Card -->
      <div class="filter-card clipping-filter-card" style="margin-bottom: 24px;">
        <div class="filter-grid-layout">
          <!-- Row 1: Filters -->
          <div class="filters-row">
            <!-- Rentang Waktu Kegiatan -->
            <div class="filter-group date-range-group">
              <label class="filter-label">Rentang Waktu Kegiatan</label>
              <div class="date-inputs-wrapper">
                <div class="date-input-field">
                  <span class="date-field-label">Mulai</span>
                  <input type="date" v-model="filterDocStartDate" @change="onDocDateRangeChange" class="date-input-native" />
                </div>
                <div class="date-range-arrow">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                  </svg>
                </div>
                <div class="date-input-field">
                  <span class="date-field-label">Selesai</span>
                  <input type="date" v-model="filterDocEndDate" @change="onDocDateRangeChange" class="date-input-native" />
                </div>
              </div>
            </div>

            <!-- Jenis Lokasi -->
            <div class="filter-group">
              <label class="filter-label">Jenis Lokasi</label>
              <select v-model="filterLocationType" @change="fetchDocumentationViewData" class="clipping-select">
                <option value="">Semua Lokasi</option>
                <option value="Internal Kampus">Internal Kampus</option>
                <option value="Lokal Daerah">Lokal Daerah</option>
                <option value="Nasional">Nasional</option>
                <option value="Internasional">Internasional</option>
              </select>
            </div>

            <!-- Kategori -->
            <div class="filter-group">
              <label class="filter-label">Kategori</label>
              <select v-model="filterDocCategory" @change="fetchDocumentationViewData" class="clipping-select">
                <option value="">Semua Kategori</option>
                <option v-for="c in categoriesList" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <!-- Triwulan -->
            <div class="filter-group">
              <label class="filter-label">Triwulan (TW)</label>
              <select v-model="filterDocQuarter" @change="onDocQuarterChange" class="clipping-select">
                <option value="">Pilih Triwulan</option>
                <option value="1">Tw 1 (Jan-Mar)</option>
                <option value="2">Tw 2 (Apr-Jun)</option>
                <option value="3">Tw 3 (Jul-Sep)</option>
                <option value="4">Tw 4 (Okt-Des)</option>
              </select>
            </div>
          </div>

          <!-- Row 2: Search & Reset -->
          <div class="search-row">
            <div class="filter-group keyword-search-group">
              <label class="filter-label">Cari Kata Kunci</label>
              <div class="search-actions-row">
                <div class="search-input-container">
                  <svg class="search-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                  </svg>
                  <input
                    v-model="docSearchQuery"
                    type="text"
                    placeholder="Cari judul atau lokasi..."
                    @input="debounceFetchDoc"
                    class="search-input-field"
                  />
                </div>
                <button @click="resetDocFilters" class="btn-filter-reset" title="Reset Filter">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                  </svg>
                  <span>Reset Filter</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid Dokumentasi -->
      <div class="documentation-portal-grid">
        <div v-for="doc in documentationTableData.items" :key="doc.id" class="doc-portal-card" @click="navigateToDetail('documentation', doc)">
          <div class="dpc-img" :style="{ backgroundImage: 'url(' + (getImageUrl(doc.thumbnail_url) || 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=600&auto=format&fit=crop&q=60') + ')' }">
            <span class="dpc-loc-badge">{{ doc.location_type }}</span>
          </div>
          <div class="dpc-content">
            <span class="dpc-date">{{ formatDate(doc.event_date) }}</span>
            <h4>{{ doc.event_name }}</h4>
            <span class="dpc-room">📍 {{ doc.location_name }}</span>
            <div class="dpc-footer">
              <span class="btn-detail-link">Lihat Selengkapnya →</span>
            </div>
          </div>
        </div>
        <div v-if="documentationTableData.items.length === 0" class="empty-docs-portal">Dokumentasi kegiatan belum tersedia.</div>
      </div>

      <div class="table-pagination" v-if="documentationTableData.total_pages > 1">
        <button :disabled="documentationTableData.page === 1" @click="changeDocPage(documentationTableData.page - 1)">« Prev</button>
        <span>Halaman {{ documentationTableData.page }} dari {{ documentationTableData.total_pages }}</span>
        <button :disabled="documentationTableData.page === documentationTableData.total_pages" @click="changeDocPage(documentationTableData.page + 1)">Next »</button>
      </div>
    </main>

    <!-- ─── VIEW 6: DETAIL DOKUMENTASI & DAFTAR HADIR ────────── -->
    <main class="portal-main-container" v-else-if="currentView === 'doc-detail'">
      <div class="subview-header-bar" v-if="docDetailData">
        <button class="btn-back-home" @click="navigateToView('documentation')">
          <span>← Kembali ke Daftar Dokumentasi</span>
        </button>
        <h2>🔵 Rincian Dokumentasi Kegiatan UIN</h2>
      </div>

      <div class="detail-container" v-if="docDetailData">
        <div class="index-card">
          <div class="index-card-header">
            <span class="ic-badge bg-blue">DOKUMENTASI EVENT</span>
            <span class="ic-badge">📍 {{ docDetailData.location_name }} ({{ docDetailData.location_type }})</span>
          </div>

          <div class="ic-banner" v-if="docDetailData.thumbnail_url">
            <img :src="getImageUrl(docDetailData.thumbnail_url)" :alt="docDetailData.event_name" referrerpolicy="no-referrer" />
          </div>
          
          <h3 class="ic-title">{{ docDetailData.event_name }}</h3>
          
          <div class="ic-meta-grid">
            <div class="ic-meta-item">
              <span class="ic-meta-lbl">Tanggal Kegiatan</span>
              <span class="ic-meta-val">{{ formatDate(docDetailData.event_date) }}</span>
            </div>
            <div class="ic-meta-item">
              <span class="ic-meta-lbl">Kategori Event</span>
              <span class="ic-meta-val">{{ docDetailData.categories ? docDetailData.categories.join(', ') : 'Umum' }}</span>
            </div>
            <div class="ic-meta-item" v-if="docDetailData.creator_name">
              <span class="ic-meta-lbl">Petugas Dokumentasi</span>
              <span class="ic-meta-val">{{ docDetailData.creator_name }}</span>
            </div>
          </div>

          <div class="ic-section" v-if="docDetailData.description">
            <h4>Deskripsi Kegiatan</h4>
            <p>{{ docDetailData.description }}</p>
          </div>

          <!-- DAFTAR HADIR TOKOH / PEJABAT -->
          <div class="ic-section">
            <h4>👥 Daftar Kehadiran Tokoh (Rektorat & Fakultas)</h4>
            <div class="attendance-table-wrapper">
              <table class="attendance-table">
                <thead>
                  <tr>
                    <th>Level</th>
                    <th>Jabatan</th>
                    <th>Nama Pejabat Hadir</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="person in docDetailData.attendance" :key="person.id">
                    <td>
                      <span :class="['badge-level', person.level === 'Rektorat' ? 'bg-green' : 'bg-blue']">
                        {{ person.level && person.level.trim() !== '' ? person.level : 'Lainnya' }}
                      </span>
                    </td>
                    <td class="font-bold">{{ person.position }}</td>
                    <td>{{ person.person_name }}</td>
                  </tr>
                  <tr v-if="!docDetailData.attendance || docDetailData.attendance.length === 0">
                    <td colspan="3" class="text-center py-4">Data daftar hadir tokoh tidak diinput untuk kegiatan ini.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tautan Drive Galeri -->
          <div class="ic-section" v-if="docDetailData.photo_folder_link || docDetailData.video_folder_link">
            <h4>📂 Akses & Pratinjau Dokumentasi</h4>
            <div class="drive-links-row">
              <a v-if="docDetailData.photo_folder_link" :href="docDetailData.photo_folder_link" target="_blank" class="drive-btn">
                📷 Buka Google Drive Album Foto
              </a>
              <a v-if="docDetailData.video_folder_link" :href="docDetailData.video_folder_link" target="_blank" class="drive-btn bg-red">
                🎥 Putar Dokumentasi Video
              </a>
            </div>

            <!-- Preview iframes -->
            <div class="iframe-grid" :class="{'single-col': !docDetailData.photo_folder_link || !docDetailData.video_folder_link}" style="margin-top: 20px;">
              <div class="iframe-box" v-if="docDetailData.photo_folder_link">
                <div class="iframe-label">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px; color: #3b82f6;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                  <span>Pratinjau Album Foto</span>
                </div>
                <iframe v-if="getIframeUrl(docDetailData.photo_folder_link)" :src="getIframeUrl(docDetailData.photo_folder_link)" frameborder="0" allowfullscreen></iframe>
                <div v-else class="iframe-error">Link folder foto tidak valid untuk pratinjau.</div>
              </div>

              <div class="iframe-box" v-if="docDetailData.video_folder_link">
                <div class="iframe-label" style="color: #8b5cf6;">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px; color: #8b5cf6;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" /></svg>
                  <span>Pratinjau Video</span>
                </div>
                <iframe v-if="getIframeUrl(docDetailData.video_folder_link)" :src="getIframeUrl(docDetailData.video_folder_link)" frameborder="0" allowfullscreen></iframe>
                <div v-else class="iframe-error">Link folder video tidak valid untuk pratinjau.</div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>

    <!-- ─── VIEW 7: SUB-VIEW RSS ────────────────────────────── -->
    <main class="portal-main-container" v-else-if="currentView === 'rss'">
      <div class="subview-header-bar">
        <button class="btn-back-home" @click="navigateToView('home')">
          <span>← Kembali ke Halaman Utama</span>
        </button>
        <h2>🟡 Sindikasi Warta Kampus (RSS Feeds)</h2>
      </div>

      <div class="rss-portal-list">
        <div v-for="rss in rssList" :key="rss.id" class="rss-portal-card" @click="openLink(rss.link)">
          <div class="rpc-img" v-if="rss.image_url" :style="{ backgroundImage: 'url(' + rss.image_url + ')' }"></div>
          <div class="rpc-content">
            <span class="rpc-site">{{ rss.site_name }}</span>
            <h4>{{ rss.title }}</h4>
            <p v-if="rss.description">{{ truncate(rss.description, 150) }}</p>
            <div class="rpc-footer">
              <span class="rpc-date">{{ formatDate(rss.pub_date) }}</span>
              <span class="rpc-creator" v-if="rss.creator">Oleh: {{ rss.creator }}</span>
            </div>
          </div>
        </div>
        <div v-if="rssList.length === 0" class="text-center py-8">Belum ada berita RSS tersinkronisasi.</div>
      </div>
    </main>

    <!-- ─── SUB-VIEW AKREDITASI ────────────────────────────── -->
    <main class="portal-main-container" v-else-if="currentView === 'accreditation'">
      <div class="subview-header-bar" style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
          <button class="btn-back-home" @click="navigateToView('home')">
            <span>← Kembali ke Halaman Utama</span>
          </button>
          <h2 style="margin: 0;">🎓 Portal Informasi Akreditasi Program Studi</h2>
        </div>
        <button @click="openReportsModal" class="btn-filter-reset" style="background: #10b981; color: white; border-color: #10b981; height: 38px; padding: 0 16px; margin: 0; display: inline-flex; align-items: center; justify-content: center;" title="Buka Laporan Analisis">
          <span>📊 Laporan Analisis</span>
        </button>
      </div>

      <!-- Filter Card -->
      <div class="filter-card clipping-filter-card" style="margin-bottom: 24px;">
        <div class="filter-grid-layout">
          <!-- Row 1: Filters -->
          <div class="filters-row">
            <!-- Fakultas -->
            <div class="filter-group">
              <label class="filter-label">Fakultas</label>
              <select v-model="accreditationFilters.facultyId" @change="fetchAccreditationProdis" class="clipping-select">
                <option value="">Semua Fakultas</option>
                <option v-for="f in publicFaculties" :key="f.id" :value="f.id">{{ f.singkatan }}</option>
              </select>
            </div>

            <!-- Jenjang -->
            <div class="filter-group">
              <label class="filter-label">Jenjang</label>
              <select v-model="accreditationFilters.jenjang" @change="fetchAccreditationProdis" class="clipping-select">
                <option value="">Semua Jenjang</option>
                <option value="D4">D4</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
                <option value="Profesi">Profesi</option>
              </select>
            </div>

            <!-- Peringkat Akreditasi -->
            <div class="filter-group">
              <label class="filter-label">Peringkat Akreditasi</label>
              <select v-model="accreditationFilters.akreditasi" @change="fetchAccreditationProdis" class="clipping-select">
                <option value="">Semua Akreditasi</option>
                <option value="Unggul">Unggul</option>
                <option value="Baik Sekali">Baik Sekali</option>
                <option value="Baik">Baik</option>
              </select>
            </div>

            <!-- Jalur Masuk -->
            <div class="filter-group">
              <label class="filter-label">Jalur Masuk</label>
              <select v-model="accreditationFilters.jalurMasuk" @change="fetchAccreditationProdis" class="clipping-select">
                <option value="">Semua Jalur</option>
                <option value="SNBT">SNBT</option>
                <option value="UTBK">UTBK</option>
                <option value="SPAN">SPAN</option>
                <option value="UM-PTKIN">UM-PTKIN</option>
                <option value="Portofolio UTBK">Portofolio UTBK</option>
                <option value="Portofolio UM-PTKIN">Portofolio UM-PTKIN</option>
                <option value="Reguler UINAR">Reguler UINAR</option>
              </select>
            </div>
          </div>

          <!-- Row 2: Search & Actions -->
          <div class="search-row">
            <div class="filter-group keyword-search-group">
              <label class="filter-label">Cari Program Studi</label>
              <div class="search-actions-row">
                <div class="search-input-container">
                  <svg class="search-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                  </svg>
                  <input
                    v-model="accreditationFilters.search"
                    type="text"
                    placeholder="Masukkan nama prodi..."
                    @input="debounceFetchAccreditation"
                    class="search-input-field"
                  />
                </div>

                <button @click="resetAccreditationFilters" class="btn-filter-reset" title="Reset Filter">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                  </svg>
                  <span>Reset Filter</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabel Prodi -->
      <div class="table-card">
        <div class="table-card-header">
          <h3>📋 Daftar Peringkat Akreditasi Program Studi</h3>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 60px;">No</th>
                <th>Program Studi</th>
                <th>Fakultas</th>
                <th>Jenjang</th>
                <th>Akreditasi Saat Ini</th>
                <th>Masa Berlaku</th>
                <th>Countdown Expired</th>
                <th>Jalur Masuk</th>
                <th style="text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, idx) in paginatedAccreditationProdis" :key="p.id">
                <td>{{ (accreditationPage - 1) * accreditationPageSize + idx + 1 }}</td>
                <td class="font-bold text-slate">{{ p.nama_prodi }}</td>
                <td><span class="badge badge-media-violet">{{ p.singkatan_fakultas }}</span></td>
                <td>
                  <span class="table-tag" :class="jenjangClass(p.jenjang)">{{ p.jenjang }}</span>
                </td>
                <td>
                  <span class="badge" :class="gradeClass(p.akreditasi_sekarang)">{{ p.akreditasi_sekarang }}</span>
                </td>
                <td>
                  <span v-if="p.masa_berlaku">{{ formatDateShort(p.masa_berlaku) }}</span>
                  <span v-else class="text-slate-400">—</span>
                </td>
                <td>
                  <span 
                    v-if="p.masa_berlaku && getCountdown(p.masa_berlaku)" 
                    :class="['countdown-badge', getCountdown(p.masa_berlaku).isCritical ? 'critical' : (getCountdown(p.masa_berlaku).isWarning ? 'warning' : '')]"
                  >
                    {{ getCountdown(p.masa_berlaku).text }}
                  </span>
                  <span v-else class="text-slate-400">—</span>
                </td>
                <td>
                  <div class="tags-row" v-if="p.jalur_masuk">
                    <span v-for="path in parseJalur(p.jalur_masuk)" :key="path" class="table-tag" :class="pathClass(path)">{{ path }}</span>
                  </div>
                  <span v-else class="text-slate-400">—</span>
                </td>
                <td>
                  <div style="display: flex; gap: 6px; align-items: center; justify-content: center;">
                    <a v-if="p.sertifikat_berlaku" :href="p.sertifikat_berlaku" target="_blank" class="btn-action-violet" style="text-decoration:none; display:inline-block; font-size:11px; padding: 5px 10px; white-space: nowrap;">SK / Sertifikat ↗</a>
                    <a v-if="p.website" :href="p.website" target="_blank" class="btn-action-outline" style="text-decoration:none; display:inline-block; font-size:11px; padding: 4px 9px; white-space: nowrap;">Website ↗</a>
                    <span v-if="!p.sertifikat_berlaku && !p.website" class="text-slate-400">—</span>
                  </div>
                </td>
              </tr>
              <tr v-if="accreditationProdis.length === 0">
                <td colspan="8" class="text-center py-8 text-slate-400">Data program studi tidak ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="table-pagination" v-if="accreditationTotalPages > 1">
          <button :disabled="accreditationPage === 1" @click="accreditationPage--">« Prev</button>
          <span>Halaman {{ accreditationPage }} dari {{ accreditationTotalPages }}</span>
          <button :disabled="accreditationPage === accreditationTotalPages" @click="accreditationPage++">Next »</button>
        </div>
      </div>

      <!-- POPUP MODAL LAPORAN ANALISIS AKREDITASI -->
      <div class="modal-overlay" v-if="reportsModalOpen" @click.self="closeReportsModal">
        <div class="modal-card modal-large" style="width: 100%; max-width: 800px; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; max-height: 90vh;">
          <div class="modal-header" style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">📊 Laporan Analisis Akreditasi Prodi</h3>
            <button @click="closeReportsModal" class="modal-close" style="background: transparent; border: none; font-size: 24px; color: #64748b; cursor: pointer;">&times;</button>
          </div>
          
          <div class="modal-body" style="padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 24px;">
            <!-- Stat Cards -->
            <div class="reports-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
              <div class="rep-stat-card" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe; padding: 18px; border-radius: 12px; display: flex; flex-direction: column; gap: 4px;">
                <span style="font-size: 12px; font-weight: 600; color: #1e40af;">TOTAL FAKULTAS</span>
                <span style="font-size: 28px; font-weight: 800; color: #1d4ed8;">{{ accreditationReportsData.total_faculties }}</span>
              </div>
              <div class="rep-stat-card" style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); border: 1px solid #ddd6fe; padding: 18px; border-radius: 12px; display: flex; flex-direction: column; gap: 4px;">
                <span style="font-size: 12px; font-weight: 600; color: #5b21b6;">TOTAL PRODI</span>
                <span style="font-size: 28px; font-weight: 800; color: #6d28d9;">{{ accreditationReportsData.total_prodis }}</span>
              </div>
              <div class="rep-stat-card" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #a7f3d0; padding: 18px; border-radius: 12px; display: flex; flex-direction: column; gap: 4px;">
                <span style="font-size: 12px; font-weight: 600; color: #065f46;">AKREDITASI UNGGUL</span>
                <span style="font-size: 28px; font-weight: 800; color: #047857;">{{ accreditationReportsData.total_unggul }}</span>
              </div>
            </div>

            <!-- Analysis Charts replacement (progress bars) -->
            <div class="reports-charts-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
              
              <!-- Peringkat Ratio -->
              <div class="chart-box-card" style="border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; background: #fff;">
                <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Peringkat Akreditasi</h4>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                  <div v-for="(count, grade) in accreditationReportsData.accreditation_ratio" :key="grade">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">
                      <span>{{ grade }}</span>
                      <span>{{ count }} Prodi ({{ getPercentage(count, accreditationReportsData.total_prodis) }}%)</span>
                    </div>
                    <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                      <div :class="['progress-bar', { 'bg-green': grade === 'Unggul', 'bg-blue': grade === 'Baik Sekali', 'bg-violet': grade === 'Baik' }]" :style="{ width: getPercentage(count, accreditationReportsData.total_prodis) + '%', height: '100%' }"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Jenjang Ratio -->
              <div class="chart-box-card" style="border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; background: #fff;">
                <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Distribusi Jenjang Studi</h4>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                  <div v-for="(count, jenjang) in accreditationReportsData.jenjang_ratio" :key="jenjang">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">
                      <span>{{ jenjang }}</span>
                      <span>{{ count }} Prodi ({{ getPercentage(count, accreditationReportsData.total_prodis) }}%)</span>
                    </div>
                    <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                      <div :class="['progress-bar', { 'bg-red': jenjang === 'S2' || jenjang === 'S3', 'bg-green': jenjang === 'Profesi', 'bg-blue': jenjang !== 'S2' && jenjang !== 'S3' && jenjang !== 'Profesi' }]" :style="{ width: getPercentage(count, accreditationReportsData.total_prodis) + '%', height: '100%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
              
            </div>

            <!-- Prodis Per Faculty -->
            <div class="chart-box-card" style="border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; background: #fff; width: 100%; box-sizing: border-box;">
              <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 16px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Sebaran Program Studi per Fakultas</h4>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
                <div v-for="fac in accreditationReportsData.prodi_per_faculty" :key="fac.id" style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #f1f5f9;">
                  <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                    <span>{{ fac.nama_fakultas }} ({{ fac.singkatan }})</span>
                    <span style="color: #1d4ed8;">{{ fac.count }} Prodi</span>
                  </div>
                  <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                    <div class="progress-bar bg-blue" :style="{ width: getPercentage(fac.count, accreditationReportsData.total_prodis || 1) + '%', height: '100%' }"></div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; background: #f8fafc;">
            <button @click="closeReportsModal" class="btn-cancel" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; color: #475569; font-weight: 600; font-size: 13px; cursor: pointer;">Tutup</button>
          </div>
        </div>
      </div>
    </main>

    <!-- ─── ANTARMUKA PENCARIAN GLOBAL (SEARCH RESULTS STATE) ─── -->
    <main class="search-results-root" v-else-if="currentView === 'search'">
      <div class="results-header-bar">
        <button class="btn-back-home" @click="navigateToView('home')">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
          </svg>
          <span>Kembali ke Halaman Depan</span>
        </button>
        
        <!-- Small Search Bar in Results -->
        <div class="results-search-box">
          <input 
            type="text" 
            v-model="searchQuery" 
            @keyup.enter="handleSearch('internal')"
            placeholder="Ketik kata kunci pencarian..." 
            class="results-search-input"
          />
          
          <!-- Tombol Mic -->
          <button 
            v-if="isSpeechSupported" 
            type="button" 
            class="btn-voice-search res-voice-btn" 
            :class="{ active: isListening }"
            @click="toggleVoiceSearch" 
            title="Cari dengan suara"
          >
            <svg viewBox="0 0 24 24" class="mic-icon" fill="currentColor" width="16" height="16">
              <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
            </svg>
          </button>
          
          <!-- Tombol Google (G) -->
          <button class="btn-google-search res-google-btn" @click="handleSearch('google_cse')" title="Cari di Seluruh Web Kampus (Google)">
            <svg class="google-g-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
            </svg>
            <span class="btn-google-text">Google</span>
          </button>
          
          <button class="btn-search-trigger" @click="handleSearch('internal')">Cari</button>
        </div>
      </div>

      <div class="results-layout">
        <!-- Sidebar filters -->
        <aside class="results-sidebar-filters">
          <h4>Filter Kategori</h4>
          <button :class="['filter-btn', { active: activeResultFilter === 'all' }]" @click="activeResultFilter = 'all'">
            📂 Semua Hasil <span class="badge">{{ searchResults.total_results }}</span>
          </button>
          <button :class="['filter-btn', { active: activeResultFilter === 'news' }]" @click="activeResultFilter = 'news'">
            🟢 Berita Online <span class="badge">{{ searchResults.news.length }}</span>
          </button>
          <button :class="['filter-btn', { active: activeResultFilter === 'clippings' }]" @click="activeResultFilter = 'clippings'">
            🟣 Kliping Pers <span class="badge">{{ searchResults.clippings.length }}</span>
          </button>
          <button :class="['filter-btn', { active: activeResultFilter === 'documentation' }]" @click="activeResultFilter = 'documentation'">
            🔵 Dokumentasi <span class="badge">{{ searchResults.documentation.length }}</span>
          </button>
          <button :class="['filter-btn', { active: activeResultFilter === 'accreditation' }]" @click="activeResultFilter = 'accreditation'">
            🎓 Akreditasi Prodi <span class="badge">{{ searchResults.accreditation.length }}</span>
          </button>
        </aside>

        <!-- Main results list -->
        <div class="results-list-container">
          <div class="results-summary">
            Ditemukan <strong>{{ filteredResultsCount }}</strong> data hasil pencarian untuk kata kunci "<span class="search-word">{{ lastSearchQuery }}</span>"
          </div>

          <div class="results-loading-card" v-if="searchLoading">
            <div class="spinner"></div>
            <span>Mencari data ke database...</span>
          </div>

          <div v-else>
            <!-- A. Hasil Pencarian Internal (HumasHub) -->
            <div class="internal-results-section" v-show="filteredResultsCount > 0">
              <h4 class="internal-results-title">📂 Hasil Pencarian Internal (HumasHub)</h4>
              <div class="results-list">
                <!-- A. Berita Online -->
                <div class="results-group" v-if="activeResultFilter === 'all' || activeResultFilter === 'news'">
                  <div v-for="res in searchResults.news" :key="'n-' + res.id" class="result-card result-news" @click="openLink(res.news_link)">
                    <div class="rc-header">
                      <span class="rc-type type-news">Berita Online</span>
                      <span class="rc-date">{{ formatDate(res.news_date) }}</span>
                    </div>
                    <h4 v-html="highlightKeyword(res.title, lastSearchQuery)"></h4>
                    <div class="rc-meta">
                      <span>Sumber: <strong>{{ res.media_name || 'Rilis Humas' }}</strong></span>
                      <span>Tipe: {{ res.source_type }}</span>
                    </div>
                  </div>
                </div>

                <!-- B. Kliping Pers -->
                <div class="results-group" v-if="activeResultFilter === 'all' || activeResultFilter === 'clippings'">
                  <div v-for="res in searchResults.clippings" :key="'c-' + res.id" class="result-card result-clippings" @click="navigateToDetail('clippings', res)">
                    <div class="rc-header">
                      <span class="rc-type type-clippings">Kliping Pers</span>
                      <span class="rc-date">{{ formatDate(res.clipping_date) }}</span>
                    </div>
                    <h4 v-html="highlightKeyword(res.title, lastSearchQuery)"></h4>
                    <p v-if="res.summary" v-html="highlightKeyword(res.summary, lastSearchQuery)"></p>
                    <div class="rc-meta">
                      <span>Media: <strong>{{ res.media_name }}</strong></span>
                      <span>Lokasi Fisik: {{ res.storage_building }}, Ruang {{ res.storage_room }}</span>
                    </div>
                  </div>
                </div>

                <!-- C. Dokumentasi Kegiatan -->
                <div class="results-group" v-if="activeResultFilter === 'all' || activeResultFilter === 'documentation'">
                  <div v-for="res in searchResults.documentation" :key="'d-' + res.id" class="result-card result-doc" @click="navigateToDetail('documentation', res)">
                    <div class="rc-header">
                      <span class="rc-type type-doc">Dokumentasi</span>
                      <span class="rc-date">{{ formatDate(res.event_date) }}</span>
                    </div>
                    <h4 v-html="highlightKeyword(res.event_name, lastSearchQuery)"></h4>
                    <p v-if="res.description" v-html="highlightKeyword(res.description, lastSearchQuery)"></p>
                    <div class="rc-meta">
                      <span>Lokasi: {{ res.location_name }} ({{ res.location_type }})</span>
                    </div>
                  </div>
                </div>

                <!-- D. Akreditasi Program Studi -->
                <div class="results-group" v-if="activeResultFilter === 'all' || activeResultFilter === 'accreditation'">
                  <div v-for="res in searchResults.accreditation" :key="'a-' + res.id" class="result-card result-accreditation" @click="openLink(res.website || res.sertifikat_berlaku)">
                    <div class="rc-header">
                      <span class="rc-type type-accreditation">Akreditasi Prodi</span>
                      <span class="rc-date" v-if="res.masa_berlaku">Berlaku s/d: {{ formatDateShort(res.masa_berlaku) }}</span>
                    </div>
                    <h4 v-html="highlightKeyword(res.nama_prodi + ' (' + res.jenjang + ')', lastSearchQuery)"></h4>
                    <div class="rc-details-row" style="margin-top: 6px; font-size: 13px; color: #475569; display: flex; gap: 16px;">
                      <span>Fakultas: <strong>{{ res.singkatan_fakultas }}</strong></span>
                      <span>Peringkat: <span class="badge" :class="gradeClass(res.akreditasi_sekarang)" style="display:inline-block; padding: 2px 6px; font-size: 11px;">{{ res.akreditasi_sekarang }}</span></span>
                    </div>
                    <div class="rc-meta">
                      <span v-if="res.website">Website: <strong>{{ res.website }}</strong></span>
                      <span v-if="res.sertifikat_berlaku">Sertifikat: Tersedia ↗</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- B. Empty State untuk Pencarian Internal -->
            <div class="results-empty-state" v-if="filteredResultsCount === 0 && searchType !== 'google_cse'">
              <div class="empty-emoji">🔍</div>
              <h4>Data tidak ditemukan</h4>
              <p>Maaf, kami tidak dapat menemukan hasil yang cocok untuk kata kunci pencarian Anda di database internal. Coba gunakan kata kunci lain.</p>
            </div>

             <!-- C. Widget Hasil Google CSE -->
             <div v-show="searchType === 'google_cse' && activeResultFilter === 'all'" class="google-cse-results-container">
               <!-- Header -->
               <div class="cse-results-header">
                 <h4 class="cse-results-title">🌐 Pencarian Web Kampus (Google)</h4>
                 <span class="cse-badge inline">Hasil Inline</span>
               </div>

               <!-- Panel Rekomendasi / Pencarian Manual ke Google (Tidak Terbuka Otomatis) -->
               <div class="cse-recommendation-bar">
                 <div class="cse-rec-info">
                   <span class="cse-rec-icon">💡</span>
                   <span>Menampilkan hasil pencarian untuk kata kunci <strong>"{{ lastSearchQuery }}"</strong> dari seluruh ekosistem web UIN Ar-Raniry.</span>
                 </div>
                 <div class="cse-rec-actions">
                   <a
                     :href="'https://www.google.com/search?q=' + encodeURIComponent(lastSearchQuery + ' site:ar-raniry.ac.id')"
                     target="_blank" rel="noopener"
                     class="cse-rec-btn primary"
                   >
                     <svg viewBox="0 0 24 24" width="14" height="14"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/></svg>
                     Buka di Google.com
                   </a>
                   <a
                     :href="'https://www.google.com/search?q=' + encodeURIComponent(lastSearchQuery + ' site:warta.ar-raniry.ac.id')"
                     target="_blank" rel="noopener"
                     class="cse-rec-btn secondary"
                   >📰 Warta UIN</a>
                   <a
                     :href="'https://www.google.com/search?q=' + encodeURIComponent(lastSearchQuery + ' UIN Ar-Raniry')"
                     target="_blank" rel="noopener"
                     class="cse-rec-btn secondary"
                   >🌐 Cari Lebih Luas</a>
                 </div>
               </div>

               <!-- Skeleton Loading saat inisialisasi CSE -->
               <div v-if="cseLoading" class="cse-loading">
                 <div class="cse-skeleton" v-for="i in 3" :key="i">
                   <div class="cse-sk-source"></div>
                   <div class="cse-sk-title"></div>
                   <div class="cse-sk-body"></div>
                 </div>
               </div>

               <!-- Elemen Container Google CSE Resmi -->
               <div class="cse-widget-wrapper" v-show="!cseLoading">
                 <div class="gcse-searchresults-only" data-gname="campus-search" data-linktarget="_blank"></div>
               </div>

               <!-- Internal results teaser -->
               <div class="cse-internal-teaser" v-if="searchResults.total_results > 0 && !cseLoading" style="margin-top: 20px;">
                 <div class="cse-teaser-label">ℹ️ Data internal HumasHub ditemukan</div>
                 <p>Ditemukan <strong>{{ searchResults.total_results }} data</strong> di database internal. Klik filter di sidebar untuk melihat detail.</p>
               </div>
             </div>
           </div>
         </div>
       </div>
     </main>

    <!-- ─── FOOTER ───────────────────────────────────────────── -->
    <footer class="portal-footer">
      <div class="footer-container">
        <p>{{ settings.app_footer || '© 2026 UIN Ar-Raniry Banda Aceh. Hak Cipta Dilindungi Undang-Undang.' }}</p>
        <div class="footer-links">
          <a href="https://ar-raniry.ac.id" target="_blank">Website Kampus</a>
          <a href="https://warta.ar-raniry.ac.id" target="_blank">Warta UIN</a>
          <router-link to="/login">Portal Admin</router-link>
        </div>
      </div>
    </footer>
    <!-- Voice Search Overlay -->
    <div class="voice-search-overlay" v-if="isListening" @click="stopVoiceSearch">
      <div class="voice-search-modal" @click.stop>
        <div class="voice-wave-container">
          <div class="voice-wave-bar" v-for="i in 5" :key="i"></div>
        </div>
        <h4>Sedang mendengarkan...</h4>
        <p>Katakan kata kunci pencarian Anda</p>
        <button class="btn-voice-close" @click="stopVoiceSearch">Batal</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import { useAuthStore } from '../stores/auth'

Chart.register(...registerables)

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()

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
  app_subtitle: '',
  app_description: '',
  app_footer: '',
  app_logo: '',
  app_favicon: '',
  seo_title: '',
  seo_keywords: '',
  seo_author: '',
  seo_image: ''
})

const fetchSettings = async () => {
  try {
    const res = await axios.get('/api/public/settings')
    if (res.data.status === 'success') {
      settings.value = res.data.data
      triggerSEOUpdate()
    }
  } catch (err) {
    console.error('Gagal mengambil pengaturan publik:', err)
  }
}

// State
const isDarkMode = ref(false)
const liveTime = ref('')
const liveTimer = ref(null)

const searchQuery = ref('')
const searchType = ref('all')
const currentView = ref('home') // home, news, clippings, clipping-detail, documentation, doc-detail, rss, search
const searchLoading = ref(false)
const lastSearchQuery = ref('')
const activeResultFilter = ref('all')

// Voice Search (Speech Recognition) State
const isSpeechSupported = ref(false)
const isListening = ref(false)
let recognition = null

const searchResults = ref({
  news: [],
  clippings: [],
  documentation: [],
  accreditation: [],
  total_results: 0
})

// Weather State
const weatherLoading = ref(true)
const weatherInfo = ref({
  temp: 29,
  status: 'Cerah Berawan',
  icon: 'cloudy',
  wind: 8,
  forecast: []
})

// Shalat State
const shalatTimes = ref({
  Imsak: '05:01',
  Subuh: '05:11',
  Syuruk: '06:29',
  Dzuhur: '12:38',
  Ashar: '16:04',
  Maghrib: '18:51',
  Isya: '20:03'
})
const nextPrayer = ref(null)
const prayerCountdown = ref('')
let countdownInterval = null

// Flight Board State
const flightTab = ref('dep')
const flightsDep = ref([
  { code: 'GA-147', target: 'Jakarta (CGK)', time: '07:15', status: 'Landed', statusClass: 'status-gray' },
  { code: 'JT-305', target: 'Medan (KNO)', time: '10:30', status: 'On Time', statusClass: 'status-green' },
  { code: 'AK-421', target: 'Kuala Lumpur (KUL)', time: '11:15', status: 'Boarding', statusClass: 'status-gold' },
  { code: 'QZ-192', target: 'Singapore (SIN)', time: '14:20', status: 'On Time', statusClass: 'status-green' },
  { code: 'ID-6897', target: 'Jakarta (HLP)', time: '16:05', status: 'Scheduled', statusClass: 'status-blue' }
])
const flightsArr = ref([
  { code: 'GA-146', target: 'Jakarta (CGK)', time: '09:40', status: 'Landed', statusClass: 'status-gray' },
  { code: 'JT-396', target: 'Batam (BTH)', time: '12:15', status: 'Landed', statusClass: 'status-gray' },
  { code: 'FY-3402', target: 'Penang (PEN)', time: '13:55', status: 'Delayed (10m)', statusClass: 'status-red' },
  { code: 'AK-420', target: 'Kuala Lumpur (KUL)', time: '15:10', status: 'On Time', statusClass: 'status-green' },
  { code: 'JT-306', target: 'Medan (KNO)', time: '18:25', status: 'Scheduled', statusClass: 'status-blue' }
])

// Currency exchange state
const selectedCurrency = ref('USD')
const exchangeRates = ref({
  USD: 16145,
  MYR: 3432,
  SAR: 4305
})

// Preacher Friday state
const khatibData = ref({
  date: '',
  main: null,
  others: []
})

// Visual stats KPI & Graph
const kpis = ref([
  { label: 'Total Mahasiswa Aktif', value: '25.924', sub: '+8.4% dari tahun lalu', trend: 'up', icon: 'students' },
  { label: 'Dosen & Tenaga Ahli', value: '842', sub: 'Rasio dosen/mahasiswa ideal', trend: 'stable', icon: 'lecturers' },
  { label: 'Program Studi Unggul', value: '28', sub: 'Dari total 54 program studi', trend: 'up', icon: 'accreditation' },
  { label: 'Publikasi Ilmiah', value: '3.082', sub: 'Scopus & SINTA terindeks', trend: 'up', icon: 'publications' }
])

const activeChartTab = ref('studentTrend')
const chartsLoading = ref(true)
const statsChartCanvas = ref(null)
let statsChartInstance = null

const currencyChartCanvas = ref(null)
let currencyChartInstance = null

// News aggregator lists
const localNewsList = ref([])
const clippingList = ref([])
const rssList = ref([])
const featuredNews = ref(null)

// App Launcher Dynamic Menus
const dynamicMenus = ref([])
const showWaffleMenu = ref(false)

const toggleWaffleMenu = () => {
  showWaffleMenu.value = !showWaffleMenu.value
}

const closeWaffleMenu = () => {
  showWaffleMenu.value = false
}

const clickWaffleMenu = (menu) => {
  clickMenu(menu)
  closeWaffleMenu()
}

const handleGlobalClick = (event) => {
  const container = document.querySelector('.waffle-menu-container')
  if (container && !container.contains(event.target)) {
    closeWaffleMenu()
  }
}

// Documentation Gallery state
const documentationList = ref([])
const galleryFilter = ref('all')

const fallbackDocs = [
  {
    id: 'm1',
    event_name: 'Sidang Senat Terbuka Wisuda Sarjana Universitas',
    event_date: '2026-05-20',
    location_name: 'Auditorium Ali Hasjmy',
    location_type: 'Internal Kampus',
    thumbnail_url: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=600&auto=format&fit=crop&q=60',
    photo_folder_link: 'https://ar-raniry.ac.id',
    video_folder_link: 'https://www.youtube.com'
  },
  {
    id: 'm2',
    event_name: 'Seminar Internasional Transformasi Digital & AI',
    event_date: '2026-05-18',
    location_name: 'Aula Lt. 3 Rektorat',
    location_type: 'Internasional',
    thumbnail_url: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600&auto=format&fit=crop&q=60',
    photo_folder_link: 'https://ar-raniry.ac.id',
    video_folder_link: 'https://www.youtube.com'
  },
  {
    id: 'm3',
    event_name: 'Pelepasan KKN Melayu Serumpun Angkatan V',
    event_date: '2026-05-15',
    location_name: 'Lapangan Biro Rektorat UIN',
    location_type: 'Internal Kampus',
    thumbnail_url: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&auto=format&fit=crop&q=60',
    photo_folder_link: 'https://ar-raniry.ac.id',
    video_folder_link: 'https://www.youtube.com'
  },
  {
    id: 'm4',
    event_name: 'MoU Kerjasama Riset Akademik Internasional',
    event_date: '2026-05-10',
    location_name: 'Selangor, Malaysia',
    location_type: 'Internasional',
    thumbnail_url: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=600&auto=format&fit=crop&q=60',
    photo_folder_link: 'https://ar-raniry.ac.id',
    video_folder_link: 'https://www.youtube.com'
  }
]

const filteredDocs = computed(() => {
  const docs = documentationList.value.length > 0 ? documentationList.value : fallbackDocs
  if (galleryFilter.value === 'all') {
    return docs.slice(0, 4)
  }
  return docs.filter(doc => doc.location_type === galleryFilter.value).slice(0, 4)
})

// Accreditation State
const accreditationProdis = ref([])
const publicFaculties = ref([])
const accreditationPage = ref(1)
const accreditationPageSize = ref(10)
const accreditationReportsData = ref({
  total_faculties: 0,
  total_prodis: 0,
  total_unggul: 0,
  accreditation_ratio: {},
  jenjang_ratio: {},
  prodi_per_faculty: []
})
const reportsModalOpen = ref(false)

const accreditationFilters = ref({
  search: '',
  facultyId: '',
  jenjang: '',
  akreditasi: '',
  jalurMasuk: ''
})

const accreditationTotalPages = computed(() => {
  return Math.ceil(accreditationProdis.value.length / accreditationPageSize.value) || 1
})

const paginatedAccreditationProdis = computed(() => {
  const start = (accreditationPage.value - 1) * accreditationPageSize.value
  const end = start + accreditationPageSize.value
  return accreditationProdis.value.slice(start, end)
})

const fetchAccreditationViewData = async () => {
  try {
    const [facsRes, reportsRes] = await Promise.all([
      axios.get('/api/public/accreditation/faculties'),
      axios.get('/api/public/accreditation/reports')
    ])
    publicFaculties.value = facsRes.data.data
    accreditationReportsData.value = reportsRes.data.data
    await fetchAccreditationProdis()
  } catch (err) {
    console.error('Gagal memuat data akreditasi:', err)
  }
}

const fetchAccreditationProdis = async () => {
  try {
    const res = await axios.get('/api/public/accreditation/study-programs', {
      params: {
        search: accreditationFilters.value.search,
        faculty_id: accreditationFilters.value.facultyId,
        jenjang: accreditationFilters.value.jenjang,
        akreditasi_sekarang: accreditationFilters.value.akreditasi,
        jalur_masuk: accreditationFilters.value.jalurMasuk
      }
    })
    if (res.data.status === 'success') {
      accreditationProdis.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal memuat prodi:', err)
  }
}

const debounceAccreditationTimeout = ref(null)
const debounceFetchAccreditation = () => {
  if (debounceAccreditationTimeout.value) clearTimeout(debounceAccreditationTimeout.value)
  debounceAccreditationTimeout.value = setTimeout(() => {
    accreditationPage.value = 1
    fetchAccreditationProdis()
  }, 300)
}

const resetAccreditationFilters = () => {
  accreditationFilters.value = {
    search: '',
    facultyId: '',
    jenjang: '',
    akreditasi: '',
    jalurMasuk: ''
  }
  accreditationPage.value = 1
  fetchAccreditationProdis()
}

const openReportsModal = () => {
  reportsModalOpen.value = true
}

const closeReportsModal = () => {
  reportsModalOpen.value = false
}

const jenjangClass = (jenjang) => {
  if (jenjang === 'S2' || jenjang === 'S3') return 'jenjang-s23'
  if (jenjang === 'Profesi') return 'jenjang-profesi'
  return 'jenjang-undergrad'
}

const gradeClass = (grade) => {
  const g = grade ? grade.trim() : ''
  if (g === 'Unggul') return 'badge-unggul'
  if (g === 'Baik Sekali') return 'badge-baik-sekali'
  if (g === 'Baik') return 'badge-baik'
  return 'badge-gray'
}

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

const pathClass = (path) => {
  const p = path.toUpperCase()
  if (p.includes('UTBK') || p.includes('SNBT')) return 'path-blue'
  if (p.includes('SPAN') || p.includes('UM-PTKIN') || p.includes('UMPTKIN')) return 'path-green'
  if (p.includes('REGULER')) return 'path-yellow'
  return 'path-gray'
}

const parseJalur = (jalur) => {
  if (!jalur) return []
  try {
    const parsed = JSON.parse(jalur)
    return Array.isArray(parsed) ? parsed : [parsed]
  } catch (e) {
    return jalur.split(',').map(s => s.trim()).filter(Boolean)
  }
}

const formatDateShort = (dateStr) => {
  if (!dateStr) return '-'
  const parts = dateStr.split('-')
  if (parts.length !== 3) return dateStr
  const [year, month, day] = parts
  return `${day}/${month}/${year}`
}

// Sub-View Detail states
const clippingDetailData = ref(null)
const docDetailData = ref(null)

// Sub-View List Data Tables
const newsTableData = ref({ items: [], total_items: 0, page: 1, limit: 10, total_pages: 0 })
const clippingsTableData = ref({ items: [], total_items: 0, page: 1, limit: 10, total_pages: 0 })
const documentationTableData = ref({ items: [], total_items: 0, page: 1, limit: 12, total_pages: 0 })

// Filter states
const newsFilters = ref({ search: '', categoryId: '', mediaId: '' })
const categoriesList = ref([])
const mediaList = ref([])
const etalaseList = ref([])
const followedMedia = ref([])

// Clipping Filter states
const filterStartDate = ref('')
const filterEndDate = ref('')
const filterMedia = ref('')
const filterCategory = ref('')
const filterQuarter = ref('')
const clippingSearchQuery = ref('')

// Documentation Filter states
const filterDocStartDate = ref('')
const filterDocEndDate = ref('')
const filterDocCategory = ref('')
const filterLocationType = ref('')
const filterDocQuarter = ref('')
const docSearchQuery = ref('')

const mediaCetakList = computed(() =>
  mediaList.value.filter(m => m.media_type === 'cetak' || !m.media_type || m.media_type === 'print')
)

const onClippingQuarterChange = () => {
  if (filterQuarter.value) {
    const year = new Date().getFullYear()
    switch (filterQuarter.value) {
      case '1': filterStartDate.value = `${year}-01-01`; filterEndDate.value = `${year}-03-31`; break
      case '2': filterStartDate.value = `${year}-04-01`; filterEndDate.value = `${year}-06-30`; break
      case '3': filterStartDate.value = `${year}-07-01`; filterEndDate.value = `${year}-09-30`; break
      case '4': filterStartDate.value = `${year}-10-01`; filterEndDate.value = `${year}-12-31`; break
    }
  } else {
    filterStartDate.value = ''
    filterEndDate.value = ''
  }
  clippingsTableData.value.page = 1
  fetchClippingsViewData()
}

const onClippingDateRangeChange = () => {
  filterQuarter.value = ''
  clippingsTableData.value.page = 1
  fetchClippingsViewData()
}

const debounceClippingTimeout = ref(null)
const debounceFetchClippings = () => {
  if (debounceClippingTimeout.value) clearTimeout(debounceClippingTimeout.value)
  debounceClippingTimeout.value = setTimeout(() => {
    clippingsTableData.value.page = 1
    fetchClippingsViewData()
  }, 300)
}

const resetClippingFilters = () => {
  clippingSearchQuery.value = ''
  filterMedia.value = ''
  filterCategory.value = ''
  filterStartDate.value = ''
  filterEndDate.value = ''
  filterQuarter.value = ''
  clippingsTableData.value.page = 1
  fetchClippingsViewData()
}

const onDocQuarterChange = () => {
  if (filterDocQuarter.value) {
    const year = new Date().getFullYear()
    switch (filterDocQuarter.value) {
      case '1': filterDocStartDate.value = `${year}-01-01`; filterDocEndDate.value = `${year}-03-31`; break
      case '2': filterDocStartDate.value = `${year}-04-01`; filterDocEndDate.value = `${year}-06-30`; break
      case '3': filterDocStartDate.value = `${year}-07-01`; filterDocEndDate.value = `${year}-09-30`; break
      case '4': filterDocStartDate.value = `${year}-10-01`; filterDocEndDate.value = `${year}-12-31`; break
    }
  } else {
    filterDocStartDate.value = ''
    filterDocEndDate.value = ''
  }
  documentationTableData.value.page = 1
  fetchDocumentationViewData()
}

const onDocDateRangeChange = () => {
  filterDocQuarter.value = ''
  documentationTableData.value.page = 1
  fetchDocumentationViewData()
}

const debounceDocTimeout = ref(null)
const debounceFetchDoc = () => {
  if (debounceDocTimeout.value) clearTimeout(debounceDocTimeout.value)
  debounceDocTimeout.value = setTimeout(() => {
    documentationTableData.value.page = 1
    fetchDocumentationViewData()
  }, 300)
}

const resetDocFilters = () => {
  docSearchQuery.value = ''
  filterLocationType.value = ''
  filterDocCategory.value = ''
  filterDocStartDate.value = ''
  filterDocEndDate.value = ''
  filterDocQuarter.value = ''
  documentationTableData.value.page = 1
  fetchDocumentationViewData()
}

const mediaStatsData = ref({
  media: [],
  categories: []
})
const statsTotals = ref({ news: 100 })

// Computed
const formattedPrayers = computed(() => {
  return shalatTimes.value
})

const currentFlights = computed(() => {
  return flightTab.value === 'dep' ? flightsDep.value : flightsArr.value
})

const formattedExchangeRate = computed(() => {
  return new Intl.NumberFormat('id-ID').format(exchangeRates.value[selectedCurrency.value])
})

const filteredResultsCount = computed(() => {
  if (activeResultFilter.value === 'all') return searchResults.value.total_results
  if (activeResultFilter.value === 'news') return searchResults.value.news.length
  if (activeResultFilter.value === 'clippings') return searchResults.value.clippings.length
  if (activeResultFilter.value === 'documentation') return searchResults.value.documentation.length
  if (activeResultFilter.value === 'accreditation') return searchResults.value.accreditation.length
  return 0
})

// Methods
const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value
  document.body.classList.toggle('dark-theme', isDarkMode.value)
}

const updateLiveClock = () => {
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  
  const now = new Date()
  const dayName = days[now.getDay()]
  const dateNum = now.getDate()
  const monthName = months[now.getMonth()]
  const year = now.getFullYear()
  
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  const seconds = String(now.getSeconds()).padStart(2, '0')
  
  liveTime.value = `${dayName}, ${dateNum} ${monthName} ${year} — ${hours}:${minutes}:${seconds} WIB`
}

// Live Flight simulation
const simulateFlights = () => {
  const statusesDep = ['Scheduled', 'Scheduled', 'Boarding', 'On Time', 'Delayed (15m)', 'Landed']
  const statusesArr = ['Scheduled', 'Scheduled', 'On Time', 'On Time', 'Delayed (10m)', 'Landed']
  
  flightsDep.value.forEach(fl => {
    if (Math.random() > 0.7) {
      const newStatus = statusesDep[Math.floor(Math.random() * statusesDep.length)]
      fl.status = newStatus
      fl.statusClass = getStatusClass(newStatus)
    }
  })
  
  flightsArr.value.forEach(fl => {
    if (Math.random() > 0.7) {
      const newStatus = statusesArr[Math.floor(Math.random() * statusesArr.length)]
      fl.status = newStatus
      fl.statusClass = getStatusClass(newStatus)
    }
  })
}

const getStatusClass = (status) => {
  if (status === 'Landed' || status === 'Gate Closed') return 'status-gray'
  if (status === 'On Time') return 'status-green'
  if (status === 'Boarding' || status === 'Final Call') return 'status-gold'
  if (status.includes('Delayed')) return 'status-red'
  return 'status-blue'
}

let bodyMutationObserver = null

const startGSEObserver = () => {
  if (typeof MutationObserver === 'undefined') return
  if (bodyMutationObserver) bodyMutationObserver.disconnect()
  
  bodyMutationObserver = new MutationObserver(() => {
    const targetContainer = document.querySelector('.google-cse-results-container')
    const gseOverlay = document.querySelector('.gsc-results-wrapper-overlay')
    const gseBg = document.querySelector('.gsc-modal-background-image')
    
    if (targetContainer) {
      if (gseOverlay && gseOverlay.parentElement !== targetContainer) {
        targetContainer.appendChild(gseOverlay)
      }
      if (gseBg && gseBg.parentElement !== targetContainer) {
        targetContainer.appendChild(gseBg)
      }
    }
  })
  
  bodyMutationObserver.observe(document.body, {
    childList: true,
    subtree: true
  })
}

// Google CSE state
const cseLoading = ref(false)
const CSE_CX = '81f09a87bde22401c'

const fetchCSEResults = (start = 1) => {
  if (!lastSearchQuery.value) return
  
  cseLoading.value = true
  
  // 1. Pastikan script Google CSE dimuat
  if (!window.__cse_script_loaded) {
    const gcse = document.createElement('script')
    gcse.type = 'text/javascript'
    gcse.async = true
    gcse.src = 'https://cse.google.com/cse.js?cx=' + CSE_CX
    const s = document.getElementsByTagName('script')[0]
    if (s && s.parentNode) {
      s.parentNode.insertBefore(gcse, s)
    } else {
      document.head.appendChild(gcse)
    }
    window.__cse_script_loaded = true
  }

  // Helper untuk melakukan eksekusi pencarian di widget Google CSE
  const runGoogleSearchWidget = () => {
    try {
      if (window.google && window.google.search && window.google.search.cse && window.google.search.cse.element) {
        const element = window.google.search.cse.element.getElement('campus-search')
        if (element) {
          element.execute(lastSearchQuery.value)
          cseLoading.value = false
          return true
        }
      }
    } catch (e) {
      console.error('Error executing CSE search:', e)
    }
    return false
  }

  // Cek secara berkala apakah objek google & element CSE sudah siap untuk dieksekusi
  let attempts = 0
  const checkInterval = setInterval(() => {
    attempts++
    const success = runGoogleSearchWidget()
    if (success || attempts > 50) { // maksimal 5 detik
      clearInterval(checkInterval)
      cseLoading.value = false
    }
  }, 100)
}

// backward compat
const loadGoogleCSEScript = () => { fetchCSEResults(1) }
const extractDomain = (url) => {
  try { return new URL(url).hostname.replace('www.', '') } catch { return url }
}

// Voice Search Logic (Web Speech API)
const initSpeechRecognition = () => {
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition
  if (SpeechRecognition) {
    isSpeechSupported.value = true
    recognition = new SpeechRecognition()
    recognition.continuous = false
    recognition.lang = 'id-ID' // Bahasa Indonesia
    recognition.interimResults = false
    recognition.maxAlternatives = 1

    recognition.onstart = () => {
      isListening.value = true
    }

    recognition.onresult = (event) => {
      const resultText = event.results[0][0].transcript
      searchQuery.value = resultText
      isListening.value = false
      
      // Jalankan pencarian otomatis setelah jeda singkat agar user bisa melihat teksnya terisi
      setTimeout(() => {
        handleSearch('internal')
      }, 500)
    }

    recognition.onerror = (event) => {
      console.error('Speech recognition error:', event.error)
      isListening.value = false
    }

    recognition.onend = () => {
      isListening.value = false
    }
  }
}

const toggleVoiceSearch = () => {
  if (!recognition) return
  if (isListening.value) {
    recognition.stop()
  } else {
    recognition.start()
  }
}

const stopVoiceSearch = () => {
  if (recognition && isListening.value) {
    recognition.stop()
  }
}

// Global Search trigger
const handleSearch = (type = 'internal') => {
  if (!searchQuery.value.trim()) return
  const q = searchQuery.value.trim()
  router.push({ path: '/pencarian', query: { q: q, type: type } })
}

const runSearchAPI = async (queryText) => {
  searchLoading.value = true
  lastSearchQuery.value = queryText
  activeResultFilter.value = 'all'
  
  try {
    const apiType = (searchType.value === 'google_cse' || searchType.value === 'internal') ? 'all' : searchType.value
    const res = await axios.get('/api/public/search', {
      params: {
        q: queryText,
        type: apiType
      }
    })
    if (res.data.status === 'success') {
      searchResults.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal melakukan pencarian:', err)
  } finally {
    searchLoading.value = false
    if (searchType.value === 'google_cse') {
      nextTick(() => {
        fetchCSEResults(1)
      })
    }
  }
}

const quickSearch = (keyword) => {
  searchQuery.value = keyword
  handleSearch('internal')
}

const highlightKeyword = (text, keyword) => {
  if (!text || !keyword) return text || ''
  const regex = new RegExp(`(${escapeRegex(keyword)})`, 'gi')
  return text.replace(regex, '<mark class="highlight-match">$1</mark>')
}

const escapeRegex = (string) => {
  return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')
}

// Weather fetching (Open-Meteo)
const fetchWeather = async () => {
  try {
    const res = await axios.get('https://api.open-meteo.com/v1/forecast', {
      params: {
        latitude: 5.5724,
        longitude: 95.3719,
        current_weather: true,
        daily: 'temperature_2m_max,temperature_2m_min,weathercode',
        timezone: 'Asia/Jakarta'
      }
    })
    
    if (res.data && res.data.current_weather) {
      const code = res.data.current_weather.weathercode
      const temp = Math.round(res.data.current_weather.temperature)
      const wind = Math.round(res.data.current_weather.windspeed)
      
      const parsed = parseWeatherCode(code)
      const forecastDays = []
      const days = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
      const today = new Date()
      
      for (let i = 1; i <= 3; i++) {
        const dateObj = new Date(today)
        dateObj.setDate(today.getDate() + i)
        const dayName = days[dateObj.getDay()]
        const fcCode = res.data.daily.weathercode[i]
        const maxT = Math.round(res.data.daily.temperature_2m_max[i])
        const minT = Math.round(res.data.daily.temperature_2m_min[i])
        const fcParsed = parseWeatherCode(fcCode)
        
        forecastDays.push({
          name: dayName,
          emoji: fcParsed.emoji,
          max: maxT,
          min: minT
        })
      }

      weatherInfo.value = {
        temp,
        status: parsed.status,
        icon: parsed.icon,
        wind,
        forecast: forecastDays
      }
    }
  } catch (err) {
    console.error('Weather API failed, using fallback:', err)
    weatherInfo.value.forecast = [
      { name: 'Besok', emoji: '⛅', max: 32, min: 25 },
      { name: 'Rabu', emoji: '🌧️', max: 30, min: 24 },
      { name: 'Kamis', emoji: '⛈️', max: 29, min: 23 }
    ]
  } finally {
    weatherLoading.value = false
  }
}

const parseWeatherCode = (code) => {
  if (code === 0) return { status: 'Cerah', icon: 'sunny', emoji: '☀️' }
  if (code >= 1 && code <= 3) return { status: 'Cerah Berawan', icon: 'cloudy', emoji: '⛅' }
  if (code >= 45 && code <= 48) return { status: 'Kabut Berawan', icon: 'cloudy', emoji: '🌫️' }
  if (code >= 51 && code <= 67) return { status: 'Gerimis Ringan', icon: 'rainy', emoji: '🌧️' }
  if (code >= 80 && code <= 82) return { status: 'Hujan Lebat', icon: 'rainy', emoji: '🌧️' }
  if (code >= 95 && code <= 99) return { status: 'Hujan Petir', icon: 'rainy', emoji: '⛈️' }
  return { status: 'Hujan Sedang', icon: 'rainy', emoji: '🌧️' }
}

// Prayer times calculation & countdown
const fetchPrayerTimes = async () => {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  
  try {
    const res = await axios.get(`https://api.myquran.com/v2/sholat/jadwal/0110/${year}/${month}/${day}`)
    if (res.data && res.data.status) {
      const j = res.data.data.jadwal
      shalatTimes.value = {
        Imsak: j.imsak,
        Subuh: j.subuh,
        Syuruk: j.terbit,
        Dzuhur: j.dzuhur,
        Ashar: j.ashar,
        Maghrib: j.maghrib,
        Isya: j.isya
      }
    }
  } catch (err) {
    console.error('Prayer Times API failed:', err)
  }
  
  startPrayerCountdown()
}

const startPrayerCountdown = () => {
  if (countdownInterval) clearInterval(countdownInterval)
  
  const updateCountdown = () => {
    const now = new Date()
    const currentHour = now.getHours()
    const currentMin = now.getMinutes()
    const currentSec = now.getSeconds()
    const currentTimeSec = (currentHour * 3600) + (currentMin * 60) + currentSec
    
    const sorted = []
    Object.keys(shalatTimes.value).forEach(name => {
      if (name === 'Imsak' || name === 'Syuruk') return
      
      const tStr = shalatTimes.value[name]
      const parts = tStr.split(':')
      const timeSec = (parseInt(parts[0]) * 3600) + (parseInt(parts[1]) * 60)
      
      sorted.push({ name, sec: timeSec, timeStr: tStr })
    })
    
    sorted.sort((a, b) => a.sec - b.sec)
    
    let target = null
    for (let i = 0; i < sorted.length; i++) {
      if (sorted[i].sec > currentTimeSec) {
        target = sorted[i]
        break;
      }
    }
    
    if (!target) {
      target = { name: 'Subuh', sec: sorted[0].sec + 24 * 3600, timeStr: sorted[0].timeStr }
    }
    
    nextPrayer.value = { name: target.name, time: target.timeStr }
    
    let diff = target.sec - currentTimeSec
    if (diff < 0) diff += 24 * 3600
    
    const h = String(Math.floor(diff / 3600)).padStart(2, '0')
    const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0')
    const s = String(diff % 60).padStart(2, '0')
    
    prayerCountdown.value = `${h}:${m}:${s}`
  }
  
  updateCountdown()
  countdownInterval = setInterval(updateCountdown, 1000)
}

// Exchange Rates API
const fetchExchangeRates = async () => {
  try {
    const res = await axios.get('https://open.er-api.com/v6/latest/USD')
    if (res.data && res.data.rates) {
      const idr = res.data.rates.IDR
      const myr = res.data.rates.MYR
      const sar = res.data.rates.SAR
      
      exchangeRates.value = {
        USD: Math.round(idr),
        MYR: Math.round(idr / myr),
        SAR: Math.round(idr / sar)
      }
    }
  } catch (err) {
    console.error('Kurs Exchange API error:', err)
  }
  
  initCurrencyChart()
}

// Currency mini trend chart generator
const initCurrencyChart = () => {
  if (currencyChartInstance) currencyChartInstance.destroy()
  if (!currencyChartCanvas.value) return
  
  const ctx = currencyChartCanvas.value.getContext('2d')
  const centerVal = exchangeRates.value[selectedCurrency.value]
  const trendData = []
  
  const rngSeed = { USD: 0.95, MYR: 1.02, SAR: 0.99 }[selectedCurrency.value] || 1
  for (let i = 0; i < 7; i++) {
    const multiplier = 1 + (Math.sin(i * 1.5) * 0.008 * rngSeed) + (i * 0.001)
    trendData.push(Math.round(centerVal * multiplier))
  }
  
  currencyChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
      datasets: [{
        data: trendData,
        borderColor: '#10b981',
        borderWidth: 2,
        tension: 0.3,
        fill: false,
        pointRadius: 2,
        pointHoverRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      },
      scales: {
        x: { display: true, grid: { display: false } },
        y: { display: false }
      }
    }
  })
}

const updateCurrencyChart = () => {
  initCurrencyChart()
}

// Friday Khatib Schedule API
const fetchKhatibSchedule = async () => {
  try {
    const res = await axios.get('/api/public/khatib-schedule')
    if (res.data.status === 'success') {
      khatibData.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil jadwal khatib:', err)
  }
}

// KPI and Academic Data visualizer (Aceh Studies Inspired)
let visualStatsData = null
const fetchVisualStats = async () => {
  try {
    const res = await axios.get('/api/public/stats-visual')
    if (res.data.status === 'success') {
      visualStatsData = res.data.data
      kpis.value = res.data.data.kpis
    }
  } catch (err) {
    console.error('Gagal memuat visual stats:', err)
  } finally {
    chartsLoading.value = false
    initStatsChart()
  }
}

const initStatsChart = () => {
  if (statsChartInstance) statsChartInstance.destroy()
  if (!statsChartCanvas.value || !visualStatsData) return
  
  const ctx = statsChartCanvas.value.getContext('2d')
  
  if (activeChartTab.value === 'studentTrend') {
    const trend = visualStatsData.charts.studentTrend
    statsChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: trend.labels,
        datasets: trend.datasets.map(ds => ({
          ...ds,
          borderWidth: 3,
          tension: 0.3,
          fill: true,
          pointRadius: 4,
          pointHoverRadius: 6
        }))
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top', labels: { boxWidth: 12 } }
        },
        scales: {
          x: { grid: { display: false } },
          y: { grid: { color: 'rgba(0,0,0,0.05)' } }
        }
      }
    })
  } else if (activeChartTab.value === 'international') {
    const inter = visualStatsData.charts.internationalStudents
    statsChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: inter.labels,
        datasets: [{
          data: inter.data,
          backgroundColor: inter.backgroundColor,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'right', labels: { boxWidth: 15 } }
        }
      }
    })
  } else if (activeChartTab.value === 'publications') {
    const pub = visualStatsData.charts.publicationTrend
    statsChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: pub.labels,
        datasets: [{
          label: 'Jumlah Publikasi Jurnal',
          data: pub.data,
          backgroundColor: pub.backgroundColor || '#3b82f6',
          borderColor: pub.borderColor || '#2563eb',
          borderWidth: 1.5,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: { grid: { display: false } },
          y: { grid: { color: 'rgba(0,0,0,0.05)' } }
        }
      }
    })
  }
}

const switchChartTab = (tabName) => {
  activeChartTab.value = tabName
  initStatsChart()
}

// News, Clippings, and RSS feeds
const fetchAggregates = async () => {
  try {
    // 1. Fetch News
    const newsRes = await axios.get('/api/public/news', { params: { limit: 6 } })
    if (newsRes.data.status === 'success') {
      const items = newsRes.data.data.items
      localNewsList.value = items
      if (items.length > 0) {
        featuredNews.value = items[0]
      }
    }
    
    // 2. Fetch Clippings
    const clipRes = await axios.get('/api/public/clippings', { params: { limit: 5 } })
    if (clipRes.data.status === 'success') {
      clippingList.value = clipRes.data.data.items
    }
    
    // 3. Fetch RSS Items
    const rssRes = await axios.get('/api/public/rss-items', { params: { limit: 12 } })
    if (rssRes.data.status === 'success') {
      rssList.value = rssRes.data.data
    }

    // 4. Fetch Documentation
    const docRes = await axios.get('/api/public/documentation', { params: { limit: 8 } })
    if (docRes.data.status === 'success') {
      documentationList.value = docRes.data.data.items
    }
  } catch (err) {
    console.error('Gagal mengambil data agregat:', err)
  }
}

// Fetch Dynamic Menus for App Launcher
const fetchDynamicMenus = async () => {
  try {
    const res = await axios.get('/api/public/menus')
    if (res.data.status === 'success') {
      dynamicMenus.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil menu aplikasi:', err)
  }
}

// Helper to cycle icon background color classes
const getShortcutColor = (index) => {
  const colors = ['bg-green', 'bg-violet', 'bg-blue', 'bg-amber', 'bg-red', 'bg-gray']
  return colors[index % colors.length]
}

// Navigation handler for dynamic launcher shortcuts
const clickMenu = (menu) => {
  if (!menu.url) return
  if (menu.url.startsWith('http://') || menu.url.startsWith('https://')) {
    window.open(menu.url, '_blank')
  } else {
    // Check if the relative URL matches our route viewNames
    const pathMap = {
      '/': 'home',
      '/berita': 'news',
      '/kliping': 'clippings',
      '/dokumentasi': 'documentation',
      '/rss': 'rss',
      '/akreditasi': 'accreditation'
    }
    const viewName = pathMap[menu.url]
    if (viewName) {
      navigateToView(viewName)
    } else {
      router.push(menu.url)
    }
  }
}

const scrollToData = () => {
  const el = document.getElementById('pusat-data')
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' })
  }
}

// SEO metadata helper
const updateSEOMetadata = (pageTitle, pageDesc, pageImage) => {
  const baseTitle = settings.value.seo_title || settings.value.app_name || 'UIN Ar-Raniry'
  if (pageTitle && pageTitle !== 'Pusat Pencarian Data & Informasi Kampus' && pageTitle !== baseTitle) {
    document.title = `${pageTitle} | ${baseTitle}`
  } else {
    document.title = baseTitle
  }

  const descVal = pageDesc || settings.value.app_description || 'Portal Informasi & Data Terpadu UIN Ar-Raniry'
  let metaDesc = document.querySelector('meta[name="description"]')
  if (!metaDesc) {
    metaDesc = document.createElement('meta')
    metaDesc.setAttribute('name', 'description')
    document.head.appendChild(metaDesc)
  }
  metaDesc.setAttribute('content', descVal)

  if (settings.value.seo_keywords) {
    let metaKeywords = document.querySelector('meta[name="keywords"]')
    if (!metaKeywords) {
      metaKeywords = document.createElement('meta')
      metaKeywords.setAttribute('name', 'keywords')
      document.head.appendChild(metaKeywords)
    }
    metaKeywords.setAttribute('content', settings.value.seo_keywords)
  }

  if (settings.value.seo_author) {
    let metaAuthor = document.querySelector('meta[name="author"]')
    if (!metaAuthor) {
      metaAuthor = document.createElement('meta')
      metaAuthor.setAttribute('name', 'author')
      document.head.appendChild(metaAuthor)
    }
    metaAuthor.setAttribute('content', settings.value.seo_author)
  }

  if (settings.value.app_favicon) {
    const faviconUrl = getFileUrl(settings.value.app_favicon)
    let favLink = document.querySelector('link[rel="icon"]') || document.querySelector('link[rel="shortcut icon"]')
    if (!favLink) {
      favLink = document.createElement('link')
      favLink.setAttribute('rel', 'icon')
      document.head.appendChild(favLink)
    }
    favLink.setAttribute('href', faviconUrl)
  }

  // Dynamic OpenGraph / Meta Image Injection
  const imgUrl = pageImage || (settings.value.seo_image ? getFileUrl(settings.value.seo_image) : (settings.value.app_logo ? getFileUrl(settings.value.app_logo) : ''))
  if (imgUrl) {
    // og:image
    let ogImg = document.querySelector('meta[property="og:image"]')
    if (!ogImg) {
      ogImg = document.createElement('meta')
      ogImg.setAttribute('property', 'og:image')
      document.head.appendChild(ogImg)
    }
    ogImg.setAttribute('content', imgUrl)
    
    // twitter:image
    let twImg = document.querySelector('meta[name="twitter:image"]')
    if (!twImg) {
      twImg = document.createElement('meta')
      twImg.setAttribute('name', 'twitter:image')
      document.head.appendChild(twImg)
    }
    twImg.setAttribute('content', imgUrl)
  }
}

const triggerSEOUpdate = () => {
  const path = route.path
  if (path === '/') {
    updateSEOMetadata('Cari Informasi Kampus', 'Portal informasi dan data terpadu UIN Ar-Raniry Banda Aceh. Akses berita online, arsip kliping pers, dan dokumentasi kegiatan.')
  } else if (path === '/berita') {
    updateSEOMetadata('Portal Berita & Rilis Humas', 'Kumpulan berita resmi, press release, dan rilis humas terbaru UIN Ar-Raniry Banda Aceh.')
  } else if (path === '/kliping') {
    updateSEOMetadata('Arsip Kliping Koran & Pers', 'Dokumentasi kliping berita media cetak seputar UIN Ar-Raniry dari berbagai media pers nasional dan lokal.')
  } else if (path === '/dokumentasi') {
    updateSEOMetadata('Pusat Dokumentasi Kegiatan Rektorat', 'Galeri foto dan dokumentasi video serta daftar kehadiran tokoh pada kegiatan rektorat UIN Ar-Raniry.')
  } else if (path === '/rss') {
    updateSEOMetadata('Sindikasi Warta Kampus (RSS)', 'Feed sindikasi berita dan warta terhangat seputar aktivitas akademik UIN Ar-Raniry.')
  } else if (path === '/pencarian') {
    const q = route.query.q || ''
    updateSEOMetadata(`Hasil Pencarian: "${q}"`, 'Hasil pencarian portal data terpadu UIN Ar-Raniry untuk kata kunci: ' + q)
  } else if (clippingDetailData.value && path.startsWith('/kliping/')) {
    updateSEOMetadata(clippingDetailData.value.title, clippingDetailData.value.summary || 'Detail arsip kliping koran UIN Ar-Raniry.')
  } else if (docDetailData.value && path.startsWith('/dokumentasi/')) {
    updateSEOMetadata(docDetailData.value.event_name, docDetailData.value.description || 'Rincian dokumentasi event UIN Ar-Raniry.')
  } else {
    updateSEOMetadata()
  }
}

// ── SUB-VIEWS LOGIC (NAVIGATION & DATA LOADING) ─────────
const navigateToView = (viewName) => {
  if (viewName === 'admin') {
    router.push(authStore.isAuthenticated ? '/dashboard' : '/login')
    return
  }
  const pathMap = {
    home: '/',
    news: '/berita',
    clippings: '/kliping',
    documentation: '/dokumentasi',
    rss: '/rss',
    search: '/pencarian',
    accreditation: '/akreditasi'
  }
  router.push(pathMap[viewName] || '/')
}

const navigateToDetail = (module, itemOrId) => {
  let id = typeof itemOrId === 'object' && itemOrId !== null ? itemOrId.id : itemOrId
  let title = ''
  if (typeof itemOrId === 'object' && itemOrId !== null) {
    title = itemOrId.title || itemOrId.event_name || ''
  }
  const slugTitle = title
    ? title.toLowerCase()
           .replace(/[^a-z0-9]+/g, '-')
           .replace(/(^-|-$)/g, '')
    : 'detail'

  if (module === 'clippings') {
    router.push(`/kliping/${id}-${slugTitle}`)
  } else if (module === 'documentation') {
    router.push(`/dokumentasi/${id}-${slugTitle}`)
  }
}

const scrollToDataHub = () => {
  const el = document.getElementById('pusat-data')
  if (el) el.scrollIntoView({ behavior: 'smooth' })
}

// 1. Loading Subview: News View
const fetchNewsViewData = async () => {
  try {
    // A. Fetch categories & media for drop-down filters
    const [catsRes, medRes, etalaseRes, statsRes] = await Promise.all([
      axios.get('/api/public/categories'),
      axios.get('/api/public/media'), // Fetch media list
      axios.get('/api/public/news-etalase'),
      axios.get('/api/public/media-stats')
    ])
    
    categoriesList.value = catsRes.data.data
    mediaList.value = medRes.data.data
    etalaseList.value = etalaseRes.data.data
    mediaStatsData.value = statsRes.data.data
    
    // Sum stats total
    let sum = 0
    statsRes.data.data.media.forEach(m => sum += parseInt(m.count))
    statsTotals.value.news = sum > 0 ? sum : 100
    
    // B. Fetch Table List
    await fetchNewsTable()
  } catch (err) {
    console.error('Gagal mengambil data halaman berita:', err)
  }
}

const fetchNewsTable = async () => {
  try {
    const res = await axios.get('/api/public/news', {
      params: {
        page: newsTableData.value.page,
        limit: newsTableData.value.limit,
        search: newsFilters.value.search,
        media_id: newsFilters.value.mediaId,
        category_id: newsFilters.value.categoryId
      }
    })
    if (res.data.status === 'success') {
      newsTableData.value = {
        items: res.data.data.items,
        total_items: res.data.data.total_items,
        page: res.data.data.page,
        limit: res.data.data.limit,
        total_pages: res.data.data.total_pages
      }
    }
  } catch (err) {
    console.error('Gagal mengambil tabel berita:', err)
  }
}

const changeNewsPage = (page) => {
  newsTableData.value.page = page
  fetchNewsTable()
}

const filterNewsByMedia = (mediaId) => {
  newsFilters.value.mediaId = mediaId
  fetchNewsTable()
  // Scroll down to table
  const el = document.querySelector('.table-card')
  if (el) el.scrollIntoView({ behavior: 'smooth' })
}

const toggleFollowMedia = (mediaId) => {
  if (followedMedia.value.includes(mediaId)) {
    followedMedia.value = followedMedia.value.filter(id => id !== mediaId)
  } else {
    followedMedia.value.push(mediaId)
  }
}

const resetNewsFilters = () => {
  newsFilters.value = { search: '', categoryId: '', mediaId: '' }
  fetchNewsTable()
}

const getPercentage = (count, total) => {
  return Math.round((parseInt(count) / total) * 100)
}

// 2. Loading Subview: Clippings View
const fetchClippingsViewData = async () => {
  try {
    if (mediaList.value.length === 0 || categoriesList.value.length === 0) {
      const [catsRes, medRes] = await Promise.all([
        axios.get('/api/public/categories'),
        axios.get('/api/public/media')
      ])
      categoriesList.value = catsRes.data.data
      mediaList.value = medRes.data.data
    }

    const res = await axios.get('/api/public/clippings', {
      params: {
        page: clippingsTableData.value.page,
        limit: clippingsTableData.value.limit,
        search: clippingSearchQuery.value,
        media_id: filterMedia.value,
        category_id: filterCategory.value,
        start_date: filterStartDate.value,
        end_date: filterEndDate.value,
        quarter: filterQuarter.value
      }
    })
    if (res.data.status === 'success') {
      clippingsTableData.value = {
        items: res.data.data.items,
        total_items: res.data.data.total_items,
        page: res.data.data.page,
        limit: res.data.data.limit,
        total_pages: res.data.data.total_pages
      }
    }
  } catch (err) {
    console.error('Gagal mengambil tabel kliping:', err)
  }
}

const changeClippingsPage = (page) => {
  clippingsTableData.value.page = page
  fetchClippingsViewData()
}

const fetchClippingDetail = async (id) => {
  try {
    const res = await axios.get(`/api/public/clippings/${id}`)
    if (res.data.status === 'success') {
      clippingDetailData.value = res.data.data
      updateSEOMetadata(
        clippingDetailData.value.title,
        clippingDetailData.value.summary || 'Detail arsip kliping koran UIN Ar-Raniry.',
        clippingDetailData.value.media_logo ? getMediaLogoUrl(clippingDetailData.value.media_logo) : null
      )
    }
  } catch (err) {
    console.error('Gagal memuat detail kliping:', err)
  }
}

// 3. Loading Subview: Documentation View
const fetchDocumentationViewData = async () => {
  try {
    if (categoriesList.value.length === 0) {
      const catsRes = await axios.get('/api/public/categories')
      categoriesList.value = catsRes.data.data
    }

    const res = await axios.get('/api/public/documentation', {
      params: {
        page: documentationTableData.value.page,
        limit: documentationTableData.value.limit,
        search: docSearchQuery.value,
        category_id: filterDocCategory.value,
        location_type: filterLocationType.value,
        start_date: filterDocStartDate.value,
        end_date: filterDocEndDate.value,
        quarter: filterDocQuarter.value
      }
    })
    if (res.data.status === 'success') {
      documentationTableData.value = {
        items: res.data.data.items,
        total_items: res.data.data.total_items,
        page: res.data.data.page,
        limit: res.data.data.limit,
        total_pages: res.data.data.total_pages
      }
    }
  } catch (err) {
    console.error('Gagal mengambil tabel dokumentasi:', err)
  }
}

const changeDocPage = (page) => {
  documentationTableData.value.page = page
  fetchDocumentationViewData()
}

const fetchDocDetail = async (id) => {
  try {
    const res = await axios.get(`/api/public/documentation/${id}`)
    if (res.data.status === 'success') {
      const data = res.data.data
      if (data.attendance && Array.isArray(data.attendance)) {
        const order = { 'Rektorat': 1, 'Fakultas': 2 }
        data.attendance.sort((a, b) => {
          const levelA = a.level ? a.level.trim() : ''
          const levelB = b.level ? b.level.trim() : ''
          const valA = order[levelA] || 3
          const valB = order[levelB] || 3
          return valA - valB
        })
      }
      docDetailData.value = data
      updateSEOMetadata(
        docDetailData.value.event_name,
        docDetailData.value.description || 'Rincian dokumentasi event UIN Ar-Raniry.',
        docDetailData.value.thumbnail_url ? getImageUrl(docDetailData.value.thumbnail_url) : null
      )
    }
  } catch (err) {
    console.error('Gagal memuat detail dokumentasi:', err)
  }
}

// Utilities
const getImageUrl = (url) => {
  if (!url) return '';
  const gdriveMatch = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
  if (gdriveMatch && gdriveMatch[1]) {
    // Gunakan lh3.googleusercontent.com yang lebih stabil dan bersifat publik (tanpa login Google)
    return `https://lh3.googleusercontent.com/d/${gdriveMatch[1]}=w1200`;
  }
  return url;
}

const getIframeUrl = (url) => {
  if (!url) return null;
  // Match folder ID
  const folderMatch = url.match(/folders\/([a-zA-Z0-9_-]+)/) || url.match(/id=([a-zA-Z0-9_-]+)/);
  if (folderMatch && folderMatch[1]) {
    return `https://drive.google.com/embeddedfolderview?id=${folderMatch[1]}#grid`;
  }
  return null;
}

const getPdfUrl = (filePath) => {
  if (!filePath) return ''
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
  return `${apiBase}/uploads/clippings/${filePath}`
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const dateObj = new Date(dateStr)
  if (isNaN(dateObj.getTime())) return dateStr
  
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  
  const dayName = days[dateObj.getDay()]
  const dateNum = dateObj.getDate()
  const monthName = months[dateObj.getMonth()]
  const year = dateObj.getFullYear()
  
  return `${dayName}, ${dateNum} ${monthName} ${year}`
}

const getMediaLogoUrl = (logoPath) => {
  if (!logoPath) return ''
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
  return `${apiBase}/uploads/media/${logoPath}`
}

const truncate = (text, len) => {
  if (!text) return ''
  return text.length > len ? text.slice(0, len) + '...' : text
}

const openLink = (link) => {
  if (!link) return
  if (link.startsWith('http') || link.startsWith('/')) {
    window.open(link, '_blank')
  }
}

// Dynamic state watcher on URL route path & parameters for clean SEO URLs
watch(() => route, async (newRoute) => {
  const path = newRoute.path
  if (path === '/') {
    currentView.value = 'home'
    updateSEOMetadata('Cari Informasi Kampus', 'Portal informasi dan data terpadu UIN Ar-Raniry Banda Aceh. Akses berita online, arsip kliping pers, dan dokumentasi kegiatan.')
    nextTick(() => {
      initScrollReveal()
    })
  } else if (path === '/berita') {
    currentView.value = 'news'
    newsFilters.value.search = newRoute.query.search || ''
    newsFilters.value.categoryId = newRoute.query.category_id || ''
    newsFilters.value.mediaId = newRoute.query.media_id || ''
    updateSEOMetadata('Portal Berita & Rilis Humas', 'Kumpulan berita resmi, press release, dan rilis humas terbaru UIN Ar-Raniry Banda Aceh.')
    await fetchNewsViewData()
  } else if (path === '/kliping') {
    currentView.value = 'clippings'
    clippingSearchQuery.value = newRoute.query.search || ''
    filterMedia.value = newRoute.query.media_id || ''
    filterCategory.value = newRoute.query.category_id || ''
    filterStartDate.value = newRoute.query.start_date || ''
    filterEndDate.value = newRoute.query.end_date || ''
    filterQuarter.value = newRoute.query.quarter || ''
    updateSEOMetadata('Arsip Kliping Koran & Pers', 'Dokumentasi kliping berita media cetak seputar UIN Ar-Raniry dari berbagai media pers nasional dan lokal.')
    await fetchClippingsViewData()
  } else if (path === '/dokumentasi') {
    currentView.value = 'documentation'
    docSearchQuery.value = newRoute.query.search || ''
    filterLocationType.value = newRoute.query.location_type || ''
    filterDocCategory.value = newRoute.query.category_id || ''
    filterDocStartDate.value = newRoute.query.start_date || ''
    filterDocEndDate.value = newRoute.query.end_date || ''
    filterDocQuarter.value = newRoute.query.quarter || ''
    updateSEOMetadata('Pusat Dokumentasi Kegiatan Rektorat', 'Galeri foto dan dokumentasi video serta daftar kehadiran tokoh pada kegiatan rektorat UIN Ar-Raniry.')
    await fetchDocumentationViewData()
  } else if (path === '/rss') {
    currentView.value = 'rss'
    updateSEOMetadata('Sindikasi Warta Kampus (RSS)', 'Feed sindikasi berita dan warta terhangat seputar aktivitas akademik UIN Ar-Raniry.')
  } else if (path === '/akreditasi') {
    currentView.value = 'accreditation'
    accreditationFilters.value.search = newRoute.query.search || ''
    accreditationFilters.value.facultyId = newRoute.query.faculty_id || ''
    accreditationFilters.value.jenjang = newRoute.query.jenjang || ''
    accreditationFilters.value.akreditasi = newRoute.query.akreditasi || ''
    accreditationFilters.value.jalurMasuk = newRoute.query.jalur_masuk || ''
    updateSEOMetadata('Portal Informasi Akreditasi Prodi', 'Portal informasi peringkat akreditasi program studi UIN Ar-Raniry Banda Aceh.')
    await fetchAccreditationViewData()
  } else if (path === '/pencarian') {
    currentView.value = 'search'
    const q = newRoute.query.q || ''
    const t = newRoute.query.type || 'internal'
    searchQuery.value = q
    searchType.value = t
    updateSEOMetadata(`Hasil Pencarian: "${q}"`, 'Hasil pencarian portal data terpadu UIN Ar-Raniry untuk kata kunci: ' + q)
    if (q) {
      await runSearchAPI(q)
    }
  } else if (path.startsWith('/kliping/')) {
    currentView.value = 'clipping-detail'
    const slug = newRoute.params.slug || path.substring(9)
    const id = parseInt(slug.split('-')[0])
    if (id) {
      await fetchClippingDetail(id)
    }
  } else if (path.startsWith('/dokumentasi/')) {
    currentView.value = 'doc-detail'
    const slug = newRoute.params.slug || path.substring(13)
    const id = parseInt(slug.split('-')[0])
    if (id) {
      await fetchDocDetail(id)
    }
  }
}, { immediate: true, deep: true })

const isSameQuery = (q1, q2) => {
  const keys1 = Object.keys(q1)
  const keys2 = Object.keys(q2)
  if (keys1.length !== keys2.length) return false
  for (const k of keys1) {
    if (String(q1[k] || '') !== String(q2[k] || '')) return false
  }
  return true
}

watch(() => ({
  search: newsFilters.value.search,
  categoryId: newsFilters.value.categoryId,
  mediaId: newsFilters.value.mediaId
}), (newVal) => {
  if (currentView.value !== 'news') return
  const query = {}
  if (newVal.search) query.search = newVal.search
  if (newVal.categoryId) query.category_id = newVal.categoryId
  if (newVal.mediaId) query.media_id = newVal.mediaId
  if (!isSameQuery(query, route.query)) {
    router.replace({ path: '/berita', query })
  }
}, { deep: true })

watch(() => ({
  search: clippingSearchQuery.value,
  mediaId: filterMedia.value,
  categoryId: filterCategory.value,
  startDate: filterStartDate.value,
  endDate: filterEndDate.value,
  quarter: filterQuarter.value
}), (newVal) => {
  if (currentView.value !== 'clippings') return
  const query = {}
  if (newVal.search) query.search = newVal.search
  if (newVal.mediaId) query.media_id = newVal.mediaId
  if (newVal.categoryId) query.category_id = newVal.categoryId
  if (newVal.startDate) query.start_date = newVal.startDate
  if (newVal.endDate) query.end_date = newVal.endDate
  if (newVal.quarter) query.quarter = newVal.quarter
  if (!isSameQuery(query, route.query)) {
    router.replace({ path: '/kliping', query })
  }
}, { deep: true })

watch(() => ({
  search: docSearchQuery.value,
  locationType: filterLocationType.value,
  categoryId: filterDocCategory.value,
  startDate: filterDocStartDate.value,
  endDate: filterDocEndDate.value,
  quarter: filterDocQuarter.value
}), (newVal) => {
  if (currentView.value !== 'documentation') return
  const query = {}
  if (newVal.search) query.search = newVal.search
  if (newVal.locationType) query.location_type = newVal.locationType
  if (newVal.categoryId) query.category_id = newVal.categoryId
  if (newVal.startDate) query.start_date = newVal.startDate
  if (newVal.endDate) query.end_date = newVal.endDate
  if (newVal.quarter) query.quarter = newVal.quarter
  if (!isSameQuery(query, route.query)) {
    router.replace({ path: '/dokumentasi', query })
  }
}, { deep: true })

watch(() => ({
  search: accreditationFilters.value.search,
  facultyId: accreditationFilters.value.facultyId,
  jenjang: accreditationFilters.value.jenjang,
  akreditasi: accreditationFilters.value.akreditasi,
  jalurMasuk: accreditationFilters.value.jalurMasuk
}), (newVal) => {
  if (currentView.value !== 'accreditation') return
  const query = {}
  if (newVal.search) query.search = newVal.search
  if (newVal.facultyId) query.faculty_id = newVal.facultyId
  if (newVal.jenjang) query.jenjang = newVal.jenjang
  if (newVal.akreditasi) query.akreditasi = newVal.akreditasi
  if (newVal.jalurMasuk) query.jalur_masuk = newVal.jalurMasuk
  if (!isSameQuery(query, route.query)) {
    router.replace({ path: '/akreditasi', query })
  }
}, { deep: true })

// Scroll Reveal Observer
const revealObserver = ref(null)
const initScrollReveal = () => {
  if (typeof IntersectionObserver === 'undefined') return
  if (revealObserver.value) {
    revealObserver.value.disconnect()
  }
  revealObserver.value = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('reveal-visible')
        revealObserver.value.unobserve(entry.target)
      }
    })
  }, {
    root: null,
    rootMargin: '0px 0px -50px 0px',
    threshold: 0.05
  })
  nextTick(() => {
    const elements = document.querySelectorAll('.scroll-reveal')
    elements.forEach(el => {
      el.classList.remove('reveal-visible')
      revealObserver.value.observe(el)
    })
  })
}

// Lifecycle Hooks
onMounted(() => {
  fetchSettings()
  fetchDynamicMenus()
  updateLiveClock()
  liveTimer.value = setInterval(updateLiveClock, 1000)
  initSpeechRecognition()
  
  setInterval(simulateFlights, 10000)
  
  fetchWeather()
  fetchPrayerTimes()
  fetchExchangeRates()
  fetchKhatibSchedule()
  fetchVisualStats()
  fetchAggregates()
  
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    isDarkMode.value = true
    document.body.classList.add('dark-theme')
  }

  document.addEventListener('click', handleGlobalClick)

  nextTick(() => {
    initScrollReveal()
  })
})

onUnmounted(() => {
  if (liveTimer.value) clearInterval(liveTimer.value)
  if (countdownInterval) clearInterval(countdownInterval)
  if (statsChartInstance) statsChartInstance.destroy()
  if (currencyChartInstance) currencyChartInstance.destroy()
  if (revealObserver.value) revealObserver.value.disconnect()
  if (bodyMutationObserver) bodyMutationObserver.disconnect()
  document.removeEventListener('click', handleGlobalClick)
})</script>

<style scoped>
/* ─── BASE PORTAL STYLING ──────────────────────────────── */
.portal-root {
  min-height: 100vh;
  width: 100%;
  background: #f8fafc;
  color: #1e293b;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  transition: background-color 0.3s, color 0.3s;
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
}

.portal-root.dark-mode {
  background: #0f172a;
  color: #f1f5f9;
}

.portal-root.dark-mode :deep(body) {
  background: #0f172a;
}

/* ─── HEADER NAV ───────────────────────────────────────── */
.portal-nav {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid #e2e8f0;
  transition: all 0.3s;
}
.portal-root.dark-mode .portal-nav {
  background: rgba(15, 23, 42, 0.85);
  border-bottom: 1px solid #1e293b;
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 14px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand-left-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
}

.waffle-menu-container {
  position: relative;
}

.btn-waffle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: transparent;
  border: none;
  outline: none;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}
.portal-root.dark-mode .btn-waffle {
  color: #94a3b8;
}
.btn-waffle:hover {
  background: #f1f5f9;
  color: #1e293b;
}
.portal-root.dark-mode .btn-waffle:hover {
  background: #1e293b;
  color: #f8fafc;
}

.waffle-icon {
  width: 20px;
  height: 20px;
}

.waffle-dropdown {
  position: absolute;
  top: 52px;
  left: 0;
  width: 320px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  z-index: 1000;
  overflow: hidden;
}
.portal-root.dark-mode .waffle-dropdown {
  background: #1e293b;
  border-color: #334155;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
}

.waffle-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  padding: 20px;
}

.waffle-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  cursor: pointer;
  padding: 12px 8px;
  border-radius: 12px;
  transition: all 0.2s ease;
}
.waffle-item:hover {
  background: #f8fafc;
  transform: translateY(-2px);
}
.portal-root.dark-mode .waffle-item:hover {
  background: #334155;
}

.waffle-item-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: white;
  margin-bottom: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
}

.waffle-item-label {
  font-size: 11.5px;
  font-weight: 600;
  color: #334155;
  line-height: 1.3;
}
.portal-root.dark-mode .waffle-item-label {
  color: #cbd5e1;
}

/* Waffle Transition */
.waffle-fade-enter-active,
.waffle-fade-leave-active {
  transition: all 0.2s ease-out;
}
.waffle-fade-enter-from,
.waffle-fade-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-10px);
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-logo {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #10b981, #047857);
  border-radius: 10px;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
}
.brand-logo svg {
  width: 22px;
  height: 22px;
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.main-title {
  font-weight: 900;
  font-size: 15px;
  letter-spacing: 0.5px;
  color: #10b981;
}
.sub-title {
  font-size: 11px;
  font-weight: 500;
  color: #64748b;
}
.portal-root.dark-mode .sub-title {
  color: #94a3b8;
}

.nav-center {
  display: flex;
  align-items: center;
}

.live-clock {
  background: #f1f5f9;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 12.5px;
  font-weight: 600;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid #e2e8f0;
}
.portal-root.dark-mode .live-clock {
  background: #1e293b;
  color: #cbd5e1;
  border-color: #334155;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.theme-toggle {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.portal-root.dark-mode .theme-toggle {
  background: #1e293b;
  border-color: #334155;
}
.theme-toggle:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.btn-dashboard, .btn-login {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 700;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
  transition: all 0.2s;
}
.btn-dashboard:hover, .btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(16, 185, 129, 0.35);
}
.btn-dashboard svg, .btn-login svg {
  width: 16px;
  height: 16px;
}

/* ─── HERO SEARCH SECTION ───────────────────────────────── */
.hero-search-section {
  position: relative;
  max-width: 100%;
  margin: 0 auto;
  width: 100%;
  height: calc(100vh - 68px);
  min-height: 520px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 24px;
  text-align: center;
  overflow: hidden;
  box-sizing: border-box;
}

/* Scroll Reveal animation classes */
.scroll-reveal {
  opacity: 0;
  transform: translateY(40px);
  transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
  will-change: transform, opacity;
}
.scroll-reveal.reveal-visible {
  opacity: 1;
  transform: translateY(0);
}

/* Scroll Down Indicator */
.scroll-down-indicator {
  position: absolute;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  z-index: 10;
  opacity: 0.7;
  transition: all 0.2s ease;
}
.scroll-down-indicator:hover {
  opacity: 1;
  transform: translateX(-50%) translateY(2px);
}
.scroll-text {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: #64748b;
}
.portal-root.dark-mode .scroll-text {
  color: #94a3b8;
}
.scroll-arrow {
  width: 22px;
  height: 22px;
  color: #10b981;
  animation: waffle-bounce 2s infinite;
}
@keyframes waffle-bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-6px);
  }
  60% {
    transform: translateY(-3px);
  }
}

.hero-bg-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 600px;
  height: 250px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(59, 130, 246, 0.05) 50%, rgba(0,0,0,0) 100%);
  z-index: 0;
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 860px;
  margin: 0 auto;
  width: 100%;
}

.hero-title {
  font-size: 38px;
  font-weight: 900;
  letter-spacing: -1px;
  margin-bottom: 12px;
  color: #0f172a;
}
.portal-root.dark-mode .hero-title {
  color: white;
}

.highlight-text {
  background: linear-gradient(120deg, #10b981, #3b82f6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-desc {
  font-size: 15px;
  color: #64748b;
  margin-bottom: 24px;
  line-height: 1.6;
}
.portal-root.dark-mode .hero-desc {
  color: #94a3b8;
}

/* Search Engine Box */
.search-engine-box {
  display: flex;
  background: white;
  border-radius: 22px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  padding: 10px 12px;
  gap: 10px;
  margin-bottom: 24px;
  transition: all 0.3s;
}
.portal-root.dark-mode .search-engine-box {
  background: #1e293b;
  border-color: #334155;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.search-engine-box:focus-within {
  border-color: #10b981;
  box-shadow: 0 10px 30px rgba(16, 185, 129, 0.15);
  transform: translateY(-2px);
}

.search-category-select {
  flex-shrink: 0;
  border-right: 1px solid #e2e8f0;
  padding-right: 12px;
}
.portal-root.dark-mode .search-category-select {
  border-right-color: #334155;
}

.cat-dropdown {
  border: none;
  background: transparent;
  outline: none;
  font-size: 14.5px;
  font-weight: 700;
  color: #475569;
  padding: 10px 14px;
  cursor: pointer;
}
.cat-dropdown option {
  color: #1e293b;
  background-color: #ffffff;
}
.portal-root.dark-mode .cat-dropdown {
  color: #cbd5e1;
}
.portal-root.dark-mode .cat-dropdown option {
  color: #f1f5f9;
  background-color: #1e293b;
}

.search-input-wrapper {
  flex-grow: 1;
  display: flex;
  align-items: center;
  position: relative;
}

.search-input {
  width: 100%;
  border: none;
  background: transparent;
  outline: none;
  font-size: 16.5px;
  padding: 10px 16px;
  color: #1e293b;
}
.portal-root.dark-mode .search-input {
  color: white;
}

.btn-clear {
  position: absolute;
  right: 16px;
  background: transparent;
  border: none;
  font-size: 14px;
  color: #94a3b8;
  cursor: pointer;
}

.btn-search-trigger {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 16px;
  font-weight: 800;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-search-trigger:hover {
  background: #059669;
  transform: scale(1.02);
}
.btn-search-trigger svg {
  width: 16px;
  height: 16px;
}

/* ─── MSN-STYLE SHORTCUT BAR ────────────────────────────── */
.msn-shortcut-bar {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: 28px;
  margin-bottom: 24px;
  flex-wrap: wrap;
  overflow-x: auto;
  padding: 6px;
}

.shortcut-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;
  width: 90px;
}
.shortcut-item:hover {
  transform: translateY(-3px);
}

.shortcut-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  background: white;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 10px rgba(0,0,0,0.03);
  transition: all 0.2s;
}
.portal-root.dark-mode .shortcut-icon {
  background: #1e293b;
  border-color: #334155;
  box-shadow: none;
}
.shortcut-item:hover .shortcut-icon {
  box-shadow: 0 8px 18px rgba(16, 185, 129, 0.15);
  border-color: #10b981;
}

.shortcut-icon.bg-green { border-bottom: 3.5px solid #10b981; }
.shortcut-icon.bg-violet { border-bottom: 3.5px solid #8b5cf6; }
.shortcut-icon.bg-blue { border-bottom: 3.5px solid #3b82f6; }
.shortcut-icon.bg-amber { border-bottom: 3.5px solid #f59e0b; }
.shortcut-icon.bg-red { border-bottom: 3.5px solid #ef4444; }
.shortcut-icon.bg-gray { border-bottom: 3.5px solid #64748b; }

.shortcut-label {
  font-size: 11.5px;
  font-weight: 700;
  color: #475569;
  transition: color 0.2s;
}
.portal-root.dark-mode .shortcut-label {
  color: #cbd5e1;
}
.shortcut-item:hover .shortcut-label {
  color: #10b981;
}

/* Quick links */
.quick-links {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 12px;
  font-size: 13px;
}
.ql-label {
  color: #64748b;
}
.quick-links a {
  color: #10b981;
  text-decoration: none;
  font-weight: 600;
  border-bottom: 1.5px dashed rgba(16, 185, 129, 0.4);
  transition: all 0.2s;
}
.quick-links a:hover {
  color: #059669;
  border-bottom-style: solid;
}

/* ─── PORTAL GRID LAYOUT ───────────────────────────────── */
.portal-main-grid {
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  padding: 0 24px 80px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.full-width {
  grid-column: span 3;
}

.flex-2 {
  grid-column: span 2;
}

.flex-1 {
  grid-column: span 1;
}

/* ─── KPI STATS VISUALIZER ─────────────────────────────── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}
.kpi-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.01);
  transition: all 0.3s ease;
}
.portal-root.dark-mode .kpi-card {
  background: #1e293b;
  border-color: #2e3a4e;
}
.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.08);
  border-color: rgba(16, 185, 129, 0.3);
}
.kpi-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
}
.kpi-icon-wrapper.students { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.kpi-icon-wrapper.lecturers { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.kpi-icon-wrapper.accreditation { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.kpi-icon-wrapper.publications { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

.kpi-details {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}
.kpi-val {
  font-size: 20px;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.2;
}
.portal-root.dark-mode .kpi-val {
  color: white;
}
.kpi-lbl {
  font-size: 11.5px;
  font-weight: 700;
  color: #64748b;
  margin-top: 2px;
}
.kpi-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  margin-top: 4px;
}
.kpi-trend.up { color: #10b981; }
.kpi-trend.stable { color: #64748b; }
.kpi-trend.down { color: #ef4444; }
.trend-sub {
  color: #94a3b8;
}

/* Charts Dashboard Container */
.charts-dashboard-container {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 24px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
  margin-bottom: 32px;
  min-width: 0;
}
.portal-root.dark-mode .charts-dashboard-container {
  background: #1e293b;
  border-color: #2e3a4e;
}
.charts-sidebar-tabs {
  display: flex;
  flex-direction: column;
  gap: 8px;
  border-right: 1px solid #e2e8f0;
  padding-right: 20px;
}
.portal-root.dark-mode .charts-sidebar-tabs {
  border-right-color: #2e3a4e;
}
.chart-tab-btn {
  text-align: left;
  padding: 12px 16px;
  border: none;
  background: transparent;
  color: #475569;
  font-size: 13px;
  font-weight: 700;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
}
.portal-root.dark-mode .chart-tab-btn {
  color: #cbd5e1;
}
.chart-tab-btn:hover {
  background: rgba(16, 185, 129, 0.05);
}
.chart-tab-btn.active {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border-left: 4px solid #10b981;
  padding-left: 12px;
}
.chart-canvas-wrapper {
  min-height: 250px;
  position: relative;
  width: 100%;
  min-width: 0;
  overflow: hidden;
}
.chart-loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  z-index: 10;
}
.portal-root.dark-mode .chart-loading-overlay {
  background: rgba(30, 41, 59, 0.85);
}

/* Spinner helper */
.spinner, .spinner-sm {
  border: 3px solid rgba(16, 185, 129, 0.1);
  border-radius: 50%;
  border-top: 3px solid #10b981;
  animation: spin 1s linear infinite;
}
.spinner {
  width: 28px;
  height: 28px;
}
.spinner-sm {
  width: 18px;
  height: 18px;
  border-width: 2px;
  border-top-width: 2px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* ─── MSN WIDGETS LAYOUT ───────────────────────────────── */
.portal-widget {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
  display: flex;
  flex-direction: column;
  gap: 16px;
  transition: all 0.3s ease;
  overflow: hidden;
}
.portal-root.dark-mode .portal-widget {
  background: #1e293b;
  border-color: #2e3a4e;
}
.portal-widget:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.06);
  border-color: rgba(16, 185, 129, 0.3);
}
.portal-widget-combo {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.widget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1.5px solid #f1f5f9;
  padding-bottom: 12px;
  margin-bottom: 4px;
  flex-wrap: wrap;
  gap: 12px;
}
.portal-root.dark-mode .widget-header {
  border-bottom-color: #24334a;
}
.widget-header h3 {
  font-size: 15.5px;
  font-weight: 850;
  margin: 0;
  color: #0f172a;
}
.portal-root.dark-mode .widget-header h3 {
  color: white;
}
.widget-date-badge {
  font-size: 10px;
  font-weight: 800;
  background: #f1f5f9;
  padding: 3px 8px;
  border-radius: 6px;
  color: #64748b;
}
.portal-root.dark-mode .widget-date-badge {
  background: #131c2e;
  color: #cbd5e1;
}
.widget-sub {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
}

/* ─── KHATIB WIDGET ────────────────────────────────────── */
.khatib-highlight-card {
  position: relative;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.06), rgba(245, 158, 11, 0.03));
  border: 1px solid rgba(16, 185, 129, 0.15);
  border-radius: 16px;
  padding: 20px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.005);
}
.portal-root.dark-mode .khatib-highlight-card {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(245, 158, 11, 0.05));
  border-color: rgba(16, 185, 129, 0.25);
}
.card-glow {
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 60%);
  pointer-events: none;
  animation: pulse-glow 8s infinite alternate;
}
@keyframes pulse-glow {
  0% { transform: scale(0.9) translate(-10px, -10px); }
  100% { transform: scale(1.1) translate(10px, 10px); }
}
.badge-main-mosque {
  display: inline-block;
  font-size: 8.5px;
  font-weight: 900;
  background: #10b981;
  color: white;
  padding: 3px 8px;
  border-radius: 6px;
  margin-bottom: 10px;
  letter-spacing: 0.5px;
}
.khatib-highlight-card h4 {
  font-size: 16px;
  font-weight: 900;
  color: #0f172a;
  margin: 0 0 14px;
}
.portal-root.dark-mode .khatib-highlight-card h4 {
  color: white;
}
.khatib-details-row {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 16px;
}
.khatib-photo-placeholder {
  font-size: 32px;
  width: 56px;
  height: 56px;
  background: rgba(16, 185, 129, 0.12);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(16, 185, 129, 0.1);
}
.khatib-names {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.khatib-label {
  font-size: 9px;
  font-weight: 900;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.khatib-name {
  font-size: 15px;
  font-weight: 900;
  color: #047857;
}
.portal-root.dark-mode .khatib-name {
  color: #10b981;
}
.khatib-title {
  font-size: 12px;
  font-style: italic;
  color: #475569;
}
.portal-root.dark-mode .khatib-title {
  color: #cbd5e1;
}
.card-footer-info {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #64748b;
  border-top: 1px solid rgba(16, 185, 129, 0.15);
  padding-top: 10px;
  flex-wrap: wrap;
  gap: 8px;
}
.portal-root.dark-mode .card-footer-info {
  color: #94a3b8;
}

.other-khatibs-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 8px;
}
.other-khatibs-title {
  font-size: 11px;
  font-weight: 900;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.other-mosque-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px;
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  gap: 12px;
}
.portal-root.dark-mode .other-mosque-item {
  background: #152033;
  border-color: #24334a;
}
.omi-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.omi-info h5 {
  font-size: 12.5px;
  font-weight: 850;
  margin: 0;
  color: #1e293b;
}
.portal-root.dark-mode .omi-info h5 {
  color: white;
}
.omi-loc {
  font-size: 10px;
  color: #94a3b8;
}
.omi-khatib {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  text-align: right;
  gap: 2px;
}
.omi-kh-name {
  font-size: 12px;
  font-weight: 800;
  color: #475569;
}
.portal-root.dark-mode .omi-kh-name {
  color: #cbd5e1;
}
.omi-kh-title {
  font-size: 10px;
  font-style: italic;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
}

/* ─── SHALAT WIDGET ────────────────────────────────────── */
.shalat-next-alert {
  background: rgba(16, 185, 129, 0.06);
  border: 1px solid rgba(16, 185, 129, 0.15);
  border-radius: 12px;
  padding: 10px 14px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 14px;
}
.sna-title {
  font-size: 9px;
  font-weight: 900;
  color: #10b981;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.sna-value {
  font-size: 13px;
  font-weight: 900;
  color: #047857;
}
.portal-root.dark-mode .sna-value {
  color: #10b981;
}
.sna-countdown {
  font-size: 11px;
  font-weight: 800;
  color: #f59e0b;
}
.shalat-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.shalat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10px 8px;
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 10px;
  transition: all 0.2s;
  gap: 4px;
}
.portal-root.dark-mode .shalat-item {
  background: #152033;
  border-color: #24334a;
}
.shalat-item.next-prayer-active {
  background: rgba(16, 185, 129, 0.08);
  border-color: #10b981;
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
}
.sh-name {
  font-size: 10px;
  font-weight: 900;
  color: #64748b;
  text-transform: uppercase;
}
.shalat-item.next-prayer-active .sh-name {
  color: #10b981;
}
.sh-val {
  font-size: 13px;
  font-weight: 850;
  color: #0f172a;
}
.portal-root.dark-mode .sh-val {
  color: white;
}

/* ─── WEATHER WIDGET ───────────────────────────────────── */
.weather-content {
  min-height: 110px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.weather-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 12px;
  color: #64748b;
}
.weather-main-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
}
.weather-icon-wrapper {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.weather-icon {
  width: 100%;
  height: 100%;
}
.weather-temp-block {
  display: flex;
  flex-direction: column;
}
.weather-temp {
  font-size: 22px;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.1;
}
.portal-root.dark-mode .weather-temp {
  color: white;
}
.weather-status {
  font-size: 12px;
  font-weight: 800;
  color: #64748b;
}
.weather-stats {
  display: flex;
  gap: 16px;
  font-size: 11px;
  color: #64748b;
  margin-bottom: 12px;
}
.weather-forecast {
  display: flex;
  justify-content: space-between;
  border-top: 1px solid #f1f5f9;
  padding-top: 10px;
  margin-top: 4px;
}
.portal-root.dark-mode .weather-forecast {
  border-top-color: #24334a;
}
.forecast-day {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}
.fc-name {
  font-size: 9px;
  font-weight: 900;
  color: #94a3b8;
  text-transform: uppercase;
}
.fc-icon {
  font-size: 15px;
}
.fc-temp {
  font-size: 10px;
  font-weight: 750;
  color: #64748b;
}

/* ─── FLIGHT WIDGET ────────────────────────────────────── */
.flight-tabs {
  display: flex;
  background: #f1f5f9;
  border-radius: 8px;
  padding: 2.5px;
  gap: 2px;
}
.portal-root.dark-mode .flight-tabs {
  background: #131c2e;
}
.flight-tabs button {
  border: none;
  background: transparent;
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 900;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}
.portal-root.dark-mode .flight-tabs button {
  color: #94a3b8;
}
.flight-tabs button.active {
  background: white;
  color: #0f172a;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
.portal-root.dark-mode .flight-tabs button.active {
  background: #1e293b;
  color: white;
}
.flight-table {
  width: 100%;
}
.flight-thead {
  display: grid;
  grid-template-columns: 1fr 2fr 1fr 1fr;
  padding: 8px 16px;
  background: #f8fafc;
  font-size: 10px;
  font-weight: 900;
  color: #64748b;
  text-transform: uppercase;
  border-bottom: 1.5px solid #e2e8f0;
}
.portal-root.dark-mode .flight-thead {
  background: #131c2e;
  border-bottom-color: #24334a;
}
.flight-tbody {
  max-height: 135px;
  overflow-y: auto;
}
.flight-row {
  display: grid;
  grid-template-columns: 1fr 2fr 1fr 1fr;
  padding: 10px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 11.5px;
  align-items: center;
}
.portal-root.dark-mode .flight-row {
  border-bottom-color: #24334a;
}
.f-code {
  font-weight: 900;
  color: #3b82f6;
}
.f-dest {
  font-weight: 800;
  color: #334155;
}
.portal-root.dark-mode .f-dest {
  color: #cbd5e1;
}
.f-time {
  color: #64748b;
  font-weight: 700;
}
.f-status {
  font-size: 9px;
  font-weight: 950;
  text-transform: uppercase;
  padding: 3px 6px;
  border-radius: 4px;
  text-align: center;
  align-self: center;
}
.status-boarding { background: #fee2e2; color: #ef4444; }
.status-delayed { background: #fef3c7; color: #d97706; }
.status-ontime { background: #d1fae5; color: #10b981; }
.status-landed { background: #dbeafe; color: #2563eb; }

/* ─── CURRENCY WIDGET ──────────────────────────────────── */
.currency-select {
  padding: 4px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 800;
  outline: none;
  background: white;
  cursor: pointer;
}
.portal-root.dark-mode .currency-select {
  background: #131c2e;
  border-color: #24334a;
  color: white;
}
.currency-value-bar {
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  font-weight: 800;
  margin-bottom: 10px;
}
.portal-root.dark-mode .currency-value-bar {
  background: #152033;
  border-color: #24334a;
}
.c-val-label {
  color: #64748b;
}
.c-val-price {
  color: #10b981;
  font-weight: 900;
}
.currency-chart-box {
  height: 60px;
  width: 100%;
  position: relative;
  margin-top: 4px;
  min-width: 0;
  overflow: hidden;
}

/* ─── NEWS AGGREGATOR (Baris 3) ────────────────────────── */
.news-aggregator-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.news-aggregator-layout {
  display: grid;
  grid-template-columns: 420px 1fr;
  gap: 24px;
  align-items: start;
}
.headline-container {
  display: flex;
}
.headline-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
  display: flex;
  flex-direction: column;
  height: 100%;
}
.portal-root.dark-mode .headline-card {
  background: #1e293b;
  border-color: #2e3a4e;
}
.headline-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.06);
  border-color: rgba(16, 185, 129, 0.3);
}
.hc-image-placeholder {
  height: 220px;
  background-size: cover;
  background-position: center;
  background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=800&auto=format&fit=crop&q=80');
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 16px;
}
.hc-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(15,23,42,0.1), rgba(15,23,42,0.85));
}
.hc-tag {
  position: relative;
  z-index: 1;
  background: #ef4444;
  color: white;
  font-size: 9px;
  font-weight: 900;
  padding: 3px 8px;
  border-radius: 6px;
  align-self: flex-start;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.hc-meta {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  font-size: 10px;
  font-weight: 800;
  color: rgba(255, 255, 255, 0.9);
}
.hc-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex-grow: 1;
}
.hc-content h3 {
  font-size: 17px;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.4;
  margin: 0;
}
.portal-root.dark-mode .hc-content h3 {
  color: white;
}
.hc-content p {
  font-size: 12.5px;
  color: #64748b;
  line-height: 1.5;
}
.portal-root.dark-mode .hc-content p {
  color: #cbd5e1;
}
.hc-action {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 900;
  color: #10b981;
  margin-top: auto;
  padding-top: 12px;
}
.hc-action svg {
  width: 14px;
  height: 14px;
  transition: transform 0.2s;
}
.headline-card:hover .hc-action svg {
  transform: translateX(4px);
}

.feeds-grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}
.feed-column {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.01);
  display: flex;
  flex-direction: column;
}
.portal-root.dark-mode .feed-column {
  background: #1e293b;
  border-color: #2e3a4e;
}
.feed-header {
  padding: 14px 18px;
  border-bottom: 1px solid #e2e8f0;
}
.portal-root.dark-mode .feed-header {
  border-bottom-color: #24334a;
}
.feed-header h4 {
  font-size: 13.5px;
  font-weight: 900;
  margin: 0;
  color: white;
}
.feed-header.bg-green { background: linear-gradient(135deg, #10b981, #059669); }
.feed-header.bg-violet { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.feed-header.bg-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }

.feed-list {
  display: flex;
  flex-direction: column;
  max-height: 480px;
  overflow-y: auto;
}
.feed-item {
  padding: 14px 18px;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.portal-root.dark-mode .feed-item {
  border-bottom-color: #24334a;
}
.feed-item:hover {
  background: rgba(16, 185, 129, 0.02);
}
.feed-item:last-child {
  border-bottom: none;
}
.fi-date {
  font-size: 9px;
  font-weight: 800;
  color: #94a3b8;
}
.feed-item h5 {
  font-size: 12.5px;
  font-weight: 800;
  line-height: 1.4;
  color: #334155;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.portal-root.dark-mode .feed-item h5 {
  color: #cbd5e1;
}
.feed-item:hover h5 {
  color: #10b981;
}
.fi-desc {
  font-size: 11px;
  color: #64748b;
  line-height: 1.4;
}
.fi-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 4px;
}
.fi-tag {
  font-size: 8.5px;
  font-weight: 850;
  background: #f1f5f9;
  color: #475569;
  padding: 2px 6px;
  border-radius: 4px;
}
.portal-root.dark-mode .fi-tag {
  background: #131c2e;
  color: #94a3b8;
}
.fi-tag.bg-green-light { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.fi-tag.bg-violet-light { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.fi-tag.bg-amber-light { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

/* ─── SUB-VIEWS PORTAL CONTAINER ──────────────────────── */
.portal-main-container {
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  padding: 32px 24px 80px;
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.subview-header-bar {
  display: flex;
  flex-direction: column;
  gap: 8px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 16px;
}
.portal-root.dark-mode .subview-header-bar {
  border-bottom-color: #1e293b;
}

.subview-header-bar h2 {
  font-size: 24px;
  font-weight: 900;
  color: #0f172a;
}
.portal-root.dark-mode .subview-header-bar h2 {
  color: white;
}

.btn-back-home {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: none;
  background: transparent;
  color: #10b981;
  font-size: 13.5px;
  font-weight: 800;
  cursor: pointer;
  align-self: flex-start;
  transition: all 0.2s;
}
.btn-back-home:hover {
  color: #059669;
}
.btn-back-home svg {
  width: 16px;
  height: 16px;
  stroke-width: 2.5;
}

/* ─── GOOGLE NEWS ETALASE BERITA ──────────────────────── */
.etalase-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.etalase-section h3 {
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
}
.portal-root.dark-mode .etalase-section h3 {
  color: white;
}
.section-info-desc {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 12px;
}

.etalase-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.etalase-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.01);
  display: flex;
  flex-direction: column;
  height: 280px;
  transition: all 0.25s;
}
.portal-root.dark-mode .etalase-card {
  background: #1e293b;
  border-color: #2e3a4e;
}
.etalase-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.05);
  border-color: rgba(16, 185, 129, 0.4);
}

.ec-header {
  padding: 16px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.portal-root.dark-mode .ec-header {
  background: #151f32;
  border-bottom-color: #2e3a4e;
}

.ec-media-title {
  cursor: pointer;
}
.ec-media-title h4 {
  font-size: 14px;
  font-weight: 800;
  color: #0f172a;
}
.portal-root.dark-mode .ec-media-title h4 {
  color: white;
}
.ec-media-title:hover h4 {
  color: #10b981;
}

.btn-follow {
  padding: 4px 8px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  background: white;
  font-size: 10px;
  font-weight: 800;
  color: #475569;
  cursor: pointer;
}
.portal-root.dark-mode .btn-follow {
  background: #1e293b;
  border-color: #334155;
  color: #cbd5e1;
}
.btn-follow:hover {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.ec-news-list {
  padding: 16px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
  overflow-y: auto;
}

.ec-news-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
  cursor: pointer;
}
.ec-news-item:hover h5 {
  color: #10b981;
}

.ec-news-date {
  font-size: 9.5px;
  font-weight: 700;
  color: #94a3b8;
}

.ec-news-item h5 {
  font-size: 12px;
  font-weight: 700;
  line-height: 1.4;
  color: #334155;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.portal-root.dark-mode .ec-news-item h5 {
  color: #cbd5e1;
}

.ec-empty {
  font-size: 11px;
  color: #94a3b8;
  text-align: center;
  padding: 20px 0;
}

.ec-footer {
  padding: 12px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  text-align: center;
  font-size: 11.5px;
  font-weight: 800;
  color: #10b981;
  border-left: none;
  border-right: none;
  border-bottom: none;
  cursor: pointer;
  width: 100%;
}
.portal-root.dark-mode .ec-footer {
  background: #151f32;
  border-top-color: #2e3a4e;
}
.ec-footer:hover {
  color: #059669;
  background: rgba(16, 185, 129, 0.04);
}

/* ─── REPORTS DISTRIBUTION PANEL ──────────────────────── */
.reports-distribution-panel {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.rep-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 22px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.01);
}
.portal-root.dark-mode .rep-card {
  background: #1e293b;
  border-color: #2e3a4e;
}
.rep-card h4 {
  font-size: 14.5px;
  font-weight: 800;
  margin: 0 0 16px;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.portal-root.dark-mode .rep-card h4 {
  color: white;
}

.rep-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.rep-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rep-item-lbl {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 700;
}
.rep-item-lbl span {
  color: #475569;
}
.portal-root.dark-mode .rep-item-lbl span {
  color: #cbd5e1;
}
.rep-item-lbl strong {
  color: #10b981;
}

.rep-bar-wrapper {
  height: 6px;
  background: #f1f5f9;
  border-radius: 4px;
  overflow: hidden;
  width: 100%;
}
.portal-root.dark-mode .rep-bar-wrapper {
  background: #131c2e;
}
.rep-bar {
  height: 100%;
  border-radius: 4px;
}
.rep-bar.bg-green { background: #10b981; }
.rep-bar.bg-blue { background: #3b82f6; }

/* ─── DATA TABLES STYLING ─────────────────────────────── */
.table-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 22px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.01);
}
.portal-root.dark-mode .table-card {
  background: #1e293b;
  border-color: #2e3a4e;
}

.table-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}
.table-card-header h3 {
  font-size: 16px;
  font-weight: 850;
}

.table-filters {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-search {
  padding: 8px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  font-size: 12.5px;
}
.portal-root.dark-mode .filter-search {
  background: #131c2e;
  border-color: #334155;
  color: white;
}

.filter-select {
  padding: 8px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  font-size: 12.5px;
  font-weight: 700;
  color: #475569;
  background: white;
}
.portal-root.dark-mode .filter-select {
  background: #131c2e;
  border-color: #334155;
  color: #cbd5e1;
}

.btn-reset-filters {
  padding: 8px 16px;
  background: #64748b;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  font-size: 12px;
  cursor: pointer;
}
.btn-reset-filters:hover {
  background: #475569;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.data-table th {
  background: #f8fafc;
  padding: 14px 16px;
  font-size: 12px;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  border-bottom: 1.5px solid #e2e8f0;
}
.portal-root.dark-mode .data-table th {
  background: #131c2e;
  border-bottom-color: #2e3a4e;
}

.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13.5px;
  color: #475569;
}
.portal-root.dark-mode .data-table td {
  border-bottom-color: #2e3a4e;
  color: #cbd5e1;
}
.data-table tr:hover td {
  background: rgba(16, 185, 129, 0.02);
}

.font-bold {
  font-weight: 700;
}
.text-slate {
  color: #1e293b !important;
}
.portal-root.dark-mode .text-slate {
  color: white !important;
}

.badge {
  font-size: 9.5px;
  font-weight: 900;
  padding: 3px 8px;
  border-radius: 6px;
  text-transform: uppercase;
}
.badge-media { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.badge-media-violet { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.badge-source { background: rgba(16, 185, 129, 0.1); color: #10b981; }

.table-tag {
  font-size: 9px;
  font-weight: 800;
  background: #f1f5f9;
  padding: 2.5px 6px;
  border-radius: 4px;
  color: #475569;
}
.portal-root.dark-mode .table-tag {
  background: #131c2e;
  color: #94a3b8;
}

.tags-row {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.badge-status {
  font-size: 9.5px;
  font-weight: 900;
  padding: 3px 8px;
  border-radius: 6px;
}

.btn-action {
  padding: 6px 12px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 700;
  font-size: 11px;
  cursor: pointer;
}
.btn-action:hover {
  background: #059669;
}

.btn-action-violet {
  padding: 6px 12px;
  background: #8b5cf6;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 700;
  font-size: 11px;
  cursor: pointer;
}
.btn-action-violet:hover {
  background: #7c3aed;
}

.table-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
  font-size: 12.5px;
  font-weight: 700;
  color: #64748b;
}
.table-pagination button {
  padding: 6px 14px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 800;
  cursor: pointer;
}
.portal-root.dark-mode .table-pagination button {
  background: #131c2e;
  border-color: #334155;
  color: white;
}
.table-pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ─── DOKUMENTASI PORTAL GRID ────────────────────────── */
.documentation-portal-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.doc-portal-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: all 0.25s;
}
.portal-root.dark-mode .doc-portal-card {
  background: #1e293b;
  border-color: #2e3a4e;
}
.doc-portal-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.03);
}

.dpc-img {
  height: 140px;
  background-size: cover;
  background-position: center;
  position: relative;
}

.dpc-loc-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(16, 185, 129, 0.85);
  color: white;
  font-size: 9px;
  font-weight: 900;
  padding: 2.5px 6px;
  border-radius: 4px;
}

.dpc-content {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex-grow: 1;
}

.dpc-date {
  font-size: 9.5px;
  font-weight: 700;
  color: #94a3b8;
}

.dpc-content h4 {
  font-size: 13.5px;
  font-weight: 850;
  color: #1e293b;
  line-height: 1.4;
  height: 38px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.portal-root.dark-mode .dpc-content h4 {
  color: white;
}

.dpc-room {
  font-size: 11px;
  color: #64748b;
}

.dpc-footer {
  margin-top: auto;
  border-top: 1px solid #f1f5f9;
  padding-top: 10px;
}
.portal-root.dark-mode .dpc-footer {
  border-top-color: #2e3a4e;
}

.btn-detail-link {
  font-size: 11px;
  font-weight: 800;
  color: #10b981;
}

.empty-docs-portal {
  grid-column: span 4;
  text-align: center;
  padding: 40px 0;
  color: #94a3b8;
}

/* ─── VIEW 4 & 6: DETAIL ARSIP INDEX CARD ──────────────── */
.detail-container {
  display: flex;
  justify-content: center;
}

.index-card {
  width: 100%;
  max-width: 850px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.02);
  padding: 32px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.portal-root.dark-mode .index-card {
  background: #1e293b;
  border-color: #2e3a4e;
}

.ic-banner {
  width: 100%;
  height: 380px;
  border-radius: 12px;
  overflow: hidden;
  background: #f1f5f9;
}
.portal-root.dark-mode .ic-banner {
  background: #131c2e;
}
.ic-banner img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Clipping 2-column layout */
.clipping-split-layout {
  display: flex;
  gap: 24px;
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  align-items: stretch;
  justify-content: stretch;
}
.clipping-split-layout .index-card {
  width: 30%;
  flex-shrink: 0;
  max-width: none;
}
.clipping-split-layout .ic-meta-grid {
  grid-template-columns: 1fr;
  gap: 12px;
}
.clipping-split-layout .loc-grid {
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}
.clipping-viewer-panel {
  width: 70%;
  flex-grow: 1;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.02);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: calc(100vh - 220px);
  min-height: 600px;
}
.portal-root.dark-mode .clipping-viewer-panel {
  background: #1e293b;
  border-color: #2e3a4e;
}
.clipping-pdf-iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.ic-media-logo-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px dashed #e2e8f0;
}
.portal-root.dark-mode .ic-media-logo-section {
  border-top-color: #2e3a4e;
}
.ic-media-logo-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
}
.ic-media-logo {
  height: 48px;
  max-width: 140px;
  object-fit: contain;
  border-radius: 6px;
  background: #fafafa;
  padding: 4px;
  border: 1px solid #e2e8f0;
}
.portal-root.dark-mode .ic-media-logo {
  border-color: #2e3a4e;
  background: #131c2e;
}
.ic-media-name-fallback {
  font-size: 14px;
  font-weight: 700;
  color: #7c3aed;
  background: #f5f3ff;
  padding: 4px 10px;
  border-radius: 6px;
}
.portal-root.dark-mode .ic-media-name-fallback {
  background: rgba(124, 58, 237, 0.15);
  color: #a78bfa;
}

@media (max-width: 1024px) {
  .clipping-split-layout {
    flex-direction: column;
  }
  .clipping-split-layout .index-card {
    width: 100%;
  }
  .clipping-viewer-panel {
    width: 100%;
    height: 600px;
    min-height: auto;
  }
}

.index-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1.5px solid #f1f5f9;
  padding-bottom: 16px;
}
.portal-root.dark-mode .index-card-header {
  border-bottom-color: #2e3a4e;
}

.ic-badge {
  font-size: 10px;
  font-weight: 900;
  padding: 4px 10px;
  background: rgba(139, 92, 246, 0.1);
  color: #8b5cf6;
  border-radius: 8px;
}
.ic-badge.bg-blue {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.ic-title {
  font-size: 20px;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.4;
  margin: 0;
}
.portal-root.dark-mode .ic-title {
  color: white;
}

.ic-meta-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  background: #f8fafc;
  padding: 16px;
  border-radius: 12px;
}
.portal-root.dark-mode .ic-meta-grid {
  background: #131c2e;
}

.ic-meta-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.ic-meta-lbl {
  font-size: 10px;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
}
.ic-meta-val {
  font-size: 13px;
  font-weight: 800;
  color: #475569;
}
.portal-root.dark-mode .ic-meta-val {
  color: #cbd5e1;
}

.ic-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.ic-section h4 {
  font-size: 13.5px;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.ic-section p {
  font-size: 14px;
  line-height: 1.6;
  color: #475569;
}
.portal-root.dark-mode .ic-section p {
  color: #cbd5e1;
}

/* Lokasi Fisik Box */
.location-info-box {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(59, 130, 246, 0.02));
  border: 1px solid rgba(139, 92, 246, 0.15);
  border-radius: 16px;
  padding: 20px;
}
.loc-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
.loc-item {
  display: flex;
  flex-direction: column;
}
.loc-lbl {
  font-size: 9.5px;
  font-weight: 800;
  color: #8b5cf6;
  text-transform: uppercase;
}
.loc-val {
  font-size: 13.5px;
  font-weight: 850;
  color: #0f172a;
}
.portal-root.dark-mode .loc-val {
  color: white;
}

.ic-actions {
  display: flex;
}
.btn-download-pdf {
  display: block;
  width: 100%;
  text-align: center;
  padding: 14px;
  background: #8b5cf6;
  color: white;
  text-decoration: none;
  border-radius: 12px;
  font-weight: 800;
  font-size: 14px;
  transition: background-color 0.2s;
}
.btn-download-pdf:hover {
  background: #7c3aed;
}

.drive-links-row {
  display: flex;
  gap: 12px;
}
.drive-btn {
  flex: 1;
  display: block;
  text-align: center;
  padding: 12px;
  background: #10b981;
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 800;
  font-size: 13px;
}
.drive-btn:hover {
  background: #059669;
}
.drive-btn.bg-red { background: #ef4444; }
.drive-btn.bg-red:hover { background: #dc2626; }

/* Iframe Previews */
.iframe-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.iframe-grid.single-col {
  grid-template-columns: 1fr;
}
@media (max-width: 768px) {
  .iframe-grid {
    grid-template-columns: 1fr;
  }
}

.iframe-box {
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.portal-root.dark-mode .iframe-box {
  background: #1e293b;
  border-color: #334155;
}

.iframe-label {
  padding: 12px 16px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  font-size: 14px;
  font-weight: 700;
  color: #3b82f6;
  display: flex;
  align-items: center;
  gap: 8px;
}
.portal-root.dark-mode .iframe-label {
  background: #0f172a;
  border-bottom-color: #334155;
}

.iframe-box iframe {
  width: 100%;
  height: 450px;
  border: none;
}

.iframe-error {
  height: 450px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-style: italic;
  background: #f1f5f9;
}
.portal-root.dark-mode .iframe-error {
  background: #0f172a;
  color: #64748b;
}

/* DAFTAR HADIR TABLE */
.attendance-table-wrapper {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}
.portal-root.dark-mode .attendance-table-wrapper {
  border-color: #2e3a4e;
}
.attendance-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.attendance-table th {
  background: #f8fafc;
  padding: 12px 16px;
  font-size: 11px;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
}
.portal-root.dark-mode .attendance-table th {
  background: #131c2e;
}
.attendance-table td {
  padding: 12px 16px;
  font-size: 13px;
  border-top: 1px solid #f1f5f9;
  color: #475569;
}
.portal-root.dark-mode .attendance-table td {
  border-top-color: #2e3a4e;
  color: #cbd5e1;
}
.badge-level {
  font-size: 9px;
  font-weight: 900;
  padding: 2.5px 6px;
  border-radius: 4px;
  color: white;
}
.badge-level.bg-green { background: #10b981; }
.badge-level.bg-blue { background: #3b82f6; }

/* ─── SUB-VIEW RSS Feeds Portal ────────────────────────── */
.rss-portal-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.rss-portal-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  overflow: hidden;
  display: flex;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(0,0,0,0.01);
}
.portal-root.dark-mode .rss-portal-card {
  background: #1e293b;
  border-color: #2e3a4e;
}
.rss-portal-card:hover {
  transform: translateY(-1px);
  border-color: #f59e0b;
}

.rpc-img {
  width: 180px;
  background-size: cover;
  background-position: center;
  flex-shrink: 0;
}

.rpc-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex-grow: 1;
}

.rpc-site {
  font-size: 10px;
  font-weight: 900;
  color: #f59e0b;
  text-transform: uppercase;
}

.rpc-content h4 {
  font-size: 15px;
  font-weight: 850;
  color: #1e293b;
  line-height: 1.4;
}
.portal-root.dark-mode .rpc-content h4 {
  color: white;
}

.rpc-content p {
  font-size: 12.5px;
  color: #64748b;
  line-height: 1.5;
}

.rpc-footer {
  display: flex;
  gap: 16px;
  font-size: 11px;
  color: #94a3b8;
  border-top: 1px solid #f1f5f9;
  padding-top: 8px;
  margin-top: 4px;
}
.portal-root.dark-mode .rpc-footer {
  border-top-color: #2e3a4e;
}

/* ─── PENDUKUNG PENCARIAN ─────────────────────────────── */
.search-results-root {
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  padding: 24px 24px 80px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.results-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 12px 18px;
}
.portal-root.dark-mode .results-header-bar {
  background: #1e293b;
  border-color: #2e3a4e;
}

.results-search-box {
  display: flex;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #f8fafc;
  overflow: hidden;
  max-width: 500px;
  width: 100%;
}
.portal-root.dark-mode .results-search-box {
  border-color: #334155;
  background: #131c2e;
}

.results-cat-dropdown {
  border: none;
  background: transparent;
  outline: none;
  font-size: 12px;
  font-weight: 700;
  padding: 0 10px;
  color: #475569;
  border-right: 1px solid #e2e8f0;
}
.results-cat-dropdown option {
  color: #1e293b;
  background-color: #ffffff;
}
.portal-root.dark-mode .results-cat-dropdown {
  color: #cbd5e1;
  border-right-color: #334155;
}
.portal-root.dark-mode .results-cat-dropdown option {
  color: #f1f5f9;
  background-color: #1e293b;
}

.results-search-input {
  flex-grow: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 13.5px;
  padding: 10px 14px;
}
.portal-root.dark-mode .results-search-input {
  color: white;
}

.results-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 24px;
  align-items: start;
}

.results-sidebar-filters {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.portal-root.dark-mode .results-sidebar-filters {
  background: #1e293b;
  border-color: #2e3a4e;
}
.results-sidebar-filters h4 {
  font-size: 13px;
  font-weight: 800;
  margin: 0 0 10px;
  color: #64748b;
  text-transform: uppercase;
}

.filter-btn {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px;
  border: none;
  background: transparent;
  color: #475569;
  font-size: 13px;
  font-weight: 700;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}
.portal-root.dark-mode .filter-btn {
  color: #cbd5e1;
}
.filter-btn:hover {
  background: rgba(16, 185, 129, 0.05);
}
.filter-btn.active {
  background: rgba(16, 185, 129, 0.12);
  color: #10b981;
}
.filter-btn .badge {
  background: #f1f5f9;
  color: #475569;
  font-size: 11px;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 6px;
}
.portal-root.dark-mode .filter-btn .badge {
  background: #131c2e;
  color: #94a3b8;
}
.filter-btn.active .badge {
  background: #10b981;
  color: white;
}

.results-list-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.results-summary {
  font-size: 14px;
  color: #64748b;
}
.results-summary strong, .results-summary .search-word {
  color: #10b981;
}

.results-loading-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: #64748b;
  font-size: 13.5px;
}
.portal-root.dark-mode .results-loading-card {
  background: #1e293b;
  border-color: #2e3a4e;
}

.results-empty-state {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 60px 40px;
  text-align: center;
  max-width: 500px;
  margin: 40px auto;
}
.portal-root.dark-mode .results-empty-state {
  background: #1e293b;
  border-color: #2e3a4e;
}
.empty-emoji {
  font-size: 40px;
  margin-bottom: 12px;
}
.results-empty-state h4 {
  font-size: 18px;
  font-weight: 800;
  margin-bottom: 6px;
}
.results-empty-state p {
  font-size: 13px;
  color: #64748b;
}

.results-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.result-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 18px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.portal-root.dark-mode .result-card {
  background: #1e293b;
  border-color: #2e3a4e;
}
.result-card:hover {
  transform: translateY(-1px);
  border-color: #10b981;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.05);
}

.rc-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.rc-type {
  font-size: 9.5px;
  font-weight: 950;
  text-transform: uppercase;
  padding: 2.5px 7px;
  border-radius: 4px;
  letter-spacing: 0.5px;
}
.type-news { background: rgba(16, 185, 129, 0.12); color: #047857; }
.type-clippings { background: rgba(139, 92, 246, 0.12); color: #6d28d9; }
.type-doc { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.type-accreditation { background: rgba(16, 185, 129, 0.12); color: #047857; }

.rc-date {
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
}

.result-card h4 {
  font-size: 14.5px;
  font-weight: 800;
  color: #1e293b;
  margin: 0;
  line-height: 1.4;
}
.portal-root.dark-mode .result-card h4 {
  color: white;
}

.result-card p {
  font-size: 12px;
  color: #64748b;
  line-height: 1.5;
}

.rc-meta {
  display: flex;
  gap: 16px;
  font-size: 11px;
  color: #94a3b8;
  border-top: 1px solid #f1f5f9;
  padding-top: 8px;
  margin-top: 4px;
}
.portal-root.dark-mode .rc-meta {
  border-top-color: #24334a;
}
.rc-meta strong {
  color: #64748b;
}

:deep(.highlight-match) {
  background: rgba(16, 185, 129, 0.2);
  color: #047857;
  padding: 1px 3px;
  border-radius: 3px;
  font-weight: bold;
}
.portal-root.dark-mode :deep(.highlight-match) {
  background: rgba(16, 185, 129, 0.35);
  color: #a7f3d0;
}

/* ─── FOOTER ───────────────────────────────────────────── */
.portal-footer {
  margin-top: auto;
  background: white;
  border-top: 1px solid #e2e8f0;
  padding: 24px;
}
.portal-root.dark-mode .portal-footer {
  background: #111827;
  border-top-color: #1e293b;
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12.5px;
  color: #64748b;
}
.portal-root.dark-mode .footer-container {
  color: #94a3b8;
}

.footer-links {
  display: flex;
  gap: 16px;
}
.footer-links a {
  color: #64748b;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}
.portal-root.dark-mode .footer-links a {
  color: #cbd5e1;
}
.footer-links a:hover {
  color: #10b981;
}

/* ─── GALLERY WIDGET STYLING ───────────────────────────── */
.gallery-filters {
  display: flex;
  background: #f1f5f9;
  padding: 3px;
  border-radius: 8px;
  gap: 2px;
}
.portal-root.dark-mode .gallery-filters {
  background: #131c2e;
}
.gallery-filters button {
  border: none;
  background: transparent;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}
.portal-root.dark-mode .gallery-filters button {
  color: #94a3b8;
}
.gallery-filters button.active {
  background: white;
  color: #1e293b;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.portal-root.dark-mode .gallery-filters button.active {
  background: #1e293b;
  color: white;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}

.gallery-card {
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all 0.2s;
  cursor: pointer;
}
.portal-root.dark-mode .gallery-card {
  background: #152033;
  border-color: #24334a;
}
.gallery-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.04);
}

.gc-img {
  height: 120px;
  background-size: cover;
  background-position: center;
  position: relative;
  overflow: hidden;
}
.gc-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 10px;
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
}
.gallery-card:hover .gc-overlay {
  opacity: 1;
}

.gc-loc {
  font-size: 9px;
  font-weight: 800;
  color: white;
  background: rgba(0,0,0,0.4);
  padding: 2px 6px;
  border-radius: 4px;
  align-self: flex-start;
}

.gc-actions {
  display: flex;
  gap: 6px;
  width: 100%;
}

.gc-btn {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 0;
  background: #10b981;
  color: white;
  text-decoration: none;
  font-size: 10px;
  font-weight: 800;
  border-radius: 6px;
  transition: background-color 0.2s;
}
.gc-btn:hover {
  background: #059669;
}
.gc-btn.bg-red {
  background: #ef4444;
}
.gc-btn.bg-red:hover {
  background: #dc2626;
}

.gc-desc {
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.gc-desc h5 {
  font-size: 11.5px;
  font-weight: 800;
  margin: 0;
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 31px;
}
.gc-date {
  font-size: 9.5px;
  font-weight: 700;
  color: #94a3b8;
}

/* ─── RESPONSIVE MEDIA QUERIES ─────────────────────────── */
@media (max-width: 1200px) {
  .portal-main-grid {
    grid-template-columns: 1fr 1fr;
  }
  .full-width {
    grid-column: span 2;
  }
  .khatib-widget {
    grid-column: span 2;
  }
  .news-aggregator-layout {
    grid-template-columns: 1fr;
  }
  .etalase-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 900px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .charts-dashboard-container {
    grid-template-columns: 1fr;
  }
  .charts-sidebar-tabs {
    flex-direction: row;
    border-right: none;
    border-bottom: 1px solid #e2e8f0;
    overflow-x: auto;
    padding: 10px;
  }
  .portal-root.dark-mode .charts-sidebar-tabs {
    border-bottom-color: #2e3a4e;
  }
  .chart-tab-btn {
    white-space: nowrap;
    width: auto;
    padding: 8px 12px;
  }
  .chart-tab-btn.active {
    border-left: none;
    border-bottom: 3px solid #10b981;
    padding-left: 12px;
    padding-bottom: 5px;
  }
  .results-layout {
    grid-template-columns: 1fr;
  }
  .results-sidebar-filters {
    flex-direction: row;
    overflow-x: auto;
    padding: 10px;
  }
  .filter-btn {
    white-space: nowrap;
    width: auto;
  }
  .reports-distribution-panel {
    grid-template-columns: 1fr;
  }
  .gallery-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .documentation-portal-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .loc-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .ic-meta-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .nav-container {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
  }
  .live-clock {
    display: none !important;
  }
  .hero-search-section {
    padding: 20px 16px 50px;
    height: calc(100vh - 68px);
    min-height: 540px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-sizing: border-box;
  }
  .scroll-down-indicator {
    bottom: 12px;
  }
  .scroll-text {
    font-size: 9.5px;
    letter-spacing: 1px;
  }
  .hero-title {
    font-size: 26px;
  }
  .search-engine-box {
    flex-direction: column;
    border-radius: 16px;
    padding: 12px;
  }
  .search-category-select {
    border-right: none;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 6px;
    padding-right: 0;
  }
  .portal-root.dark-mode .search-category-select {
    border-bottom-color: #334155;
  }
  .cat-dropdown {
    width: 100%;
    padding: 8px;
  }
  .btn-search-trigger {
    width: 100%;
    justify-content: center;
  }
  .portal-main-grid {
    grid-template-columns: 1fr;
    padding: 0 16px 40px;
  }
  .full-width {
    grid-column: span 1;
  }
  .khatib-widget {
    grid-column: span 1;
  }
  .kpi-grid {
    grid-template-columns: 1fr;
  }
  .feeds-grid-container {
    grid-template-columns: 1fr;
  }
  .footer-container {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }
  .etalase-grid {
    grid-template-columns: 1fr;
  }
  .gallery-widget {
    grid-column: span 1 !important;
  }
  .gallery-grid {
    grid-template-columns: 1fr;
  }
  .documentation-portal-grid {
    grid-template-columns: 1fr;
  }
  .loc-grid {
    grid-template-columns: 1fr;
  }
  .ic-meta-grid {
    grid-template-columns: 1fr;
  }
  
  /* Responsive widgets overrides */
  .other-mosque-item {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 6px;
  }
  .omi-khatib {
    align-items: flex-start !important;
    text-align: left !important;
  }
  
  /* Flight table responsive columns */
  .flight-thead span:first-child,
  .flight-row .f-code {
    display: none !important;
  }
  .flight-thead,
  .flight-row {
    grid-template-columns: 2fr 1fr 1fr !important;
  }
}

/* Filter Card Styles */
.filter-card {
  padding: 24px;
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
  margin-bottom: 24px;
}

.portal-root.dark-mode .filter-card {
  background: #1e293b;
  border-color: #334155;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.filter-grid-layout {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
}

.filters-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  align-items: flex-end;
}

@media (min-width: 1024px) {
  .filters-row {
    grid-template-columns: 1.8fr 1.1fr 1.1fr 1fr;
  }
}

.search-row {
  border-top: 1px solid #f1f5f9;
  padding-top: 16px;
}

.portal-root.dark-mode .search-row {
  border-top-color: #334155;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-label {
  font-size: 11px;
  font-weight: 800;
  color: #0f172a;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.portal-root.dark-mode .filter-label {
  color: #cbd5e1;
}

.date-inputs-wrapper {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 4px 12px;
  gap: 8px;
  height: 48px;
  transition: all 0.2s;
}

.portal-root.dark-mode .date-inputs-wrapper {
  background: #152033;
  border-color: #24334a;
}

.date-inputs-wrapper:focus-within {
  border-color: #f59e0b;
  background: white;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
}

.portal-root.dark-mode .date-inputs-wrapper:focus-within {
  background: #1e293b;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.2);
}

.date-input-field {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
  position: relative;
}

.date-field-label {
  font-size: 8px;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
  margin-bottom: 1px;
  letter-spacing: 0.5px;
}

.date-input-native {
  border: none !important;
  background: transparent !important;
  padding: 0 !important;
  margin: 0 !important;
  height: 20px !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  color: #0f172a !important;
  outline: none !important;
  cursor: pointer;
  width: 100%;
}

.portal-root.dark-mode .date-input-native {
  color: #f1f5f9 !important;
}

.date-input-native::-webkit-calendar-picker-indicator {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: auto;
  height: auto;
  color: transparent;
  background: transparent;
  cursor: pointer;
}

.date-range-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #cbd5e1;
  flex-shrink: 0;
}

.portal-root.dark-mode .date-range-arrow {
  color: #475569;
}

.date-range-arrow svg {
  width: 14px;
  height: 14px;
}

.clipping-select {
  height: 48px;
  padding: 0 36px 0 16px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-family: inherit;
  font-size: 13.5px;
  font-weight: 600;
  color: #475569;
  outline: none;
  cursor: pointer;
  transition: all 0.2s;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 14px center;
  background-size: 14px;
}

.portal-root.dark-mode .clipping-select {
  background-color: #152033;
  border-color: #24334a;
  color: #cbd5e1;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 14px center;
  background-size: 14px;
}

.clipping-select:focus {
  border-color: #f59e0b;
  background-color: white;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
}

.portal-root.dark-mode .clipping-select:focus {
  background-color: #1e293b;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.2);
}

.search-actions-row {
  display: flex;
  gap: 12px;
}

.search-input-container {
  position: relative;
  flex: 1;
  min-width: 0;
}

.search-input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #94a3b8;
}

.search-input-field {
  width: 100%;
  height: 48px;
  padding: 0 16px 0 38px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-family: inherit;
  font-size: 13.5px;
  font-weight: 600;
  color: #1e293b;
  outline: none;
  transition: all 0.2s;
}

.portal-root.dark-mode .search-input-field {
  background: #152033;
  border-color: #24334a;
  color: #f1f5f9;
}

.search-input-field:focus {
  border-color: #f59e0b;
  background: white;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
}

.portal-root.dark-mode .search-input-field:focus {
  background: #1e293b;
  box-shadow: 0 0 0 3px rgba(245,158,11,0.2);
}

.btn-filter-reset {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 0 20px;
  height: 48px;
  border-radius: 12px;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  border: 1.5px solid #e2e8f0;
  font-family: inherit;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.portal-root.dark-mode .btn-filter-reset {
  background: #1e293b;
  border-color: #24334a;
  color: #cbd5e1;
}

.btn-filter-reset:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.portal-root.dark-mode .btn-filter-reset:hover {
  background: #24334a;
  color: white;
}

.btn-filter-reset svg {
  width: 16px;
  height: 16px;
}

.nav-logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.brand-logo.has-img {
  background: transparent;
  box-shadow: none;
}

/* Modal reports styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.modal-card {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
}
.modal-card.modal-large {
  max-width: 800px;
}
.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}
.modal-close {
  background: transparent;
  border: none;
  font-size: 24px;
  color: #64748b;
  cursor: pointer;
}
.modal-body {
  padding: 24px;
  overflow-y: auto;
  max-height: 70vh;
}
.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  background: #f8fafc;
}
.btn-cancel {
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: white;
  color: #475569;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-cancel:hover {
  background: #f8fafc;
  border-color: #94a3b8;
}

.table-jenjang-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
}
.jenjang-s23 {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fee2e2;
}
.jenjang-profesi {
  background-color: #f0fdf4;
  color: #16a34a;
  border: 1px solid #dcfce7;
}
.jenjang-undergrad {
  background-color: #eff6ff;
  color: #2563eb;
  border: 1px solid #dbeafe;
}

.progress-bar {
  transition: width 0.4s ease;
}
.progress-bar.bg-green {
  background-color: #10b981;
}
.progress-bar.bg-blue {
  background-color: #3b82f6;
}
.progress-bar.bg-violet {
  background-color: #8b5cf6;
}
.progress-bar.bg-red {
  background-color: #ef4444;
}

.path-blue { background-color: #eff6ff; color: #1d4ed8; }
.path-green { background-color: #f0fdf4; color: #15803d; }
.path-yellow { background-color: #fef9c3; color: #a16207; }
.path-gray { background-color: #f1f5f9; color: #475569; }

.badge-unggul {
  background-color: #ecfdf5 !important;
  color: #047857 !important;
  border: 1px solid #a7f3d0;
}
.badge-baik-sekali {
  background-color: #eff6ff !important;
  color: #1d4ed8 !important;
  border: 1px solid #bfdbfe;
}
.badge-baik {
  background-color: #fffbeb !important;
  color: #d97706 !important;
  border: 1px solid #fde68a;
}

.portal-root.dark-mode .badge-unggul {
  background-color: rgba(4, 120, 87, 0.15) !important;
  color: #34d399 !important;
  border-color: rgba(52, 211, 153, 0.2);
}
.portal-root.dark-mode .badge-baik-sekali {
  background-color: rgba(29, 78, 216, 0.15) !important;
  color: #60a5fa !important;
  border-color: rgba(96, 165, 250, 0.2);
}
.portal-root.dark-mode .badge-baik {
  background-color: rgba(217, 119, 6, 0.15) !important;
  color: #fbbf24 !important;
  border-color: rgba(251, 191, 36, 0.2);
}

.countdown-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  background: #eff6ff;
  color: #1d4ed8;
  display: inline-block;
  white-space: nowrap;
}
.countdown-badge.warning {
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fde68a;
}
.countdown-badge.critical {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fca5a5;
}

.portal-root.dark-mode .countdown-badge {
  background: rgba(29, 78, 216, 0.15);
  color: #60a5fa;
}
.portal-root.dark-mode .countdown-badge.warning {
  background: rgba(217, 119, 6, 0.15);
  color: #fbbf24;
  border-color: rgba(251, 191, 36, 0.2);
}
.portal-root.dark-mode .countdown-badge.critical {
  background: rgba(220, 38, 38, 0.15);
  color: #f87171;
  border-color: rgba(248, 113, 113, 0.2);
}

.btn-action-outline {
  padding: 5px 11px;
  background: transparent;
  color: #8b5cf6;
  border: 1.5px solid #8b5cf6;
  border-radius: 6px;
  font-weight: 700;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-action-outline:hover {
  background: rgba(139, 92, 246, 0.08);
}
.portal-root.dark-mode .btn-action-outline {
  color: #a78bfa;
  border-color: #a78bfa;
}
.portal-root.dark-mode .btn-action-outline:hover {
  background: rgba(167, 139, 250, 0.15);
}

/* ─── DUAL SEARCH BUTTONS STYLING ─── */
.btn-google-search {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #ffffff;
  color: #3c4043;
  border: 1px solid #dadce0;
  border-radius: 16px;
  font-weight: 700;
  font-size: 14.5px;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.btn-google-search:hover {
  background: #f8f9fa;
  border-color: #c6c9cc;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  transform: translateY(-1px);
}

.google-g-logo {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.portal-root.dark-mode .btn-google-search {
  background: #1e293b;
  color: #cbd5e1;
  border-color: #334155;
  box-shadow: none;
}

.portal-root.dark-mode .btn-google-search:hover {
  background: #2e3b4e;
  border-color: #475569;
}

/* Small Google button on search results page */
.results-search-box .res-google-btn {
  background: transparent;
  border: none;
  border-left: 1px solid #e2e8f0;
  border-radius: 0;
  padding: 0 16px;
  height: auto;
  align-self: stretch;
  display: inline-flex;
  align-items: center;
  box-shadow: none;
}

.portal-root.dark-mode .results-search-box .res-google-btn {
  background: transparent;
  border-left-color: #334155;
  color: #cbd5e1;
}

.results-search-box .res-google-btn:hover {
  background: rgba(0, 0, 0, 0.03);
  transform: none;
  box-shadow: none;
}

.portal-root.dark-mode .results-search-box .res-google-btn:hover {
  background: rgba(255, 255, 255, 0.05);
}

.results-search-box .btn-search-trigger {
  border-radius: 0;
  padding: 0 20px;
  height: auto;
  align-self: stretch;
  display: inline-flex;
  align-items: center;
  font-size: 13.5px;
}

/* Results section headers styling */
.internal-results-title, .cse-results-title {
  font-size: 15px;
  font-weight: 800;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 10px 0 20px;
  padding-bottom: 8px;
  border-bottom: 2px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.portal-root.dark-mode .internal-results-title,
.portal-root.dark-mode .cse-results-title {
  color: #94a3b8;
  border-bottom-color: #2e3a4e;
}

/* Recommendation Bar */
.cse-recommendation-bar {
  display: flex;
  flex-direction: column;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 24px;
  gap: 16px;
}
.portal-root.dark-mode .cse-recommendation-bar {
  background: #1e293b;
  border-color: #334155;
}

.cse-rec-info {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13.5px;
  color: #475569;
  line-height: 1.5;
}
.portal-root.dark-mode .cse-rec-info {
  color: #cbd5e1;
}
.cse-rec-info strong {
  color: #10b981;
}

.cse-rec-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.cse-rec-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 12.5px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
}
.cse-rec-btn.primary {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  box-shadow: 0 4px 12px rgba(16,185,129,0.2);
}
.cse-rec-btn.primary:hover {
  opacity: 0.95;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(16,185,129,0.28);
}
.cse-rec-btn.secondary {
  background: #ffffff;
  color: #475569;
  border: 1px solid #e2e8f0;
}
.portal-root.dark-mode .cse-rec-btn.secondary {
  background: #0f172a;
  color: #94a3b8;
  border-color: #334155;
}
.cse-rec-btn.secondary:hover {
  border-color: #10b981;
  color: #10b981;
  background: rgba(16,185,129,0.05);
}

.cse-widget-wrapper {
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  background: transparent;
}

/* Custom override untuk container google cse agar terlihat rapi dan tidak merusak layout */
.gsc-control-cse {
  background-color: transparent !important;
  border: none !important;
  padding: 0 !important;
  font-family: inherit !important;
}
.gsc-webResult.gsc-result {
  background-color: transparent !important;
  border: none !important;
  border-bottom: 1px solid #f1f5f9 !important;
  padding: 16px 0 !important;
}
.portal-root.dark-mode .gsc-webResult.gsc-result {
  border-bottom-color: #334155 !important;
}

@media (max-width: 600px) {
  .cse-rec-actions {
    flex-direction: column;
    align-items: stretch;
  }
  .cse-rec-btn {
    justify-content: center;
  }
}

.google-cse-results-container {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 24px;
  margin-top: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
}
.portal-root.dark-mode .google-cse-results-container {
  background: #1e293b;
  border-color: #2e3a4e;
  box-shadow: none;
}

/* Header row: title + open in new tab */
.cse-results-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 2px solid #e2e8f0;
}
.portal-root.dark-mode .cse-results-header { border-bottom-color: #2e3a4e; }
/* CSE badge */
.cse-badge {
  font-size: 11px;
  font-weight: 600;
  color: #10b981;
  background: rgba(16,185,129,0.12);
  border: 1px solid rgba(16,185,129,0.3);
  border-radius: 20px;
  padding: 3px 10px;
  white-space: nowrap;
}

/* Skeleton Loading */
.cse-loading { display: flex; flex-direction: column; gap: 16px; }
.cse-skeleton {
  padding: 16px 18px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  display: flex; flex-direction: column; gap: 9px;
}
.portal-root.dark-mode .cse-skeleton { background: #131c2e; border-color: #24334a; }
.cse-sk-source, .cse-sk-title, .cse-sk-body { border-radius: 5px; animation: cse-shimmer 1.4s ease infinite; }
.cse-sk-source { height: 10px; width: 30%; background: linear-gradient(90deg, #d1fae5 25%, #ecfdf5 50%, #d1fae5 75%); background-size: 400% 100%; }
.cse-sk-title  { height: 15px; width: 65%; background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 400% 100%; }
.cse-sk-body   { height: 11px; width: 90%; background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 400% 100%; }
.portal-root.dark-mode .cse-sk-source { background: linear-gradient(90deg, #064e3b 25%, #065f46 50%, #064e3b 75%); background-size: 400% 100%; }
.portal-root.dark-mode .cse-sk-title,
.portal-root.dark-mode .cse-sk-body  { background: linear-gradient(90deg, #1e293b 25%, #2e3a4e 50%, #1e293b 75%); background-size: 400% 100%; }
@keyframes cse-shimmer { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }

/* Redirect Panel */
.cse-redirect-panel {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 32px 24px;
  gap: 20px;
}
.cse-redirect-icon {
  width: 64px; height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(5,150,105,0.05));
  border: 2px solid rgba(16,185,129,0.3);
  display: flex; align-items: center; justify-content: center;
  color: #10b981;
}
.cse-redirect-icon svg { width: 28px; height: 28px; }
.cse-redirect-text h5 {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px;
  line-height: 1.4;
}
.portal-root.dark-mode .cse-redirect-text h5 { color: #e2e8f0; }
.cse-redirect-text h5 em { color: #10b981; font-style: normal; }
.cse-redirect-text p { font-size: 13px; color: #64748b; margin: 0; line-height: 1.6; max-width: 480px; }
.portal-root.dark-mode .cse-redirect-text p { color: #94a3b8; }

.cse-redirect-actions {
  display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;
}
.cse-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 18px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
}
.cse-action-btn.primary {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  box-shadow: 0 4px 12px rgba(16,185,129,0.25);
}
.cse-action-btn.primary:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(16,185,129,0.3); }
.cse-action-btn.secondary {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}
.portal-root.dark-mode .cse-action-btn.secondary { background: #1e293b; color: #94a3b8; border-color: #334155; }
.cse-action-btn.secondary:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.06); }

/* Internal results teaser */
.cse-internal-teaser {
  width: 100%;
  max-width: 520px;
  background: rgba(16,185,129,0.06);
  border: 1px solid rgba(16,185,129,0.2);
  border-radius: 12px;
  padding: 14px 18px;
  text-align: left;
}
.cse-teaser-label {
  font-size: 12px;
  font-weight: 700;
  color: #10b981;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  margin-bottom: 6px;
}
.cse-internal-teaser p { font-size: 13px; color: #475569; margin: 0; }
.portal-root.dark-mode .cse-internal-teaser p { color: #94a3b8; }

/* Voice Search (Mic Button) Styling */
.btn-voice-search {
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  border-radius: 50%;
  margin-right: 4px;
}
.btn-voice-search:hover {
  color: #10b981;
  background: rgba(16, 185, 129, 0.08);
  transform: scale(1.08);
}
.portal-root.dark-mode .btn-voice-search {
  color: #94a3b8;
}
.portal-root.dark-mode .btn-voice-search:hover {
  color: #34d399;
  background: rgba(52, 211, 153, 0.08);
}

/* Specific styling for results voice search button */
.results-search-box .res-voice-btn {
  margin-right: 0;
  padding: 0 12px;
  border-radius: 0;
  height: auto;
  align-self: stretch;
  border-left: 1px solid transparent;
}
.results-search-box .res-voice-btn:hover {
  transform: none;
  background: rgba(16, 185, 129, 0.05);
}

/* Voice Search Modal Overlay */
.voice-search-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fade-in 0.25s ease-out;
}
.voice-search-modal {
  background: #ffffff;
  padding: 40px 30px;
  border-radius: 24px;
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  max-width: 360px;
  width: 90%;
  animation: scale-up 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.portal-root.dark-mode .voice-search-modal {
  background: #1e293b;
  border: 1px solid #334155;
}

.voice-search-modal h4 {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px;
}
.portal-root.dark-mode .voice-search-modal h4 {
  color: #f1f5f9;
}
.voice-search-modal p {
  font-size: 13.5px;
  color: #64748b;
  margin: 0 0 28px;
}
.portal-root.dark-mode .voice-search-modal p {
  color: #94a3b8;
}

/* Sound Wave Animation */
.voice-wave-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  height: 60px;
  margin-bottom: 24px;
}
.voice-wave-bar {
  width: 5px;
  height: 12px;
  background: linear-gradient(to top, #10b981, #34d399);
  border-radius: 3px;
  animation: voice-bounce 1.0s ease-in-out infinite alternate;
}
.voice-wave-bar:nth-child(1) { animation-delay: 0.1s; }
.voice-wave-bar:nth-child(2) { animation-delay: 0.25s; }
.voice-wave-bar:nth-child(3) { animation-delay: 0.4s; }
.voice-wave-bar:nth-child(4) { animation-delay: 0.55s; }
.voice-wave-bar:nth-child(5) { animation-delay: 0.7s; }

@keyframes voice-bounce {
  0% { height: 10px; transform: scaleY(1); }
  100% { height: 48px; transform: scaleY(1.2); }
}

.btn-voice-close {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 10px 24px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-voice-close:hover {
  background: #e2e8f0;
  color: #1e293b;
}
.portal-root.dark-mode .btn-voice-close {
  background: #334155;
  color: #cbd5e1;
}
.portal-root.dark-mode .btn-voice-close:hover {
  background: #475569;
  color: #ffffff;
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes scale-up {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

@media (max-width: 600px) {
  .btn-google-search .btn-google-text { display: none; }
  .btn-google-search { padding: 12px 14px; }
  .cse-redirect-actions { flex-direction: column; align-items: stretch; }
  .cse-action-btn { justify-content: center; }
}
</style>

<style>
/* Global overrides - keep body scroll */
body.gsc-overflow-hidden { overflow: auto !important; }
</style>
