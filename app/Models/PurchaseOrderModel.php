<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table         = 'purchase_orders';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['po_number', 'supplier_name', 'payment_type', 'payment_due_date', 'proof_of_payment', 'amount', 'status', 'remarks', 'created_by', 'submitted_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason', 'created_at', 'updated_at'];
    protected $useTimestamps  = true;

    public function generatePoNumber(): string
    {
        $year = date('Y');
        $last = $this->db->query("SELECT po_number FROM purchase_orders WHERE po_number LIKE 'PO-{$year}-%' ORDER BY id DESC LIMIT 1")->getRowArray();
        $seq  = $last ? ((int) substr($last['po_number'], -5)) + 1 : 1;
        return 'PO-' . $year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function getWithDetails(int $id): ?array
    {
        return $this->db->query("
            SELECT po.*, u.name as created_by_name, ua.name as approved_by_name, ur.name as rejected_by_name
            FROM purchase_orders po
            JOIN users u ON u.id = po.created_by
            LEFT JOIN users ua ON ua.id = po.approved_by
            LEFT JOIN users ur ON ur.id = po.rejected_by
            WHERE po.id = ?
        ", [$id])->getRowArray();
    }
}
