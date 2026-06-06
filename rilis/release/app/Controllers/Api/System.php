<?php

namespace App\Controllers\Api;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * API Controller — Manajemen Sistem (Update & Backup Wizard)
 */
class System extends BaseApiController
{
    /**
     * GET /api/admin/system/version
     * Membaca informasi versi server saat ini
     */
    public function getVersion()
    {
        $versionData = $this->getLocalVersion();
        
        return $this->respond([
            'status' => 'success',
            'data'   => $versionData
        ]);
    }

    /**
     * GET /api/admin/system/backup-db
     * Melakukan backup database MySQL dalam bentuk file SQL terkompresi ZIP
     */
    public function backupDb()
    {
        $db = \Config\Database::connect();
        
        try {
            $tables = $db->listTables();
            if (empty($tables)) {
                return $this->respondWithError("Tidak ada tabel ditemukan di database.", 500);
            }

            $sql = "-- Humas Hub Database Backup\n";
            $sql .= "-- Domain: humas.ar-raniry.ac.id\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- -----------------------------------------------------\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Skema Create Table
                $showCreate = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
                $sql .= "-- -----------------------------------------------------\n";
                $sql .= "-- Table structure for table `{$table}`\n";
                $sql .= "-- -----------------------------------------------------\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $showCreate['Create Table'] . ";\n\n";

                // Dump Data
                $rows = $db->table($table)->get()->getResultArray();
                if (!empty($rows)) {
                    $sql .= "-- Dumping data for table `{$table}`\n";
                    
                    // Kita kumpulkan insert statements
                    foreach ($rows as $row) {
                        $fields = array_keys($row);
                        $escapedFields = array_map(function($f) use ($db) {
                            return "`" . $db->escapeString($f) . "`";
                        }, $fields);
                        
                        $escapedValues = array_map(function($v) use ($db) {
                            if ($v === null) {
                                return 'NULL';
                            }
                            return "'" . $db->escapeString($v) . "'";
                        }, array_values($row));

                        $sql .= "INSERT INTO `{$table}` (" . implode(', ', $escapedFields) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Buat file SQL temporer di folder writable
            $sqlFilename = 'db-backup-' . date('Ymd-His') . '.sql';
            $sqlPath = WRITEPATH . $sqlFilename;
            file_put_contents($sqlPath, $sql);

            // Kompres SQL ke berkas ZIP
            $zipFilename = 'db-backup-' . date('Ymd-His') . '.zip';
            $zipPath = WRITEPATH . $zipFilename;
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFile($sqlPath, $sqlFilename);
                $zip->close();
                
                // Hapus file SQL mentah setelah dikompresi
                unlink($sqlPath);
            } else {
                unlink($sqlPath);
                throw new Exception("Gagal mengompresi berkas SQL.");
            }

            $this->logActivity("Melakukan backup database", "Manajemen Sistem");

            // Kirim file ZIP ke browser dan langsung hapus setelah selesai dikirim
            if (file_exists($zipPath)) {
                // Tambahkan CORS headers agar browser tidak memblokir download
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
                header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-KEY');
                header('Access-Control-Expose-Headers: Content-Disposition');

                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($zipPath));
                
                // Bersihkan output buffer untuk mencegah file rusak
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                flush();
                
                readfile($zipPath);
                unlink($zipPath); // Hapus zip temporer di server
                exit();
            } else {
                throw new Exception("Berkas backup ZIP tidak ditemukan.");
            }

        } catch (Exception $e) {
            return $this->respondWithError("Gagal melakukan pencadangan database: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/admin/system/backup-files
     * Melakukan backup seluruh sistem (file project) dan diunduh sebagai berkas ZIP
     */
    public function backupFiles()
    {
        try {
            $zipFilename = 'system-backup-' . date('Ymd-His') . '.zip';
            $zipPath = WRITEPATH . $zipFilename;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Gagal menginisiasi berkas ZIP.");
            }

            $rootPath = realpath(ROOTPATH);
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if ($file->isDir()) {
                    continue;
                }

                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Pengecualian folder temporer & node_modules untuk efisiensi ukuran file backup
                if (
                    strpos($relativePath, 'writable' . DIRECTORY_SEPARATOR . 'cache') === 0 ||
                    strpos($relativePath, 'writable' . DIRECTORY_SEPARATOR . 'debugbar') === 0 ||
                    strpos($relativePath, 'writable' . DIRECTORY_SEPARATOR . 'session') === 0 ||
                    strpos($relativePath, 'node_modules') !== false ||
                    $filePath === $zipPath
                ) {
                    continue;
                }

                // Normalisasi path separator ke slash (/) di dalam struktur arsip ZIP
                $zipPathName = str_replace('\\', '/', $relativePath);
                $zip->addFile($filePath, $zipPathName);
            }

            $zip->close();

            $this->logActivity("Melakukan backup berkas sistem proyek", "Manajemen Sistem");

            // Kirim file ZIP ke browser dan hapus setelah terkirim
            if (file_exists($zipPath)) {
                // Tambahkan CORS headers agar browser tidak memblokir download
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
                header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-KEY');
                header('Access-Control-Expose-Headers: Content-Disposition');

                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($zipPath));
                
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                flush();
                
                readfile($zipPath);
                unlink($zipPath); // Hapus zip temporer di server
                exit();
            } else {
                throw new Exception("Berkas ZIP cadangan tidak berhasil dibuat.");
            }

        } catch (Exception $e) {
            return $this->respondWithError("Gagal melakukan pencadangan berkas: " . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/admin/system/update-zip
     * Melakukan upload ZIP pembaruan, verifikasi versi, ekstraksi overwrite, dan jalankan migrasi database
     */
    public function updateZip()
    {
        $file = $this->request->getFile('file');
        $action = $this->request->getVar('action') ?? 'verify'; // 'verify' atau 'update'

        if (!$file || !$file->isValid()) {
            return $this->respondWithError("Berkas pembaruan tidak valid atau tidak ditemukan.", 400);
        }

        // Pastikan extension adalah zip
        if ($file->getExtension() !== 'zip') {
            return $this->respondWithError("Format berkas harus berupa arsip .zip", 400);
        }

        // Pindahkan berkas ZIP sementara ke folder writable/uploads
        $tempName = 'update-' . time() . '.zip';
        $file->move(WRITEPATH . 'uploads/', $tempName);
        $zipPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                unlink($zipPath);
                return $this->respondWithError("Gagal membuka berkas ZIP. Berkas kemungkinan rusak.", 400);
            }

            // 1. Baca data version.json di dalam ZIP secara internal
            $versionJson = $zip->getFromName('version.json');
            
            // Coba cari di dalam subfolder backend/version.json jika tidak ada di root
            if (!$versionJson) {
                $versionJson = $zip->getFromName('backend/version.json');
            }

            if (!$versionJson) {
                $zip->close();
                unlink($zipPath);
                return $this->respondWithError("Berkas tidak valid. version.json tidak ditemukan di dalam ZIP.", 400);
            }

            $zipVersionData = json_decode($versionJson, true);
            if (!$zipVersionData || !isset($zipVersionData['version'])) {
                $zip->close();
                unlink($zipPath);
                return $this->respondWithError("Format metadata version.json di dalam ZIP tidak valid.", 400);
            }

            $localVersionData = $this->getLocalVersion();
            $currentVersion = $localVersionData['version'];
            $newVersion = $zipVersionData['version'];

            // 2. Tindakan: Hanya Verifikasi Versi (Verify)
            if ($action === 'verify') {
                $zip->close();
                unlink($zipPath);

                return $this->respond([
                    'status' => 'success',
                    'data'   => [
                        'current_version' => $currentVersion,
                        'new_version'     => $newVersion,
                        'build_date'      => $zipVersionData['build_date'] ?? 'N/A',
                        'changelog'       => $zipVersionData['changelog'] ?? 'Tidak ada deskripsi changelog.',
                        'can_update'      => true // Selalu izinkan update (bisa downgrade / reinstall juga)
                    ]
                ]);
            }

            // 3. Tindakan: Terapkan Pembaruan (Extract & Migrate)
            if ($action === 'update') {
                // Ekstrak berkas menimpa project saat ini
                $zip->extractTo(ROOTPATH);
                $zip->close();
                unlink($zipPath); // Hapus berkas ZIP upload

                // Jalankan Database Migrations secara Programmatic
                $migrations = \Config\Services::migrations();
                try {
                    $migrations->latest();
                } catch (Exception $migrateError) {
                    // Log error tapi lanjutkan karena file telah sukses diekstrak
                    log_message('error', 'Update Database Migrations Error: ' . $migrateError->getMessage());
                }

                // Kosongkan Folder Cache
                $this->clearCache();

                $this->logActivity("Melakukan pembaruan sistem ke v{$newVersion}", "Manajemen Sistem");

                return $this->respond([
                    'status'  => 'success',
                    'message' => "Sistem berhasil diperbarui dari v{$currentVersion} ke v{$newVersion}."
                ]);
            }

            // Aksi lain yang tidak dikenali
            $zip->close();
            unlink($zipPath);
            return $this->respondWithError("Aksi tidak dikenal.", 400);

        } catch (Exception $e) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            return $this->respondWithError("Terjadi kesalahan sistem saat memproses pembaruan: " . $e->getMessage(), 500);
        }
    }

    /**
     * Membaca versi lokal dari version.json
     */
    private function getLocalVersion(): array
    {
        $versionPath = ROOTPATH . 'version.json';
        if (file_exists($versionPath)) {
            $content = file_get_contents($versionPath);
            $data = json_decode($content, true);
            if ($data && isset($data['version'])) {
                return $data;
            }
        }

        // Fallback default
        return [
            'version'    => '1.0.0',
            'build_date' => '2026-05-25',
            'changelog'  => 'Versi awal sistem.'
        ];
    }

    /**
     * Membersihkan cache CodeIgniter
     */
    private function clearCache()
    {
        $cachePath = WRITEPATH . 'cache/';
        if (is_dir($cachePath)) {
            $files = glob($cachePath . '*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== 'index.html' && basename($file) !== '.htaccess') {
                    @unlink($file);
                }
            }
        }
    }
}
