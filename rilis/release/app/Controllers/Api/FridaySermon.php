<?php

namespace App\Controllers\Api;

use App\Models\FridaySermonModel;

/**
 * API Controller — Manajemen Jadwal Khutbah Jumat
 */
class FridaySermon extends BaseApiController
{
    /**
     * GET /api/admin/khutbah/sermons
     */
    public function listSermons()
    {
        $sermonModel = new FridaySermonModel();
        $mosqueType  = $this->request->getGet('mosque_type') ?: 'fathun_qarib';
        $search      = $this->request->getGet('search');

        $builder = $sermonModel->where('mosque_type', $mosqueType);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('khatib', $search)
                ->orLike('mosque_name', $search)
                ->orLike('imam', $search)
                ->orLike('muazzin', $search)
                ->groupEnd();
        }

        $sermons = $builder->orderBy('date', 'DESC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $sermons
        ]);
    }

    /**
     * POST /api/admin/khutbah/sermons
     */
    public function createSermon()
    {
        $sermonModel = new FridaySermonModel();

        $input = $this->request->getJSON(true) ?? [];
        if (empty($input)) {
            $input = $this->request->getPost() ?? [];
        }

        $mosqueType = $input['mosque_type'] ?? 'fathun_qarib';
        $mosqueName = $mosqueType === 'fathun_qarib' ? 'Masjid Fathun Qarib UIN Ar-Raniry' : trim($input['mosque_name'] ?? '');
        $date       = $input['date'] ?? null;
        $khatib     = trim($input['khatib'] ?? '');
        $imam       = $mosqueType === 'fathun_qarib' ? trim($input['imam'] ?? '') : null;
        $muazzin    = $mosqueType === 'fathun_qarib' ? trim($input['muazzin'] ?? '') : null;

        if (empty($date)) {
            return $this->respondWithError('Tanggal wajib diisi.', 400);
        }

        // Validate Friday
        $dayOfWeek = date('w', strtotime($date));
        if ($dayOfWeek != 5) {
            return $this->respondWithError('Tanggal harus jatuh pada hari Jumat.', 400);
        }

        if (empty($khatib)) {
            return $this->respondWithError('Nama Khatib wajib diisi.', 400);
        }

        if ($mosqueType === 'other' && empty($mosqueName)) {
            return $this->respondWithError('Nama Masjid wajib diisi.', 400);
        }

        // Duplicate check
        $dup = $sermonModel->where('date', $date)
                           ->where('mosque_type', $mosqueType)
                           ->where('mosque_name', $mosqueName)
                           ->first();
        if ($dup) {
            return $this->respondWithError('Jadwal untuk masjid ini pada tanggal tersebut sudah ada.', 409);
        }

        $id = $sermonModel->insert([
            'mosque_type' => $mosqueType,
            'mosque_name' => $mosqueName,
            'date'        => $date,
            'khatib'      => $khatib,
            'imam'        => $imam ?: null,
            'muazzin'     => $muazzin ?: null
        ], true);

        $this->logActivity("Menambahkan jadwal khutbah di {$mosqueName} tanggal {$date}", 'Khutbah Jumat', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Jadwal khutbah berhasil ditambahkan.',
            'data'    => $sermonModel->find($id)
        ], 201);
    }

    /**
     * POST /api/admin/khutbah/sermons/bulk
     */
    public function bulkCreateSermons()
    {
        $input = $this->request->getJSON(true) ?? [];
        if (empty($input)) {
            $input = $this->request->getPost() ?? [];
        }

        $mosqueName   = trim($input['mosque_name'] ?? '');
        $schedulesRaw = $input['schedules'] ?? null;

        $schedules = [];
        if (!empty($schedulesRaw)) {
            if (is_array($schedulesRaw)) {
                $schedules = $schedulesRaw;
            } else {
                $schedules = json_decode($schedulesRaw, true) ?? [];
            }
        }

        if (empty($mosqueName)) {
            return $this->respondWithError('Nama Masjid wajib diisi.', 400);
        }
        if (empty($schedules)) {
            return $this->respondWithError('Jadwal khatib wajib diisi.', 400);
        }

        $sermonModel = new FridaySermonModel();
        $insertedCount = 0;
        $updatedCount = 0;

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            foreach ($schedules as $sched) {
                $date   = trim($sched['date'] ?? '');
                $khatib = trim($sched['khatib'] ?? '');

                if (empty($date) || empty($khatib)) {
                    continue;
                }

                // Validate Friday
                $dayOfWeek = date('w', strtotime($date));
                if ($dayOfWeek != 5) {
                    $db->transRollback();
                    return $this->respondWithError("Tanggal {$date} harus jatuh pada hari Jumat.", 400);
                }

                // Duplicate check
                $dup = $sermonModel->where('date', $date)
                                   ->where('mosque_type', 'other')
                                   ->where('mosque_name', $mosqueName)
                                   ->first();
                if ($dup) {
                    // Overwrite/update existing
                    $sermonModel->update($dup['id'], ['khatib' => $khatib]);
                    $updatedCount++;
                    continue;
                }

                $sermonModel->insert([
                    'mosque_type' => 'other',
                    'mosque_name' => $mosqueName,
                    'date'        => $date,
                    'khatib'      => $khatib,
                    'imam'        => null,
                    'muazzin'     => null
                ]);
                $insertedCount++;
            }
            $db->transCommit();

            $this->logActivity("Melakukan input masal jadwal khutbah di {$mosqueName}. Berhasil: {$insertedCount}, Diperbarui: {$updatedCount}", 'Khutbah Jumat');

            return $this->respond([
                'status'  => 'success',
                'message' => "Bulk insert berhasil. {$insertedCount} jadwal ditambahkan, {$updatedCount} jadwal diperbarui.",
                'data'    => [
                    'inserted' => $insertedCount,
                    'updated'  => $updatedCount
                ]
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->respondWithError("Terjadi kesalahan: " . $e->getMessage(), 500);
        }
    }

    /**
     * POST/PUT /api/admin/khutbah/sermons/(:num)
     */
    public function updateSermon($id)
    {
        $sermonModel = new FridaySermonModel();
        $sermon = $sermonModel->find((int)$id);
        if (!$sermon) {
            return $this->respondWithError('Jadwal tidak ditemukan.', 404);
        }

        $input = $this->request->getJSON(true) ?? [];
        if (empty($input)) {
            $input = $this->request->getPost() ?? [];
        }

        $date       = $input['date'] ?? $sermon['date'];
        $khatib     = isset($input['khatib']) ? trim($input['khatib']) : $sermon['khatib'];
        $imam       = $sermon['mosque_type'] === 'fathun_qarib' ? (isset($input['imam']) ? trim($input['imam']) : $sermon['imam']) : null;
        $muazzin    = $sermon['mosque_type'] === 'fathun_qarib' ? (isset($input['muazzin']) ? trim($input['muazzin']) : $sermon['muazzin']) : null;
        $mosqueName = $sermon['mosque_type'] === 'fathun_qarib' ? $sermon['mosque_name'] : (isset($input['mosque_name']) ? trim($input['mosque_name']) : $sermon['mosque_name']);

        if (empty($date)) {
            return $this->respondWithError('Tanggal wajib diisi.', 400);
        }

        // Validate Friday
        $dayOfWeek = date('w', strtotime($date));
        if ($dayOfWeek != 5) {
            return $this->respondWithError('Tanggal harus jatuh pada hari Jumat.', 400);
        }

        if (empty($khatib)) {
            return $this->respondWithError('Nama Khatib wajib diisi.', 400);
        }

        if ($sermon['mosque_type'] === 'other' && empty($mosqueName)) {
            return $this->respondWithError('Nama Masjid wajib diisi.', 400);
        }

        // Duplicate check
        $dup = $sermonModel->where('date', $date)
                           ->where('mosque_type', $sermon['mosque_type'])
                           ->where('mosque_name', $mosqueName)
                           ->where('id !=', (int)$id)
                           ->first();
        if ($dup) {
            return $this->respondWithError('Jadwal untuk masjid ini pada tanggal tersebut sudah ada.', 409);
        }

        $sermonModel->update((int)$id, [
            'date'        => $date,
            'khatib'      => $khatib,
            'imam'        => $imam ?: null,
            'muazzin'     => $muazzin ?: null,
            'mosque_name' => $mosqueName
        ]);

        $this->logActivity("Memperbarui jadwal khutbah di {$mosqueName} tanggal {$date}", 'Khutbah Jumat', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Jadwal khutbah berhasil diperbarui.',
            'data'    => $sermonModel->find((int)$id)
        ]);
    }

    /**
     * DELETE /api/admin/khutbah/sermons/(:num)
     */
    public function deleteSermon($id)
    {
        $sermonModel = new FridaySermonModel();
        $sermon = $sermonModel->find((int)$id);
        if (!$sermon) {
            return $this->respondWithError('Jadwal tidak ditemukan.', 404);
        }

        $sermonModel->delete((int)$id);
        $this->logActivity("Menghapus jadwal khutbah di {$sermon['mosque_name']} tanggal {$sermon['date']}", 'Khutbah Jumat', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Jadwal khutbah berhasil dihapus.'
        ]);
    }
}
