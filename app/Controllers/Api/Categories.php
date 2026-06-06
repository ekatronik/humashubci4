<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;

/**
 * API Controller — Manajemen Kategori (Referensi Data / Taxonomy)
 *
 * Endpoints:
 *   GET    /api/admin/categories         — Daftar semua kategori
 *   POST   /api/admin/categories         — Tambah kategori baru
 *   PUT    /api/admin/categories/{id}    — Edit kategori
 *   DELETE /api/admin/categories/{id}    — Hapus kategori
 */
class Categories extends BaseApiController
{
    /**
     * GET /api/admin/categories
     */
    public function index()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->orderBy('name', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $categories
        ]);
    }

    /**
     * POST /api/admin/categories
     */
    public function create()
    {
        $input = $this->request->getJSON(true);
        $name  = trim($input['name'] ?? '');

        if (empty($name)) {
            return $this->respondWithError('Nama kategori wajib diisi.', 400);
        }

        $categoryModel = new CategoryModel();

        // Cek duplikat
        if ($categoryModel->where('name', $name)->first()) {
            return $this->respondWithError('Kategori dengan nama ini sudah ada.', 409);
        }

        $id = $categoryModel->insert(['name' => $name], true);

        $this->logActivity("Menambahkan kategori: {$name}", 'Referensi Data', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Kategori berhasil ditambahkan.',
            'data'    => ['id' => $id, 'name' => $name]
        ], 201);
    }

    /**
     * PUT /api/admin/categories/{id}
     */
    public function update($id)
    {
        $categoryModel = new CategoryModel();
        $existing      = $categoryModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('Kategori tidak ditemukan.', 404);
        }

        $input = $this->request->getJSON(true);
        $name  = trim($input['name'] ?? '');

        if (empty($name)) {
            return $this->respondWithError('Nama kategori wajib diisi.', 400);
        }

        // Cek duplikat nama selain dirinya sendiri
        $duplicate = $categoryModel->where('name', $name)->where('id !=', (int)$id)->first();
        if ($duplicate) {
            return $this->respondWithError('Kategori dengan nama ini sudah ada.', 409);
        }

        $categoryModel->update((int)$id, ['name' => $name]);

        $this->logActivity("Mengubah nama kategori: {$existing['name']} → {$name}", 'Referensi Data', (int)$id);

        return $this->respond(['status' => 'success', 'message' => 'Kategori berhasil diperbarui.']);
    }

    /**
     * DELETE /api/admin/categories/{id}
     */
    public function delete($id)
    {
        $categoryModel = new CategoryModel();
        $existing      = $categoryModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('Kategori tidak ditemukan.', 404);
        }

        $categoryModel->delete((int)$id);

        $this->logActivity("Menghapus kategori: {$existing['name']}", 'Referensi Data', (int)$id);

        return $this->respond(['status' => 'success', 'message' => 'Kategori berhasil dihapus.']);
    }
}
