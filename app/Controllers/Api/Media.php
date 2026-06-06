<?php

namespace App\Controllers\Api;

use App\Models\MediaModel;

/**
 * API Controller — Manajemen Media (Referensi Data / Taxonomy)
 *
 * Endpoints:
 *   GET    /api/admin/media         — Daftar semua media
 *   POST   /api/admin/media         — Tambah media baru
 *   PUT    /api/admin/media/{id}    — Edit media
 *   DELETE /api/admin/media/{id}    — Hapus media
 */
class Media extends BaseApiController
{
    /**
     * GET /api/admin/media
     */
    public function index()
    {
        $mediaModel = new MediaModel();

        $type = $this->request->getGet('type');
        if (!empty($type)) {
            $mediaModel->where('media_type', $type);
        }

        $media = $mediaModel->orderBy('media_name', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $media
        ]);
    }

    /**
     * POST /api/admin/media
     */
    public function create()
    {
        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (!empty($input)) {
            $mediaName  = trim($input['media_name'] ?? '');
            $mediaType  = $input['media_type'] ?? 'cetak';
            $mediaScale = $input['media_scale'] ?? null;
        } else {
            $mediaName  = trim($this->request->getPost('media_name') ?? '');
            $mediaType  = $this->request->getPost('media_type') ?? 'cetak';
            $mediaScale = $this->request->getPost('media_scale') ?? null;
        }

        if (empty($mediaName)) {
            return $this->respondWithError('Nama media wajib diisi.', 400);
        }

        if (!in_array($mediaType, ['cetak', 'online'])) {
            return $this->respondWithError('Tipe media harus berupa "cetak" atau "online".', 400);
        }

        $mediaModel = new MediaModel();

        // Cek duplikat nama
        if ($mediaModel->where('media_name', $mediaName)->first()) {
            return $this->respondWithError('Media dengan nama ini sudah ada.', 409);
        }

        // Handle File Upload Logo
        $file = $this->request->getFile('logo');
        $logoName = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = ROOTPATH . 'public/uploads/media/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            $logoName = $file->getRandomName();
            $file->move($uploadPath, $logoName);
        }

        $id = $mediaModel->insert([
            'media_name'  => $mediaName,
            'media_type'  => $mediaType,
            'media_scale' => $mediaScale,
            'media_logo'  => $logoName,
        ], true);

        $this->logActivity("Menambahkan media: {$mediaName}", 'Referensi Data', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Media berhasil ditambahkan.',
            'data'    => [
                'id'          => $id, 
                'media_name'  => $mediaName, 
                'media_type'  => $mediaType,
                'media_scale' => $mediaScale,
                'media_logo'  => $logoName
            ]
        ], 201);
    }

    /**
     * PUT /api/admin/media/{id}
     */
    public function update($id)
    {
        $mediaModel = new MediaModel();
        $existing   = $mediaModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('Media tidak ditemukan.', 404);
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (!empty($input)) {
            $mediaName  = trim($input['media_name'] ?? '');
            $mediaType  = $input['media_type'] ?? $existing['media_type'];
            $mediaScale = $input['media_scale'] ?? $existing['media_scale'];
            $clearLogo  = false;
        } else {
            $mediaName  = trim($this->request->getPost('media_name') ?? '');
            $mediaType  = $this->request->getPost('media_type') ?? $existing['media_type'];
            $mediaScale = $this->request->getPost('media_scale') ?? $existing['media_scale'];
            $clearLogo  = $this->request->getPost('clear_logo') === '1';
        }

        if (empty($mediaName)) {
            return $this->respondWithError('Nama media wajib diisi.', 400);
        }

        // Cek duplikat selain dirinya sendiri
        $duplicate = $mediaModel->where('media_name', $mediaName)->where('id !=', (int)$id)->first();
        if ($duplicate) {
            return $this->respondWithError('Media dengan nama ini sudah ada.', 409);
        }

        // Handle File Upload Logo
        $file = $this->request->getFile('logo');
        $logoName = $existing['media_logo'];
        $oldFileToDelete = null;

        if ($clearLogo) {
            $oldFileToDelete = $existing['media_logo'];
            $logoName = null;
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = ROOTPATH . 'public/uploads/media/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            if ($existing['media_logo']) {
                $oldFileToDelete = $existing['media_logo'];
            }
            $logoName = $newName;
        }

        $mediaModel->update((int)$id, [
            'media_name'  => $mediaName,
            'media_type'  => $mediaType,
            'media_scale' => $mediaScale,
            'media_logo'  => $logoName,
        ]);

        if ($oldFileToDelete) {
            $oldFullFile = ROOTPATH . 'public/uploads/media/' . $oldFileToDelete;
            if (is_file($oldFullFile)) {
                unlink($oldFullFile);
            }
        }

        $this->logActivity("Mengubah media: {$existing['media_name']} → {$mediaName}", 'Referensi Data', (int)$id);

        return $this->respond(['status' => 'success', 'message' => 'Media berhasil diperbarui.']);
    }

    /**
     * DELETE /api/admin/media/{id}
     */
    public function delete($id)
    {
        $mediaModel = new MediaModel();
        $existing   = $mediaModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('Media tidak ditemukan.', 404);
        }

        $mediaModel->delete((int)$id);

        if ($existing['media_logo']) {
            $fullFile = ROOTPATH . 'public/uploads/media/' . $existing['media_logo'];
            if (is_file($fullFile)) {
                unlink($fullFile);
            }
        }

        $this->logActivity("Menghapus media: {$existing['media_name']}", 'Referensi Data', (int)$id);

        return $this->respond(['status' => 'success', 'message' => 'Media berhasil dihapus.']);
    }
}
