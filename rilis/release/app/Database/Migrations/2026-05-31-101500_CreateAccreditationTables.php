<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccreditationTables extends Migration
{
    public function up()
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS=0;");

        // 1. TABLE: campus_accreditation (single-row for institution accreditation)
        if (!$this->db->tableExists('campus_accreditation')) {
            $this->db->query("
                CREATE TABLE campus_accreditation (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nama_institusi VARCHAR(255) NOT NULL DEFAULT 'UIN Ar-Raniry Banda Aceh',
                    akreditasi_sekarang ENUM('Unggul','Baik Sekali','Baik') NULL,
                    masa_berlaku DATE NULL,
                    sertifikat_berlaku VARCHAR(255) NULL,
                    akreditasi_periode_1 ENUM('Unggul','Baik Sekali','Baik') NULL,
                    masa_berlaku_periode_1 DATE NULL,
                    sertifikat_periode_1 VARCHAR(255) NULL,
                    akreditasi_periode_2 ENUM('Unggul','Baik Sekali','Baik') NULL,
                    masa_berlaku_periode_2 DATE NULL,
                    sertifikat_periode_2 VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Seed default row
            $this->db->table('campus_accreditation')->insert([
                'nama_institusi' => 'UIN Ar-Raniry Banda Aceh',
            ]);
        }

        // 2. TABLE: faculties
        if (!$this->db->tableExists('faculties')) {
            $this->db->query("
                CREATE TABLE faculties (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nama_fakultas VARCHAR(255) NOT NULL,
                    singkatan VARCHAR(50) NOT NULL,
                    website VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 3. TABLE: study_programs
        if (!$this->db->tableExists('study_programs')) {
            $this->db->query("
                CREATE TABLE study_programs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nama_prodi VARCHAR(255) NOT NULL,
                    faculty_id INT NOT NULL,
                    jenjang ENUM('D4','S1','S2','S3') NOT NULL DEFAULT 'S1',
                    akreditasi_sekarang ENUM('Unggul','Baik Sekali','Baik') NOT NULL DEFAULT 'Baik',
                    masa_berlaku DATE NULL,
                    sertifikat_berlaku VARCHAR(255) NULL,
                    akreditasi_periode_1 ENUM('Unggul','Baik Sekali','Baik') NULL,
                    masa_berlaku_periode_1 DATE NULL,
                    sertifikat_periode_1 VARCHAR(255) NULL,
                    akreditasi_periode_2 ENUM('Unggul','Baik Sekali','Baik') NULL,
                    masa_berlaku_periode_2 DATE NULL,
                    sertifikat_periode_2 VARCHAR(255) NULL,
                    jalur_masuk TEXT NULL COMMENT 'JSON array of admission paths',
                    website VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT fk_sp_faculty FOREIGN KEY (faculty_id)
                        REFERENCES faculties (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        $this->db->query("SET FOREIGN_KEY_CHECKS=1;");

        // ── Seed permissions: add 'accreditation' to Super Admin and Pranata Humas ──
        $db = \Config\Database::connect();
        $rolesToUpdate = ['Super Admin', 'Pranata Humas'];

        foreach ($rolesToUpdate as $roleName) {
            $role = $db->table('roles')->where('role_name', $roleName)->get()->getRowArray();
            if ($role) {
                $permissions = json_decode($role['permissions'] ?? '[]', true);
                if (!in_array('accreditation', $permissions)) {
                    $permissions[] = 'accreditation';
                    $db->table('roles')->where('id', $role['id'])->update([
                        'permissions' => json_encode($permissions)
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS=0;");
        $this->db->query("DROP TABLE IF EXISTS study_programs;");
        $this->db->query("DROP TABLE IF EXISTS faculties;");
        $this->db->query("DROP TABLE IF EXISTS campus_accreditation;");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1;");

        // Remove 'accreditation' from role permissions
        $db = \Config\Database::connect();
        $rolesToUpdate = ['Super Admin', 'Pranata Humas'];

        foreach ($rolesToUpdate as $roleName) {
            $role = $db->table('roles')->where('role_name', $roleName)->get()->getRowArray();
            if ($role) {
                $permissions = json_decode($role['permissions'] ?? '[]', true);
                $permissions = array_values(array_filter($permissions, fn($p) => $p !== 'accreditation'));
                $db->table('roles')->where('id', $role['id'])->update([
                    'permissions' => json_encode($permissions)
                ]);
            }
        }
    }
}
