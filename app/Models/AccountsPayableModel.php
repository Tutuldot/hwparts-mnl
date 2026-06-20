<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountsPayableModel extends Model
{
    protected $table         = 'accounts_payable';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'po_id', 
        'supplier_id', 
        'amount', 
        'due_date', 
        'status', 
        'payment_reference', 
        'payment_type', 
        'cheque_details', 
        'proof_of_payment', 
        'paid_at', 
        'paid_by', 
        'created_at', 
        'updated_at'
    ];
    protected $useTimestamps  = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithDetails(int $id): ?array
    {
        return $this->db->query("
            SELECT ap.*, po.po_number, s.name as supplier_name, s.emails_for_notice, u.name as paid_by_name
            FROM accounts_payable ap
            JOIN purchase_orders po ON po.id = ap.po_id
            JOIN suppliers s ON s.id = ap.supplier_id
            LEFT JOIN users u ON u.id = ap.paid_by
            WHERE ap.id = ?
        ", [$id])->getRowArray();
    }

    public function getAllWithDetails(): array
    {
        return $this->db->query("
            SELECT ap.*, po.po_number, s.name as supplier_name
            FROM accounts_payable ap
            JOIN purchase_orders po ON po.id = ap.po_id
            JOIN suppliers s ON s.id = ap.supplier_id
            ORDER BY ap.due_date ASC, ap.status DESC
        ")->getResultArray();
    }
}
