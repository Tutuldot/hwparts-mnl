<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRemarksToSupplierContacts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('supplier_contacts', [
            'remarks' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'mobile',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('supplier_contacts', 'remarks');
    }
}
