<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up()
    {
        // Menonaktifkan foreign key checks sementara untuk menghindari urutan pembuatan tabel
        $this->db->query("SET FOREIGN_KEY_CHECKS=0;");

        // 1. TABLE: roles
        if (!$this->db->tableExists('roles')) {
            $this->db->query("
                CREATE TABLE roles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    role_name VARCHAR(50) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 2. TABLE: users
        if (!$this->db->tableExists('users')) {
            $this->db->query("
                CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    full_name VARCHAR(100) NULL,
                    role_id INT NULL,
                    status ENUM('active','inactive') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_users_role FOREIGN KEY (role_id) 
                        REFERENCES roles (id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 3. TABLE: categories
        if (!$this->db->tableExists('categories')) {
            $this->db->query("
                CREATE TABLE categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 4. TABLE: media
        if (!$this->db->tableExists('media')) {
            $this->db->query("
                CREATE TABLE media (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    media_name VARCHAR(100) NOT NULL UNIQUE,
                    media_type ENUM('cetak','online') DEFAULT 'cetak',
                    media_logo VARCHAR(255) NULL,
                    media_scale VARCHAR(50) NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 5. TABLE: modules
        if (!$this->db->tableExists('modules')) {
            $this->db->query("
                CREATE TABLE modules (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    module_name VARCHAR(50) NOT NULL UNIQUE,
                    display_name VARCHAR(100) NOT NULL,
                    icon VARCHAR(50) NULL,
                    status ENUM('active','inactive') DEFAULT 'active',
                    sort_order INT DEFAULT 0
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 6. TABLE: settings
        if (!$this->db->tableExists('settings')) {
            $this->db->query("
                CREATE TABLE settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) NOT NULL UNIQUE,
                    setting_value TEXT NULL,
                    setting_group VARCHAR(50) DEFAULT 'general',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 7. TABLE: news_online
        if (!$this->db->tableExists('news_online')) {
            $this->db->query("
                CREATE TABLE news_online (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    news_date DATE NOT NULL,
                    media_id INT NULL,
                    category_id INT NULL, -- Dipertahankan untuk kompatibilitas mundur
                    source_type ENUM('Rilis Humas','Liputan Wartawan') NOT NULL,
                    news_link TEXT NOT NULL,
                    created_by INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_news_media FOREIGN KEY (media_id) 
                        REFERENCES media (id) ON DELETE SET NULL,
                    CONSTRAINT fk_news_creator FOREIGN KEY (created_by) 
                        REFERENCES users (id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 8. TABLE: news_online_category_rel
        if (!$this->db->tableExists('news_online_category_rel')) {
            $this->db->query("
                CREATE TABLE news_online_category_rel (
                    news_id INT NOT NULL,
                    category_id INT NOT NULL,
                    PRIMARY KEY (news_id, category_id),
                    CONSTRAINT fk_nocr_news FOREIGN KEY (news_id) 
                        REFERENCES news_online (id) ON DELETE CASCADE,
                    CONSTRAINT fk_nocr_category FOREIGN KEY (category_id) 
                        REFERENCES categories (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 9. TABLE: clippings
        if (!$this->db->tableExists('clippings')) {
            $this->db->query("
                CREATE TABLE clippings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    clipping_date DATE NOT NULL,
                    category_id INT NULL, -- Dipertahankan untuk kompatibilitas mundur
                    media_id INT NULL,
                    media_scale VARCHAR(50) NULL,
                    summary TEXT NULL,
                    file_path VARCHAR(255) NULL,
                    storage_building VARCHAR(100) NULL,
                    storage_room VARCHAR(100) NULL,
                    storage_rack VARCHAR(100) NULL,
                    storage_folder VARCHAR(100) NULL,
                    is_borrowable TINYINT(1) DEFAULT 1,
                    created_by INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_clippings_media FOREIGN KEY (media_id) 
                        REFERENCES media (id) ON DELETE SET NULL,
                    CONSTRAINT fk_clippings_creator FOREIGN KEY (created_by) 
                        REFERENCES users (id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 10. TABLE: clipping_category_rel
        if (!$this->db->tableExists('clipping_category_rel')) {
            $this->db->query("
                CREATE TABLE clipping_category_rel (
                    clipping_id INT NOT NULL,
                    category_id INT NOT NULL,
                    PRIMARY KEY (clipping_id, category_id),
                    CONSTRAINT fk_ccr_clipping FOREIGN KEY (clipping_id) 
                        REFERENCES clippings (id) ON DELETE CASCADE,
                    CONSTRAINT fk_ccr_category FOREIGN KEY (category_id) 
                        REFERENCES categories (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 11. TABLE: documentation
        if (!$this->db->tableExists('documentation')) {
            $this->db->query("
                CREATE TABLE documentation (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    event_name VARCHAR(255) NOT NULL,
                    description TEXT NULL,
                    news_link VARCHAR(255) NULL,
                    event_date DATE NOT NULL,
                    category_id INT NULL, -- Dipertahankan untuk kompatibilitas mundur
                    location_name VARCHAR(255) NULL,
                    location_type ENUM('Internal Kampus','Lokal Daerah','Nasional','Internasional') DEFAULT 'Internal Kampus',
                    thumbnail_url VARCHAR(255) NULL,
                    photo_folder_link VARCHAR(255) NULL,
                    video_folder_link VARCHAR(255) NULL,
                    creator_name VARCHAR(100) NULL,
                    created_by INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_doc_creator FOREIGN KEY (created_by) 
                        REFERENCES users (id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 12. TABLE: documentation_attendance
        if (!$this->db->tableExists('documentation_attendance')) {
            $this->db->query("
                CREATE TABLE documentation_attendance (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    documentation_id INT NOT NULL,
                    level ENUM('Rektorat','Fakultas') NOT NULL,
                    position VARCHAR(100) NOT NULL,
                    person_name VARCHAR(255) NOT NULL,
                    CONSTRAINT fk_da_doc FOREIGN KEY (documentation_id) 
                        REFERENCES documentation (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 13. TABLE: documentation_category_rel
        if (!$this->db->tableExists('documentation_category_rel')) {
            $this->db->query("
                CREATE TABLE documentation_category_rel (
                    documentation_id INT NOT NULL,
                    category_id INT NOT NULL,
                    PRIMARY KEY (documentation_id, category_id),
                    CONSTRAINT fk_dcr_doc FOREIGN KEY (documentation_id) 
                        REFERENCES documentation (id) ON DELETE CASCADE,
                    CONSTRAINT fk_dcr_category FOREIGN KEY (category_id) 
                        REFERENCES categories (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 14. TABLE: activity_logs
        if (!$this->db->tableExists('activity_logs')) {
            $this->db->query("
                CREATE TABLE activity_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    activity VARCHAR(255) NULL,
                    module VARCHAR(100) NULL,
                    target_id INT NULL,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_al_user FOREIGN KEY (user_id) 
                        REFERENCES users (id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        $this->db->query("SET FOREIGN_KEY_CHECKS=1;");
    }

    public function down()
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS=0;");

        $tables = [
            'activity_logs',
            'documentation_category_rel',
            'documentation_attendance',
            'documentation',
            'clipping_category_rel',
            'clippings',
            'news_online_category_rel',
            'news_online',
            'settings',
            'modules',
            'media',
            'categories',
            'users',
            'roles'
        ];

        foreach ($tables as $t) {
            $this->db->query("DROP TABLE IF EXISTS `$t`;");
        }

        $this->db->query("SET FOREIGN_KEY_CHECKS=1;");
    }
}

