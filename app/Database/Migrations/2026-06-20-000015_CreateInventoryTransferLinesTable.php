<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTransferLinesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'transfer_id'              => ['type' => 'INT', 'unsigned' => true],
            'part_id'                  => ['type' => 'INT', 'unsigned' => true],
            'variant_id'               => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'part_type'                => ['type' => 'ENUM', 'constraint' => ['quantity', 'non_quantity'], 'default' => 'quantity'],
            'quantity_requested'       => ['type' => 'INT', 'null' => true],
            'quantity_transferred'     => ['type' => 'INT', 'default' => 0],
            'parts_detail_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'to_warehouse_location_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status'                   => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'partially_transferred', 'transferred', 'cancelled'],
                'default'    => 'pending',
            ],
            'transferred_at' => ['type' => 'DATETIME', 'null' => true],
            'transferred_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'remarks'        => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['transfer_id', 'status']);
        $this->forge->addForeignKey('transfer_id', 'inventory_transfers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parts_detail_id', 'parts_details', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('to_warehouse_location_id', 'warehouse_locations', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('inventory_transfer_lines');
    }

    public function down(): void
    {
        $this->forge->dropTable('inventory_transfer_lines');
    }
}
