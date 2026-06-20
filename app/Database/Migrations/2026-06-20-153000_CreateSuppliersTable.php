<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSuppliersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'emails_for_notice' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Multiple emails separated by ;',
            ],
            'contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'contact_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'mobile_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tags' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'Comma separated tags',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('name');
        $this->forge->createTable('suppliers');
    }

    public function down(): void
    {
        $this->forge->dropTable('suppliers');
    }
}
