<?php

namespace App\Controllers\Api;

use App\Models\AppMenuModel;

/**
 * API Controller — Master Menu (App Launcher)
 *
 * Endpoints Admin (JWT Protected):
 *   GET    /api/admin/menus              — Daftar semua menu (termasuk non-aktif)
 *   POST   /api/admin/menus              — Tambah menu baru
 *   PUT    /api/admin/menus/{id}         — Edit menu
 *   DELETE /api/admin/menus/{id}         — Hapus menu
 *   POST   /api/admin/menus/reorder      — Simpan urutan drag & drop
 */
class AppMenu extends BaseApiController
{
    /**
     * GET /api/admin/menus
     * Daftar semua menu untuk panel admin
     */
    public function index()
    {
        $menuModel = new AppMenuModel();
        $menus = $menuModel->orderBy('sort_order', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $menus
        ]);
    }

    /**
     * POST /api/admin/menus
     * Tambah menu baru
     */
    public function create()
    {
        $input = $this->request->getJSON(true);
        $name  = trim($input['name'] ?? '');
        $url   = trim($input['url'] ?? '');
        $icon  = trim($input['icon'] ?? '🔗');

        if (empty($name)) {
            return $this->respondWithError('Nama menu wajib diisi.', 400);
        }

        if (empty($url)) {
            return $this->respondWithError('Link/URL menu wajib diisi.', 400);
        }

        $menuModel = new AppMenuModel();

        // Hitung sort_order terbesar + 1
        $maxSort = $menuModel->selectMax('sort_order', 'max_sort')->first();
        $nextSort = ($maxSort['max_sort'] ?? 0) + 1;

        $id = $menuModel->insert([
            'name'       => $name,
            'url'        => $url,
            'icon'       => $icon,
            'sort_order' => $nextSort,
            'is_active'  => (int)($input['is_active'] ?? 1),
        ], true);

        $this->logActivity("Menambahkan menu aplikasi: {$name}", 'Master Menu', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Menu berhasil ditambahkan.',
            'data'    => $menuModel->find($id)
        ], 201);
    }

    /**
     * PUT /api/admin/menus/{id}
     * Edit menu
     */
    public function update($id)
    {
        $menuModel = new AppMenuModel();
        $existing  = $menuModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('Menu tidak ditemukan.', 404);
        }

        $input = $this->request->getJSON(true);
        $name  = trim($input['name'] ?? '');
        $url   = trim($input['url'] ?? '');
        $icon  = trim($input['icon'] ?? $existing['icon']);

        if (empty($name)) {
            return $this->respondWithError('Nama menu wajib diisi.', 400);
        }

        if (empty($url)) {
            return $this->respondWithError('Link/URL menu wajib diisi.', 400);
        }

        $updateData = [
            'name'      => $name,
            'url'       => $url,
            'icon'      => $icon,
            'is_active' => (int)($input['is_active'] ?? $existing['is_active']),
        ];

        $menuModel->update((int)$id, $updateData);

        $this->logActivity("Mengubah menu aplikasi: {$existing['name']} → {$name}", 'Master Menu', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Menu berhasil diperbarui.',
            'data'    => $menuModel->find((int)$id)
        ]);
    }

    /**
     * DELETE /api/admin/menus/{id}
     * Hapus menu
     */
    public function delete($id)
    {
        $menuModel = new AppMenuModel();
        $existing  = $menuModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('Menu tidak ditemukan.', 404);
        }

        $menuModel->delete((int)$id);

        $this->logActivity("Menghapus menu aplikasi: {$existing['name']}", 'Master Menu', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Menu berhasil dihapus.'
        ]);
    }

    /**
     * POST /api/admin/menus/reorder
     * Simpan urutan drag & drop
     * Body: { "order": [3, 1, 4, 2] } — array of menu IDs in new order
     */
    public function reorder()
    {
        $input = $this->request->getJSON(true);
        $order = $input['order'] ?? [];

        if (empty($order) || !is_array($order)) {
            return $this->respondWithError('Data urutan tidak valid.', 400);
        }

        $menuModel = new AppMenuModel();
        foreach ($order as $index => $menuId) {
            $menuModel->update((int)$menuId, ['sort_order' => $index + 1]);
        }

        $this->logActivity("Mengubah urutan menu aplikasi", 'Master Menu');

        return $this->respond([
            'status'  => 'success',
            'message' => 'Urutan menu berhasil disimpan.'
        ]);
    }
}
