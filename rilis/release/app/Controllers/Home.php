<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return $this->serveFrontend();
    }

    public function serveFrontend()
    {
        // Load URL helper to ensure base_url() and current_url() are available
        helper('url');

        $indexFile = FCPATH . 'index.html';
        if (!file_exists($indexFile)) {
            return '<h1>Frontend tidak ditemukan</h1><p>Pastikan index.html ada di folder public/</p>';
        }

        $html = file_get_contents($indexFile);

        // Default SEO metadata
        $title = "Pusat Pencarian Data & Informasi Kampus";
        $description = "Portal informasi dan data terpadu UIN Ar-Raniry Banda Aceh. Akses berita online, arsip kliping pers, dan dokumentasi kegiatan.";
        $image = '';

        // Get the route path relative to baseURL
        $cleanPath = $this->request->getPath();
        $cleanPath = trim($cleanPath, '/');

        $db = \Config\Database::connect();

        if ($cleanPath === 'berita') {
            $title = "Portal Berita & Rilis Humas";
            $description = "Kumpulan berita resmi, press release, dan rilis humas terbaru UIN Ar-Raniry Banda Aceh.";
        } elseif ($cleanPath === 'kliping') {
            $title = "Arsip Kliping Koran & Pers";
            $description = "Dokumentasi kliping berita media cetak seputar UIN Ar-Raniry dari berbagai media pers nasional dan lokal.";
        } elseif ($cleanPath === 'dokumentasi') {
            $title = "Pusat Dokumentasi Kegiatan Rektorat";
            $description = "Galeri foto dan dokumentasi video serta daftar kehadiran tokoh pada kegiatan rektorat UIN Ar-Raniry.";
        } elseif ($cleanPath === 'rss') {
            $title = "Sindikasi Warta Kampus (RSS)";
            $description = "Feed sindikasi berita dan warta terhangat seputar aktivitas akademik UIN Ar-Raniry.";
        } elseif ($cleanPath === 'pencarian') {
            $q = $this->request->getVar('q') ?? '';
            if ($q) {
                $title = "Hasil Pencarian: \"" . $q . "\"";
                $description = "Hasil pencarian portal data terpadu UIN Ar-Raniry untuk kata kunci: " . $q;
            } else {
                $title = "Pencarian Global";
                $description = "Cari berita online, arsip kliping pers, dan dokumentasi kegiatan UIN Ar-Raniry.";
            }
        } elseif (preg_match('/^kliping\/(\d+)/', $cleanPath, $matches)) {
            $id = $matches[1];
            try {
                $clipping = $db->table('clippings c')
                    ->select('c.title, c.summary, m.media_logo')
                    ->join('media m', 'c.media_id = m.id', 'left')
                    ->where('c.id', $id)
                    ->get()->getRow();
                if ($clipping) {
                    $title = $clipping->title;
                    $description = $clipping->summary ?: "Detail arsip kliping koran UIN Ar-Raniry.";
                    if ($clipping->media_logo) {
                        $image = base_url("uploads/media/" . $clipping->media_logo);
                    }
                }
            } catch (\Exception $e) {
                // Ignore DB errors and fallback
            }
        } elseif (preg_match('/^dokumentasi\/(\d+)/', $cleanPath, $matches)) {
            $id = $matches[1];
            try {
                $doc = $db->table('documentation')
                    ->select('event_name, description, thumbnail_url')
                    ->where('id', $id)
                    ->get()->getRow();
                if ($doc) {
                    $title = $doc->event_name;
                    $description = $doc->description ?: "Rincian dokumentasi event UIN Ar-Raniry.";
                    if ($doc->thumbnail_url) {
                        $image = $this->formatImageUrl($doc->thumbnail_url);
                    }
                }
            } catch (\Exception $e) {
                // Ignore DB errors and fallback
            }
        }

        // Terapkan meta tag injection ke HTML
        $html = $this->injectMetaTags($html, $title, $description, $image);

        return $this->response->setBody($html);
    }

    private function formatImageUrl($url)
    {
        if (empty($url)) return '';
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return "https://lh3.googleusercontent.com/d/" . $matches[1] . "=w1200";
        }
        return $url;
    }

    private function injectMetaTags($html, $title, $description, $image = '')
    {
        // 1. Ganti title tag
        $html = preg_replace('/<title>.*?<\/title>/i', "<title>" . htmlspecialchars($title) . " | UIN Ar-Raniry</title>", $html);

        // 2. Buat kumpulan meta tags baru
        $metaTags = [
            'description'         => $description,
            'og:title'            => $title . " | UIN Ar-Raniry",
            'og:description'      => $description,
            'og:type'             => 'website',
            'og:url'              => current_url(),
            'twitter:card'        => 'summary_large_image',
            'twitter:title'       => $title . " | UIN Ar-Raniry",
            'twitter:description' => $description,
        ];

        if (!empty($image)) {
            $metaTags['og:image'] = $image;
            $metaTags['twitter:image'] = $image;
        }

        $metaHtml = '';
        foreach ($metaTags as $name => $value) {
            if (empty($value)) continue;
            $valEscaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            if (str_starts_with($name, 'og:')) {
                $metaHtml .= "\n    <meta property=\"{$name}\" content=\"{$valEscaped}\" />";
            } else {
                $metaHtml .= "\n    <meta name=\"{$name}\" content=\"{$valEscaped}\" />";
            }
        }

        // 3. Bersihkan meta description bawaan jika ada
        $html = preg_replace('/<meta\s+name=["\']description["\']\s+content=["\'].*?["\']\s*\/?>/i', '', $html);

        // 4. Sisipkan meta tags tepat sebelum </head>
        return str_ireplace('</head>', $metaHtml . "\n  </head>", $html);
    }
}
