<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppMenuTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => '🔗',
                'comment'    => 'Emoji or icon identifier',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('app_menus');

        // Insert default menus
        $db = \Config\Database::connect();
        $defaultMenus = [
            ['name' => 'Kliping',        'url' => '/kliping',     'icon' => '📰', 'sort_order' => 1, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Berita Online',   'url' => '/berita',      'icon' => '📡', 'sort_order' => 2, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Dokumentasi',     'url' => '/dokumentasi', 'icon' => '📸', 'sort_order' => 3, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'RSS Feed',        'url' => '/rss',         'icon' => '📡', 'sort_order' => 4, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Akreditasi',      'url' => '/akreditasi',  'icon' => '🎓', 'sort_order' => 5, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];
        $db->table('app_menus')->insertBatch($defaultMenus);
    }

    public function down()
    {
        $this->forge->dropTable('app_menus');
    }
}
