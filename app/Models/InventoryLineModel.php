<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryLineModel extends Model
{
    protected $table         = 'inventory_lines';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['inventory_header_id', 'part_id', 'variant_id', 'warehouse_id', 'warehouse_location_id', 'transfer_id', 'quantity', 'acquisition_cost', 'total_cost', 'remarks', 'created_at'];
    protected $useTimestamps  = false;

    public function getStockAtWarehouse(int $partId, int $warehouseId, ?int $variantId = null): int
    {
        $builder = $this->selectSum('quantity', 'stock')
            ->where('part_id', $partId)
            ->where('warehouse_id', $warehouseId);
        if ($variantId !== null) {
            $builder->where('variant_id', $variantId);
        }
        $row = $builder->first();
        return (int)($row['stock'] ?? 0);
    }

    public function getByHeader(int $headerId): array
    {
        return $this->db->query("
            SELECT il.*, p.name as part_name, p.sku, p.type as part_type,
                   pv.variant_name, w.name as warehouse_name, wl.name as location_name
            FROM inventory_lines il
            JOIN parts p ON p.id = il.part_id
            LEFT JOIN part_variants pv ON pv.id = il.variant_id
            JOIN warehouses w ON w.id = il.warehouse_id
            LEFT JOIN warehouse_locations wl ON wl.id = il.warehouse_location_id
            WHERE il.inventory_header_id = ?
        ", [$headerId])->getResultArray();
    }
}
