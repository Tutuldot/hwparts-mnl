<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryHeadersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reference_no' => ['type' => 'VARCHAR', 'constraint' => 60],
            'source'       => ['type' => 'ENUM', 'constraint' => ['manual', 'purchase_order', 'transfer'], 'default' => 'manual'],
            'po_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'transfer_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'warehouse_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'remarks'      => ['type' => 'TEXT', 'null' => true],
            'created_by'   => ['type' => 'INT', 'unsigned' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('reference_no');
        $this->forge->addForeignKey('po_id', 'purchase_orders', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('transfer_id', 'inventory_transfers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory_headers');
    }

    public function down(): void
    {
        $this->forge->dropTable('inventory_headers');
    }
}
