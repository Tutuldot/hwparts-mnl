<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePartPhotosAndSuppliers extends Migration
{
    public function up(): void
    {
        // part_photos
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'part_id'    => ['type' => 'INT', 'unsigned' => true],
            'photo_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('part_photos');

        // part_suppliers (Many-to-Many join table)
        $this->forge->addField([
            'part_id'     => ['type' => 'INT', 'unsigned' => true],
            'supplier_id' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey(['part_id', 'supplier_id'], true);
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('part_suppliers');
    }

    public function down(): void
    {
        $this->forge->dropTable('part_suppliers', true);
        $this->forge->dropTable('part_photos', true);
    }
}
