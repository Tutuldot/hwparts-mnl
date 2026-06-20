<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchaseOrdersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'po_number'        => ['type' => 'VARCHAR', 'constraint' => 60],
            'supplier_name'    => ['type' => 'VARCHAR', 'constraint' => 200],
            'payment_type'     => ['type' => 'ENUM', 'constraint' => ['cash', 'cheque', 'bank_transfer', 'credit_card', 'terms'], 'default' => 'cash'],
            'proof_of_payment' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'amount'           => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'status'           => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'submitted', 'approved', 'partially_received', 'fully_received', 'cancelled', 'rejected'],
                'default'    => 'draft',
            ],
            'remarks'          => ['type' => 'TEXT', 'null' => true],
            'created_by'       => ['type' => 'INT', 'unsigned' => true],
            'submitted_at'     => ['type' => 'DATETIME', 'null' => true],
            'approved_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_at'      => ['type' => 'DATETIME', 'null' => true],
            'rejected_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'rejected_at'      => ['type' => 'DATETIME', 'null' => true],
            'rejection_reason' => ['type' => 'TEXT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('po_number');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('purchase_orders');
    }

    public function down(): void
    {
        $this->forge->dropTable('purchase_orders');
    }
}
