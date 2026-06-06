<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['setting_key', 'setting_value', 'setting_group'];
    protected $useTimestamps    = false; // We use database timestamps or raw fields as in initial schema migration

    /**
     * Mengambil nilai setting berdasarkan key
     */
    public function getVal(string $key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting['setting_value'] : $default;
    }

    /**
     * Memperbarui atau membuat baru setting
     */
    public function setVal(string $key, $value, string $group = 'general')
    {
        $setting = $this->where('setting_key', $key)->first();
        
        $data = [
            'setting_key'   => $key,
            'setting_value' => $value === null ? null : (string)$value,
            'setting_group' => $group
        ];

        if ($setting) {
            return $this->update($setting['id'], $data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Mengambil semua setting dalam satu group sebagai array asosiatif
     */
    public function getGroup(string $group): array
    {
        $settings = $this->where('setting_group', $group)->findAll();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        return $result;
    }
}
