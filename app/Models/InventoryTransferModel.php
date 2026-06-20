<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryTransferModel extends Model
{
    protected $table         = 'inventory_transfers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['transfer_no', 'from_warehouse_id', 'to_warehouse_id', 'status', 'transfer_date', 'remarks', 'created_by', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason', 'completed_by', 'completed_at', 'cancelled_by', 'cancelled_at', 'created_at', 'updated_at'];
    protected $useTimestamps  = true;

    public function generateTransferNo(): string
    {
        $year = date('Y');
        $last = $this->db->query("SELECT transfer_no FROM inventory_transfers WHERE transfer_no LIKE 'TRF-{$year}-%' ORDER BY id DESC LIMIT 1")->getRowArray();
        $seq  = $last ? ((int) substr($last['transfer_no'], -5)) + 1 : 1;
        return 'TRF-' . $year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function getWithWarehouses(int $id): ?array
    {
        return $this->db->query("
            SELECT t.*,
                fw.name as from_warehouse_name, fw.code as from_warehouse_code,
                tw.name as to_warehouse_name, tw.code as to_warehouse_code,
                uc.name as created_by_name,
                us.name as submitted_by_name,
                ua.name as approved_by_name,
                ur.name as rejected_by_name,
                ucp.name as completed_by_name
            FROM inventory_transfers t
            JOIN warehouses fw ON fw.id = t.from_warehouse_id
            JOIN warehouses tw ON tw.id = t.to_warehouse_id
            JOIN users uc ON uc.id = t.created_by
            LEFT JOIN users us ON us.id = t.submitted_by
            LEFT JOIN users ua ON ua.id = t.approved_by
            LEFT JOIN users ur ON ur.id = t.rejected_by
            LEFT JOIN users ucp ON ucp.id = t.completed_by
            WHERE t.id = ?
        ", [$id])->getRowArray();
    }

    public function listWithWarehouses(): array
    {
        return $this->db->query("
            SELECT t.*,
                fw.name as from_warehouse_name,
                tw.name as to_warehouse_name,
                uc.name as created_by_name
            FROM inventory_transfers t
            JOIN warehouses fw ON fw.id = t.from_warehouse_id
            JOIN warehouses tw ON tw.id = t.to_warehouse_id
            JOIN users uc ON uc.id = t.created_by
            ORDER BY t.id DESC
        ")->getResultArray();
    }
}
