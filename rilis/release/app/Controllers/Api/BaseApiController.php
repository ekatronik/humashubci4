<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseApiController extends Controller
{
    /**
     * @var string Kunci rahasia untuk tanda tangan JWT
     */
    protected $jwtSecret = 'HumasHubSecretKey2026@XYZ!';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Panggil initController induk
        parent::initController($request, $response, $logger);

        // Atur header CORS untuk mengizinkan request dari browser lokal (Vite port 5173 dll)
        $this->response->setHeader("Access-Control-Allow-Origin", "*");
        $this->response->setHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS, PUT, DELETE");
        $this->response->setHeader("Access-Control-Allow-Headers", "Content-Type, Authorization, X-Requested-With, X-API-KEY");
        $this->response->setHeader("Content-Type", "application/json; charset=UTF-8");

        // Tangani preflight OPTIONS request
        if ($this->request->getMethod() === 'options') {
            $this->response->setStatusCode(200)->send();
            exit();
        }
    }

    /**
     * Mengirimkan respon JSON standar
     */
    protected function respond(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response->setStatusCode($statusCode)->setJSON($data);
    }

    /**
     * Mengirimkan respon error JSON standar
     */
    protected function respondWithError(string $message, int $statusCode = 400): ResponseInterface
    {
        return $this->respond([
            'status'  => 'error',
            'message' => $message
        ], $statusCode);
    }

    /**
     * Mencatat log aktivitas admin ke database
     */
    protected function logActivity(string $activity, string $module, ?int $targetId = null): void
    {
        $userId = $this->request->decodedToken['uid'] ?? null;
        if (!$userId) return;

        $db = \Config\Database::connect();
        $db->table('activity_logs')->insert([
            'user_id'    => (int)$userId,
            'activity'   => $activity,
            'module'     => $module,
            'target_id'  => $targetId,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
        ]);
    }
}
