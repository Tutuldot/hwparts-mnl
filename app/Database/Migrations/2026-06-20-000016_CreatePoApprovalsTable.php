<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePoApprovalsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'po_id'     => ['type' => 'INT', 'unsigned' => true],
            'action'    => ['type' => 'ENUM', 'constraint' => ['created', 'submitted', 'approved', 'rejected', 'received', 'cancelled']],
            'action_by' => ['type' => 'INT', 'unsigned' => true],
            'action_at' => ['type' => 'DATETIME', 'null' => true],
            'notes'     => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('po_id');
        $this->forge->addForeignKey('po_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('action_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('po_approvals');
    }

    public function down(): void
    {
        $this->forge->dropTable('po_approvals');
    }
}
