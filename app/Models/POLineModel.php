<?php

namespace App\Models;

use CodeIgniter\Model;

class POLineModel extends Model
{
    protected $table         = 'purchase_order_lines';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['po_id', 'part_id', 'variant_id', 'quantity_ordered', 'quantity_received', 'unit_cost', 'total_cost', 'is_received', 'received_at', 'received_by', 'remarks'];
    protected $useTimestamps  = false;

    public function getByPo(int $poId): array
    {
        return $this->db->query("
            SELECT pol.*, p.name as part_name, p.sku, p.type as part_type, pv.variant_name
            FROM purchase_order_lines pol
            JOIN parts p ON p.id = pol.part_id
            LEFT JOIN part_variants pv ON pv.id = pol.variant_id
            WHERE pol.po_id = ?
            ORDER BY pol.id ASC
        ", [$poId])->getResultArray();
    }
}
