<?php

namespace App\Controllers\Api;

use App\Models\NewsModel;
use App\Models\MediaModel;
use App\Models\CategoryModel;
use Exception;

class News extends BaseApiController
{
    /**
     * Mengambil daftar berita online (dengan penanganan filter & pencarian)
     * GET /api/admin/news
     */
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Ambil filter query parameter
        $search     = $this->request->getVar('search') ?? '';
        $mediaId    = $this->request->getVar('media_id') ?? '';
        $categoryId = $this->request->getVar('category_id') ?? '';
        $startDate  = $this->request->getVar('start_date') ?? '';
        $endDate    = $this->request->getVar('end_date') ?? '';
        $quarter    = $this->request->getVar('quarter') ?? '';
        $year       = $this->request->getVar('year') ?? date('Y');
        $sourceType = $this->request->getVar('source_type') ?? '';

        $page       = $this->request->getVar('page') ? (int) $this->request->getVar('page') : 1;

        $limitParam = $this->request->getVar('limit');
        if ($limitParam === 'all') {
            $limit = 999999;
        } else {
            $limit = $limitParam ? (int) $limitParam : 10;
        }

        $offset     = ($page - 1) * $limit;

        // Query Utama dengan JOIN
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
            GROUP_CONCAT(c.name SEPARATOR '|') as category_names,
            GROUP_CONCAT(c.id SEPARATOR '|') as category_ids
        ");
        $builder->join('media m', 'n.media_id = m.id', 'left');
        $builder->join('news_online_category_rel nocr', 'n.id = nocr.news_id', 'left');
        $builder->join('categories c', 'nocr.category_id = c.id', 'left');

        // Filter Pencarian
        if (!empty($search)) {
            $builder->groupStart()
                ->like('n.title', $search)
                ->orLike('n.news_link', $search)
                ->groupEnd();
        }

        // Filter Media
        if (!empty($mediaId)) {
            $builder->where('n.media_id', $mediaId);
        }

        // Filter Kategori
        if (!empty($categoryId)) {
            // Kita gunakan subquery atau WHERE IN untuk memastikan pencarian multi-kategori tepat
            $builder->whereIn('n.id', function($subquery) use ($categoryId) {
                return $subquery->select('news_id')
                    ->from('news_online_category_rel')
                    ->where('category_id', $categoryId);
            });
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
            $builder->where('n.news_date >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('n.news_date <=', $endDate);
        }

        // Filter Sumber
        if (!empty($sourceType)) {
            $builder->where('n.source_type', $sourceType);
        }

        // Group by news ID untuk GROUP_CONCAT
        $builder->groupBy('n.id');
        $builder->orderBy('n.news_date', 'DESC');
        $builder->orderBy('n.id', 'DESC');

        // Kloning builder untuk menghitung total baris (untuk paginasi)
        $countBuilder = clone $builder;
        $totalItems = $db->newQuery()->from('(' . $countBuilder->getCompiledSelect(false) . ') as sub')->countAllResults();

        // Ambil data terpilih dengan limit & offset
        $results = $builder->limit($limit, $offset)->get()->getResultArray();

        // Format respon data kategori menjadi array terstruktur
        foreach ($results as &$row) {
            $row['categories'] = [];
            if (!empty($row['category_names']) && !empty($row['category_ids'])) {
                $names = explode('|', $row['category_names']);
                $ids   = explode('|', $row['category_ids']);
                for ($i = 0; $i < count($names); $i++) {
                    if (isset($names[$i]) && isset($ids[$i])) {
                        $row['categories'][] = [
                            'id'   => (int) $ids[$i],
                            'name' => $names[$i]
                        ];
                    }
                }
            }
            // Hapus string concat sementara
            unset($row['category_names']);
            unset($row['category_ids']);
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
     * Membuat berita online baru
     * POST /api/admin/news
     */
    public function create()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $title       = $json['title'] ?? '';
        $newsDate    = $json['news_date'] ?? '';
        $mediaId     = $json['media_id'] ?? null;
        $sourceType  = $json['source_type'] ?? '';
        $newsLink    = $json['news_link'] ?? '';
        $categoryIds = $json['category_ids'] ?? []; // Array of category IDs (multi-select)

        // Validasi input wajib
        if (empty($title) || empty($newsDate) || empty($sourceType) || empty($newsLink)) {
            return $this->respondWithError("Kolom Judul, Tanggal, Sumber, dan Tautan Berita wajib diisi.", 400);
        }

        $db->transBegin();
        try {
            // Ambil ID user dari JWT token payload yang dilampirkan oleh filter
            $userId = $this->request->decodedToken['uid'] ?? null;

            // Masukkan data berita utama
            $newsModel = new NewsModel();
            $newsData = [
                'title'       => $title,
                'news_date'   => $newsDate,
                'media_id'    => $mediaId ? (int)$mediaId : null,
                'category_id' => !empty($categoryIds) ? (int)$categoryIds[0] : null, // Kompatibilitas mundur (kategori pertama)
                'source_type' => $sourceType,
                'news_link'   => $newsLink,
                'created_by'  => $userId ? (int)$userId : null
            ];
            
            $newsId = $newsModel->insert($newsData, true);

            // Masukkan hubungan multi-kategori
            if (!empty($categoryIds) && is_array($categoryIds)) {
                $relBuilder = $db->table('news_online_category_rel');
                $relData = [];
                foreach ($categoryIds as $catId) {
                    $relData[] = [
                        'news_id'     => $newsId,
                        'category_id' => (int)$catId
                    ];
                }
                $relBuilder->insertBatch($relData);
            }

            $this->logActivity("Menambahkan berita online: " . $title, "Berita Online", $newsId);
            $db->transCommit();
            return $this->respond([
                'status'  => 'success',
                'message' => 'Berita online berhasil disimpan.',
                'news_id' => $newsId
            ], 201);

        } catch (Exception $e) {
            $db->transRollback();
            return $this->respondWithError("Gagal menyimpan berita: " . $e->getMessage(), 500);
        }
    }

    /**
     * Memperbarui berita online
     * PUT /api/admin/news/$id
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->respondWithError("ID berita tidak valid.", 400);
        }

        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);

        $title       = $json['title'] ?? '';
        $newsDate    = $json['news_date'] ?? '';
        $mediaId     = $json['media_id'] ?? null;
        $sourceType  = $json['source_type'] ?? '';
        $newsLink    = $json['news_link'] ?? '';
        $categoryIds = $json['category_ids'] ?? [];

        if (empty($title) || empty($newsDate) || empty($sourceType) || empty($newsLink)) {
            return $this->respondWithError("Kolom Judul, Tanggal, Sumber, dan Tautan Berita wajib diisi.", 400);
        }

        $newsModel = new NewsModel();
        $news = $newsModel->find($id);
        if (!$news) {
            return $this->respondWithError("Berita tidak ditemukan.", 404);
        }

        $db->transBegin();
        try {
            // Update tabel utama
            $newsData = [
                'title'       => $title,
                'news_date'   => $newsDate,
                'media_id'    => $mediaId ? (int)$mediaId : null,
                'category_id' => !empty($categoryIds) ? (int)$categoryIds[0] : null, // Kompatibilitas mundur
                'source_type' => $sourceType,
                'news_link'   => $newsLink
            ];
            
            $newsModel->update($id, $newsData);

            // Bersihkan relasi lama & masukkan yang baru
            $db->table('news_online_category_rel')->where('news_id', $id)->delete();

            if (!empty($categoryIds) && is_array($categoryIds)) {
                $relBuilder = $db->table('news_online_category_rel');
                $relData = [];
                foreach ($categoryIds as $catId) {
                    $relData[] = [
                        'news_id'     => $id,
                        'category_id' => (int)$catId
                    ];
                }
                $relBuilder->insertBatch($relData);
            }

            $this->logActivity("Memperbarui berita online: " . $title, "Berita Online", (int)$id);
            $db->transCommit();
            return $this->respond([
                'status'  => 'success',
                'message' => 'Berita online berhasil diperbarui.'
            ]);

        } catch (Exception $e) {
            $db->transRollback();
            return $this->respondWithError("Gagal memperbarui berita: " . $e->getMessage(), 500);
        }
    }

    /**
     * Menghapus berita online
     * DELETE /api/admin/news/$id
     */
    public function delete($id = null)
    {
        if (!$id) {
            return $this->respondWithError("ID berita tidak valid.", 400);
        }

        $newsModel = new NewsModel();
        $news = $newsModel->find($id);
        if (!$news) {
            return $this->respondWithError("Berita tidak ditemukan.", 404);
        }

        try {
            // Karena foreign key ON DELETE CASCADE telah dikonfigurasi di InitialSchema,
            // baris relasi di news_online_category_rel otomatis akan terhapus bersih oleh DBMS.
            $this->logActivity("Menghapus berita online: " . $news['title'], "Berita Online", (int)$id);
            $newsModel->delete($id);
            return $this->respond([
                'status'  => 'success',
                'message' => 'Berita online berhasil dihapus.'
            ]);
        } catch (Exception $e) {
            return $this->respondWithError("Gagal menghapus berita: " . $e->getMessage(), 500);
        }
    }

    /**
     * Impor berita online via berkas CSV
     * POST /api/admin/news/import
     */
    public function importCsv()
    {
        $db   = \Config\Database::connect();
        $file = $this->request->getFile('csv_file');

        if (!$file || !$file->isValid()) {
            return $this->respondWithError("Berkas tidak valid atau tidak ditemukan. Pastikan field 'csv_file' terisi.", 400);
        }

        if (strtolower($file->getExtension()) !== 'csv') {
            return $this->respondWithError("Format berkas wajib berupa .csv.", 400);
        }

        $filePath = $file->getTempName();
        $handle   = fopen($filePath, 'r');
        if (!$handle) {
            return $this->respondWithError("Gagal membuka berkas CSV.", 500);
        }

        // Lewati baris header: title,news_date,news_link,media_name,source_type,categories
        fgetcsv($handle, 4096, ',');

        $stats      = ['inserted' => 0, 'skipped' => 0, 'errors' => 0];
        $userId     = $this->request->decodedToken['uid'] ?? null;
        $mediaCache = [];
        $catCache   = [];

        $db->transBegin();
        try {
            while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                // Kolom wajib minimal 3 (title, news_date, news_link)
                if (count($row) < 3) {
                    $stats['errors']++;
                    continue;
                }

                $title      = trim($row[0] ?? '');
                $newsDate   = trim($row[1] ?? date('Y-m-d'));
                $newsLink   = trim($row[2] ?? '');
                $mediaName  = trim($row[3] ?? '');
                $sourceType = trim($row[4] ?? 'Rilis Humas');
                $catsStr    = trim($row[5] ?? '');

                if (empty($title) || empty($newsLink)) {
                    $stats['errors']++;
                    continue;
                }

                // Cek duplikat berdasarkan news_link
                if ($db->table('news_online')->where('news_link', $newsLink)->countAllResults() > 0) {
                    $stats['skipped']++;
                    continue;
                }

                // Validasi format tanggal — terima YYYY-MM-DD dan DD-MM-YYYY
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $newsDate)) {
                    $p = explode('-', $newsDate);
                    $newsDate = $p[2] . '-' . $p[1] . '-' . $p[0];
                }
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newsDate)) {
                    $newsDate = date('Y-m-d');
                }

                // Normalisasi source_type
                if (!in_array($sourceType, ['Rilis Humas', 'Liputan Wartawan'])) {
                    $sourceType = 'Rilis Humas';
                }

                // --- Resolve Media (dengan cache) ---
                $mediaId = null;
                if (!empty($mediaName)) {
                    $cacheKey = strtolower($mediaName);
                    if (!isset($mediaCache[$cacheKey])) {
                        $mRow = $db->table('media')
                            ->where('LOWER(media_name)', $cacheKey)
                            ->get()->getRowArray();
                        if ($mRow) {
                            $mediaCache[$cacheKey] = $mRow['id'];
                        } else {
                            $db->table('media')->insert(['media_name' => $mediaName, 'media_type' => 'online']);
                            $mediaCache[$cacheKey] = $db->insertID();
                        }
                    }
                    $mediaId = $mediaCache[$cacheKey];
                }

                // --- Resolve Kategori (dipisah titik koma ; dengan cache) ---
                $catIds = [];
                if (!empty($catsStr)) {
                    foreach (explode(';', $catsStr) as $rawCat) {
                        $catName = trim($rawCat);
                        if (empty($catName)) continue;

                        $catKey = strtolower($catName);
                        if (!isset($catCache[$catKey])) {
                            $cRow = $db->table('categories')
                                ->where('LOWER(name)', $catKey)
                                ->get()->getRowArray();
                            if ($cRow) {
                                $catCache[$catKey] = $cRow['id'];
                            } else {
                                $db->table('categories')->insert(['name' => $catName]);
                                $catCache[$catKey] = $db->insertID();
                            }
                        }
                        $catIds[] = $catCache[$catKey];
                    }
                }

                // --- Sisipkan berita ke database ---
                $db->table('news_online')->insert([
                    'title'       => $title,
                    'news_date'   => $newsDate,
                    'media_id'    => $mediaId,
                    'source_type' => $sourceType,
                    'news_link'   => $newsLink,
                    'created_by'  => $userId ? (int)$userId : null,
                ]);
                $newsId = $db->insertID();

                // --- Hubungkan relasi kategori ---
                foreach (array_unique(array_filter($catIds)) as $cid) {
                    $db->table('news_online_category_rel')->insert([
                        'news_id'     => $newsId,
                        'category_id' => $cid,
                    ]);
                }

                $stats['inserted']++;
            }

            fclose($handle);
            if ($stats['inserted'] > 0) {
                $this->logActivity("Melakukan impor massal berita online via CSV. Total berhasil: " . $stats['inserted'], "Berita Online");
            }
            $db->transCommit();

            $i = $stats['inserted'];
            $s = $stats['skipped'];
            $e = $stats['errors'];
            $stats['message'] = "{$i} berita berhasil ditambahkan, {$s} dilewati (duplikat), {$e} baris bermasalah.";

            return $this->respond([
                'status'  => 'success',
                'message' => 'Impor CSV selesai.',
                'data'    => $stats
            ]);

        } catch (\Exception $ex) {
            fclose($handle);
            $db->transRollback();
            return $this->respondWithError("Impor gagal: " . $ex->getMessage(), 500);
        }
    }
}
