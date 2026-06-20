<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PartCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $categories = [
            ['code' => 'SEN', 'name' => 'Sensor'],
            ['code' => 'SHK', 'name' => 'Shocks & Struts'],
            ['code' => 'BRK', 'name' => 'Brakes'],
            ['code' => 'FLT', 'name' => 'Filters'],
            ['code' => 'BLT', 'name' => 'Belts & Chains'],
            ['code' => 'LGT', 'name' => 'Lights & Lamps'],
            ['code' => 'ENG', 'name' => 'Engine Parts'],
            ['code' => 'BDY', 'name' => 'Body Parts'],
            ['code' => 'ELC', 'name' => 'Electrical & Electronics'],
            ['code' => 'SUS', 'name' => 'Suspension'],
            ['code' => 'TRN', 'name' => 'Transmission'],
            ['code' => 'CLG', 'name' => 'Cooling System'],
            ['code' => 'EXH', 'name' => 'Exhaust System'],
            ['code' => 'STR', 'name' => 'Steering'],
            ['code' => 'TYR', 'name' => 'Tyres & Wheels'],
            ['code' => 'GSK', 'name' => 'Gaskets & Seals'],
            ['code' => 'BRG', 'name' => 'Bearings'],
            ['code' => 'FST', 'name' => 'Fasteners & Hardware'],
            ['code' => 'FLD', 'name' => 'Fluids & Lubricants'],
            ['code' => 'OTH', 'name' => 'Other'],
        ];

        foreach ($categories as &$cat) {
            $cat['is_active']  = 1;
            $cat['created_at'] = $now;
        }

        $this->db->table('part_categories')->insertBatch($categories);
    }
}
