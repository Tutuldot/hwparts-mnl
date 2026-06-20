<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesSystem extends Migration
{
    public function up(): void
    {
        // 1. Create customers table
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'type'            => ['type' => 'ENUM', 'constraint' => ['individual', 'corporate'], 'default' => 'individual'],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'company_name'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'billing_address' => ['type' => 'TEXT'],
            'shipping_address'=> ['type' => 'TEXT'],
            'tin'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'payment_terms'   => ['type' => 'INT', 'default' => 0],
            'username'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'password'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('customers');

        // 2. Create customer_contacts table
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'customer_id'  => ['type' => 'INT', 'unsigned' => true],
            'contact_type' => ['type' => 'ENUM', 'constraint' => ['mobile', 'email'], 'default' => 'email'],
            'value'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'remarks'      => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('customer_id');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('customer_contacts');

        // 3. Create sales_orders table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'so_number'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'customer_id' => ['type' => 'INT', 'unsigned' => true],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.00],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'draft'],
            'remarks'     => ['type' => 'TEXT', 'null' => true],
            'created_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('so_number');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('sales_orders');

        // 4. Create sales_order_lines table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'so_id'       => ['type' => 'INT', 'unsigned' => true],
            'part_id'     => ['type' => 'INT', 'unsigned' => true],
            'variant_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'quantity'    => ['type' => 'INT', 'default' => 1],
            'unit_price'  => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.00],
            'total_price' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.00],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('so_id');
        $this->forge->addForeignKey('so_id', 'sales_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sales_order_lines');

        // 5. Create accounts_receivable table
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'so_id'             => ['type' => 'INT', 'unsigned' => true],
            'customer_id'       => ['type' => 'INT', 'unsigned' => true],
            'invoice_number'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'amount'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.00],
            'amount_paid'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'due_date'          => ['type' => 'DATE', 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['unpaid', 'paid'], 'default' => 'unpaid'],
            'payment_reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'payment_type'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'proof_of_payment'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'paid_at'           => ['type' => 'DATETIME', 'null' => true],
            'paid_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('invoice_number');
        $this->forge->addForeignKey('so_id', 'sales_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('paid_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('accounts_receivable');

        // 6. Create receivable_logs table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ar_id'       => ['type' => 'INT', 'unsigned' => true],
            'notice_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 20],
            'recipient'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'message'     => ['type' => 'TEXT'],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ar_id');
        $this->forge->addForeignKey('ar_id', 'accounts_receivable', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('receivable_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('receivable_logs', true);
        $this->forge->dropTable('accounts_receivable', true);
        $this->forge->dropTable('sales_order_lines', true);
        $this->forge->dropTable('sales_orders', true);
        $this->forge->dropTable('customer_contacts', true);
        $this->forge->dropTable('customers', true);
    }
}
