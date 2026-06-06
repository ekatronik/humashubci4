<?php
/**
 * Humas Hub Installation Wizard
 * File: install.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$lockFile = __DIR__ . '/install.lock';

// 1. Cek apakah instalasi sudah terkunci
if (file_exists($lockFile)) {
    showErrorPage("Sistem Sudah Terpasang", "Aplikasi Humas Hub sudah terpasang di server ini. Demi alasan keamanan, silakan hapus berkas <code>public/install.php</code> dari server Anda.");
    exit();
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// 2. Kebutuhan Sistem
$requirements = [
    'php_version' => [
        'name' => 'PHP Version >= 8.1',
        'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
        'current' => PHP_VERSION
    ],
    'mysqli' => [
        'name' => 'MySQLi Extension',
        'status' => extension_loaded('mysqli'),
        'current' => extension_loaded('mysqli') ? 'Loaded' : 'Not Loaded'
    ],
    'pdo' => [
        'name' => 'PDO Extension',
        'status' => extension_loaded('pdo'),
        'current' => extension_loaded('pdo') ? 'Loaded' : 'Not Loaded'
    ],
    'mbstring' => [
        'name' => 'Mbstring Extension',
        'status' => extension_loaded('mbstring'),
        'current' => extension_loaded('mbstring') ? 'Loaded' : 'Not Loaded'
    ],
    'intl' => [
        'name' => 'Intl Extension',
        'status' => extension_loaded('intl'),
        'current' => extension_loaded('intl') ? 'Loaded' : 'Not Loaded'
    ],
    'env_writable' => [
        'name' => '.env File Writable',
        'status' => is_writable(__DIR__ . '/') || is_writable(__DIR__ . '/.env'),
        'current' => (is_writable(__DIR__ . '/') || is_writable(__DIR__ . '/.env')) ? 'Writable' : 'Not Writable'
    ],
    'writable_dir' => [
        'name' => 'writable/ Directory Writable',
        'status' => is_writable(__DIR__ . '/writable'),
        'current' => is_writable(__DIR__ . '/writable') ? 'Writable' : 'Not Writable'
    ]
];

$requirementsPassed = true;
foreach ($requirements as $req) {
    if (!$req['status']) {
        $requirementsPassed = false;
    }
}

// 3. Proses POST Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Konfigurasi Database & Admin
        $db_host = trim($_POST['db_host'] ?? 'localhost');
        $db_user = trim($_POST['db_user'] ?? '');
        $db_pass = $_POST['db_pass'] ?? '';
        $db_name = trim($_POST['db_name'] ?? '');
        
        $admin_user = trim($_POST['admin_user'] ?? 'admin');
        $admin_pass = $_POST['admin_pass'] ?? '';
        $admin_name = trim($_POST['admin_name'] ?? 'Super Admin');
        $app_url    = trim($_POST['app_url'] ?? '');

        // Validasi input
        if (empty($db_name) || empty($admin_user) || empty($admin_pass) || empty($admin_name)) {
            $error = "Semua kolom wajib diisi kecuali password database.";
        } else {
            // Coba koneksi database
            try {
                $mysqli = @new mysqli($db_host, $db_user, $db_pass);
                if ($mysqli->connect_error) {
                    $error = "Gagal menyambung ke server database: " . $mysqli->connect_error;
                } else {
                    // Buat database jika belum ada
                    $db_name_safe = $mysqli->real_escape_string($db_name);
                    if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$db_name_safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
                        $error = "Gagal membuat database: " . $mysqli->error;
                    } else {
                        $mysqli->select_db($db_name);
                        $mysqli->close();

                        // Tulis berkas .env
                        $envPath = __DIR__ . '/.env';
                        $envContent = "";
                        
                        if (file_exists($envPath)) {
                            $envContent = file_get_contents($envPath);
                        } else {
                            $envContent = file_get_contents(__DIR__ . '/env'); // fallback template
                        }

                        // Replace / Append database credentials & base url
                        $replacements = [
                            '/CI_ENVIRONMENT\s*=\s*\w+/' => 'CI_ENVIRONMENT = production',
                            '/app\.baseURL\s*=\s*\'[^\']*\'/' => "app.baseURL = '{$app_url}'",
                            '/database\.default\.hostname\s*=\s*\S*/' => "database.default.hostname = {$db_host}",
                            '/database\.default\.database\s*=\s*\S*/' => "database.default.database = {$db_name}",
                            '/database\.default\.username\s*=\s*\S*/' => "database.default.username = {$db_user}",
                            '/database\.default\.password\s*=\s*\S*/' => "database.default.password = {$db_pass}",
                        ];

                        foreach ($replacements as $pattern => $replacement) {
                            if (preg_match($pattern, $envContent)) {
                                $envContent = preg_replace($pattern, $replacement, $envContent);
                            } else {
                                // append
                                $envContent .= "\n" . str_replace('\\', '', $replacement);
                            }
                        }

                        if (file_put_contents($envPath, $envContent) === false) {
                            $error = "Gagal menulis file konfigurasi .env. Pastikan hak akses file sesuai.";
                        } else {
                            // Paksa env vars ke proses PHP saat ini agar CI4 BaseConfig
                            // dapat membacanya dengan benar saat bootstrap parsial.
                            // DotEnv::load() tidak selalu meng-override $_ENV yang sudah ada.
                            $envVarsToSet = [
                                'CI_ENVIRONMENT'                => 'production',
                                'app.baseURL'                   => $app_url,
                                'database.default.hostname'     => $db_host,
                                'database.default.database'     => $db_name,
                                'database.default.username'     => $db_user,
                                'database.default.password'     => $db_pass,
                                'database.default.DBDriver'     => 'MySQLi',
                                'database.default.DBPrefix'     => '',
                                'database.default.port'         => 3306,
                            ];
                            foreach ($envVarsToSet as $key => $value) {
                                $_ENV[$key]    = $value;
                                $_SERVER[$key] = $value;
                                putenv("{$key}={$value}");
                            }

                            // Jalankan Migrasi & Seeding melalui CI Bootstrap
                            try {
                                // Bootstrap CodeIgniter 4 Environment
                                define('FCPATH', __DIR__ . '/');
                                require __DIR__ . '/app/Config/Paths.php';
                                $paths = new Config\Paths();
                                
                                define('APPPATH', realpath(rtrim($paths->appDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
                                define('ROOTPATH', realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
                                define('SYSTEMPATH', realpath(rtrim($paths->systemDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
                                define('WRITEPATH', realpath(rtrim($paths->writableDirectory, '\\/ ')) . DIRECTORY_SEPARATOR);
                                
                                require_once APPPATH . 'Config/Constants.php';
                                require_once SYSTEMPATH . 'Config/DotEnv.php';
                                (new \CodeIgniter\Config\DotEnv(APPPATH . '../'))->load();
                                
                                if (! defined('ENVIRONMENT')) {
                                    define('ENVIRONMENT', 'production');
                                }
                                
                                if (is_file(APPPATH . 'Config/Boot/' . ENVIRONMENT . '.php')) {
                                    require_once APPPATH . 'Config/Boot/' . ENVIRONMENT . '.php';
                                }
                                
                                require_once SYSTEMPATH . 'Common.php';
                                require_once SYSTEMPATH . 'Config/AutoloadConfig.php';
                                require_once APPPATH . 'Config/Autoload.php';
                                require_once SYSTEMPATH . 'Modules/Modules.php';
                                require_once APPPATH . 'Config/Modules.php';
                                require_once SYSTEMPATH . 'Autoloader/Autoloader.php';
                                require_once SYSTEMPATH . 'Config/BaseService.php';
                                require_once SYSTEMPATH . 'Config/Services.php';
                                require_once APPPATH . 'Config/Services.php';
                                
                                \Config\Services::autoloader()->initialize(new \Config\Autoload(), new \Config\Modules())->register();
                                
                                // 1. Jalankan Migrasi database ke versi terbaru
                                $migrations = \Config\Services::migrations();
                                $migrations->latest();
                                
                                // 2. Jalankan Seeder default
                                $db = \Config\Database::connect();
                                
                                // Seed Roles
                                $roles = [
                                    ['id' => 1, 'role_name' => 'Super Admin'],
                                    ['id' => 2, 'role_name' => 'Pranata Humas'],
                                    ['id' => 3, 'role_name' => 'Operator'],
                                ];
                                foreach ($roles as $role) {
                                    $exists = $db->table('roles')->where('id', $role['id'])->countAllResults();
                                    if (!$exists) {
                                        $db->table('roles')->insert($role);
                                    }
                                }

                                // Seed Categories
                                $categories = [
                                    ['id' => 1, 'name' => 'Akademik'],
                                    ['id' => 2, 'name' => 'Kemahasiswaan'],
                                    ['id' => 3, 'name' => 'Kerjasama & Event'],
                                    ['id' => 4, 'name' => 'Riset & Pengabdian'],
                                ];
                                foreach ($categories as $cat) {
                                    $exists = $db->table('categories')->where('id', $cat['id'])->countAllResults();
                                    if (!$exists) {
                                        $db->table('categories')->insert($cat);
                                    }
                                }

                                // Seed Media
                                $media = [
                                    ['id' => 1, 'media_name' => 'Serambi Indonesia', 'media_type' => 'cetak'],
                                    ['id' => 2, 'media_name' => 'Kanal Humas UIN', 'media_type' => 'online'],
                                    ['id' => 3, 'media_name' => 'Detik.com', 'media_type' => 'online'],
                                ];
                                foreach ($media as $m) {
                                    $exists = $db->table('media')->where('id', $m['id'])->countAllResults();
                                    if (!$exists) {
                                        $db->table('media')->insert($m);
                                    }
                                }

                                // Insert Admin User yang diinput oleh user
                                $adminUser = [
                                    'username'  => $admin_user,
                                    'password'  => password_hash($admin_pass, PASSWORD_BCRYPT),
                                    'full_name' => $admin_name,
                                    'role_id'   => 1,
                                    'status'    => 'active',
                                ];
                                
                                // Bersihkan admin lama dari database untuk mencegah duplikasi user_id
                                $db->table('users')->where('role_id', 1)->delete();
                                $db->table('users')->insert($adminUser);

                                // 3. Tulis file lock
                                file_put_contents($lockFile, date('Y-m-d H:i:s'));
                                
                                // Redirect ke sukses
                                header("Location: install.php?step=3");
                                exit();
                                
                            } catch (\Throwable $e) {
                                $error = "Proses setup database / migrasi gagal: " . $e->getMessage() . "<br>Detail: " . $e->getFile() . " baris " . $e->getLine();
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                $error = "Gagal menyambung ke server database: " . $e->getMessage();
            }
        }
    }
}

// Deteksi otomatis baseURL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('/install.php', '', $_SERVER['SCRIPT_NAME']);
$autoBaseUrl = $protocol . $domainName . $scriptName . '/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Humas Hub — Wizard Instalasi Aplikasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --success: #10b981;
            --error: #ef4444;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .install-container {
            width: 100%;
            max-width: 680px;
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-banner {
            background: linear-gradient(135deg, var(--primary), #9333ea);
            color: white;
            padding: 40px 32px;
            text-align: center;
            position: relative;
        }

        .header-banner h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .header-banner p {
            font-size: 14px;
            opacity: 0.85;
            font-weight: 500;
        }

        .step-progress {
            display: flex;
            justify-content: space-around;
            background: #f1f5f9;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step-item.active {
            color: var(--primary);
        }

        .step-number {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #cbd5e1;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }

        .step-item.active .step-number {
            background: var(--primary);
        }

        .content-body {
            padding: 32px;
        }

        h2 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
            padding-left: 10px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 13.5px;
            font-weight: 500;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fee2e2;
        }

        .alert-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #d1fae5;
        }

        /* Checkbox lists */
        .req-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 28px;
        }

        .req-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 13.5px;
        }

        .req-name {
            font-weight: 600;
            color: #334155;
        }

        .badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .badge-ok {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-fail {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Form styling */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-group-full {
            grid-column: span 2;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            height: 46px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .actions-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 32px;
            border-top: 1px solid var(--border);
            padding-top: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 48px;
            padding: 0 28px;
            border-radius: 12px;
            border: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:disabled {
            background: #cbd5e1;
            color: #94a3b8;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .success-box {
            text-align: center;
            padding: 20px 0;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #d1fae5;
            color: var(--success);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .success-icon svg {
            width: 32px;
            height: 32px;
        }

        .success-box h3 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .success-box p {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 32px;
        }
    </style>
</head>
<body>

<div class="install-container">
    <div class="header-banner">
        <h1>Humas Hub</h1>
        <p>Sistem Management &amp; Kliping Berita UIN Ar-Raniry</p>
    </div>

    <div class="step-progress">
        <div class="step-item <?= $step === 1 ? 'active' : '' ?>">
            <span class="step-number">1</span> Cek Server
        </div>
        <div class="step-item <?= $step === 2 ? 'active' : '' ?>">
            <span class="step-number">2</span> Konfigurasi
        </div>
        <div class="step-item <?= $step === 3 ? 'active' : '' ?>">
            <span class="step-number">3</span> Selesai
        </div>
    </div>

    <div class="content-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <strong>Error: </strong><?= $error ?>
            </div>
        <?php endif; ?>

        <!-- STEP 1: CEK PERSYARATAN -->
        <?php if ($step === 1): ?>
            <h2>Persyaratan Server</h2>
            <div class="req-list">
                <?php foreach ($requirements as $req): ?>
                    <div class="req-item">
                        <span class="req-name"><?= $req['name'] ?></span>
                        <span class="badge <?= $req['status'] ? 'badge-ok' : 'badge-fail' ?>">
                            <?= $req['status'] ? 'OK' : 'Error' ?> (<?= $req['current'] ?>)
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$requirementsPassed): ?>
                <div class="alert alert-error">
                    Beberapa persyaratan server belum terpenuhi. Harap lengkapi persyaratan sebelum melanjutkan instalasi.
                </div>
            <?php endif; ?>

            <div class="actions-row">
                <a href="install.php?step=2" class="btn btn-primary" <?= !$requirementsPassed ? 'disabled style="pointer-events: none;"' : '' ?>>
                    Lanjutkan ke Konfigurasi
                </a>
            </div>

        <!-- STEP 2: FORM SETUP -->
        <?php elseif ($step === 2): ?>
            <form method="POST">
                <h2>Konfigurasi Database</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label for="db_name">Nama Database</label>
                        <input type="text" id="db_name" name="db_name" value="humashub" required>
                    </div>
                    <div class="form-group">
                        <label for="db_user">Username Database</label>
                        <input type="text" id="db_user" name="db_user" value="root" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Password Database</label>
                        <input type="password" id="db_pass" name="db_pass" placeholder="Biarkan kosong jika localhost default">
                    </div>
                    <div class="form-group form-group-full">
                        <label for="app_url">Base URL Aplikasi (Backend)</label>
                        <input type="text" id="app_url" name="app_url" value="<?= htmlspecialchars($autoBaseUrl) ?>" required>
                    </div>
                </div>

                <h2>Konfigurasi Akun Super Admin</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="admin_user">Username Admin</label>
                        <input type="text" id="admin_user" name="admin_user" value="admin" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_name">Nama Lengkap</label>
                        <input type="text" id="admin_name" name="admin_name" value="Administrator Humas" required>
                    </div>
                    <div class="form-group form-group-full">
                        <label for="admin_pass">Password Admin</label>
                        <input type="password" id="admin_pass" name="admin_pass" placeholder="Buat password yang kuat" required>
                    </div>
                </div>

                <div class="actions-row">
                    <button type="submit" class="btn btn-primary">Pasang Sistem Sekarang</button>
                </div>
            </form>

        <!-- STEP 3: SUKSES -->
        <?php elseif ($step === 3): ?>
            <div class="success-box">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                <h3>Instalasi Sukses!</h3>
                <p>Database Humas Hub berhasil dikonfigurasi, struktur tabel dimigrasikan, dan akun admin baru Anda telah siap digunakan.</p>
                <div class="alert alert-error" style="text-align: left;">
                    <strong>PENTING:</strong> Demi alasan keamanan, segeralah hapus berkas <code>public/install.php</code> dari server Anda agar instalasi tidak dapat dipicu ulang oleh pihak luar.
                </div>
                <a href="index.html" class="btn btn-primary" style="margin-top: 10px;">Buka Halaman Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php
function showErrorPage($title, $message) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title><?= $title ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; padding: 40px 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
            .error-card { max-width: 500px; background: white; border-radius: 20px; border: 1px solid #e2e8f0; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.02); text-align: center; }
            .error-icon { width: 54px; height: 54px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 24px; font-weight: bold; }
            h1 { font-size: 20px; font-weight: 800; margin-bottom: 12px; }
            p { font-size: 14px; color: #64748b; line-height: 1.6; }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">!</div>
            <h1><?= $title ?></h1>
            <p><?= $message ?></p>
        </div>
    </body>
    </html>
    <?php
}
?>
