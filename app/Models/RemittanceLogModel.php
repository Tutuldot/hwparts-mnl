<?php

namespace App\Models;

use CodeIgniter\Model;

class RemittanceLogModel extends Model
{
    protected $table         = 'remittance_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'ap_id',
        'type',
        'recipient',
        'message',
        'status',
        'created_at'
    ];
    protected $useTimestamps = false;
}
