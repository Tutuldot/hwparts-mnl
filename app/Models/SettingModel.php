<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['setting_key', 'setting_value', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    /**
     * Get all settings as key-value associative array.
     */
    public function getAsMap(): array
    {
        $rows = $this->findAll();
        $map = [
            'company_name'    => 'HW TRUCK PARTS TRADING',
            'company_tagline' => 'Ana Lourdes C. Bagalihog - Prop.',
            'company_address' => "Unit C 8116 Dr A. Santos Avenue, San Dionisio, 1700\nCity of Parañaque NCR, Fourth District, Philippines",
            'company_tin'     => '427-851-105-00000',
            'company_phone'   => '+63 917 123 4567',
            'company_email'   => 'sales@hwtruckparts.ph',
            'atp_text'        => "20 Bklts. (50x3) 10001 - 11000\nAuthority to Print No. OCN: 052AU20260000005621\nDate of ATP: 04-15-2026",
        ];

        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
        }

        return $map;
    }

    /**
     * Get a single setting value by key with optional default.
     */
    public function getVal(string $key, ?string $default = null): ?string
    {
        $row = $this->where('setting_key', $key)->first();
        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Update or insert a key-value setting.
     */
    public function setVal(string $key, ?string $val): bool
    {
        $existing = $this->where('setting_key', $key)->first();
        if ($existing) {
            return $this->update($existing['id'], ['setting_value' => $val]);
        }
        return (bool)$this->insert(['setting_key' => $key, 'setting_value' => $val]);
    }
}
