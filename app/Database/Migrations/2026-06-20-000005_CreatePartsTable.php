<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePartsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'sku'             => ['type' => 'VARCHAR', 'constraint' => 60],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 200],
            'category_id'     => ['type' => 'INT', 'unsigned' => true],
            'type'            => ['type' => 'ENUM', 'constraint' => ['quantity', 'non_quantity'], 'default' => 'quantity'],
            'description'     => ['type' => 'TEXT', 'null' => true],
            'barcode_value'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'qr_code_value'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'qr_code_image'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'unit_of_measure' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'pcs'],
            'min_stock_level' => ['type' => 'INT', 'default' => 0],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by'      => ['type' => 'INT', 'unsigned' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('sku');
        $this->forge->addKey(['category_id', 'type']);
        $this->forge->addForeignKey('category_id', 'part_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('parts');
    }

    public function down(): void
    {
        $this->forge->dropTable('parts');
    }
}
