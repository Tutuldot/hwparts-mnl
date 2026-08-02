<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWithholdingTaxRateToCustomers extends Migration
{
    public function up(): void
    {
        $fields = [
            'withholding_tax_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
                'after'      => 'payment_terms',
            ],
        ];

        $this->forge->addColumn('customers', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('customers', 'withholding_tax_rate');
    }
}
