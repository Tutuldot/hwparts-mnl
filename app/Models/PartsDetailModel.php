<?php

namespace App\Models;

use CodeIgniter\Model;

class PartsDetailModel extends Model
{
    protected $table         = 'parts_details';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['inventory_header_id', 'inventory_line_id', 'part_id', 'variant_id', 'warehouse_id', 'warehouse_location_id', 'unique_qr_code', 'qr_code_image', 'status', 'consumed', 'sales_order_id', 'remarks', 'actual_photo', 'created_date', 'consumed_date', 'consumed_by', 'updated_at'];
    protected $useTimestamps  = false;

    public function generateUniqueQr(string $sku, int $seq): string
    {
        return 'HWPD-' . $sku . '-' . date('YmdHis') . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function getAvailableAtWarehouse(int $partId, int $warehouseId, ?int $variantId = null): array
    {
        $builder = $this->where('part_id', $partId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'available')
            ->where('consumed', 0);
        if ($variantId !== null) {
            $builder->where('variant_id', $variantId);
        }
        return $builder->findAll();
    }

    public function getWithDetails(int $id): ?array
    {
        return $this->db->query("
            SELECT pd.*, p.name as part_name, p.sku, pv.variant_name,
                   w.name as warehouse_name, wl.name as location_name
            FROM parts_details pd
            JOIN parts p ON p.id = pd.part_id
            LEFT JOIN part_variants pv ON pv.id = pd.variant_id
            LEFT JOIN warehouses w ON w.id = pd.warehouse_id
            LEFT JOIN warehouse_locations wl ON wl.id = pd.warehouse_location_id
            WHERE pd.id = ?
        ", [$id])->getRowArray();
    }
}
