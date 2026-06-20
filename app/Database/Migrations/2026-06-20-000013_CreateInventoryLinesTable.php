<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryLinesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'inventory_header_id'   => ['type' => 'INT', 'unsigned' => true],
            'part_id'               => ['type' => 'INT', 'unsigned' => true],
            'variant_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'warehouse_id'          => ['type' => 'INT', 'unsigned' => true],
            'warehouse_location_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'transfer_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'quantity'              => ['type' => 'INT', 'default' => 0],
            'acquisition_cost'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total_cost'            => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'remarks'               => ['type' => 'TEXT', 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['part_id', 'warehouse_id']);
        $this->forge->addForeignKey('inventory_header_id', 'inventory_headers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_location_id', 'warehouse_locations', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('transfer_id', 'inventory_transfers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('inventory_lines');
    }

    public function down(): void
    {
        $this->forge->dropTable('inventory_lines');
    }
}
