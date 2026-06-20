<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsVisibleToSupplierContacts extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('supplier_contacts', [
            'is_visible' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1=Yes, 0=No'
            ]
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('supplier_contacts', 'is_visible');
    }
}
