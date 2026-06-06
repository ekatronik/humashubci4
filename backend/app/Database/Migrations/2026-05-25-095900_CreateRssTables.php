<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRssTables extends Migration
{
    public function up()
    {
        // 1. TABLE: rss_sources
        if (!$this->db->tableExists('rss_sources')) {
            $this->db->query("
                CREATE TABLE `rss_sources` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `site_name` VARCHAR(150) NOT NULL,
                    `site_url` VARCHAR(255) NOT NULL,
                    `feed_url` VARCHAR(255) NOT NULL,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `last_synced_at` DATETIME NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 2. TABLE: rss_items
        if (!$this->db->tableExists('rss_items')) {
            $this->db->query("
                CREATE TABLE `rss_items` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `source_id` INT NOT NULL,
                    `title` VARCHAR(255) NOT NULL,
                    `link` VARCHAR(255) NOT NULL UNIQUE,
                    `description` TEXT NULL,
                    `creator` VARCHAR(100) NULL,
                    `pub_date` DATETIME NOT NULL,
                    `image_url` VARCHAR(255) NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_rss_items_source FOREIGN KEY (`source_id`) 
                        REFERENCES `rss_sources` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 3. SEEDING: Default RSS sources
        $sources = [
            [
                'site_name' => 'Warta UIN Ar-Raniry',
                'site_url'  => 'https://warta.ar-raniry.ac.id',
                'feed_url'  => 'https://warta.ar-raniry.ac.id/feed/',
                'is_active' => 1
            ],
            [
                'site_name' => 'FEBI UIN Ar-Raniry',
                'site_url'  => 'https://febi.ar-raniry.ac.id',
                'feed_url'  => 'https://febi.ar-raniry.ac.id/feed/',
                'is_active' => 1
            ],
            [
                'site_name' => 'FDK UIN Ar-Raniry',
                'site_url'  => 'https://fdk.ar-raniry.ac.id',
                'feed_url'  => 'https://fdk.ar-raniry.ac.id/feed/',
                'is_active' => 1
            ]
        ];
        $this->db->table('rss_sources')->insertBatch($sources);

        // 4. SEEDING: Default Settings for RSS
        $settings = [
            [
                'setting_key'   => 'rss_cleanup_days',
                'setting_value' => '14',
                'setting_group' => 'rss'
            ],
            [
                'setting_key'   => 'rss_cleanup_enabled',
                'setting_value' => '1',
                'setting_group' => 'rss'
            ],
            [
                'setting_key'   => 'rss_last_cron_run',
                'setting_value' => null,
                'setting_group' => 'rss'
            ],
            [
                'setting_key'   => 'rss_last_cron_status',
                'setting_value' => null,
                'setting_group' => 'rss'
            ]
        ];
        
        // Insert settings checking for uniqueness
        foreach ($settings as $setting) {
            $builder = $this->db->table('settings');
            $exists = $builder->where('setting_key', $setting['setting_key'])->countAllResults() > 0;
            if (!$exists) {
                $builder->insert($setting);
            }
        }
    }

    public function down()
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS=0;");
        $this->db->query("DROP TABLE IF EXISTS `rss_items`;");
        $this->db->query("DROP TABLE IF EXISTS `rss_sources`;");
        
        // Cleanup settings keys
        $this->db->table('settings')
            ->whereIn('setting_key', ['rss_cleanup_days', 'rss_cleanup_enabled', 'rss_last_cron_run', 'rss_last_cron_status'])
            ->delete();

        $this->db->query("SET FOREIGN_KEY_CHECKS=1;");
    }
}
