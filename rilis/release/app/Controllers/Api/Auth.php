<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use App\Libraries\JWT;

class Auth extends BaseApiController
{
    /**
     * Endpoint Login RESTful API
     * POST /api/auth/login
     */
    public function login()
    {
        // 1. Ambil data JSON request dari Axios/Fetch
        $json = $this->request->getJSON(true);
        
        $username = $json['username'] ?? $this->request->getVar('username');
        $password = $json['password'] ?? $this->request->getVar('password');

        // 2. Validasi input sederhana
        if (empty($username) || empty($password)) {
            return $this->respondWithError("Username dan password harus diisi.", 400);
        }

        // 3. Cari user berdasarkan username
        $userModel = new UserModel();
        $user = $userModel->where('username', $username)->first();

        if (!$user) {
            return $this->respondWithError("Username atau password salah.", 401);
        }

        // 4. Periksa apakah akun aktif
        if ($user['status'] !== 'active') {
            return $this->respondWithError("Akun Anda telah dinonaktifkan. Silakan hubungi super administrator.", 403);
        }

        // 5. Validasi password (hashing standard PHP password_verify)
        if (!password_verify($password, $user['password'])) {
            return $this->respondWithError("Username atau password salah.", 401);
        }

        // 6. Ambil permissions dari tabel roles
        $db = \Config\Database::connect();
        $role = $db->table('roles')->where('id', (int)$user['role_id'])->get()->getRowArray();
        $permissions = json_decode($role['permissions'] ?? '[]', true);

        // 7. Buat payload JWT token rahasia (berlaku selama 8 jam)
        $payload = [
            'uid'       => (int) $user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'],
            'role_id'   => (int) $user['role_id'],
            'permissions' => $permissions
        ];

        $token = JWT::generate($payload, $this->jwtSecret, 28800); // 8 jam

        // 8. Berikan respon sukses beserta token
        return $this->respond([
            'status'  => 'success',
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'        => (int) $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'role_id'   => (int) $user['role_id'],
                'permissions' => $permissions
            ]
        ], 200);
    }

    /**
     * Endpoint untuk mengambil profil dan permissions ter-update
     * GET /api/admin/profile
     */
    public function profile()
    {
        $userId = $this->request->decodedToken['uid'] ?? null;
        if (!$userId) {
            return $this->respondWithError('Tidak terautentikasi.', 401);
        }

        $db = \Config\Database::connect();
        $user = $db->table('users u')
            ->select('u.id, u.username, u.full_name, u.role_id, u.status, r.role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', (int)$userId)
            ->get()->getRowArray();

        if (!$user) {
            return $this->respondWithError('User tidak ditemukan.', 404);
        }

        // Pastikan status aktif
        if ($user['status'] !== 'active') {
            return $this->respondWithError('Akun dinonaktifkan.', 403);
        }

        // Ambil permissions terbaru dari role user tersebut
        $role = $db->table('roles')->where('id', (int)$user['role_id'])->get()->getRowArray();
        $permissions = json_decode($role['permissions'] ?? '[]', true);

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'id'          => (int)$user['id'],
                'username'    => $user['username'],
                'full_name'   => $user['full_name'],
                'role_id'     => (int)$user['role_id'],
                'role_name'   => $user['role_name'] ?? '',
                'permissions' => $permissions
            ]
        ]);
    }
}
