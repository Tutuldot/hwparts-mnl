<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePartStockThresholdsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'part_id'         => ['type' => 'INT', 'unsigned' => true],
            'variant_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'warehouse_id'    => ['type' => 'INT', 'unsigned' => true],
            'min_stock_level' => ['type' => 'INT', 'default' => 0],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by'      => ['type' => 'INT', 'unsigned' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['part_id', 'variant_id', 'warehouse_id']);
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('variant_id', 'part_variants', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('part_stock_thresholds');
    }

    public function down(): void
    {
        $this->forge->dropTable('part_stock_thresholds');
    }
}
