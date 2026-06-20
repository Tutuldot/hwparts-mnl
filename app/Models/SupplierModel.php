<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table         = 'suppliers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'name',
        'emails_for_notice',
        'contact_name',
        'contact_email',
        'mobile_number',
        'address',
        'tags',
        'is_active',
        'created_by',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps  = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
    }
}
