<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOemBrandToParts extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('parts', [
            'oem' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'type',
            ],
            'brand' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'oem',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('parts', ['oem', 'brand']);
    }
}
