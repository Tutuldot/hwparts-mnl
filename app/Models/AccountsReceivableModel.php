<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountsReceivableModel extends Model
{
    protected $table         = 'accounts_receivable';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'so_id',
        'customer_id',
        'invoice_number',
        'amount',
        'amount_paid',
        'due_date',
        'status',
        'payment_reference',
        'payment_type',
        'proof_of_payment',
        'paid_at',
        'paid_by',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;

    public function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $last = $this->db->query("SELECT invoice_number FROM accounts_receivable WHERE invoice_number LIKE 'SI-{$year}-%' ORDER BY id DESC LIMIT 1")->getRowArray();
        $seq  = $last ? ((int) substr($last['invoice_number'], -5)) + 1 : 1;
        return 'SI-' . $year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function getWithDetails(int $id): ?array
    {
        return $this->db->query("
            SELECT ar.*, so.so_number, c.name as customer_name, c.company_name, c.billing_address, c.shipping_address, c.tin, c.payment_terms,
                   u.name as paid_by_name
            FROM accounts_receivable ar
            JOIN sales_orders so ON so.id = ar.so_id
            JOIN customers c ON c.id = ar.customer_id
            LEFT JOIN users u ON u.id = ar.paid_by
            WHERE ar.id = ?
        ", [$id])->getRowArray();
    }

    public function getAllWithDetails(): array
    {
        return $this->db->query("
            SELECT ar.*, so.so_number, c.name as customer_name
            FROM accounts_receivable ar
            JOIN sales_orders so ON so.id = ar.so_id
            JOIN customers c ON c.id = ar.customer_id
            ORDER BY ar.due_date ASC, ar.status DESC
        ")->getResultArray();
    }
}
