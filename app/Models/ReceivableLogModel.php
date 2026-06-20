<?php

namespace App\Models;

use CodeIgniter\Model;

class ReceivableLogModel extends Model
{
    protected $table         = 'receivable_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'ar_id',
        'notice_type',
        'type',
        'recipient',
        'message',
        'status',
        'created_at'
    ];
    protected $useTimestamps = false;
}
