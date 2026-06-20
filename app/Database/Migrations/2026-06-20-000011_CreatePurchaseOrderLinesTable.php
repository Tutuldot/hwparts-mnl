<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseOrderLinesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'po_id'             => ['type' => 'INT', 'unsigned' => true],
            'part_id'           => ['type' => 'INT', 'unsigned' => true],
            'variant_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'quantity_ordered'  => ['type' => 'INT', 'default' => 0],
            'quantity_received' => ['type' => 'INT', 'default' => 0],
            'unit_cost'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total_cost'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'is_received'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'received_at'       => ['type' => 'DATETIME', 'null' => true],
            'received_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'remarks'           => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('po_id');
        $this->forge->addForeignKey('po_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('purchase_order_lines');
    }

    public function down(): void
    {
        $this->forge->dropTable('purchase_order_lines');
    }
}
