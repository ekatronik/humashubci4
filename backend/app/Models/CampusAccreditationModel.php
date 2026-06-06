<?php

namespace App\Models;

use CodeIgniter\Model;

class CampusAccreditationModel extends Model
{
    protected $table            = 'campus_accreditation';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_institusi',
        'akreditasi_sekarang', 'masa_berlaku', 'sertifikat_berlaku',
        'akreditasi_periode_1', 'masa_berlaku_periode_1', 'sertifikat_periode_1',
        'akreditasi_periode_2', 'masa_berlaku_periode_2', 'sertifikat_periode_2',
    ];

    protected $useTimestamps = false;
}
