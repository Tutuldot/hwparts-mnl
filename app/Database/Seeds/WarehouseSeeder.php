<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Insert default warehouse
        $this->db->table('warehouses')->insert([
            'code'           => 'WH-MAIN',
            'name'           => 'Main Warehouse',
            'address'        => 'Manila, Philippines',
            'contact_person' => 'System Administrator',
            'contact_number' => null,
            'is_active'      => 1,
            'created_by'     => 1,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $warehouseId = $this->db->insertID();

        // Insert default sub-locations
        $locations = [
            ['code' => 'SHF-A',  'name' => 'Shelf A',  'description' => 'Main shelf row A'],
            ['code' => 'SHF-B',  'name' => 'Shelf B',  'description' => 'Main shelf row B'],
            ['code' => 'BIN-01', 'name' => 'Bin 01',   'description' => 'Storage bin 01'],
        ];

        foreach ($locations as &$loc) {
            $loc['warehouse_id'] = $warehouseId;
            $loc['is_active']    = 1;
            $loc['created_at']   = $now;
            $loc['updated_at']   = $now;
        }

        $this->db->table('warehouse_locations')->insertBatch($locations);
    }
}
