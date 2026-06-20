<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPricingAndDiscountsAndCogs extends Migration
{
    public function up(): void
    {
        // 1. part_prices — single active selling price per part/variant
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'part_id'          => ['type' => 'INT', 'unsigned' => true],
            'variant_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'selling_price'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'min_selling_price'=> ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'notes'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'updated_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['part_id', 'variant_id']);
        $this->forge->addForeignKey('part_id', 'parts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('part_prices');

        // 2. so_line_cogs — FIFO acquisition cost tracking per SO line
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'so_line_id'         => ['type' => 'INT', 'unsigned' => true],
            'inventory_line_id'  => ['type' => 'INT', 'unsigned' => true],
            'qty_consumed'       => ['type' => 'INT', 'default' => 0],
            'acquisition_cost'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('so_line_id');
        $this->forge->addForeignKey('so_line_id', 'sales_order_lines', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('inventory_line_id', 'inventory_lines', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('so_line_cogs');

        // 3. report_permissions — role-based report access matrix
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'report_key'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'role'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['report_key', 'role']);
        $this->forge->createTable('report_permissions');

        // 4. Add consumed_qty to inventory_lines
        $this->db->query('ALTER TABLE inventory_lines ADD COLUMN consumed_qty INT NOT NULL DEFAULT 0 AFTER quantity');

        // 5. Add discount columns to sales_order_lines
        $this->db->query("ALTER TABLE sales_order_lines
            ADD COLUMN discount_type ENUM('none','percent','amount') NOT NULL DEFAULT 'none' AFTER total_price,
            ADD COLUMN discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER discount_type,
            ADD COLUMN line_discount DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER discount_value
        ");

        // 6. Seed default report_permissions — admin gets access to all reports
        $now = date('Y-m-d H:i:s');
        $reports = [
            'sales_summary', 'sales_by_part', 'ar_aging', 'ap_aging',
            'po_summary', 'inventory_stock', 'price_list', 'customer_ledger'
        ];
        $roles = ['admin', 'warehouse', 'purchasing', 'approver'];

        // Admin gets all, others get none by default (configurable via matrix)
        foreach ($reports as $rk) {
            $this->db->table('report_permissions')->insert([
                'report_key' => $rk,
                'role'       => 'admin',
                'created_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE sales_order_lines DROP COLUMN line_discount, DROP COLUMN discount_value, DROP COLUMN discount_type");
        $this->db->query("ALTER TABLE inventory_lines DROP COLUMN consumed_qty");
        $this->forge->dropTable('report_permissions', true);
        $this->forge->dropTable('so_line_cogs', true);
        $this->forge->dropTable('part_prices', true);
    }
}
