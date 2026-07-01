<?php

namespace App\Models;

use CodeIgniter\Model;

class InquiryModel extends Model
{
    protected $table         = 'inquiries';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['customer_id', 'status', 'sales_order_id', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    // Helper to get inquiries with customer name and sales order details
    public function getInquiriesWithDetails($customerId = null)
    {
        $builder = $this->db->table('inquiries i')
            ->select('i.*, c.name as customer_name, c.company_name, so.so_number, so.status as so_status')
            ->join('customers c', 'c.id = i.customer_id')
            ->join('sales_orders so', 'so.id = i.sales_order_id', 'left')
            ->orderBy('i.status', 'ASC') // open first (if sorted alphabetically, closed is last, but since ENUM we do custom or let SQL order)
            ->orderBy('i.id', 'DESC');   // then newest

        if ($customerId !== null) {
            $builder->where('i.customer_id', $customerId);
        }

        return $builder->get()->getResultArray();
    }
}
