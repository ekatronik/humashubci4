<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfesiToJenjang extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE study_programs MODIFY COLUMN jenjang ENUM('D4', 'S1', 'S2', 'S3', 'Profesi') NOT NULL DEFAULT 'S1';");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE study_programs MODIFY COLUMN jenjang ENUM('D4', 'S1', 'S2', 'S3') NOT NULL DEFAULT 'S1';");
    }
}
