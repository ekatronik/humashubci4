<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Sajikan langsung konten index.html tanpa redirect
// (menghindari ketergantungan pada app.baseURL di .env)
$routes->get('/', 'Home::serveFrontend');
$routes->get('berita', 'Home::serveFrontend');
$routes->get('kliping', 'Home::serveFrontend');
$routes->get('kliping/(:any)', 'Home::serveFrontend');
$routes->get('dokumentasi', 'Home::serveFrontend');
$routes->get('dokumentasi/(:any)', 'Home::serveFrontend');
$routes->get('rss', 'Home::serveFrontend');
$routes->get('pencarian', 'Home::serveFrontend');
$routes->get('login', 'Home::serveFrontend');
$routes->get('dashboard', 'Home::serveFrontend');
$routes->get('dashboard/(:any)', 'Home::serveFrontend');

// ── RESTFUL API ROUTING ──────────────────────────────────────
$routes->group('api', function($routes) {
    
    // 1. Endpoint Autentikasi (Public)
    $routes->post('auth/login', 'Api\Auth::login');
    $routes->options('auth/login', 'Api\Auth::login'); // Penanganan CORS preflight OPTIONS

    // 1b. Endpoint Portal Publik (Public Data Hub)
    $routes->group('public', function($routes) {
        $routes->get('search', 'Api\PublicController::search');
        $routes->get('news', 'Api\PublicController::news');
        $routes->get('categories', 'Api\PublicController::categories');
        $routes->get('media', 'Api\PublicController::media');
        $routes->get('news-etalase', 'Api\PublicController::newsEtalase');
        $routes->get('news_etalase', 'Api\PublicController::newsEtalase');
        $routes->get('media-stats', 'Api\PublicController::mediaStats');
        $routes->get('clippings', 'Api\PublicController::clippings');
        $routes->get('clippings/(:num)', 'Api\PublicController::clippingDetail/$1');
        $routes->get('documentation', 'Api\PublicController::documentation');
        $routes->get('documentation/(:num)', 'Api\PublicController::documentationDetail/$1');
        $routes->get('rss-items', 'Api\PublicController::rssItems');
        $routes->get('stats', 'Api\PublicController::stats');
        $routes->get('stats-visual', 'Api\PublicController::statsVisual');
        $routes->get('khatib-schedule', 'Api\PublicController::khatibSchedule');
    });

    // 2. Endpoint Terproteksi JWT (Admin)
    $routes->group('admin', ['filter' => 'jwt'], function($routes) {
        
        // ── Endpoint Uji Coba Token JWT ──────────────────────────
        $routes->get('ping', function() {
            return service('response')->setJSON([
                'status'  => 'success',
                'message' => 'Token JWT Anda valid!',
                'user'    => service('request')->decodedToken
            ]);
        });
        
        // ── Modul Berita Online (CRUD + Import CSV) ──────────────
        $routes->get('news', 'Api\News::index', ['filter' => ['jwt', 'role:news']]);
        $routes->post('news', 'Api\News::create', ['filter' => ['jwt', 'role:news']]);
        $routes->post('news/import', 'Api\News::importCsv', ['filter' => ['jwt', 'role:news']]);   // Import massal via CSV
        $routes->put('news/(:num)', 'Api\News::update/$1', ['filter' => ['jwt', 'role:news']]);
        $routes->delete('news/(:num)', 'Api\News::delete/$1', ['filter' => ['jwt', 'role:news']]);

        // ── Modul Kliping Surat Kabar (CRUD) ─────────────────────
        $routes->get('clippings', 'Api\Clippings::index', ['filter' => ['jwt', 'role:clippings']]);
        $routes->post('clippings', 'Api\Clippings::create', ['filter' => ['jwt', 'role:clippings']]);
        $routes->get('clippings/(:num)', 'Api\Clippings::show/$1', ['filter' => ['jwt', 'role:clippings']]);
        $routes->put('clippings/(:num)', 'Api\Clippings::update/$1', ['filter' => ['jwt', 'role:clippings']]);
        $routes->post('clippings/(:num)', 'Api\Clippings::update/$1', ['filter' => ['jwt', 'role:clippings']]); // Guna parsing multipart file upload PHP
        $routes->delete('clippings/(:num)', 'Api\Clippings::delete/$1', ['filter' => ['jwt', 'role:clippings']]);

        // ── Modul Dokumentasi Kegiatan (CRUD) ────────────────────
        $routes->get('documentation', 'Api\Documentation::index', ['filter' => ['jwt', 'role:documentation']]);
        $routes->post('documentation', 'Api\Documentation::create', ['filter' => ['jwt', 'role:documentation']]);
        $routes->get('documentation/(:num)', 'Api\Documentation::show/$1', ['filter' => ['jwt', 'role:documentation']]);
        $routes->put('documentation/(:num)', 'Api\Documentation::update/$1', ['filter' => ['jwt', 'role:documentation']]);
        $routes->delete('documentation/(:num)', 'Api\Documentation::delete/$1', ['filter' => ['jwt', 'role:documentation']]);

        // ── Log Audit / Activity Logs ────────────────────────────
        $routes->get('activity-logs', 'Api\ActivityLogs::index', ['filter' => ['jwt', 'role:activity']]);

        // ── Statistik Dashboard Per-Modul ────────────────────────
        $routes->get('dashboard-stats', 'Api\DashboardStats::index');

        // ── Laporan & Analitik ──────────────────────────────────
        $routes->get('reports', 'Api\Reports::index');

        // ── Profil Pengguna (Dinamis) ───────────────────────────
        $routes->get('profile', 'Api\Auth::profile');

        // ── Referensi Data / Taxonomy — Kategori ─────────────────
        $routes->get('categories', 'Api\Categories::index');
        $routes->post('categories', 'Api\Categories::create', ['filter' => ['jwt', 'role:taxonomy']]);
        $routes->put('categories/(:num)', 'Api\Categories::update/$1', ['filter' => ['jwt', 'role:taxonomy']]);
        $routes->delete('categories/(:num)', 'Api\Categories::delete/$1', ['filter' => ['jwt', 'role:taxonomy']]);

        // ── Referensi Data / Taxonomy — Media ────────────────────
        $routes->get('media', 'Api\Media::index');
        $routes->post('media', 'Api\Media::create', ['filter' => ['jwt', 'role:taxonomy']]);
        $routes->put('media/(:num)', 'Api\Media::update/$1', ['filter' => ['jwt', 'role:taxonomy']]);
        $routes->post('media/(:num)', 'Api\Media::update/$1', ['filter' => ['jwt', 'role:taxonomy']]); // Guna parsing multipart file upload PHP
        $routes->delete('media/(:num)', 'Api\Media::delete/$1', ['filter' => ['jwt', 'role:taxonomy']]);

        // ── Sindikasi Berita RSS ─────────────────────────────────
        $routes->get('rss/sources', 'Api\RssFeed::getSources', ['filter' => ['jwt', 'role:rss']]);
        $routes->post('rss/sources', 'Api\RssFeed::createSource', ['filter' => ['jwt', 'role:rss']]);
        $routes->put('rss/sources/(:num)', 'Api\RssFeed::updateSource/$1', ['filter' => ['jwt', 'role:rss']]);
        $routes->delete('rss/sources/(:num)', 'Api\RssFeed::deleteSource/$1', ['filter' => ['jwt', 'role:rss']]);
        $routes->get('rss/settings', 'Api\RssFeed::getSettings', ['filter' => ['jwt', 'role:rss']]);
        $routes->post('rss/settings', 'Api\RssFeed::updateSettings', ['filter' => ['jwt', 'role:rss']]);
        $routes->post('rss/sync', 'Api\RssFeed::manualSync', ['filter' => ['jwt', 'role:rss']]);
        $routes->get('rss/items', 'Api\RssFeed::getItems', ['filter' => ['jwt', 'role:rss']]);

        // ── Manajemen & Update Sistem ────────────────────────────
        $routes->get('system/version', 'Api\System::getVersion');
        $routes->get('system/backup-db', 'Api\System::backupDb', ['filter' => ['jwt', 'role:system']]);
        $routes->get('system/backup-files', 'Api\System::backupFiles', ['filter' => ['jwt', 'role:system']]);
        $routes->post('system/update-zip', 'Api\System::updateZip', ['filter' => ['jwt', 'role:system']]);

        // ── Manajemen User (CRUD) ────────────────────────────────
        $routes->get('users', 'Api\Users::index', ['filter' => ['jwt', 'role:users']]);
        $routes->get('roles', 'Api\Users::roles', ['filter' => ['jwt', 'role:users']]);
        $routes->post('users', 'Api\Users::create', ['filter' => ['jwt', 'role:users']]);
        $routes->put('users/(:num)', 'Api\Users::update/$1', ['filter' => ['jwt', 'role:users']]);
        $routes->post('users/(:num)', 'Api\Users::update/$1', ['filter' => ['jwt', 'role:users']]);
        $routes->put('roles/(:num)/permissions', 'Api\Users::updateRolePermissions/$1', ['filter' => ['jwt', 'role:users']]);
    });
});

