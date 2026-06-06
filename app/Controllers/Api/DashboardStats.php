<?php

namespace App\Controllers\Api;

/**
 * API Controller — Statistik Ringkasan Dashboard (Per Modul)
 *
 * Endpoint:
 *   GET /api/admin/dashboard-stats    — Statistik semua modul
 */
class DashboardStats extends BaseApiController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // ── BERITA ONLINE ───────────────────────────────────────
        $totalNews       = $db->table('news_online')->countAllResults();
        $totalRilis      = $db->table('news_online')->where('source_type', 'Rilis Humas')->countAllResults();
        $totalLiputan    = $db->table('news_online')->where('source_type', 'Liputan Wartawan')->countAllResults();
        $latestNews      = $db->table('news_online n')
            ->select('n.id, n.title, n.news_date, n.source_type, m.media_name')
            ->join('media m', 'm.id = n.media_id', 'left')
            ->orderBy('n.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // ── KLIPING BERITA ──────────────────────────────────────
        $totalClippings  = $db->table('clippings')->countAllResults();
        $totalBorrowable = $db->table('clippings')->where('is_borrowable', 1)->countAllResults();
        $latestClippings = $db->table('clippings c')
            ->select('c.id, c.title, c.clipping_date, m.media_name')
            ->join('media m', 'm.id = c.media_id', 'left')
            ->orderBy('c.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // ── DOKUMENTASI ─────────────────────────────────────────
        $totalDocs       = $db->table('documentation')->countAllResults();
        $totalAttendees  = $db->table('documentation_attendance')->countAllResults();
        $totalExternal   = $db->table('documentation')
            ->whereIn('location_type', ['Lokal Daerah', 'Nasional', 'Internasional'])
            ->countAllResults();
        $latestDocs      = $db->table('documentation')
            ->select('id, event_name, event_date, location_type')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'news' => [
                    'total'        => $totalNews,
                    'rilis_humas'  => $totalRilis,
                    'liputan'      => $totalLiputan,
                    'latest'       => $latestNews,
                ],
                'clippings' => [
                    'total'        => $totalClippings,
                    'borrowable'   => $totalBorrowable,
                    'latest'       => $latestClippings,
                ],
                'documentation' => [
                    'total'        => $totalDocs,
                    'attendees'    => $totalAttendees,
                    'external'     => $totalExternal,
                    'latest'       => $latestDocs,
                ],
            ]
        ]);
    }
}
