<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(PartCategorySeeder::class);
        $this->call(WarehouseSeeder::class);
    }
}
