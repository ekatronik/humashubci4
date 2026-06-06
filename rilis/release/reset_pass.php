<?php
/**
 * Humas Hub - Password Reset Tool v2
 * Upload ke: public/reset_pass.php
 * Akses via: https://humas.ar-raniry.ac.id/reset_pass.php
 * HAPUS FILE INI SETELAH SELESAI!
 */
error_reporting(0);
ini_set('display_errors', 0);

$message = '';
$messageType = '';
$dbStatus = 'not_tested';
$userList = [];

// Ambil input DB dari form atau dari .env
$envPath = __DIR__ . '/.env';
$dbDefaults = ['host' => 'localhost', 'user' => '', 'pass' => '', 'name' => ''];

if (file_exists($envPath)) {
    $lines = explode("\n", file_get_contents($envPath));
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, 'database.default.hostname') !== false)
            $dbDefaults['host'] = trim(explode('=', $line, 2)[1] ?? 'localhost');
        if (strpos($line, 'database.default.username') !== false)
            $dbDefaults['user'] = trim(explode('=', $line, 2)[1] ?? '');
        if (strpos($line, 'database.default.password') !== false)
            $dbDefaults['pass'] = trim(explode('=', $line, 2)[1] ?? '');
        if (strpos($line, 'database.default.database') !== false)
            $dbDefaults['name'] = trim(explode('=', $line, 2)[1] ?? '');
    }
}

// Gunakan POST jika ada, atau fallback ke .env
$dbHost = $_POST['db_host'] ?? $dbDefaults['host'];
$dbUser = $_POST['db_user'] ?? $dbDefaults['user'];
$dbPass = $_POST['db_pass'] ?? $dbDefaults['pass'];
$dbName = $_POST['db_name'] ?? $dbDefaults['name'];

// Coba koneksi untuk tampilkan info user
if (!empty($dbHost) && !empty($dbUser) && !empty($dbName)) {
    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser, $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
        );
        $dbStatus = 'connected';
        $userList = $pdo->query("SELECT id, username, full_name, role_id, status FROM users")->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        $dbStatus = 'error: ' . $e->getMessage();
    }
}

// Proses reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset') {
    $newPassword  = $_POST['new_password'] ?? '';
    $confirmPass  = $_POST['confirm_password'] ?? '';
    $secretKey    = $_POST['secret_key'] ?? '';
    $targetUser   = trim($_POST['username'] ?? 'admin');

    if ($secretKey !== 'HumasHub2025!') {
        $message = 'Kunci rahasia salah!';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password minimal 6 karakter.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPass) {
        $message = 'Konfirmasi password tidak cocok.';
        $messageType = 'error';
    } elseif ($dbStatus !== 'connected') {
        $message = 'Tidak dapat terhubung ke database. Periksa kredensial database di atas.';
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$targetUser]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // User tidak ada, buat baru sebagai Super Admin
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $ins = $pdo->prepare("INSERT INTO users (username, password, full_name, role_id, status) VALUES (?, ?, 'Super Admin', 1, 'active')");
                $ins->execute([$targetUser, $hash]);
                $message = "✅ User '{$targetUser}' berhasil dibuat dengan password baru. Silakan login sekarang!";
                $messageType = 'success';
            } else {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $upd = $pdo->prepare("UPDATE users SET password = ?, status = 'active' WHERE username = ?");
                $upd->execute([$hash, $targetUser]);
                $message = "✅ Password '{$targetUser}' berhasil direset! Silakan login sekarang.";
                $messageType = 'success';
            }
            // Refresh user list
            $userList = $pdo->query("SELECT id, username, full_name, role_id, status FROM users")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — Humas Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #1e293b; min-height: 100vh; display: flex; justify-content: center; padding: 40px 20px; }
        .card { width: 100%; max-width: 600px; background: white; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 8px 25px rgba(0,0,0,0.05); overflow: hidden; height: fit-content; }
        .header { background: linear-gradient(135deg, #dc2626, #991b1b); color: white; padding: 28px 32px; }
        .header h1 { font-size: 20px; font-weight: 800; }
        .header p { font-size: 13px; opacity: 0.85; margin-top: 4px; }
        .body { padding: 28px 32px; }
        .section-title { font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }
        .db-section { margin-bottom: 24px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group-full { grid-column: span 2; }
        label { font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        input { height: 42px; border: 1.5px solid #e2e8f0; border-radius: 9px; padding: 10px 13px; font-family: inherit; font-size: 14px; font-weight: 500; outline: none; transition: all 0.2s; }
        input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .status-ok { color: #059669; font-weight: 600; font-size: 13px; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; }
        .status-err { color: #dc2626; font-weight: 600; font-size: 13px; background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; }
        .btn-check { height: 40px; background: #3b82f6; color: white; border: none; border-radius: 9px; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; padding: 0 18px; }
        .divider { border: none; border-top: 1px solid #f1f5f9; margin: 24px 0; }
        .alert { padding: 14px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px; font-weight: 500; }
        .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #d1fae5; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }
        .btn-reset { width: 100%; height: 46px; background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; border: none; border-radius: 10px; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 8px; }
        .btn-reset:hover { opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
        th { background: #f8fafc; padding: 8px 10px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; }
        td { padding: 8px 10px; border-top: 1px solid #f8fafc; }
        .warning { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 16px; font-size: 13px; color: #92400e; margin-top: 20px; }
        small { color: #64748b; font-size: 12px; margin-top: 3px; display: block; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>🔑 Reset Password Admin</h1>
        <p>Humas Hub — Alat Pemulihan Akses Database</p>
    </div>
    <div class="body">

        <!-- BAGIAN 1: Konfigurasi Database -->
        <form method="POST">
        <div class="db-section">
            <div class="section-title">① Konfigurasi Koneksi Database</div>
            <?php if ($dbStatus === 'connected'): ?>
                <div class="status-ok">✓ Terhubung ke database: <strong><?= htmlspecialchars($dbName) ?></strong> pada <strong><?= htmlspecialchars($dbHost) ?></strong></div>
                <?php if (!empty($userList)): ?>
                    <div class="section-title" style="margin-top:12px">Daftar User Saat Ini</div>
                    <table>
                        <tr><th>ID</th><th>Username</th><th>Nama</th><th>Role</th><th>Status</th></tr>
                        <?php foreach ($userList as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= $u['role_id'] == 1 ? '👑 Super Admin' : ($u['role_id'] == 2 ? 'Pranata' : 'Operator') ?></td>
                            <td><?= $u['status'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p style="font-size:13px;color:#f59e0b">⚠️ Tabel users kosong! Tidak ada user di database.</p>
                <?php endif; ?>
            <?php elseif ($dbStatus !== 'not_tested'): ?>
                <div class="status-err">✗ <?= htmlspecialchars($dbStatus) ?></div>
            <?php endif; ?>

            <div class="form-grid" style="margin-top:12px">
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="<?= htmlspecialchars($dbHost) ?>" placeholder="localhost">
                </div>
                <div class="form-group">
                    <label>Nama Database</label>
                    <input type="text" name="db_name" value="<?= htmlspecialchars($dbName) ?>" placeholder="nama_database">
                </div>
                <div class="form-group">
                    <label>Username Database</label>
                    <input type="text" name="db_user" value="<?= htmlspecialchars($dbUser) ?>" placeholder="db_username">
                </div>
                <div class="form-group">
                    <label>Password Database</label>
                    <input type="password" name="db_pass" placeholder="password database">
                </div>
            </div>
            <button type="submit" class="btn-check">🔌 Cek Koneksi Database</button>
        </div>

        <hr class="divider">

        <!-- BAGIAN 2: Reset Password -->
        <div class="section-title">② Reset Password Admin</div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <input type="hidden" name="action" value="reset">
        <div class="form-grid">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="admin">
            </div>
            <div class="form-group">
                <label>Kunci Rahasia</label>
                <input type="text" name="secret_key" placeholder="HumasHub2025!">
                <small>Kunci: <strong>HumasHub2025!</strong></small>
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="new_password" placeholder="Min. 6 karakter">
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm_password" placeholder="Ulangi password">
            </div>
        </div>
        <button type="submit" class="btn-reset">🔑 Reset Password Sekarang</button>
        </form>

        <div class="warning">
            ⚠️ <strong>PENTING:</strong> Segera hapus file <code>reset_pass.php</code> dari server setelah selesai digunakan!
        </div>
    </div>
</div>
</body>
</html>
