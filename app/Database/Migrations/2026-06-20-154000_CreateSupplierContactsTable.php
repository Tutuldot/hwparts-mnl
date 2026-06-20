<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupplierContactsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'supplier_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'mobile' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'role_or_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('supplier_contacts');
    }

    public function down(): void
    {
        $this->forge->dropTable('supplier_contacts');
    }
}
