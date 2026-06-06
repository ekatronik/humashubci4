<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPermissionsToRoles extends Migration
{
    public function up()
    {
        $fields = [
            'permissions' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'JSON array of allowed modules'
            ]
        ];
        $this->forge->addColumn('roles', $fields);

        $db = \Config\Database::connect();
        
        $db->table('roles')->where('role_name', 'Super Admin')->update([
            'permissions' => json_encode(['news', 'clippings', 'documentation', 'rss', 'taxonomy', 'system', 'users', 'activity'])
        ]);
        
        $db->table('roles')->where('role_name', 'Pranata Humas')->update([
            'permissions' => json_encode(['news', 'clippings', 'documentation', 'rss', 'taxonomy', 'system', 'activity'])
        ]);
        
        $db->table('roles')->where('role_name', 'Operator Kliping')->update([
            'permissions' => json_encode(['clippings'])
        ]);
        
        $db->table('roles')->where('role_name', 'Operator Berita Online')->update([
            'permissions' => json_encode(['news'])
        ]);
        
        $db->table('roles')->where('role_name', 'Operator Foto/Video')->update([
            'permissions' => json_encode(['documentation'])
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('roles', 'permissions');
    }
}
