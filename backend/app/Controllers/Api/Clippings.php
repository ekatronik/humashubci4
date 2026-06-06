<?php

namespace App\Controllers\Api;

use App\Controllers\Api\BaseApiController;

/**
 * API Controller — Kliping Surat Kabar / Media Cetak
 *
 * Endpoints:
 *   GET    /api/admin/clippings          — Daftar kliping (paginated, search, filter)
 *   POST   /api/admin/clippings          — Tambah kliping baru
 *   GET    /api/admin/clippings/{id}     — Detail satu kliping
 *   PUT    /api/admin/clippings/{id}     — Update kliping
 *   DELETE /api/admin/clippings/{id}     — Hapus kliping
 */
class Clippings extends BaseApiController
{
    // ────────────────────────────────────────────────────────────
    //  INDEX — Daftar dengan pagination, search, filter kategori
    // ────────────────────────────────────────────────────────────
    public function index()
    {
        $db         = \Config\Database::connect();
        $search     = $this->request->getGet('search') ?? '';
        $categoryId = $this->request->getGet('category_id') ?? '';
        $mediaId    = $this->request->getGet('media_id') ?? '';
        $startDate  = $this->request->getGet('start_date') ?? '';
        $endDate    = $this->request->getGet('end_date') ?? '';
        $quarter    = $this->request->getGet('quarter') ?? '';
        $year       = $this->request->getGet('year') ?? date('Y');

        $page       = max(1, (int)($this->request->getGet('page') ?? 1));
        
        $limitParam = $this->request->getGet('limit');
        if ($limitParam === 'all') {
            $limit = 999999;
        } else {
            $limit = min(100, max(5, (int)($limitParam ?? 10)));
        }

        $offset     = ($page - 1) * $limit;

        // Base query dengan JOIN ke media
        $baseQuery = $db->table('clippings c')
            ->select('c.id, c.title, c.clipping_date, c.media_scale, c.summary,
                      c.file_path, c.storage_building, c.storage_room,
                      c.storage_rack, c.storage_folder, c.is_borrowable,
                      c.created_at, m.media_name')
            ->join('media m', 'm.id = c.media_id', 'left');

        if (!empty($quarter)) {
            switch ($quarter) {
                case '1': $startDate = "$year-01-01"; $endDate = "$year-03-31"; break;
                case '2': $startDate = "$year-04-01"; $endDate = "$year-06-30"; break;
                case '3': $startDate = "$year-07-01"; $endDate = "$year-09-30"; break;
                case '4': $startDate = "$year-10-01"; $endDate = "$year-12-31"; break;
            }
        }

        if (!empty($search)) {
            $baseQuery->groupStart()
                ->like('c.title', $search)
                ->orLike('c.summary', $search)
                ->orLike('m.media_name', $search)
                ->groupEnd();
        }

        if (!empty($mediaId)) {
            $baseQuery->where('c.media_id', (int)$mediaId);
        }

        if (!empty($categoryId)) {
            $baseQuery->join('clipping_category_rel ccr', 'ccr.clipping_id = c.id', 'inner')
                      ->where('ccr.category_id', (int)$categoryId);
        }

        if (!empty($startDate)) {
            $baseQuery->where('c.clipping_date >=', $startDate);
        }

        if (!empty($endDate)) {
            $baseQuery->where('c.clipping_date <=', $endDate);
        }

        $totalItems = (clone $baseQuery)->countAllResults(false);
        $rows       = $baseQuery->limit($limit, $offset)
                                ->orderBy('c.clipping_date', 'DESC')
                                ->get()->getResultArray();

        // Lampirkan kategori pada setiap item
        foreach ($rows as &$row) {
            $cats = $db->table('clipping_category_rel ccr')
                ->select('cat.id, cat.name')
                ->join('categories cat', 'cat.id = ccr.category_id')
                ->where('ccr.clipping_id', $row['id'])
                ->get()->getResultArray();
            $row['categories'] = $cats;
        }
        unset($row);

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'items'       => $rows,
                'total_items' => $totalItems,
                'total_pages' => (int)ceil($totalItems / $limit),
                'current_page'=> $page,
            ]
        ]);
    }

    // ────────────────────────────────────────────────────────────
    //  SHOW — Detail satu kliping
    // ────────────────────────────────────────────────────────────
    public function show($id)
    {
        $db  = \Config\Database::connect();
        $row = $db->table('clippings c')
            ->select('c.*, m.media_name, m.media_logo')
            ->join('media m', 'm.id = c.media_id', 'left')
            ->where('c.id', (int)$id)
            ->get()->getRowArray();

        if (!$row) {
            return $this->respondWithError('Kliping tidak ditemukan.', 404);
        }

        $row['categories'] = $db->table('clipping_category_rel ccr')
            ->select('cat.id, cat.name')
            ->join('categories cat', 'cat.id = ccr.category_id')
            ->where('ccr.clipping_id', $id)
            ->get()->getResultArray();

        return $this->respond(['status' => 'success', 'data' => $row]);
    }

    // ────────────────────────────────────────────────────────────
    //  CREATE — Tambah kliping baru
    // ────────────────────────────────────────────────────────────
    public function create()
    {
        $db     = \Config\Database::connect();
        $userId = $this->request->decodedToken['uid'] ?? null;

        // Mendukung request multipart/form-data
        $title   = trim($this->request->getPost('title') ?? '');
        $date    = trim($this->request->getPost('clipping_date') ?? '');
        $mediaId = $this->request->getPost('media_id');
        $mediaId = !empty($mediaId) ? (int)$mediaId : null;

        if (empty($title) || empty($date)) {
            return $this->respondWithError('Judul dan tanggal kliping wajib diisi.', 400);
        }

        // Proses upload file PDF jika ada
        $file = $this->request->getFile('file');
        $filePath = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dateParts = explode('-', $date);
            $year = $dateParts[0] ?? date('Y');
            $month = $dateParts[1] ?? date('m');
            
            $uploadPath = ROOTPATH . 'public/uploads/clippings/' . $year . '/' . $month . '/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $filePath = $year . '/' . $month . '/' . $newName;
        }

        $db->transBegin();
        try {
            $db->table('clippings')->insert([
                'title'            => $title,
                'clipping_date'    => $date,
                'media_id'         => $mediaId,
                'media_scale'      => $this->request->getPost('media_scale') ?? null,
                'summary'          => $this->request->getPost('summary') ?? null,
                'file_path'        => $filePath,
                'storage_building' => $this->request->getPost('storage_building') ?? null,
                'storage_room'     => $this->request->getPost('storage_room') ?? null,
                'storage_rack'     => $this->request->getPost('storage_rack') ?? null,
                'storage_folder'   => $this->request->getPost('storage_folder') ?? null,
                'is_borrowable'    => $this->request->getPost('is_borrowable') !== null ? (int)$this->request->getPost('is_borrowable') : 1,
                'created_by'       => $userId ? (int)$userId : null,
            ]);
            $newId = $db->insertID();

            $categoryIdsRaw = $this->request->getPost('category_ids');
            $categoryIds = [];
            if (!empty($categoryIdsRaw)) {
                $categoryIds = json_decode($categoryIdsRaw, true) ?? [];
            }
            $this->syncCategories($db, $newId, $categoryIds);

            $this->logActivity("Menambahkan kliping berita: " . $title, "Kliping", $newId);
            $db->transCommit();
            return $this->respond(['status' => 'success', 'message' => 'Kliping berhasil ditambahkan.', 'data' => ['id' => $newId]], 201);
        } catch (\Exception $e) {
            $db->transRollback();
            // Delete uploaded file if database transaction fails
            if ($filePath && is_file(ROOTPATH . 'public/uploads/clippings/' . $filePath)) {
                unlink(ROOTPATH . 'public/uploads/clippings/' . $filePath);
            }
            return $this->respondWithError('Gagal menyimpan kliping: ' . $e->getMessage(), 500);
        }
    }

    // ────────────────────────────────────────────────────────────
    //  UPDATE — Edit kliping
    // ────────────────────────────────────────────────────────────
    public function update($id)
    {
        $db = \Config\Database::connect();
        
        $existing = $db->table('clippings')->where('id', (int)$id)->get()->getRowArray();
        if (!$existing) {
            return $this->respondWithError('Kliping tidak ditemukan.', 404);
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (!empty($input)) {
            // Jika request berupa JSON (metode lama)
            $title   = trim($input['title'] ?? '');
            $date    = trim($input['clipping_date'] ?? '');
            $mediaId = !empty($input['media_id']) ? (int)$input['media_id'] : null;
            $mediaScale = $input['media_scale'] ?? null;
            $summary = $input['summary'] ?? null;
            $storageBuilding = $input['storage_building'] ?? null;
            $storageRoom = $input['storage_room'] ?? null;
            $storageRack = $input['storage_rack'] ?? null;
            $storageFolder = $input['storage_folder'] ?? null;
            $isBorrowable = isset($input['is_borrowable']) ? (int)$input['is_borrowable'] : 1;
            $categoryIds = $input['category_ids'] ?? [];
            $clearFile = false;
        } else {
            // Jika request berupa FormData (multipart/form-data)
            $title   = trim($this->request->getPost('title') ?? '');
            $date    = trim($this->request->getPost('clipping_date') ?? '');
            $mediaId = $this->request->getPost('media_id');
            $mediaId = !empty($mediaId) ? (int)$mediaId : null;
            $mediaScale = $this->request->getPost('media_scale') ?? null;
            $summary = $this->request->getPost('summary') ?? null;
            $storageBuilding = $this->request->getPost('storage_building') ?? null;
            $storageRoom = $this->request->getPost('storage_room') ?? null;
            $storageRack = $this->request->getPost('storage_rack') ?? null;
            $storageFolder = $this->request->getPost('storage_folder') ?? null;
            $isBorrowable = $this->request->getPost('is_borrowable') !== null ? (int)$this->request->getPost('is_borrowable') : 1;
            $categoryIdsRaw = $this->request->getPost('category_ids');
            $categoryIds = [];
            if (!empty($categoryIdsRaw)) {
                $categoryIds = json_decode($categoryIdsRaw, true) ?? [];
            }
            $clearFile = $this->request->getPost('clear_file') === '1';
        }

        if (empty($title) || empty($date)) {
            return $this->respondWithError('Judul dan tanggal kliping wajib diisi.', 400);
        }

        // Proses unggahan file PDF baru jika ada
        $file = $this->request->getFile('file');
        $filePath = $existing['file_path'];
        $oldFileToDelete = null;

        if ($clearFile) {
            // Menghapus file saat ini
            $oldFileToDelete = $existing['file_path'];
            $filePath = null;
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Jika ada file baru yang diunggah
            $dateParts = explode('-', $date);
            $year = $dateParts[0] ?? date('Y');
            $month = $dateParts[1] ?? date('m');
            
            $uploadPath = ROOTPATH . 'public/uploads/clippings/' . $year . '/' . $month . '/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            
            // Jadwalkan penghapusan file lama jika ada
            if ($existing['file_path']) {
                $oldFileToDelete = $existing['file_path'];
            }
            $filePath = $year . '/' . $month . '/' . $newName;
        }

        $db->transBegin();
        try {
            $db->table('clippings')->where('id', (int)$id)->update([
                'title'            => $title,
                'clipping_date'    => $date,
                'media_id'         => $mediaId,
                'media_scale'      => $mediaScale,
                'summary'          => $summary,
                'file_path'        => $filePath,
                'storage_building' => $storageBuilding,
                'storage_room'     => $storageRoom,
                'storage_rack'     => $storageRack,
                'storage_folder'   => $storageFolder,
                'is_borrowable'    => $isBorrowable,
            ]);

            $this->syncCategories($db, (int)$id, $categoryIds);

            $this->logActivity("Memperbarui kliping berita: " . $title, "Kliping", (int)$id);
            $db->transCommit();

            // Jika transaksi sukses, hapus file lama dari disk
            if ($oldFileToDelete) {
                $oldFullFile = ROOTPATH . 'public/uploads/clippings/' . $oldFileToDelete;
                if (is_file($oldFullFile)) {
                    unlink($oldFullFile);
                }
            }

            return $this->respond(['status' => 'success', 'message' => 'Kliping berhasil diperbarui.']);
        } catch (\Exception $e) {
            $db->transRollback();
            // Jika transaksi gagal, hapus file baru yang sempat diunggah
            if ($filePath && $filePath !== $existing['file_path']) {
                $newFullFile = ROOTPATH . 'public/uploads/clippings/' . $filePath;
                if (is_file($newFullFile)) {
                    unlink($newFullFile);
                }
            }
            return $this->respondWithError('Gagal memperbarui kliping: ' . $e->getMessage(), 500);
        }
    }

    // ────────────────────────────────────────────────────────────
    //  DELETE — Hapus kliping
    // ────────────────────────────────────────────────────────────
    public function delete($id)
    {
        $db = \Config\Database::connect();

        $existing = $db->table('clippings')->where('id', (int)$id)->get()->getRowArray();
        if (!$existing) {
            return $this->respondWithError('Kliping tidak ditemukan.', 404);
        }

        $clippingTitle = $existing['title'];
        $fileToDelete = $existing['file_path'];

        $db->transBegin();
        try {
            // Relasi kategori terhapus otomatis via ON DELETE CASCADE
            $db->table('clippings')->where('id', (int)$id)->delete();

            $this->logActivity("Menghapus kliping berita: " . $clippingTitle, "Kliping", (int)$id);
            $db->transCommit();

            // Hapus file fisik jika ada
            if ($fileToDelete) {
                $fullFile = ROOTPATH . 'public/uploads/clippings/' . $fileToDelete;
                if (is_file($fullFile)) {
                    unlink($fullFile);
                }
            }

            return $this->respond(['status' => 'success', 'message' => 'Kliping berhasil dihapus.']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->respondWithError('Gagal menghapus kliping: ' . $e->getMessage(), 500);
        }
    }

    // ────────────────────────────────────────────────────────────
    //  HELPER — Sinkronisasi tabel relasi kategori
    // ────────────────────────────────────────────────────────────
    private function syncCategories($db, int $clippingId, array $categoryIds): void
    {
        // Hapus relasi lama terlebih dahulu
        $db->table('clipping_category_rel')->where('clipping_id', $clippingId)->delete();

        if (empty($categoryIds)) return;

        $relData = [];
        foreach (array_unique($categoryIds) as $catId) {
            if ($catId) {
                $relData[] = ['clipping_id' => $clippingId, 'category_id' => (int)$catId];
            }
        }

        if (!empty($relData)) {
            $db->table('clipping_category_rel')->insertBatch($relData);
        }
    }
}
