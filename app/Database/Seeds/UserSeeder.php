<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Seed Roles (Try-Catch safe)
        $roles = [
            ['id' => 1, 'role_name' => 'Super Admin'],
            ['id' => 2, 'role_name' => 'Pranata Humas'],
            ['id' => 3, 'role_name' => 'Operator'],
        ];

        foreach ($roles as $role) {
            try {
                $exists = $db->table('roles')
                    ->where('id', $role['id'])
                    ->orWhere('role_name', $role['role_name'])
                    ->countAllResults();
                if (!$exists) {
                    $db->table('roles')->insert($role);
                }
            } catch (\Exception $e) {
                // Abaikan jika sudah ada
            }
        }

        // 2. Seed Default Admin User
        $adminUser = [
            'username'  => 'admin',
            'password'  => password_hash('password123', PASSWORD_BCRYPT),
            'full_name' => 'Administrator Humas',
            'role_id'   => 1,
            'status'    => 'active',
        ];

        try {
            $exists = $db->table('users')->where('username', 'admin')->countAllResults();
            if (!$exists) {
                // Pastikan role_id 1 valid
                $db->table('users')->insert($adminUser);
            }
        } catch (\Exception $e) {
            // Abaikan jika error
        }
        
        // 3. Seed Default Categories
        $categories = [
            ['id' => 1, 'name' => 'Akademik'],
            ['id' => 2, 'name' => 'Kemahasiswaan'],
            ['id' => 3, 'name' => 'Kerjasama & Event'],
            ['id' => 4, 'name' => 'Riset & Pengabdian'],
        ];

        foreach ($categories as $cat) {
            try {
                $exists = $db->table('categories')
                    ->where('id', $cat['id'])
                    ->orWhere('name', $cat['name'])
                    ->countAllResults();
                if (!$exists) {
                    $db->table('categories')->insert($cat);
                }
            } catch (\Exception $e) {
                // Abaikan jika sudah ada
            }
        }

        // 4. Seed Default Media
        $media = [
            ['id' => 1, 'media_name' => 'Serambi Indonesia', 'media_type' => 'cetak'],
            ['id' => 2, 'media_name' => 'Kanal Humas UIN', 'media_type' => 'online'],
            ['id' => 3, 'media_name' => 'Detik.com', 'media_type' => 'online'],
        ];

        foreach ($media as $m) {
            try {
                $exists = $db->table('media')
                    ->where('id', $m['id'])
                    ->orWhere('media_name', $m['media_name'])
                    ->countAllResults();
                if (!$exists) {
                    $db->table('media')->insert($m);
                }
            } catch (\Exception $e) {
                // Abaikan jika sudah ada
            }
        }
    }
}
