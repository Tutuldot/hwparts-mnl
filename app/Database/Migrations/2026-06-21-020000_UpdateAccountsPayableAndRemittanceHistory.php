<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAccountsPayableAndRemittanceHistory extends Migration
{
    public function up(): void
    {
        // 1. Add columns to accounts_payable
        $this->db->query("ALTER TABLE accounts_payable ADD COLUMN invoice_number VARCHAR(100) NULL AFTER due_date");
        $this->db->query("ALTER TABLE accounts_payable ADD COLUMN amount_paid DECIMAL(14,2) NULL AFTER amount");

        // 2. Create remittance_logs table
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ap_id'      => ['type' => 'INT', 'unsigned' => true],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'recipient'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'message'    => ['type' => 'TEXT'],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ap_id');
        $this->forge->addForeignKey('ap_id', 'accounts_payable', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('remittance_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('remittance_logs', true);
        $this->db->query("ALTER TABLE accounts_payable DROP COLUMN amount_paid");
        $this->db->query("ALTER TABLE accounts_payable DROP COLUMN invoice_number");
    }
}
