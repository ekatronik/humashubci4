<?php

namespace App\Controllers\Api;

use App\Controllers\Api\BaseApiController;

/**
 * API Controller — Dokumentasi Kegiatan (Foto & Video)
 *
 * Endpoints:
 *   GET    /api/admin/documentation          — Daftar (paginated, search, filter)
 *   POST   /api/admin/documentation          — Tambah entri dokumentasi baru
 *   GET    /api/admin/documentation/{id}     — Detail lengkap satu kegiatan
 *   PUT    /api/admin/documentation/{id}     — Update dokumentasi
 *   DELETE /api/admin/documentation/{id}     — Hapus dokumentasi
 */
class Documentation extends BaseApiController
{
    // ────────────────────────────────────────────────────────────
    //  INDEX — Daftar dengan pagination, search, filter
    // ────────────────────────────────────────────────────────────
    public function index()
    {
        $db          = \Config\Database::connect();
        $search      = $this->request->getGet('search') ?? '';
        $categoryId  = $this->request->getGet('category_id') ?? '';
        $locationType = $this->request->getGet('location_type') ?? '';
        $startDate   = $this->request->getGet('start_date') ?? '';
        $endDate     = $this->request->getGet('end_date') ?? '';
        $quarter     = $this->request->getGet('quarter') ?? '';
        $year        = $this->request->getGet('year') ?? date('Y');
        $page        = max(1, (int)($this->request->getGet('page') ?? 1));

        $limitParam = $this->request->getGet('limit');
        if ($limitParam === 'all') {
            $limit = 999999;
        } else {
            $limit = min(100, max(5, (int)($limitParam ?? 10)));
        }

        $offset      = ($page - 1) * $limit;

        $baseQuery = $db->table('documentation d')->select(
            'd.id, d.event_name, d.event_date, d.description, d.news_link,
             d.location_name, d.location_type, d.thumbnail_url,
             d.photo_folder_link, d.video_folder_link, d.creator_name, d.created_at'
        );

        if (!empty($search)) {
            $baseQuery->groupStart()
                ->like('d.event_name', $search)
                ->orLike('d.location_name', $search)
                ->orLike('d.creator_name', $search)
                ->orLike('d.description', $search)
                ->groupEnd();
        }

        if (!empty($locationType)) {
            $baseQuery->where('d.location_type', $locationType);
        }

        if (!empty($categoryId)) {
            $baseQuery->join('documentation_category_rel dcr', 'dcr.documentation_id = d.id', 'inner')
                      ->where('dcr.category_id', (int)$categoryId);
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
            $baseQuery->where('d.event_date >=', $startDate);
        }

        if (!empty($endDate)) {
            $baseQuery->where('d.event_date <=', $endDate);
        }

        $totalItems = (clone $baseQuery)->countAllResults(false);
        $rows       = $baseQuery->limit($limit, $offset)
                                ->orderBy('d.event_date', 'DESC')
                                ->get()->getResultArray();

        // Lampirkan kategori dan peserta pada setiap item
        foreach ($rows as &$row) {
            $row['categories'] = $db->table('documentation_category_rel dcr')
                ->select('cat.id, cat.name')
                ->join('categories cat', 'cat.id = dcr.category_id')
                ->where('dcr.documentation_id', $row['id'])
                ->get()->getResultArray();

            $row['attendees_count'] = $db->table('documentation_attendance')
                ->where('documentation_id', $row['id'])
                ->countAllResults();
        }
        unset($row);

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'items'        => $rows,
                'total_items'  => $totalItems,
                'total_pages'  => (int)ceil($totalItems / $limit),
                'current_page' => $page,
            ]
        ]);
    }

    // ────────────────────────────────────────────────────────────
    //  SHOW — Detail lengkap + daftar peserta
    // ────────────────────────────────────────────────────────────
    public function show($id)
    {
        $db  = \Config\Database::connect();
        $row = $db->table('documentation d')
            ->select('d.*')
            ->where('d.id', (int)$id)
            ->get()->getRowArray();

        if (!$row) {
            return $this->respondWithError('Dokumentasi tidak ditemukan.', 404);
        }

        $row['categories'] = $db->table('documentation_category_rel dcr')
            ->select('cat.id, cat.name')
            ->join('categories cat', 'cat.id = dcr.category_id')
            ->where('dcr.documentation_id', $id)
            ->get()->getResultArray();

        $row['attendees'] = $db->table('documentation_attendance')
            ->where('documentation_id', (int)$id)
            ->get()->getResultArray();

        return $this->respond(['status' => 'success', 'data' => $row]);
    }

    // ────────────────────────────────────────────────────────────
    //  CREATE — Tambah dokumentasi baru
    // ────────────────────────────────────────────────────────────
    public function create()
    {
        $db     = \Config\Database::connect();
        $input  = $this->request->getJSON(true);
        $userId = $this->request->decodedToken['uid'] ?? null;

        $eventName = trim($input['event_name'] ?? '');
        $eventDate = trim($input['event_date'] ?? '');

        if (empty($eventName) || empty($eventDate)) {
            return $this->respondWithError('Nama kegiatan dan tanggal wajib diisi.', 400);
        }

        $db->transBegin();
        try {
            $db->table('documentation')->insert([
                'event_name'        => $eventName,
                'event_date'        => $eventDate,
                'description'       => $input['description'] ?? null,
                'news_link'         => $input['news_link'] ?? null,
                'location_name'     => $input['location_name'] ?? null,
                'location_type'     => $input['location_type'] ?? 'Internal Kampus',
                'thumbnail_url'     => $input['thumbnail_url'] ?? null,
                'photo_folder_link' => $input['photo_folder_link'] ?? null,
                'video_folder_link' => $input['video_folder_link'] ?? null,
                'creator_name'      => $input['creator_name'] ?? null,
                'created_by'        => $userId ? (int)$userId : null,
            ]);
            $newId = $db->insertID();

            $this->syncCategories($db, $newId, $input['category_ids'] ?? []);
            $this->syncAttendees($db, $newId, $input['attendees'] ?? []);

            $this->logActivity("Menambahkan dokumentasi: " . $eventName, "Foto/Video", $newId);
            $db->transCommit();
            return $this->respond([
                'status'  => 'success',
                'message' => 'Dokumentasi kegiatan berhasil disimpan.',
                'data'    => ['id' => $newId]
            ], 201);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->respondWithError('Gagal menyimpan dokumentasi: ' . $e->getMessage(), 500);
        }
    }

    // ────────────────────────────────────────────────────────────
    //  UPDATE — Edit dokumentasi
    // ────────────────────────────────────────────────────────────
    public function update($id)
    {
        $db    = \Config\Database::connect();
        $input = $this->request->getJSON(true);

        if (!$db->table('documentation')->where('id', (int)$id)->countAllResults()) {
            return $this->respondWithError('Dokumentasi tidak ditemukan.', 404);
        }

        $eventName = trim($input['event_name'] ?? '');
        $eventDate = trim($input['event_date'] ?? '');

        if (empty($eventName) || empty($eventDate)) {
            return $this->respondWithError('Nama kegiatan dan tanggal wajib diisi.', 400);
        }

        $db->transBegin();
        try {
            $db->table('documentation')->where('id', (int)$id)->update([
                'event_name'        => $eventName,
                'event_date'        => $eventDate,
                'description'       => $input['description'] ?? null,
                'news_link'         => $input['news_link'] ?? null,
                'location_name'     => $input['location_name'] ?? null,
                'location_type'     => $input['location_type'] ?? 'Internal Kampus',
                'thumbnail_url'     => $input['thumbnail_url'] ?? null,
                'photo_folder_link' => $input['photo_folder_link'] ?? null,
                'video_folder_link' => $input['video_folder_link'] ?? null,
                'creator_name'      => $input['creator_name'] ?? null,
            ]);

            $this->syncCategories($db, (int)$id, $input['category_ids'] ?? []);
            $this->syncAttendees($db, (int)$id, $input['attendees'] ?? []);

            $this->logActivity("Memperbarui dokumentasi: " . $eventName, "Foto/Video", (int)$id);
            $db->transCommit();
            return $this->respond(['status' => 'success', 'message' => 'Dokumentasi berhasil diperbarui.']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->respondWithError('Gagal memperbarui dokumentasi: ' . $e->getMessage(), 500);
        }
    }

    // ────────────────────────────────────────────────────────────
    //  DELETE — Hapus dokumentasi (relasi terhapus via CASCADE)
    // ────────────────────────────────────────────────────────────
    public function delete($id)
    {
        $db = \Config\Database::connect();

        if (!$db->table('documentation')->where('id', (int)$id)->countAllResults()) {
            return $this->respondWithError('Dokumentasi tidak ditemukan.', 404);
        }

        // Ambil data untuk logging
        $doc = $db->table('documentation')->select('event_name')->where('id', (int)$id)->get()->getRowArray();
        $docEventName = $doc ? $doc['event_name'] : '';

        $db->table('documentation')->where('id', (int)$id)->delete();

        $this->logActivity("Menghapus dokumentasi: " . $docEventName, "Foto/Video", (int)$id);

        return $this->respond(['status' => 'success', 'message' => 'Dokumentasi berhasil dihapus.']);
    }

    // ────────────────────────────────────────────────────────────
    //  HELPER — Sinkronisasi kategori
    // ────────────────────────────────────────────────────────────
    private function syncCategories($db, int $docId, array $categoryIds): void
    {
        $db->table('documentation_category_rel')->where('documentation_id', $docId)->delete();
        if (empty($categoryIds)) return;

        $relData = [];
        foreach (array_unique($categoryIds) as $catId) {
            if ($catId) $relData[] = ['documentation_id' => $docId, 'category_id' => (int)$catId];
        }
        if (!empty($relData)) {
            $db->table('documentation_category_rel')->insertBatch($relData);
        }
    }

    // ────────────────────────────────────────────────────────────
    //  HELPER — Sinkronisasi peserta kegiatan
    //  Format attendees: [{ level: 'Rektorat', position: '...', person_name: '...' }]
    // ────────────────────────────────────────────────────────────
    private function syncAttendees($db, int $docId, array $attendees): void
    {
        $db->table('documentation_attendance')->where('documentation_id', $docId)->delete();
        if (empty($attendees)) return;

        $rows = [];
        foreach ($attendees as $a) {
            $name = trim($a['person_name'] ?? '');
            if (empty($name)) continue;
            $rows[] = [
                'documentation_id' => $docId,
                'level'            => $a['level'] ?? 'Rektorat',
                'position'         => $a['position'] ?? '',
                'person_name'      => $name,
            ];
        }
        if (!empty($rows)) {
            $db->table('documentation_attendance')->insertBatch($rows);
        }
    }
}
