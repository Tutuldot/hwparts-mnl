<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBirInvoiceNumberToAccountsReceivable extends Migration
{
    public function up()
    {
        $fields = [
            'bir_invoice_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'invoice_number',
            ],
        ];
        $this->forge->addColumn('accounts_receivable', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('accounts_receivable', 'bir_invoice_number');
    }
}
