<?php

namespace App\Models;

use CodeIgniter\Model;

class CashAccountModel extends Model
{
    protected $table         = 'cash_accounts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'type', 'account_number', 'balance', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}
