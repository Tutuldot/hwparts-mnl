<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CashAccountSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'           => 'GCASH A',
                'type'           => 'digital_wallet',
                'account_number' => '09171234567',
                'balance'        => 15000.00,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'GCASH B',
                'type'           => 'digital_wallet',
                'account_number' => '09187654321',
                'balance'        => 8500.50,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'China Bank',
                'type'           => 'bank',
                'account_number' => '123-45678-90',
                'balance'        => 250000.00,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'BPI',
                'type'           => 'bank',
                'account_number' => '9876-5432-10',
                'balance'        => 125300.75,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'Cash on Hand',
                'type'           => 'cash',
                'account_number' => null,
                'balance'        => 5000.00,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        // Using Query Builder
        $this->db->table('cash_accounts')->insertBatch($data);
    }
}
