<?php

namespace App\Models;

use CodeIgniter\Model;

class FridaySermonModel extends Model
{
    protected $table            = 'friday_sermons';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'mosque_type',
        'mosque_name',
        'date',
        'khatib',
        'imam',
        'muazzin',
    ];

    protected $useTimestamps = false;
}
