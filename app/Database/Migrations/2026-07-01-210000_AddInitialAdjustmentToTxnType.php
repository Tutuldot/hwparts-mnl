<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInitialAdjustmentToTxnType extends Migration
{
    public function up(): void
    {
        $this->db->query("
            ALTER TABLE cash_transactions 
            MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'transfer', 'income', 'expense', 'adjustment', 'advance', 'initial_adjustment') NOT NULL
        ");
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE cash_transactions 
            MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'transfer', 'income', 'expense', 'adjustment', 'advance') NOT NULL
        ");
    }
}
