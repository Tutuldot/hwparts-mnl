<?php

namespace App\Models;

use CodeIgniter\Model;

class DailyCashDeclarationModel extends Model
{
    protected $table         = 'daily_cash_declarations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['declaration_date', 'account_id', 'opening_balance', 'system_balance', 'discrepancy', 'created_by', 'created_at'];
    protected $useTimestamps = false;

    public function getDeclarationForDate(string $date): array
    {
        return $this->db->query("
            SELECT dcd.*, ca.name as account_name, ca.type as account_type, u.name as declared_by_name
            FROM daily_cash_declarations dcd
            JOIN cash_accounts ca ON ca.id = dcd.account_id
            JOIN users u ON u.id = dcd.created_by
            WHERE dcd.declaration_date = ?
            ORDER BY ca.name ASC
        ", [$date])->getResultArray();
    }
}
