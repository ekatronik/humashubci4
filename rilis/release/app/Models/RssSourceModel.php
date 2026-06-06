<?php

namespace App\Models;

use CodeIgniter\Model;

class RssSourceModel extends Model
{
    protected $table            = 'rss_sources';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['site_name', 'site_url', 'feed_url', 'is_active', 'last_synced_at'];
    protected $useTimestamps    = false;

    /**
     * Menyinkronkan semua feed RSS aktif
     * 
     * @param bool $isCli Apakah dipanggil via CLI (untuk menulis output teks)
     * @return array Ringkasan hasil sinkronisasi
     */
    public function syncAll(bool $isCli = false): array
    {
        $itemModel = new RssItemModel();
        $settingModel = new SettingModel();

        // 1. Ambil semua sumber RSS aktif
        $sources = $this->where('is_active', 1)->findAll();
        if (empty($sources)) {
            $msg = 'Sinkronisasi selesai. Tidak ada sumber RSS aktif yang ditemukan.';
            if ($isCli) {
                \CodeIgniter\CLI\CLI::write($msg, 'yellow');
            }
            
            $settingModel->setVal('rss_last_cron_run', date('Y-m-d H:i:s'), 'rss');
            $settingModel->setVal('rss_last_cron_status', json_encode([
                'status'         => 'success',
                'timestamp'      => date('Y-m-d H:i:s'),
                'summary'        => $msg,
                'details'        => [],
                'total_inserted' => 0,
                'total_deleted'  => 0
            ]), 'rss');

            return [
                'status'         => 'success',
                'summary'        => $msg,
                'total_inserted' => 0,
                'total_deleted'  => 0,
                'details'        => []
            ];
        }

        $totalInserted = 0;
        $totalErrors = 0;
        $summaryLogs = [];

        foreach ($sources as $source) {
            if ($isCli) {
                \CodeIgniter\CLI\CLI::write("Mengambil feed dari: {$source['site_name']} ({$source['feed_url']})...", 'cyan');
            }
            try {
                $client = \Config\Services::curlrequest([
                    'timeout'         => 12,
                    'verify'          => false,
                    'headers' => [
                        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept'          => 'application/xml,text/xml,application/xhtml+xml,text/html;q=0.9,*/*;q=0.8',
                        'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                        'Cache-Control'   => 'no-cache',
                        'Pragma'          => 'no-cache',
                    ]
                ]);
                
                $response = $client->get($source['feed_url']);
                
                if ($response->getStatusCode() !== 200) {
                    throw new \Exception("HTTP Status Code: " . $response->getStatusCode());
                }

                $xmlString = $response->getBody();
                
                // Matikan error eksternal xml sementara
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
                
                if ($xml === false) {
                    $xmlErrors = [];
                    foreach (libxml_get_errors() as $error) {
                        $xmlErrors[] = trim($error->message);
                    }
                    libxml_clear_errors();
                    throw new \Exception("XML Parse Error: " . implode(', ', $xmlErrors));
                }

                $items = $xml->channel->item ?? [];
                $insertedForSource = 0;

                foreach ($items as $item) {
                    $link = (string)$item->link;
                    if (empty($link)) continue;

                    // Cek jika link sudah ada (unique link di database)
                    $exists = $itemModel->where('link', $link)->countAllResults() > 0;
                    if ($exists) continue;

                    $title = (string)$item->title;
                    $description = (string)($item->description ?? '');
                    
                    // Bersihkan HTML tag dari description
                    $descriptionClean = strip_tags($description);
                    if (strlen($descriptionClean) > 300) {
                        $descriptionClean = substr($descriptionClean, 0, 300) . '...';
                    }

                    // Ambil creator (Penulis)
                    $creator = '';
                    if (isset($item->children('dc', true)->creator)) {
                        $creator = (string)$item->children('dc', true)->creator;
                    } elseif (isset($item->creator)) {
                        $creator = (string)$item->creator;
                    }

                    // Ambil pub_date
                    $pubDateStr = (string)$item->pubDate;
                    $timestamp = strtotime($pubDateStr);
                    $pubDateFormatted = $timestamp ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');

                    // Ambil image_url
                    $imageUrl = null;
                    // 1. media:content
                    $mediaNamespace = $item->children('media', true);
                    if ($mediaNamespace && isset($mediaNamespace->content)) {
                        $imageUrl = (string)$mediaNamespace->content->attributes()->url;
                    }
                    // 2. enclosure
                    if (empty($imageUrl) && isset($item->enclosure)) {
                        $imageUrl = (string)$item->enclosure->attributes()->url;
                    }
                    // 3. parse image from description HTML
                    if (empty($imageUrl) && !empty($description)) {
                        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $description, $matches)) {
                            $imageUrl = $matches[1];
                        }
                    }

                    $itemData = [
                        'source_id'   => $source['id'],
                        'title'       => $title,
                        'link'        => $link,
                        'description' => $descriptionClean,
                        'creator'     => $creator ?: 'Admin',
                        'pub_date'    => $pubDateFormatted,
                        'image_url'   => $imageUrl
                    ];

                    $itemModel->insert($itemData);
                    $insertedForSource++;
                }

                $totalInserted += $insertedForSource;
                
                // Update waktu sinkronisasi terakhir di rss_sources
                $this->update($source['id'], [
                    'last_synced_at' => date('Y-m-d H:i:s')
                ]);

                $summaryLogs[] = "{$source['site_name']}: Sukses (+{$insertedForSource} berita)";
                if ($isCli) {
                    \CodeIgniter\CLI\CLI::write("{$source['site_name']}: Berhasil menyinkronkan {$insertedForSource} berita baru.", 'green');
                }

            } catch (\Exception $e) {
                $totalErrors++;
                $summaryLogs[] = "{$source['site_name']}: Gagal ({$e->getMessage()})";
                if ($isCli) {
                    \CodeIgniter\CLI\CLI::write("{$source['site_name']}: Gagal sinkronisasi. Error: " . $e->getMessage(), 'red');
                }
            }
        }

        // 2. Lakukan Auto-Cleanup jika diaktifkan
        $cleanupEnabled = $settingModel->getVal('rss_cleanup_enabled', '1');
        $cleanupDays = (int)$settingModel->getVal('rss_cleanup_days', '14');

        $deletedCount = 0;
        if ($cleanupEnabled === '1' && $cleanupDays > 0) {
            if ($isCli) {
                \CodeIgniter\CLI\CLI::write("Memulai pembersihan otomatis (Auto-Cleanup)... Hapus item < {$cleanupDays} hari.", 'yellow');
            }
            
            $db = \Config\Database::connect();
            $db->table('rss_items')
               ->where('pub_date <', date('Y-m-d H:i:s', strtotime("-{$cleanupDays} days")))
               ->delete();
            $deletedCount = $db->affectedRows();
            
            if ($isCli) {
                \CodeIgniter\CLI\CLI::write("Berhasil membersihkan {$deletedCount} postingan usang.", 'green');
            }
        }

        // 3. Simpan log sinkronisasi terakhir ke tabel settings
        $statusMessage = "Sinkronisasi selesai pada " . date('d-m-Y H:i:s') . ". ";
        $statusMessage .= "Berhasil: " . (count($sources) - $totalErrors) . "/" . count($sources) . " sumber. ";
        $statusMessage .= "Total berita baru: {$totalInserted}. ";
        if ($deletedCount > 0) {
            $statusMessage .= "Pembersihan otomatis menghapus {$deletedCount} berita lama.";
        }

        $settingModel->setVal('rss_last_cron_run', date('Y-m-d H:i:s'), 'rss');
        
        $runStatus = [
            'status'         => $totalErrors === 0 ? 'success' : (count($sources) == $totalErrors ? 'failed' : 'partial'),
            'timestamp'      => date('Y-m-d H:i:s'),
            'summary'        => $statusMessage,
            'details'        => $summaryLogs,
            'total_inserted' => $totalInserted,
            'total_deleted'  => $deletedCount
        ];
        
        $settingModel->setVal('rss_last_cron_status', json_encode($runStatus), 'rss');

        if ($isCli) {
            \CodeIgniter\CLI\CLI::write("Status akhir: {$statusMessage}", 'green');
            \CodeIgniter\CLI\CLI::write('Sinkronisasi RSS selesai.', 'blue');
        }

        return $runStatus;
    }
}
