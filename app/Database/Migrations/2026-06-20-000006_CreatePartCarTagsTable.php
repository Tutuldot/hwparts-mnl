<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePartCarTagsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'part_id' => ['type' => 'INT', 'unsigned' => true],
            'brand'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'model'   => ['type' => 'VARCHAR', 'constraint' => 150],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('part_id');
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('part_car_tags');
    }

    public function down(): void
    {
        $this->forge->dropTable('part_car_tags');
    }
}
