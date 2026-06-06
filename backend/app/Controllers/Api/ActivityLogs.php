<?php

namespace App\Controllers\Api;

/**
 * API Controller — Log Audit / Audit Trail
 *
 * Endpoints:
 *   GET /api/admin/activity-logs          — Daftar log audit (paginated, search, filter)
 */
class ActivityLogs extends BaseApiController
{
    public function index()
    {
        $db     = \Config\Database::connect();
        $search = $this->request->getGet('search') ?? '';
        $module = $this->request->getGet('module') ?? '';
        $userId = $this->request->getGet('user_id') ?? '';
        $page   = max(1, (int)($this->request->getGet('page') ?? 1));
        $limit  = min(100, max(5, (int)($this->request->getGet('limit') ?? 20)));
        $offset = ($page - 1) * $limit;

        // Query utama
        $baseQuery = $db->table('activity_logs al')
            ->select('al.*, u.full_name as user_fullname, u.username')
            ->join('users u', 'u.id = al.user_id', 'left');

        if (!empty($search)) {
            $baseQuery->groupStart()
                ->like('al.activity', $search)
                ->orLike('al.ip_address', $search)
                ->orLike('u.full_name', $search)
                ->orLike('u.username', $search)
                ->groupEnd();
        }

        if (!empty($module)) {
            $baseQuery->where('al.module', $module);
        }

        if (!empty($userId)) {
            $baseQuery->where('al.user_id', (int)$userId);
        }

        $totalItems = (clone $baseQuery)->countAllResults(false);
        $rows       = $baseQuery->limit($limit, $offset)
                                ->orderBy('al.created_at', 'DESC')
                                ->get()->getResultArray();

        // Ambil data untuk opsi filter (kombinasi unik modul & user)
        $modulesList = $db->table('activity_logs')
            ->select('module')
            ->distinct()
            ->where('module IS NOT NULL')
            ->where('module !=', '')
            ->orderBy('module', 'ASC')
            ->get()->getResultArray();
        
        $usersList = $db->table('activity_logs al')
            ->select('al.user_id, u.full_name, u.username')
            ->join('users u', 'u.id = al.user_id', 'inner')
            ->distinct()
            ->orderBy('u.full_name', 'ASC')
            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'items'        => $rows,
                'total_items'  => $totalItems,
                'total_pages'  => (int)ceil($totalItems / $limit),
                'current_page' => $page,
                'filters'      => [
                    'modules' => array_column($modulesList, 'module'),
                    'users'   => $usersList
                ]
            ]
        ]);
    }
}
