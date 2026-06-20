<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table         = 'warehouses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['code', 'name', 'address', 'contact_person', 'contact_number', 'is_active', 'created_by', 'created_at', 'updated_at'];
    protected $useTimestamps  = true;

    public function getWithLocationCount(): array
    {
        return $this->db->query("
            SELECT w.id, w.code, w.name, w.address, w.contact_person, w.contact_number,
                   w.is_active, w.created_by, w.created_at, w.updated_at,
                   COUNT(wl.id) as location_count
            FROM warehouses w
            LEFT JOIN warehouse_locations wl ON wl.warehouse_id = w.id AND wl.is_active = 1
            GROUP BY w.id, w.code, w.name, w.address, w.contact_person, w.contact_number,
                     w.is_active, w.created_by, w.created_at, w.updated_at
            ORDER BY w.name ASC
        ")->getResultArray();
    }

    public function getStockSummary(int $warehouseId): array
    {
        return $this->db->query("
            SELECT
                COUNT(DISTINCT il.part_id) as total_skus,
                COALESCE(SUM(il.quantity), 0) as total_units
            FROM inventory_lines il
            WHERE il.warehouse_id = ?
        ", [$warehouseId])->getRowArray() ?? ['total_skus' => 0, 'total_units' => 0];
    }

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
    }
}
