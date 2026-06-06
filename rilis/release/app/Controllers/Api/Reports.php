<?php

namespace App\Controllers\Api;

class Reports extends BaseApiController
{
    /**
     * Endpoint RESTful API untuk mengambil data statistik/laporan grafik
     * GET /api/admin/reports?module=news|clippings|documentation[&year=YYYY]
     *
     * Jika year diberikan:
     *   - monthly → semua bulan dalam tahun tersebut
     *   - yearly  → tetap semua tahun (untuk referensi bar chart)
     * Jika year tidak diberikan:
     *   - monthly → 12 bulan terakhir
     */
    public function index()
    {
        $db     = \Config\Database::connect();
        $module = $this->request->getGet('module') ?? 'news';
        $year   = $this->request->getGet('year');   // opsional, format: YYYY

        // Validasi modul
        if (!in_array($module, ['news', 'clippings', 'documentation'])) {
            return $this->failValidationErrors('Parameter module tidak valid.');
        }

        // Validasi tahun (opsional)
        $yearInt = null;
        if ($year !== null) {
            $yearInt = (int) $year;
            if ($yearInt < 1900 || $yearInt > 2100) {
                return $this->failValidationErrors('Parameter year tidak valid.');
            }
        }

        $data = [];

        // ────────────────────────────────────────────────────────────
        //  MODUL: BERITA ONLINE
        // ────────────────────────────────────────────────────────────
        if ($module === 'news') {
            // Query bulanan — kondisional berdasarkan year
            if ($yearInt) {
                $monthlyQuery = "
                    SELECT DATE_FORMAT(news_date, '%Y-%m') AS period, COUNT(*) AS total
                    FROM news_online
                    WHERE YEAR(news_date) = $yearInt
                    GROUP BY period ORDER BY period ASC
                ";
            } else {
                $monthlyQuery = "
                    SELECT DATE_FORMAT(news_date, '%Y-%m') AS period, COUNT(*) AS total
                    FROM news_online
                    GROUP BY period ORDER BY period ASC
                ";
            }

            $data['news'] = [
                'monthly' => $db->query($monthlyQuery)->getResultArray(),

                'yearly' => $db->query("
                    SELECT YEAR(news_date) AS period, COUNT(*) AS total
                    FROM news_online
                    GROUP BY period ORDER BY period ASC
                ")->getResultArray(),

                'categories' => $db->query("
                    SELECT c.name, COUNT(*) AS total
                    FROM news_online_category_rel ncr
                    JOIN categories c ON c.id = ncr.category_id
                    GROUP BY c.name ORDER BY total DESC LIMIT 15
                ")->getResultArray(),

                'media' => $db->query("
                    SELECT m.media_name AS name, COUNT(*) AS total
                    FROM news_online n
                    JOIN media m ON m.id = n.media_id
                    GROUP BY m.media_name ORDER BY total DESC LIMIT 15
                ")->getResultArray(),
            ];
        }

        // ────────────────────────────────────────────────────────────
        //  MODUL: KLIPING MEDIA CETAK
        // ────────────────────────────────────────────────────────────
        if ($module === 'clippings') {
            if ($yearInt) {
                $monthlyQuery = "
                    SELECT DATE_FORMAT(clipping_date, '%Y-%m') AS period, COUNT(*) AS total
                    FROM clippings
                    WHERE YEAR(clipping_date) = $yearInt
                    GROUP BY period ORDER BY period ASC
                ";
            } else {
                $monthlyQuery = "
                    SELECT DATE_FORMAT(clipping_date, '%Y-%m') AS period, COUNT(*) AS total
                    FROM clippings
                    GROUP BY period ORDER BY period ASC
                ";
            }

            $data['clippings'] = [
                'monthly' => $db->query($monthlyQuery)->getResultArray(),

                'yearly' => $db->query("
                    SELECT YEAR(clipping_date) AS period, COUNT(*) AS total
                    FROM clippings
                    GROUP BY period ORDER BY period ASC
                ")->getResultArray(),

                'categories' => $db->query("
                    SELECT c.name, COUNT(*) AS total
                    FROM clipping_category_rel ccr
                    JOIN categories c ON c.id = ccr.category_id
                    GROUP BY c.name ORDER BY total DESC LIMIT 15
                ")->getResultArray(),

                'media' => $db->query("
                    SELECT m.media_name AS name, COUNT(*) AS total
                    FROM clippings cl
                    JOIN media m ON m.id = cl.media_id
                    GROUP BY m.media_name ORDER BY total DESC LIMIT 15
                ")->getResultArray(),
            ];
        }

        // ────────────────────────────────────────────────────────────
        //  MODUL: DOKUMENTASI KEGIATAN
        // ────────────────────────────────────────────────────────────
        if ($module === 'documentation') {
            if ($yearInt) {
                $monthlyQuery = "
                    SELECT DATE_FORMAT(event_date, '%Y-%m') AS period, COUNT(*) AS total
                    FROM documentation
                    WHERE YEAR(event_date) = $yearInt
                    GROUP BY period ORDER BY period ASC
                ";
            } else {
                $monthlyQuery = "
                    SELECT DATE_FORMAT(event_date, '%Y-%m') AS period, COUNT(*) AS total
                    FROM documentation
                    GROUP BY period ORDER BY period ASC
                ";
            }

            $data['documentation'] = [
                'monthly' => $db->query($monthlyQuery)->getResultArray(),

                'yearly' => $db->query("
                    SELECT YEAR(event_date) AS period, COUNT(*) AS total
                    FROM documentation
                    GROUP BY period ORDER BY period ASC
                ")->getResultArray(),

                'categories' => $db->query("
                    SELECT c.name, COUNT(*) AS total
                    FROM documentation_category_rel dcr
                    JOIN categories c ON c.id = dcr.category_id
                    GROUP BY c.name ORDER BY total DESC LIMIT 15
                ")->getResultArray(),

                'locations' => $db->query("
                    SELECT location_type AS name, COUNT(*) AS total
                    FROM documentation
                    WHERE location_type IS NOT NULL
                    GROUP BY location_type ORDER BY total DESC
                ")->getResultArray(),
            ];
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $data,
        ]);
    }
}
