<?php

namespace App\Controllers\Api;

use App\Models\UserModel;

/**
 * API Controller — Manajemen User
 *
 * Endpoints:
 *   GET    /api/admin/users         — Daftar semua user
 *   GET    /api/admin/roles         — Daftar semua level/role
 *   POST   /api/admin/users         — Tambah user baru
 *   PUT    /api/admin/users/{id}    — Edit user
 *   POST   /api/admin/users/{id}    — Edit user (compatibility)
 */
class Users extends BaseApiController
{
    /**
     * GET /api/admin/users
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $users = $db->table('users u')
            ->select('u.id, u.username, u.full_name, u.role_id, u.status, u.created_at, r.role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->orderBy('r.id', 'ASC')
            ->orderBy('u.full_name', 'ASC')
            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => $users
        ]);
    }

    /**
     * GET /api/admin/roles
     */
    public function roles()
    {
        $db = \Config\Database::connect();
        $roles = $db->table('roles')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        foreach ($roles as &$role) {
            $role['permissions'] = json_decode($role['permissions'] ?? '[]', true);
        }
        unset($role);

        return $this->respond([
            'status' => 'success',
            'data'   => $roles
        ]);
    }

    /**
     * POST /api/admin/users
     */
    public function create()
    {
        $db = \Config\Database::connect();
        $currentUserRoleId = $this->request->decodedToken['role_id'] ?? null;
        $currentRoleName = '';
        if ($currentUserRoleId) {
            $currentRole = $db->table('roles')->where('id', $currentUserRoleId)->get()->getRowArray();
            $currentRoleName = $currentRole['role_name'] ?? '';
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }

        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $fullName = trim($input['full_name'] ?? '');
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $roleId   = $input['role_id'] ?? null;
        $status   = $input['status'] ?? 'active';

        if (empty($fullName) || empty($username) || empty($password) || empty($roleId)) {
            return $this->respondWithError('Semua field wajib diisi.', 400);
        }

        // Check duplicate username
        $userModel = new UserModel();
        if ($userModel->where('username', $username)->first()) {
            return $this->respondWithError("Username '{$username}' sudah digunakan oleh akun lain.", 409);
        }

        // Restriction Check
        $targetRole = $db->table('roles')->where('id', (int)$roleId)->get()->getRowArray();
        $targetRoleName = $targetRole['role_name'] ?? '';
        if ($currentRoleName === 'Pranata Humas' && ($targetRoleName === 'Super Admin' || $targetRoleName === 'Pranata Humas')) {
            return $this->respondWithError('Gagal! Anda tidak memiliki izin untuk mengelola level ini.', 403);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $id = $userModel->insert([
            'full_name' => $fullName,
            'username'  => $username,
            'password'  => $hashedPassword,
            'role_id'   => (int)$roleId,
            'status'    => $status,
        ], true);

        $this->logActivity("Menambah user baru: {$username}", 'Manajemen User', $id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'User berhasil ditambahkan.',
            'data'    => ['id' => $id, 'username' => $username, 'full_name' => $fullName]
        ], 201);
    }

    /**
     * PUT /api/admin/users/{id}
     */
    public function update($id)
    {
        $db = \Config\Database::connect();
        $currentUserRoleId = $this->request->decodedToken['role_id'] ?? null;
        $currentRoleName = '';
        if ($currentUserRoleId) {
            $currentRole = $db->table('roles')->where('id', $currentUserRoleId)->get()->getRowArray();
            $currentRoleName = $currentRole['role_name'] ?? '';
        }

        $userModel = new UserModel();
        $existing = $userModel->find((int)$id);

        if (!$existing) {
            return $this->respondWithError('User tidak ditemukan.', 404);
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }

        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $fullName = trim($input['full_name'] ?? '');
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $roleId   = $input['role_id'] ?? null;
        $status   = $input['status'] ?? 'active';

        if (empty($fullName) || empty($username) || empty($roleId)) {
            return $this->respondWithError('Nama lengkap, username, dan level wajib diisi.', 400);
        }

        // Check duplicate username
        $duplicate = $userModel->where('username', $username)->where('id !=', (int)$id)->first();
        if ($duplicate) {
            return $this->respondWithError("Username '{$username}' sudah digunakan oleh akun lain.", 409);
        }

        // Restriction Check on existing role
        if ($currentRoleName === 'Pranata Humas') {
            $existingRole = $db->table('roles')->where('id', $existing['role_id'])->get()->getRowArray();
            $existingRoleName = $existingRole['role_name'] ?? '';
            if ($existingRoleName === 'Super Admin' || $existingRoleName === 'Pranata Humas') {
                return $this->respondWithError('Gagal! Anda tidak memiliki izin untuk mengelola level ini.', 403);
            }

            // Restriction Check on target role
            $targetRole = $db->table('roles')->where('id', (int)$roleId)->get()->getRowArray();
            $targetRoleName = $targetRole['role_name'] ?? '';
            if ($targetRoleName === 'Super Admin' || $targetRoleName === 'Pranata Humas') {
                return $this->respondWithError('Gagal! Anda tidak memiliki izin untuk mengelola level ini.', 403);
            }
        }

        $updateData = [
            'full_name' => $fullName,
            'username'  => $username,
            'role_id'   => (int)$roleId,
            'status'    => $status,
        ];

        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->update((int)$id, $updateData);

        $this->logActivity("Memperbarui user: {$username}", 'Manajemen User', (int)$id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'User berhasil diperbarui.'
        ]);
    }

    /**
     * PUT /api/admin/roles/{id}/permissions
     */
    public function updateRolePermissions($id)
    {
        $db = \Config\Database::connect();
        $role = $db->table('roles')->where('id', (int)$id)->get()->getRowArray();
        
        if (!$role) {
            return $this->respondWithError('Role tidak ditemukan.', 404);
        }

        // Jangan izinkan mengubah permissions Super Admin (id: 5 atau role_name: 'Super Admin') demi keamanan sistem
        if ($role['role_name'] === 'Super Admin' || (int)$id === 5) {
            return $this->respondWithError('Tidak diperbolehkan mengubah hak akses Super Admin.', 403);
        }

        $input = $this->request->getJSON(true);
        $permissions = $input['permissions'] ?? null;

        if ($permissions === null || !is_array($permissions)) {
            return $this->respondWithError('Data permissions tidak valid.', 400);
        }

        // Bersihkan data dan simpan
        $cleanPermissions = array_values(array_unique(array_map('trim', $permissions)));

        $db->table('roles')->where('id', (int)$id)->update([
            'permissions' => json_encode($cleanPermissions)
        ]);

        $this->logActivity("Memperbarui hak akses untuk role: {$role['role_name']}", 'Manajemen User');

        return $this->respond([
            'status'  => 'success',
            'message' => 'Hak akses role berhasil diperbarui.',
            'data'    => [
                'id'          => (int)$id,
                'role_name'   => $role['role_name'],
                'permissions' => $cleanPermissions
            ]
        ]);
    }
}
