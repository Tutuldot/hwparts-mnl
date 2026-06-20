<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentDueDateAndAccountsPayable extends Migration
{
    public function up(): void
    {
        // 1. Add payment_due_date to purchase_orders
        $this->db->query("ALTER TABLE purchase_orders ADD COLUMN payment_due_date DATE NULL AFTER payment_type");

        // 2. Create accounts_payable table
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'po_id'             => ['type' => 'INT', 'unsigned' => true],
            'supplier_id'       => ['type' => 'INT', 'unsigned' => true],
            'amount'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.00],
            'due_date'          => ['type' => 'DATE', 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['unpaid', 'paid'], 'default' => 'unpaid'],
            'payment_reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'payment_type'      => ['type' => 'ENUM', 'constraint' => ['GCASH', 'BANK TRANSFER', 'Cheque', 'Cash via Transmittal'], 'null' => true],
            'cheque_details'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'proof_of_payment'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'paid_at'           => ['type' => 'DATETIME', 'null' => true],
            'paid_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('po_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('paid_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('accounts_payable');
    }

    public function down(): void
    {
        $this->forge->dropTable('accounts_payable', true);
        $this->db->query("ALTER TABLE purchase_orders DROP COLUMN payment_due_date");
    }
}
