<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanySettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('settings');

        // Seed default company and invoice settings matching BIR sample format
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $defaults = [
            ['setting_key' => 'company_name',     'setting_value' => 'HW TRUCK PARTS TRADING', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'company_tagline',  'setting_value' => 'Ana Lourdes C. Bagalihog - Prop.', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'company_address',  'setting_value' => "Unit C 8116 Dr A. Santos Avenue, San Dionisio, 1700\nCity of Parañaque NCR, Fourth District, Philippines", 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'company_tin',      'setting_value' => '427-851-105-00000', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'company_phone',    'setting_value' => '+63 917 123 4567', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'company_email',    'setting_value' => 'sales@hwtruckparts.ph', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'atp_text',         'setting_value' => "20 Bklts. (50x3) 10001 - 11000\nAuthority to Print No. OCN: 052AU20260000005621\nDate of ATP: 04-15-2026", 'created_at' => $now, 'updated_at' => $now],
        ];

        $db->table('settings')->insertBatch($defaults);
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
