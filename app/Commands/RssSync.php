<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use App\Models\RssSourceModel;

class RssSync extends BaseCommand
{
    protected $group       = 'RSS';
    protected $name        = 'rss:sync';
    protected $description = 'Sinkronisasi feed berita RSS WordPress ke database lokal dan pembersihan data usang.';

    public function run(array $params)
    {
        $sourceModel = new RssSourceModel();
        $sourceModel->syncAll(true);
    }
}
