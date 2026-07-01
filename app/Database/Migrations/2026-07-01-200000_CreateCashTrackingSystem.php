<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCashTrackingSystem extends Migration
{
    public function up(): void
    {
        // 1. cash_accounts
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'type'           => ['type' => 'ENUM', 'constraint' => ['bank', 'digital_wallet', 'cash']],
            'account_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'balance'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cash_accounts');

        // 2. daily_cash_declarations
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'declaration_date' => ['type' => 'DATE'],
            'account_id'       => ['type' => 'INT', 'unsigned' => true],
            'opening_balance'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'system_balance'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'discrepancy'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'created_by'       => ['type' => 'INT', 'unsigned' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('declaration_date');
        $this->forge->addForeignKey('account_id', 'cash_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('daily_cash_declarations');

        // 3. cash_transactions
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'transaction_number' => ['type' => 'VARCHAR', 'constraint' => 100],
            'reference_number'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'from_account_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'to_account_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'amount'             => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'type'               => ['type' => 'ENUM', 'constraint' => ['deposit', 'withdrawal', 'transfer', 'income', 'expense', 'adjustment', 'advance']],
            'reference_source'   => ['type' => 'ENUM', 'constraint' => ['sales_payment', 'procurement_payment', 'manual']],
            'reference_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'evidence_path'      => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'remarks'            => ['type' => 'TEXT', 'null' => true],
            'status'             => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'created_by'         => ['type' => 'INT', 'unsigned' => true],
            'approved_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_at'        => ['type' => 'DATETIME', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('transaction_number');
        $this->forge->addForeignKey('from_account_id', 'cash_accounts', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('to_account_id', 'cash_accounts', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('cash_transactions');
    }

    public function down(): void
    {
        $this->forge->dropTable('cash_transactions', true);
        $this->forge->dropTable('daily_cash_declarations', true);
        $this->forge->dropTable('cash_accounts', true);
    }
}
