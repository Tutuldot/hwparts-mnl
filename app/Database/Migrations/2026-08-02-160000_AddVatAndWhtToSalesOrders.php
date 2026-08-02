<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVatAndWhtToSalesOrders extends Migration
{
    public function up(): void
    {
        $fields = [
            'vat_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 12.00,
                'after'      => 'amount',
            ],
            'withholding_tax_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'vat_rate',
            ],
            'vat_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'default'    => 0.00,
                'after'      => 'withholding_tax_rate',
            ],
            'net_of_vat_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'default'    => 0.00,
                'after'      => 'vat_amount',
            ],
            'withholding_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'default'    => 0.00,
                'after'      => 'net_of_vat_amount',
            ],
            'total_amount_due' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'default'    => 0.00,
                'after'      => 'withholding_tax_amount',
            ],
        ];

        $this->forge->addColumn('sales_orders', $fields);

        // Backfill existing rows
        $this->db->query("
            UPDATE sales_orders
            SET 
                vat_rate = 12.00,
                net_of_vat_amount = ROUND(amount / 1.12, 2),
                vat_amount = ROUND(amount - ROUND(amount / 1.12, 2), 2),
                withholding_tax_rate = 0.00,
                withholding_tax_amount = 0.00,
                total_amount_due = amount
            WHERE total_amount_due = 0.00 AND amount > 0
        ");
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales_orders', [
            'vat_rate',
            'withholding_tax_rate',
            'vat_amount',
            'net_of_vat_amount',
            'withholding_tax_amount',
            'total_amount_due',
        ]);
    }
}
