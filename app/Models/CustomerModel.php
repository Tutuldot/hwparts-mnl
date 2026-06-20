<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table         = 'customers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'type',
        'name',
        'company_name',
        'billing_address',
        'shipping_address',
        'tin',
        'payment_terms',
        'username',
        'password',
        'is_active',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
}
