<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWarehousesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'address'        => ['type' => 'TEXT', 'null' => true],
            'contact_person' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'contact_number' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by'     => ['type' => 'INT', 'unsigned' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('warehouses');
    }

    public function down(): void
    {
        $this->forge->dropTable('warehouses');
    }
}
