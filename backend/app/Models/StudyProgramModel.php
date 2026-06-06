<?php

namespace App\Models;

use CodeIgniter\Model;

class StudyProgramModel extends Model
{
    protected $table            = 'study_programs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_prodi',
        'faculty_id',
        'jenjang',
        'akreditasi_sekarang',
        'masa_berlaku',
        'sertifikat_berlaku',
        'akreditasi_periode_1',
        'masa_berlaku_periode_1',
        'sertifikat_periode_1',
        'akreditasi_periode_2',
        'masa_berlaku_periode_2',
        'sertifikat_periode_2',
        'jalur_masuk',
        'website',
    ];

    protected $useTimestamps = false;
}
