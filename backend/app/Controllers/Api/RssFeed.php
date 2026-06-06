<?php

namespace App\Controllers\Api;

use App\Models\RssSourceModel;
use App\Models\RssItemModel;
use App\Models\SettingModel;
use Exception;

/**
 * API Controller — Manajemen RSS Aggregator
 */
class RssFeed extends BaseApiController
{
    /**
     * GET /api/admin/rss/sources
     * Mengambil daftar semua sumber feed RSS beserta jumlah artikel ter-sinkronisasi
     */
    public function getSources()
    {
        $sourceModel = new RssSourceModel();
        $itemModel = new RssItemModel();
        
        $sources = $sourceModel->orderBy('site_name', 'ASC')->findAll();
        
        foreach ($sources as &$src) {
            $src['item_count'] = $itemModel->where('source_id', $src['id'])->countAllResults();
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $sources
        ]);
    }

    /**
     * POST /api/admin/rss/sources
     * Menambahkan sumber RSS baru
     */
    public function createSource()
    {
        $input = $this->request->getJSON(true);
        $siteName = trim($input['site_name'] ?? '');
        $siteUrl  = trim($input['site_url'] ?? '');
        $feedUrl  = trim($input['feed_url'] ?? '');
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($siteName) || empty($siteUrl) || empty($feedUrl)) {
            return $this->respondWithError('Nama situs, URL situs, dan URL feed RSS wajib diisi.', 400);
        }

        $sourceModel = new RssSourceModel();

        // Cek duplikat feed_url
        if ($sourceModel->where('feed_url', $feedUrl)->first()) {
            return $this->respondWithError('URL Feed RSS ini sudah terdaftar.', 409);
        }

        $data = [
            'site_name' => $siteName,
            'site_url'  => $siteUrl,
            'feed_url'  => $feedUrl,
            'is_active' => $isActive
        ];

        $id = $sourceModel->insert($data, true);
        
        $this->logActivity("Menambahkan sumber RSS: {$siteName}", 'RSS Aggregator', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Sumber RSS berhasil ditambahkan.',
            'data'    => array_merge(['id' => $id], $data)
        ], 201);
    }

    /**
     * PUT /api/admin/rss/sources/(:num)
     * Memperbarui sumber RSS
     */
    public function updateSource($id)
    {
        $sourceModel = new RssSourceModel();
        $existing = $sourceModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('Sumber RSS tidak ditemukan.', 404);
        }

        $input = $this->request->getJSON(true);
        $siteName = trim($input['site_name'] ?? '');
        $siteUrl  = trim($input['site_url'] ?? '');
        $feedUrl  = trim($input['feed_url'] ?? '');
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($siteName) || empty($siteUrl) || empty($feedUrl)) {
            return $this->respondWithError('Nama situs, URL situs, dan URL feed RSS wajib diisi.', 400);
        }

        // Cek duplikat feed_url pada baris lain
        $duplicate = $sourceModel->where('feed_url', $feedUrl)->where('id !=', (int)$id)->first();
        if ($duplicate) {
            return $this->respondWithError('URL Feed RSS ini sudah digunakan oleh sumber lain.', 409);
        }

        $data = [
            'site_name' => $siteName,
            'site_url'  => $siteUrl,
            'feed_url'  => $feedUrl,
            'is_active' => $isActive
        ];

        $sourceModel->update((int)$id, $data);

        $this->logActivity("Mengubah sumber RSS: {$existing['site_name']} → {$siteName}", 'RSS Aggregator', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Sumber RSS berhasil diperbarui.'
        ]);
    }

    /**
     * DELETE /api/admin/rss/sources/(:num)
     * Menghapus sumber RSS beserta artikelnya (CASCADE)
     */
    public function deleteSource($id)
    {
        $sourceModel = new RssSourceModel();
        $existing = $sourceModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('Sumber RSS tidak ditemukan.', 404);
        }

        $sourceModel->delete((int)$id);

        $this->logActivity("Menghapus sumber RSS: {$existing['site_name']}", 'RSS Aggregator', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Sumber RSS berhasil dihapus.'
        ]);
    }

    /**
     * GET /api/admin/rss/settings
     * Mengambil konfigurasi auto-cleanup dan status cron job
     */
    public function getSettings()
    {
        $settingModel = new SettingModel();

        $cleanupDays    = (int)$settingModel->getVal('rss_cleanup_days', '14');
        $cleanupEnabled = (int)$settingModel->getVal('rss_cleanup_enabled', '1');
        $lastCronRun    = $settingModel->getVal('rss_last_cron_run');
        $cronStatusRaw  = $settingModel->getVal('rss_last_cron_status');
        
        $cronStatus = $cronStatusRaw ? json_decode($cronStatusRaw, true) : null;

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'rss_cleanup_days'     => $cleanupDays,
                'rss_cleanup_enabled'    => $cleanupEnabled,
                'rss_last_cron_run'      => $lastCronRun,
                'rss_last_cron_status'   => $cronStatus
            ]
        ]);
    }

    /**
     * POST /api/admin/rss/settings
     * Menyimpan konfigurasi pembersihan otomatis
     */
    public function updateSettings()
    {
        $input = $this->request->getJSON(true);
        $cleanupDays    = isset($input['rss_cleanup_days']) ? (int)$input['rss_cleanup_days'] : 14;
        $cleanupEnabled = isset($input['rss_cleanup_enabled']) ? (int)$input['rss_cleanup_enabled'] : 1;

        if ($cleanupDays < 1) {
            return $this->respondWithError('Masa simpan artikel minimal 1 hari.', 400);
        }

        $settingModel = new SettingModel();
        $settingModel->setVal('rss_cleanup_days', $cleanupDays, 'rss');
        $settingModel->setVal('rss_cleanup_enabled', $cleanupEnabled, 'rss');

        $this->logActivity("Mengubah pengaturan RSS (Cleanup: {$cleanupDays} hari, Status: {$cleanupEnabled})", 'RSS Aggregator');

        return $this->respond([
            'status'  => 'success',
            'message' => 'Pengaturan RSS berhasil disimpan.'
        ]);
    }

    /**
     * POST /api/admin/rss/sync
     * Memicu sinkronisasi manual instan dari Dashboard
     */
    public function manualSync()
    {
        $sourceModel = new RssSourceModel();
        
        // Panggil sinkronisasi internal (parameter false agar tidak menulis stdout CLI)
        $result = $sourceModel->syncAll(false);
        
        $this->logActivity("Menjalankan sinkronisasi manual RSS", 'RSS Aggregator');

        return $this->respond([
            'status'  => 'success',
            'message' => 'Sinkronisasi feed RSS berhasil dijalankan.',
            'data'    => $result
        ]);
    }

    /**
     * GET /api/admin/rss/items
     * Mengambil daftar artikel feed gabungan untuk dipreview di frontend reader
     */
    public function getItems()
    {
        $db = \Config\Database::connect();
        
        $search   = $this->request->getVar('search') ?? '';
        $sourceId = $this->request->getVar('source_id') ?? '';
        
        $page     = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $limit    = $this->request->getVar('limit') ? (int)$this->request->getVar('limit') : 12;
        $offset   = ($page - 1) * $limit;

        $builder = $db->table('rss_items i');
        $builder->select('i.*, s.site_name, s.site_url');
        $builder->join('rss_sources s', 's.id = i.source_id');

        // Filter status aktif dari sumbernya (hanya tampilkan artikel dari web yang berstatus aktif)
        $builder->where('s.is_active', 1);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('i.title', $search)
                ->orLike('i.description', $search)
                ->groupEnd();
        }

        if (!empty($sourceId)) {
            $builder->where('i.source_id', (int)$sourceId);
        }

        // Kloning builder untuk menghitung total artikel
        $countBuilder = clone $builder;
        $totalItems = $countBuilder->countAllResults();

        // Ambil data
        $results = $builder->orderBy('i.pub_date', 'DESC')
                           ->orderBy('i.id', 'DESC')
                           ->limit($limit, $offset)
                           ->get()
                           ->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'items'       => $results,
                'total_items' => $totalItems,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalItems / $limit)
            ]
        ]);
    }
}
