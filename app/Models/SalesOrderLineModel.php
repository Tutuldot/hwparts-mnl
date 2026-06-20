<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesOrderLineModel extends Model
{
    protected $table         = 'sales_order_lines';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'so_id',
        'part_id',
        'variant_id',
        'quantity',
        'unit_price',
        'total_price',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;

    public function getBySo(int $soId): array
    {
        return $this->db->query("
            SELECT sol.*, p.name as part_name, p.sku, p.type as part_type, v.variant_name
            FROM sales_order_lines sol
            JOIN parts p ON p.id = sol.part_id
            LEFT JOIN part_variants v ON v.id = sol.variant_id
            WHERE sol.so_id = ?
        ", [$soId])->getResultArray();
    }
}
