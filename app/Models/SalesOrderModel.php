<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesOrderModel extends Model
{
    protected $table         = 'sales_orders';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'so_number',
        'customer_id',
        'amount',
        'status',
        'remarks',
        'created_by',
        'approved_by',
        'approved_at',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;

    public function generateSoNumber(): string
    {
        $year = date('Y');
        $last = $this->db->query("SELECT so_number FROM sales_orders WHERE so_number LIKE 'SO-{$year}-%' ORDER BY id DESC LIMIT 1")->getRowArray();
        $seq  = $last ? ((int) substr($last['so_number'], -5)) + 1 : 1;
        return 'SO-' . $year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function getWithDetails(int $id): ?array
    {
        return $this->db->query("
            SELECT so.*, c.name as customer_name, c.company_name, c.billing_address, c.shipping_address, c.tin, c.payment_terms,
                   u.name as created_by_name, ua.name as approved_by_name
            FROM sales_orders so
            JOIN customers c ON c.id = so.customer_id
            LEFT JOIN users u ON u.id = so.created_by
            LEFT JOIN users ua ON ua.id = so.approved_by
            WHERE so.id = ?
        ", [$id])->getRowArray();
    }

    public function getAllWithDetails(): array
    {
        return $this->db->query("
            SELECT so.*, c.name as customer_name, u.name as created_by_name
            FROM sales_orders so
            JOIN customers c ON c.id = so.customer_id
            LEFT JOIN users u ON u.id = so.created_by
            ORDER BY so.id DESC
        ")->getResultArray();
    }
}
