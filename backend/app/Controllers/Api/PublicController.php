<?php

namespace App\Controllers\Api;

use Exception;

class PublicController extends BaseApiController
{
    /**
     * Pencarian Global Modular
     * GET /api/public/search?q=keyword&type=all
     */
    public function search()
    {
        $db = \Config\Database::connect();
        $q = $this->request->getVar('q') ?? '';
        $type = $this->request->getVar('type') ?? 'all';

        $qClean = trim($q);
        if (empty($qClean)) {
            return $this->respond([
                'status' => 'success',
                'data'   => [
                    'news'          => [],
                    'clippings'     => [],
                    'documentation' => [],
                    'accreditation' => [],
                    'total_results' => 0
                ]
            ]);
        }

        $results = [
            'news'          => [],
            'clippings'     => [],
            'documentation' => [],
            'accreditation' => [],
            'total_results' => 0
        ];

        $terms = array_filter(explode(' ', $qClean));

        // 1. Cari di Berita Online
        if ($type === 'all' || $type === 'news') {
            $newsBuilder = $db->table('news_online n');
            $newsBuilder->select('n.id, n.title, n.news_date, n.news_link, m.media_name, n.source_type');
            $newsBuilder->join('media m', 'n.media_id = m.id', 'left');
            
            if (!empty($terms)) {
                $newsBuilder->groupStart();
                    $newsBuilder->groupStart();
                    foreach ($terms as $term) {
                        $newsBuilder->like('n.title', $term);
                    }
                    $newsBuilder->groupEnd();
                    $newsBuilder->orLike('n.news_link', $qClean);
                $newsBuilder->groupEnd();
            }

            $newsBuilder->orderBy('n.news_date', 'DESC');
            $newsBuilder->limit(20);
            $results['news'] = $newsBuilder->get()->getResultArray();
            $results['total_results'] += count($results['news']);
        }

        // 2. Cari di Kliping Berita
        if ($type === 'all' || $type === 'clippings') {
            $clippingsBuilder = $db->table('clippings c');
            $clippingsBuilder->select('c.id, c.title, c.clipping_date, c.summary, m.media_name, c.file_path, c.storage_building, c.storage_room');
            $clippingsBuilder->join('media m', 'c.media_id = m.id', 'left');
            
            if (!empty($terms)) {
                $clippingsBuilder->groupStart();
                    $clippingsBuilder->groupStart();
                    foreach ($terms as $term) {
                        $clippingsBuilder->like('c.title', $term);
                    }
                    $clippingsBuilder->groupEnd();
                    
                    $clippingsBuilder->orGroupStart();
                    foreach ($terms as $term) {
                        $clippingsBuilder->like('c.summary', $term);
                    }
                    $clippingsBuilder->groupEnd();
                $clippingsBuilder->groupEnd();
            }

            $clippingsBuilder->orderBy('c.clipping_date', 'DESC');
            $clippingsBuilder->limit(20);
            $results['clippings'] = $clippingsBuilder->get()->getResultArray();
            $results['total_results'] += count($results['clippings']);
        }

        // 3. Cari di Dokumentasi Kegiatan
        if ($type === 'all' || $type === 'documentation') {
            $docBuilder = $db->table('documentation d');
            $docBuilder->select('d.id, d.event_name, d.event_date, d.description, d.location_name, d.location_type, d.thumbnail_url, d.news_link');
            
            if (!empty($terms)) {
                $docBuilder->groupStart();
                    $docBuilder->groupStart();
                    foreach ($terms as $term) {
                        $docBuilder->like('d.event_name', $term);
                    }
                    $docBuilder->groupEnd();
                    
                    $docBuilder->orGroupStart();
                    foreach ($terms as $term) {
                        $docBuilder->like('d.description', $term);
                    }
                    $docBuilder->groupEnd();
                $docBuilder->groupEnd();
            }

            $docBuilder->orderBy('d.event_date', 'DESC');
            $docBuilder->limit(20);
            $results['documentation'] = $docBuilder->get()->getResultArray();
            $results['total_results'] += count($results['documentation']);
        }

        // 4. Cari di Akreditasi Program Studi
        if ($type === 'all' || $type === 'accreditation') {
            $prodiBuilder = $db->table('study_programs sp');
            $prodiBuilder->select('sp.id, sp.nama_prodi, sp.jenjang, sp.akreditasi_sekarang, sp.masa_berlaku, sp.sertifikat_berlaku, sp.website, f.singkatan as singkatan_fakultas');
            $prodiBuilder->join('faculties f', 'sp.faculty_id = f.id', 'left');
            
            if (!empty($terms)) {
                $prodiBuilder->groupStart();
                    $prodiBuilder->groupStart();
                    foreach ($terms as $term) {
                        $prodiBuilder->like('sp.nama_prodi', $term);
                    }
                    $prodiBuilder->groupEnd();
                    
                    $prodiBuilder->orGroupStart();
                    foreach ($terms as $term) {
                        $prodiBuilder->like('f.nama_fakultas', $term);
                    }
                    $prodiBuilder->groupEnd();
                    
                    $prodiBuilder->orLike('f.singkatan', $qClean);
                $prodiBuilder->groupEnd();
            }

            $prodiBuilder->orderBy('sp.nama_prodi', 'ASC');
            $prodiBuilder->limit(20);
            $results['accreditation'] = $prodiBuilder->get()->getResultArray();
            $results['total_results'] += count($results['accreditation']);
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $results
        ]);
    }

    /**
     * Mengambil Berita Online Humas (Dengan Filter & Paginasi)
     * GET /api/public/news
     */
    public function news()
    {
        $db = \Config\Database::connect();
        $limit = $this->request->getVar('limit') ? (int) $this->request->getVar('limit') : 10;
        $page = $this->request->getVar('page') ? (int) $this->request->getVar('page') : 1;
        $offset = ($page - 1) * $limit;

        $search = $this->request->getVar('search') ?? '';
        $mediaId = $this->request->getVar('media_id') ?? '';
        $categoryId = $this->request->getVar('category_id') ?? '';

        $builder = $db->table('news_online n');
        $builder->select("
            n.id, 
            n.title, 
            n.news_date, 
            n.media_id, 
            m.media_name, 
            n.news_link, 
            n.source_type, 
            n.created_at,
            GROUP_CONCAT(c.name SEPARATOR '|') as category_names
        ");
        $builder->join('media m', 'n.media_id = m.id', 'left');
        $builder->join('news_online_category_rel nocr', 'n.id = nocr.news_id', 'left');
        $builder->join('categories c', 'nocr.category_id = c.id', 'left');

        // Apply filters
        if (!empty($search)) {
            $builder->groupStart()
                ->like('n.title', $search)
                ->orLike('n.news_link', $search)
                ->groupEnd();
        }

        if (!empty($mediaId)) {
            $builder->where('n.media_id', $mediaId);
        }

        if (!empty($categoryId)) {
            $builder->whereIn('n.id', function($subquery) use ($categoryId) {
                return $subquery->select('news_id')
                    ->from('news_online_category_rel')
                    ->where('category_id', $categoryId);
            });
        }

        $builder->groupBy('n.id');
        $builder->orderBy('n.news_date', 'DESC');
        $builder->orderBy('n.id', 'DESC');

        // Paginasi
        $countBuilder = clone $builder;
        $totalItems = $db->newQuery()->from('(' . $countBuilder->getCompiledSelect(false) . ') as sub')->countAllResults();
        $results = $builder->limit($limit, $offset)->get()->getResultArray();

        foreach ($results as &$row) {
            $row['categories'] = !empty($row['category_names']) ? explode('|', $row['category_names']) : [];
            unset($row['category_names']);
        }

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

    /**
     * Mengambil Etalase Berita (Google News Style Grid)
     * GET /api/public/news-etalase
     */
    public function newsEtalase()
    {
        $db = \Config\Database::connect();

        // Cari 4 media terpopuler yang memiliki berita
        $mediaSources = $db->table('news_online n')
            ->select('m.id, m.media_name, COUNT(n.id) as total')
            ->join('media m', 'n.media_id = m.id')
            ->groupBy('m.id')
            ->orderBy('total', 'DESC')
            ->limit(4)
            ->get()->getResultArray();

        if (empty($mediaSources)) {
            // Fallback jika tidak ada media
            $mediaSources = [
                ['id' => 1, 'media_name' => 'Serambi Indonesia'],
                ['id' => 2, 'media_name' => 'Rakyat Aceh'],
                ['id' => 3, 'media_name' => 'Waspada']
            ];
        }

        $etalase = [];
        foreach ($mediaSources as $media) {
            $news = $db->table('news_online n')
                ->select('n.id, n.title, n.news_date, n.news_link')
                ->where('n.media_id', $media['id'])
                ->orderBy('n.news_date', 'DESC')
                ->limit(3)
                ->get()->getResultArray();

            $etalase[] = [
                'media_id'   => $media['id'],
                'media_name' => $media['media_name'],
                'news'       => $news
            ];
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $etalase
        ]);
    }

    /**
     * Mengambil Statistik Sebaran Laporan Berita
     * GET /api/public/media-stats
     */
    public function mediaStats()
    {
        $db = \Config\Database::connect();

        // Sebaran berdasarkan media
        $mediaStats = $db->table('news_online n')
            ->select('m.id, m.media_name, COUNT(n.id) as count')
            ->join('media m', 'n.media_id = m.id')
            ->groupBy('m.id')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // Sebaran berdasarkan kategori
        $categoryStats = $db->table('news_online_category_rel nocr')
            ->select('c.id, c.name, COUNT(nocr.news_id) as count')
            ->join('categories c', 'nocr.category_id = c.id')
            ->groupBy('c.id')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'media'      => $mediaStats,
                'categories' => $categoryStats
            ]
        ]);
    }

    /**
     * Mengambil Daftar Kategori secara Publik
     * GET /api/public/categories
     */
    public function categories()
    {
        $db = \Config\Database::connect();
        $results = $db->table('categories')->orderBy('name', 'ASC')->get()->getResultArray();
        return $this->respond([
            'status' => 'success',
            'data'   => $results
        ]);
    }

    /**
     * Mengambil Daftar Media secara Publik
     * GET /api/public/media
     */
    public function media()
    {
        $db = \Config\Database::connect();
        $results = $db->table('media')->orderBy('media_name', 'ASC')->get()->getResultArray();
        return $this->respond([
            'status' => 'success',
            'data'   => $results
        ]);
    }

    /**
     * Mengambil Kliping Berita
     * GET /api/public/clippings
     */
    public function clippings()
    {
        $db = \Config\Database::connect();
        $limit = $this->request->getVar('limit') ? (int) $this->request->getVar('limit') : 10;
        $page = $this->request->getVar('page') ? (int) $this->request->getVar('page') : 1;
        $offset = ($page - 1) * $limit;

        $search     = $this->request->getVar('search') ?? '';
        $categoryId = $this->request->getVar('category_id') ?? '';
        $mediaId    = $this->request->getVar('media_id') ?? '';
        $startDate  = $this->request->getVar('start_date') ?? '';
        $endDate    = $this->request->getVar('end_date') ?? '';
        $quarter    = $this->request->getVar('quarter') ?? '';
        $year       = $this->request->getVar('year') ?? date('Y');

        $builder = $db->table('clippings c');
        $builder->select("
            c.id, 
            c.title, 
            c.clipping_date, 
            c.media_id, 
            m.media_name, 
            c.media_scale, 
            c.summary, 
            c.file_path, 
            c.storage_building, 
            c.storage_room, 
            c.storage_rack, 
            c.storage_folder, 
            c.is_borrowable,
            GROUP_CONCAT(cat.name SEPARATOR '|') as category_names
        ");
        $builder->join('media m', 'c.media_id = m.id', 'left');
        $builder->join('clipping_category_rel ccr', 'c.id = ccr.clipping_id', 'left');
        $builder->join('categories cat', 'ccr.category_id = cat.id', 'left');

        if (!empty($quarter)) {
            switch ($quarter) {
                case '1': $startDate = "$year-01-01"; $endDate = "$year-03-31"; break;
                case '2': $startDate = "$year-04-01"; $endDate = "$year-06-30"; break;
                case '3': $startDate = "$year-07-01"; $endDate = "$year-09-30"; break;
                case '4': $startDate = "$year-10-01"; $endDate = "$year-12-31"; break;
            }
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('c.title', $search)
                ->orLike('c.summary', $search)
                ->orLike('m.media_name', $search)
                ->groupEnd();
        }

        if (!empty($mediaId)) {
            $builder->where('c.media_id', (int)$mediaId);
        }

        if (!empty($categoryId)) {
            $builder->where('ccr.category_id', (int)$categoryId);
        }

        if (!empty($startDate)) {
            $builder->where('c.clipping_date >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('c.clipping_date <=', $endDate);
        }

        $builder->groupBy('c.id');
        $builder->orderBy('c.clipping_date', 'DESC');

        $countBuilder = clone $builder;
        $totalItems = $db->newQuery()->from('(' . $countBuilder->getCompiledSelect(false) . ') as sub')->countAllResults();
        $results = $builder->limit($limit, $offset)->get()->getResultArray();

        foreach ($results as &$row) {
            $row['categories'] = !empty($row['category_names']) ? explode('|', $row['category_names']) : [];
            unset($row['category_names']);
        }

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

    /**
     * Mengambil Detail Kliping Publik
     * GET /api/public/clippings/(:num)
     */
    public function clippingDetail($id = null)
    {
        if (!$id) {
            return $this->respondWithError("ID Kliping tidak valid.", 400);
        }

        $db = \Config\Database::connect();
        $clipping = $db->table('clippings c')
            ->select('c.*, m.media_name, m.media_logo')
            ->join('media m', 'c.media_id = m.id', 'left')
            ->where('c.id', $id)
            ->get()->getRowArray();

        if (!$clipping) {
            return $this->respondWithError("Kliping tidak ditemukan.", 404);
        }

        // Kategori terkait
        $categories = $db->table('clipping_category_rel ccr')
            ->select('c.name')
            ->join('categories c', 'ccr.category_id = c.id')
            ->where('ccr.clipping_id', $id)
            ->get()->getResultArray();

        $clipping['categories'] = array_column($categories, 'name');

        return $this->respond([
            'status' => 'success',
            'data'   => $clipping
        ]);
    }

    /**
     * Mengambil Dokumentasi Kegiatan
     * GET /api/public/documentation
     */
    public function documentation()
    {
        $db = \Config\Database::connect();
        $search = $this->request->getVar('search') ?? '';
        $categoryId = $this->request->getVar('category_id') ?? '';
        $locationType = $this->request->getVar('location_type') ?? '';
        $startDate = $this->request->getVar('start_date') ?? '';
        $endDate = $this->request->getVar('end_date') ?? '';
        $quarter = $this->request->getVar('quarter') ?? '';
        $year = $this->request->getVar('year') ?? date('Y');

        $limit = $this->request->getVar('limit') ? (int) $this->request->getVar('limit') : 12; // default 12 for grid
        $page = $this->request->getVar('page') ? (int) $this->request->getVar('page') : 1;
        $offset = ($page - 1) * $limit;

        $builder = $db->table('documentation d');
        $builder->select("
            d.id, 
            d.event_name, 
            d.description, 
            d.news_link, 
            d.event_date, 
            d.location_name, 
            d.location_type, 
            d.thumbnail_url, 
            d.photo_folder_link, 
            d.video_folder_link, 
            d.creator_name,
            GROUP_CONCAT(c.name SEPARATOR '|') as category_names
        ");
        $builder->join('documentation_category_rel dcr', 'd.id = dcr.documentation_id', 'left');
        $builder->join('categories c', 'dcr.category_id = c.id', 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('d.event_name', $search)
                ->orLike('d.location_name', $search)
                ->orLike('d.creator_name', $search)
                ->orLike('d.description', $search)
                ->groupEnd();
        }

        if (!empty($locationType)) {
            $builder->where('d.location_type', $locationType);
        }

        if (!empty($categoryId)) {
            $builder->where('dcr.category_id', (int)$categoryId);
        }

        // Filter Triwulan & Rentang Waktu
        if (!empty($quarter)) {
            switch ($quarter) {
                case '1': $startDate = "$year-01-01"; $endDate = "$year-03-31"; break;
                case '2': $startDate = "$year-04-01"; $endDate = "$year-06-30"; break;
                case '3': $startDate = "$year-07-01"; $endDate = "$year-09-30"; break;
                case '4': $startDate = "$year-10-01"; $endDate = "$year-12-31"; break;
            }
        }

        if (!empty($startDate)) {
            $builder->where('d.event_date >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('d.event_date <=', $endDate);
        }

        $builder->groupBy('d.id');
        $builder->orderBy('d.event_date', 'DESC');

        $countBuilder = clone $builder;
        $totalItems = $db->newQuery()->from('(' . $countBuilder->getCompiledSelect(false) . ') as sub')->countAllResults();
        $results = $builder->limit($limit, $offset)->get()->getResultArray();

        foreach ($results as &$row) {
            $row['categories'] = !empty($row['category_names']) ? explode('|', $row['category_names']) : [];
            unset($row['category_names']);
        }

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

    /**
     * Mengambil Detail Dokumentasi Publik & Daftar Kehadiran Tokoh
     * GET /api/public/documentation/(:num)
     */
    public function documentationDetail($id = null)
    {
        if (!$id) {
            return $this->respondWithError("ID Dokumentasi tidak valid.", 400);
        }

        $db = \Config\Database::connect();
        $doc = $db->table('documentation d')
            ->where('d.id', $id)
            ->get()->getRowArray();

        if (!$doc) {
            return $this->respondWithError("Dokumentasi tidak ditemukan.", 404);
        }

        // Kategori terkait
        $categories = $db->table('documentation_category_rel dcr')
            ->select('c.name')
            ->join('categories c', 'dcr.category_id = c.id')
            ->where('dcr.documentation_id', $id)
            ->get()->getResultArray();
        $doc['categories'] = array_column($categories, 'name');

        // Daftar hadir tokoh (Rektorat & Fakultas)
        $attendance = $db->table('documentation_attendance da')
            ->where('da.documentation_id', $id)
            ->orderBy('da.level', 'ASC')
            ->get()->getResultArray();

        $doc['attendance'] = $attendance;

        return $this->respond([
            'status' => 'success',
            'data'   => $doc
        ]);
    }

    /**
     * Mengambil Berita RSS Feeds
     * GET /api/public/rss-items
     */
    public function rssItems()
    {
        $db = \Config\Database::connect();
        $limit = $this->request->getVar('limit') ? (int) $this->request->getVar('limit') : 10;

        if (!$db->tableExists('rss_items')) {
            return $this->respond([
                'status' => 'success',
                'data'   => []
            ]);
        }

        $builder = $db->table('rss_items ri');
        $builder->select('ri.id, ri.title, ri.link, ri.description, ri.creator, ri.pub_date, ri.image_url, rs.site_name');
        $builder->join('rss_sources rs', 'ri.source_id = rs.id');
        $builder->orderBy('ri.pub_date', 'DESC');
        $builder->limit($limit);

        $results = $builder->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => $results
        ]);
    }

    /**
     * Mengambil Statistik Ringkas Portal
     * GET /api/public/stats
     */
    public function stats()
    {
        $db = \Config\Database::connect();
        
        $newsCount = $db->table('news_online')->countAllResults();
        $clippingsCount = $db->table('clippings')->countAllResults();
        $docsCount = $db->table('documentation')->countAllResults();
        $rssCount = $db->tableExists('rss_items') ? $db->table('rss_items')->countAllResults() : 0;

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'news_online'   => $newsCount,
                'clippings'     => $clippingsCount,
                'documentation' => $docsCount,
                'rss_articles'  => $rssCount,
            ]
        ]);
    }

    /**
     * Mengambil Data Statistik Visual & Tren (Inspirasi Aceh Studies)
     * GET /api/public/stats-visual
     */
    public function statsVisual()
    {
        $data = [
            'kpis' => [
                [
                    'label' => 'Total Mahasiswa Aktif',
                    'value' => '25.924',
                    'sub' => '+8.4% dari tahun lalu',
                    'trend' => 'up',
                    'icon' => 'students'
                ],
                [
                    'label' => 'Dosen & Tenaga Ahli',
                    'value' => '842',
                    'sub' => 'Rasio dosen/mahasiswa ideal',
                    'trend' => 'stable',
                    'icon' => 'lecturers'
                ],
                [
                    'label' => 'Program Studi Terakreditasi Unggul',
                    'value' => '28',
                    'sub' => 'Dari total 54 program studi',
                    'trend' => 'up',
                    'icon' => 'accreditation'
                ],
                [
                    'label' => 'Publikasi Jurnal Terindeks',
                    'value' => '3.082',
                    'sub' => 'Scopus, SINTA, & Garuda',
                    'trend' => 'up',
                    'icon' => 'publications'
                ]
            ],
            'charts' => [
                'studentTrend' => [
                    'labels' => ['2021', '2022', '2023', '2024', '2025', '2026'],
                    'datasets' => [
                        [
                            'label' => 'Mahasiswa Baru S1',
                            'data' => [4210, 4480, 4890, 5120, 5540, 5920],
                            'borderColor' => '#10b981',
                            'backgroundColor' => 'rgba(16, 185, 129, 0.1)'
                        ],
                        [
                            'label' => 'Mahasiswa Pascasarjana',
                            'data' => [310, 350, 420, 480, 510, 580],
                            'borderColor' => '#f59e0b',
                            'backgroundColor' => 'rgba(245, 158, 11, 0.1)'
                        ]
                    ]
                ],
                'internationalStudents' => [
                    'labels' => ['Malaysia', 'Thailand', 'Brunei', 'Kamboja', 'Sudan', 'Lainnya'],
                    'data' => [245, 112, 34, 22, 15, 45],
                    'backgroundColor' => ['#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#64748b']
                ],
                'publicationTrend' => [
                    'labels' => ['2021', '2022', '2023', '2024', '2025'],
                    'data' => [320, 410, 540, 670, 810],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)'
                ]
            ]
        ];

        return $this->respond([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * Jadwal Khatib Jumat Seputaran Kampus
     * GET /api/public/khatib-schedule
     */
    public function khatibSchedule()
    {
        $sermonModel = new \App\Models\FridaySermonModel();
        $todayStr = date('Y-m-d');

        // 1. Find nearest Friday date in the future or today
        $nearestDateRow = $sermonModel->where('date >=', $todayStr)
                                      ->orderBy('date', 'ASC')
                                      ->first();

        if (!$nearestDateRow) {
            // Fallback to newest past schedule
            $nearestDateRow = $sermonModel->orderBy('date', 'DESC')->first();
        }

        if ($nearestDateRow) {
            $targetDate = $nearestDateRow['date'];
        } else {
            // Hard fallback if database is completely empty: calculate upcoming calendar Friday
            $today = time();
            $dayOfWeek = date('w', $today);
            if ($dayOfWeek == 5) {
                $targetDate = date('Y-m-d', $today);
            } else {
                $daysToFriday = (5 - $dayOfWeek + 7) % 7;
                $targetDate = date('Y-m-d', strtotime("+$daysToFriday days", $today));
            }
        }

        // 2. Fetch main sermon (Fathun Qarib)
        $mainSermon = $sermonModel->where('date', $targetDate)
                                  ->where('mosque_type', 'fathun_qarib')
                                  ->first();

        // 3. Fetch other sermons
        $otherSermons = $sermonModel->where('date', $targetDate)
                                    ->where('mosque_type', 'other')
                                    ->findAll();

        // Format Date to "d F Y" in Indonesian
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $time = strtotime($targetDate);
        $formattedDate = date('d', $time) . ' ' . $months[(int)date('m', $time)] . ' ' . date('Y', $time);

        $schedule = [
            'date' => $formattedDate,
            'main' => $mainSermon ? [
                'mosque_name' => 'Masjid Fathun Qarib UIN Ar-Raniry',
                'location'    => 'Kopelma Darussalam (Kampus UIN)',
                'khatib'      => $mainSermon['khatib'],
                'title'       => 'Membangun Generasi Rabbani di Era Digital',
                'imam'        => $mainSermon['imam'] ?: '-',
                'muadzin'     => $mainSermon['muazzin'] ?: '-',
                'note'        => 'Khatib Utama Masjid Utama UIN Ar-Raniry.'
            ] : null,
            'others' => array_map(function($s) {
                return [
                    'mosque_name' => $s['mosque_name'],
                    'location'    => 'Seputaran Kampus / Banda Aceh',
                    'khatib'      => $s['khatib'],
                    'title'       => 'Jadwal Khutbah Jumat seputaran kampus.'
                ];
            }, $otherSermons)
        ];

        return $this->respond([
            'status' => 'success',
            'data'   => $schedule
        ]);
    }

    /**
     * Mengambil Pengaturan Aplikasi secara Publik (Identity & SEO)
     * GET /api/public/settings
     */
    public function settings()
    {
        $settingModel = new \App\Models\SettingModel();
        
        $keys = [
            'app_name',
            'app_subtitle',
            'app_description',
            'app_footer',
            'app_logo',
            'app_favicon',
            'seo_title',
            'seo_keywords',
            'seo_author',
            'seo_image'
        ];
        
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $settingModel->getVal($key, '');
        }
        
        return $this->respond([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * GET /api/public/accreditation/study-programs
     */
    public function listProdis()
    {
        $prodiModel = new \App\Models\StudyProgramModel();
        
        $search = $this->request->getGet('search');
        $facultyId = $this->request->getGet('faculty_id');
        $jenjang = $this->request->getGet('jenjang');
        $akreditasi = $this->request->getGet('akreditasi_sekarang');
        $jalurMasuk = $this->request->getGet('jalur_masuk');

        $builder = $prodiModel->select('study_programs.*, faculties.nama_fakultas, faculties.singkatan as singkatan_fakultas')
            ->join('faculties', 'faculties.id = study_programs.faculty_id');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('study_programs.nama_prodi', $search)
                ->orLike('faculties.nama_fakultas', $search)
                ->orLike('faculties.singkatan', $search)
                ->groupEnd();
        }

        if (!empty($facultyId)) {
            $builder->where('study_programs.faculty_id', (int)$facultyId);
        }

        if (!empty($jenjang)) {
            $builder->where('study_programs.jenjang', $jenjang);
        }

        if (!empty($akreditasi)) {
            $builder->where('study_programs.akreditasi_sekarang', $akreditasi);
        }

        if (!empty($jalurMasuk)) {
            $builder->like('study_programs.jalur_masuk', $jalurMasuk);
        }

        $prodis = $builder->orderBy('study_programs.nama_prodi', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $prodis
        ]);
    }

    /**
     * GET /api/public/accreditation/reports
     */
    public function reports()
    {
        $facultyModel = new \App\Models\FacultyModel();
        $prodiModel   = new \App\Models\StudyProgramModel();

        $totalFaculties = $facultyModel->countAllResults();
        $totalProdis    = $prodiModel->countAllResults();
        $totalUnggul    = $prodiModel->where('akreditasi_sekarang', 'Unggul')->countAllResults();

        // 1. Accreditation ratio
        $grades = ['Unggul', 'Baik Sekali', 'Baik'];
        $accreditationRatio = [];
        foreach ($grades as $g) {
            $accreditationRatio[$g] = $prodiModel->where('akreditasi_sekarang', $g)->countAllResults();
        }

        // 2. Jenjang ratio
        $jenjangs = ['D4', 'S1', 'S2', 'S3', 'Profesi'];
        $jenjangRatio = [];
        foreach ($jenjangs as $j) {
            $jenjangRatio[$j] = $prodiModel->where('jenjang', $j)->countAllResults();
        }

        // 3. Prodis per faculty
        $faculties = $facultyModel->orderBy('nama_fakultas', 'ASC')->findAll();
        $prodiPerFaculty = [];
        foreach ($faculties as $fac) {
            $prodiPerFaculty[] = [
                'id'            => $fac['id'],
                'nama_fakultas' => $fac['nama_fakultas'],
                'singkatan'     => $fac['singkatan'],
                'count'         => $prodiModel->where('faculty_id', $fac['id'])->countAllResults()
            ];
        }

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'total_faculties'     => $totalFaculties,
                'total_prodis'        => $totalProdis,
                'total_unggul'        => $totalUnggul,
                'accreditation_ratio' => $accreditationRatio,
                'jenjang_ratio'       => $jenjangRatio,
                'prodi_per_faculty'   => $prodiPerFaculty
            ]
        ]);
    }

    /**
     * GET /api/public/accreditation/faculties
     */
    public function listFaculties()
    {
        $facultyModel = new \App\Models\FacultyModel();
        $faculties = $facultyModel->orderBy('nama_fakultas', 'ASC')->findAll();
        return $this->respond([
            'status' => 'success',
            'data'   => $faculties
        ]);
    }

    /**
     * Mengambil Daftar Menu Aplikasi secara Publik (App Launcher)
     * GET /api/public/menus
     */
    public function menus()
    {
        $menuModel = new \App\Models\AppMenuModel();
        $menus = $menuModel->where('is_active', 1)
                           ->orderBy('sort_order', 'ASC')
                           ->findAll();

        // Hanya kirim field yang diperlukan frontend
        $result = array_map(function($menu) {
            return [
                'id'   => (int)$menu['id'],
                'name' => $menu['name'],
                'url'  => $menu['url'],
                'icon' => $menu['icon'],
            ];
        }, $menus);

        return $this->respond([
            'status' => 'success',
            'data'   => $result
        ]);
    }
}

