<?php

namespace App\Models;

use CodeIgniter\Model;

class RssItemModel extends Model
{
    protected $table            = 'rss_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['source_id', 'title', 'link', 'description', 'creator', 'pub_date', 'image_url'];
    protected $useTimestamps    = false;
}
