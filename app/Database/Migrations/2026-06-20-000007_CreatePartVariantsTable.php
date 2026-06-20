<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePartVariantsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'part_id'          => ['type' => 'INT', 'unsigned' => true],
            'variant_name'     => ['type' => 'VARCHAR', 'constraint' => 200],
            'variant_sku'      => ['type' => 'VARCHAR', 'constraint' => 70],
            'barcode_value'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'qr_code_value'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'qr_code_image'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'additional_notes' => ['type' => 'TEXT', 'null' => true],
            'is_active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('variant_sku');
        $this->forge->addKey('part_id');
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('part_variants');
    }

    public function down(): void
    {
        $this->forge->dropTable('part_variants');
    }
}
