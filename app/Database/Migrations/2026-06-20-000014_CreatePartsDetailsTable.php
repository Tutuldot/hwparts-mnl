<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePartsDetailsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'inventory_header_id'   => ['type' => 'INT', 'unsigned' => true],
            'inventory_line_id'     => ['type' => 'INT', 'unsigned' => true],
            'part_id'               => ['type' => 'INT', 'unsigned' => true],
            'variant_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'warehouse_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'warehouse_location_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'unique_qr_code'        => ['type' => 'VARCHAR', 'constraint' => 500],
            'qr_code_image'         => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'status'                => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'reserved', 'consumed', 'damaged', 'returned'],
                'default'    => 'available',
            ],
            'consumed'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sales_order_id'  => ['type' => 'INT', 'null' => true],
            'remarks'         => ['type' => 'TEXT', 'null' => true],
            'actual_photo'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_date'    => ['type' => 'DATETIME', 'null' => true],
            'consumed_date'   => ['type' => 'DATETIME', 'null' => true],
            'consumed_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('unique_qr_code');
        $this->forge->addKey(['part_id', 'warehouse_id', 'status']);
        $this->forge->addForeignKey('inventory_header_id', 'inventory_headers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('inventory_line_id', 'inventory_lines', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('warehouse_location_id', 'warehouse_locations', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('parts_details');
    }

    public function down(): void
    {
        $this->forge->dropTable('parts_details');
    }
}
