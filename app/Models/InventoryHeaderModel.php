<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryHeaderModel extends Model
{
    protected $table         = 'inventory_headers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['reference_no', 'source', 'po_id', 'transfer_id', 'warehouse_id', 'remarks', 'created_by', 'created_at'];
    protected $useTimestamps  = false;

    public function generateReferenceNo(): string
    {
        $year = date('Y');
        $last = $this->db->query("SELECT reference_no FROM inventory_headers WHERE reference_no LIKE 'INV-{$year}-%' ORDER BY id DESC LIMIT 1")->getRowArray();
        $seq  = $last ? ((int) substr($last['reference_no'], -5)) + 1 : 1;
        return 'INV-' . $year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function getWithDetails(int $id): ?array
    {
        return $this->db->query("
            SELECT ih.*, w.name as warehouse_name, u.name as created_by_name
            FROM inventory_headers ih
            LEFT JOIN warehouses w ON w.id = ih.warehouse_id
            JOIN users u ON u.id = ih.created_by
            WHERE ih.id = ?
        ", [$id])->getRowArray();
    }
}
