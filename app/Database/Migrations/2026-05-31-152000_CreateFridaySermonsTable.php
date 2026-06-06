<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFridaySermonsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('friday_sermons')) {
            $this->db->query("
                CREATE TABLE friday_sermons (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    mosque_type ENUM('fathun_qarib','other') NOT NULL DEFAULT 'fathun_qarib',
                    mosque_name VARCHAR(255) NULL,
                    date DATE NOT NULL,
                    khatib VARCHAR(255) NOT NULL,
                    imam VARCHAR(255) NULL,
                    muazzin VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // Seed permissions: add 'khutbah' to Super Admin, Pranata Humas, and Operator
        $db = \Config\Database::connect();
        $rolesToUpdate = ['Super Admin', 'Pranata Humas', 'Operator'];

        foreach ($rolesToUpdate as $roleName) {
            $role = $db->table('roles')->where('role_name', $roleName)->get()->getRowArray();
            if ($role) {
                $permissions = json_decode($role['permissions'] ?? '[]', true);
                if (!in_array('khutbah', $permissions)) {
                    $permissions[] = 'khutbah';
                    $db->table('roles')->where('id', $role['id'])->update([
                        'permissions' => json_encode($permissions)
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS friday_sermons;");

        // Remove 'khutbah' from role permissions
        $db = \Config\Database::connect();
        $rolesToUpdate = ['Super Admin', 'Pranata Humas', 'Operator'];

        foreach ($rolesToUpdate as $roleName) {
            $role = $db->table('roles')->where('role_name', $roleName)->get()->getRowArray();
            if ($role) {
                $permissions = json_decode($role['permissions'] ?? '[]', true);
                $permissions = array_values(array_filter($permissions, fn($p) => $p !== 'khutbah'));
                $db->table('roles')->where('id', $role['id'])->update([
                    'permissions' => json_encode($permissions)
                ]);
            }
        }
    }
}
