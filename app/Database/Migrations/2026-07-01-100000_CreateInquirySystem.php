<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInquirySystem extends Migration
{
    public function up(): void
    {
        // 1. Create inquiries table
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'customer_id'    => ['type' => 'INT', 'unsigned' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['open', 'closed'], 'default' => 'open'],
            'sales_order_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('customer_id');
        $this->forge->addKey('sales_order_id');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sales_order_id', 'sales_orders', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('inquiries');

        // 2. Create inquiry_messages table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'inquiry_id'  => ['type' => 'INT', 'unsigned' => true],
            'sender_type' => ['type' => 'ENUM', 'constraint' => ['customer', 'user']],
            'sender_id'   => ['type' => 'INT', 'unsigned' => true],
            'message'     => ['type' => 'TEXT', 'null' => true],
            'photo_path'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('inquiry_id');
        $this->forge->addForeignKey('inquiry_id', 'inquiries', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inquiry_messages');
    }

    public function down(): void
    {
        $this->forge->dropTable('inquiry_messages', true);
        $this->forge->dropTable('inquiries', true);
    }
}
