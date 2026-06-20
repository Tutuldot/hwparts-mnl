<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWarehouseLocationsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'warehouse_id' => ['type' => 'INT', 'unsigned' => true],
            'code'         => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'  => ['type' => 'TEXT', 'null' => true],
            'is_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['warehouse_id', 'code']);
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('warehouse_locations');
    }

    public function down(): void
    {
        $this->forge->dropTable('warehouse_locations');
    }
}
