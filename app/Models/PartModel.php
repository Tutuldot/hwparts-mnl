<?php

namespace App\Models;

use CodeIgniter\Model;

class PartModel extends Model
{
    protected $table         = 'parts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['sku', 'name', 'category_id', 'type', 'oem', 'brand', 'description', 'barcode_value', 'qr_code_value', 'qr_code_image', 'unit_of_measure', 'min_stock_level', 'is_active', 'created_by', 'created_at', 'updated_at'];
    protected $useTimestamps  = true;

    public function getWithCategory(int $id): ?array
    {
        return $this->db->query("
            SELECT p.*, pc.name as category_name, pc.code as category_code
            FROM parts p
            JOIN part_categories pc ON pc.id = p.category_id
            WHERE p.id = ?
        ", [$id])->getRowArray();
    }

    public function getAllWithCategory(): array
    {
        return $this->db->query("
            SELECT p.*, pc.name as category_name, pc.code as category_code
            FROM parts p
            JOIN part_categories pc ON pc.id = p.category_id
            ORDER BY p.name ASC
        ")->getResultArray();
    }

    public function generateSku(string $categoryCode): string
    {
        $yymm   = date('ym');
        $prefix = 'HWP-' . $categoryCode . '-' . $yymm . '-';
        $last   = $this->db->query("
            SELECT sku FROM parts WHERE sku LIKE ? ORDER BY id DESC LIMIT 1
        ", [$prefix . '%'])->getRowArray();
        $seq = $last ? ((int) substr($last['sku'], -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getBrandSuggestions(string $term): array
    {
        return $this->db->query("
            SELECT DISTINCT UPPER(brand) as brand
            FROM parts
            WHERE brand IS NOT NULL AND brand != '' AND UPPER(brand) LIKE UPPER(?)
            ORDER BY brand ASC
            LIMIT 15
        ", ['%' . $term . '%'])->getResultArray();
    }

    public function getStockByWarehouse(int $partId, ?int $variantId = null, ?int $warehouseId = null): array
    {
        $sql    = "SELECT il.warehouse_id, w.name as warehouse_name, w.code as warehouse_code, SUM(il.quantity) as stock
                   FROM inventory_lines il JOIN warehouses w ON w.id = il.warehouse_id
                   WHERE il.part_id = ?";
        $params = [$partId];
        if ($variantId) { $sql .= ' AND il.variant_id = ?'; $params[] = $variantId; }
        if ($warehouseId) { $sql .= ' AND il.warehouse_id = ?'; $params[] = $warehouseId; }
        $sql .= ' GROUP BY il.warehouse_id';
        return $this->db->query($sql, $params)->getResultArray();
    }
}
