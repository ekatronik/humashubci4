<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JWT;

class JwtAuthFilter implements FilterInterface
{
    /**
     * Kunci rahasia JWT yang sama dengan BaseApiController
     */
    private $jwtSecret = 'HumasHubSecretKey2026@XYZ!';

    public function before(RequestInterface $request, $arguments = null)
    {
        // Izinkan OPTIONS request dilewati (CORS preflight)
        if (strtolower($request->getMethod()) === 'options') {
            return;
        }

        $authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? $request->getServer('redirect_HTTP_AUTHORIZATION') ?? '';
        
        if (empty($authHeader)) {
            // Ambil dari header manual jika Apache memotong Authorization header
            $headers = $request->headers();
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization']->getValue() : '';
        }

        if (empty($authHeader)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Token autentikasi tidak ditemukan. Kirimkan Authorization Header: Bearer <TOKEN>'
                ]);
        }

        // Format token: "Bearer eyJhbG..."
        $token = str_replace('Bearer ', '', $authHeader);

        $decoded = JWT::decode($token, $this->jwtSecret);

        if (!$decoded) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Sesi login telah berakhir atau token tidak valid. Silakan login kembali.'
                ]);
        }

        // Lampirkan data user ke request agar bisa diakses oleh controller
        $request->decodedToken = $decoded;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi setelah request
    }
}
