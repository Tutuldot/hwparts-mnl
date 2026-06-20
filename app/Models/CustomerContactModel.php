<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerContactModel extends Model
{
    protected $table         = 'customer_contacts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'customer_id',
        'contact_type',
        'value',
        'remarks',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
}
