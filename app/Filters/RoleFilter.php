<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Memeriksa hak akses berdasarkan modul yang didefinisikan di routes
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Izinkan request preflight OPTIONS lolos tanpa validasi
        if (strtolower($request->getMethod()) === 'options') {
            return;
        }

        // Token sudah divalidasi oleh JwtAuthFilter sebelumnya
        // dan data user ter-decode disimpan di $request->decodedToken
        $decoded = $request->decodedToken ?? null;
        if (!$decoded) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Token tidak ditemukan atau sesi kadaluarsa.'
                ]);
        }

        $userId = $decoded['uid'] ?? null;
        if (!$userId) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Token tidak valid atau sesi login kedaluwarsa.'
                ]);
        }

        $db = \Config\Database::connect();
        
        // Ambil data user dari database secara langsung untuk mendapatkan role_id ter-update
        $user = $db->table('users')->where('id', (int)$userId)->get()->getRowArray();
        if (!$user) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Pengguna tidak ditemukan.'
                ]);
        }

        if ($user['status'] !== 'active') {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Akun Anda telah dinonaktifkan.'
                ]);
        }

        $roleId = $user['role_id'];
        if (!$roleId) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Tingkat hak akses (role) tidak valid.'
                ]);
        }

        // Muat data role dari database untuk mendapatkan permissions paling baru
        $role = $db->table('roles')->where('id', (int)$roleId)->get()->getRowArray();
        
        if (!$role) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Role tidak ditemukan di database.'
                ]);
        }

        $permissions = json_decode($role['permissions'] ?? '[]', true);

        // Jika filter membutuhkan module (argument pertama)
        if (!empty($arguments) && is_array($arguments)) {
            $requiredModule = $arguments[0];
            
            // Cek apakah user memiliki akses ke modul ini
            if (!in_array($requiredModule, $permissions)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => "Akses Ditolak. Anda tidak memiliki izin untuk modul: '{$requiredModule}'."
                    ]);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi setelah
    }
}
