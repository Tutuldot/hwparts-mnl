<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTransfersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'transfer_no'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'from_warehouse_id' => ['type' => 'INT', 'unsigned' => true],
            'to_warehouse_id'   => ['type' => 'INT', 'unsigned' => true],
            'status'            => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'submitted', 'approved', 'in_transit', 'partially_transferred', 'completed', 'rejected', 'cancelled'],
                'default'    => 'draft',
            ],
            'transfer_date'      => ['type' => 'DATE', 'null' => true],
            'remarks'            => ['type' => 'TEXT', 'null' => true],
            'created_by'         => ['type' => 'INT', 'unsigned' => true],
            'submitted_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'submitted_at'       => ['type' => 'DATETIME', 'null' => true],
            'approved_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_at'        => ['type' => 'DATETIME', 'null' => true],
            'rejected_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'rejected_at'        => ['type' => 'DATETIME', 'null' => true],
            'rejection_reason'   => ['type' => 'TEXT', 'null' => true],
            'completed_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'completed_at'       => ['type' => 'DATETIME', 'null' => true],
            'cancelled_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'cancelled_at'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('transfer_no');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('from_warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('to_warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory_transfers');
    }

    public function down(): void
    {
        $this->forge->dropTable('inventory_transfers');
    }
}
