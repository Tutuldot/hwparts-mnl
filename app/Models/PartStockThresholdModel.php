<?php

namespace App\Models;

use CodeIgniter\Model;

class PartStockThresholdModel extends Model
{
    protected $table         = 'part_stock_thresholds';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['part_id', 'variant_id', 'warehouse_id', 'min_stock_level', 'is_active', 'created_by', 'created_at', 'updated_at'];
    protected $useTimestamps  = true;

    public function getLowStockAlerts(): array
    {
        return $this->db->query("
            SELECT * FROM (
                SELECT
                    p.id          AS part_id,
                    p.name        AS part_name,
                    p.sku,
                    w.id          AS warehouse_id,
                    w.name        AS warehouse_name,
                    w.code        AS warehouse_code,
                    COALESCE(SUM(il.quantity), 0)                         AS current_stock,
                    COALESCE(MAX(pst.min_stock_level), p.min_stock_level) AS threshold
                FROM parts p
                JOIN warehouses w
                    ON w.is_active = 1
                LEFT JOIN part_stock_thresholds pst
                    ON pst.part_id = p.id AND pst.warehouse_id = w.id AND pst.is_active = 1
                LEFT JOIN inventory_lines il
                    ON il.part_id = p.id AND il.warehouse_id = w.id
                WHERE p.type = 'quantity' AND p.is_active = 1
                GROUP BY p.id, p.name, p.sku, w.id, w.name, w.code, p.min_stock_level
            ) AS stock_data
            WHERE current_stock <= threshold
              AND threshold > 0
            ORDER BY (current_stock - threshold) ASC
        ")->getResultArray();
    }

    public function getForPart(int $partId): array
    {
        return $this->db->query("
            SELECT pst.*, w.name as warehouse_name, w.code as warehouse_code
            FROM part_stock_thresholds pst
            JOIN warehouses w ON w.id = pst.warehouse_id
            WHERE pst.part_id = ? AND pst.is_active = 1
        ", [$partId])->getResultArray();
    }
}
