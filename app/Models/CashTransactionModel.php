<?php

namespace App\Models;

use CodeIgniter\Model;

class CashTransactionModel extends Model
{
    protected $table         = 'cash_transactions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'transaction_number',
        'reference_number',
        'from_account_id',
        'to_account_id',
        'amount',
        'type',
        'reference_source',
        'reference_id',
        'evidence_path',
        'remarks',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;

    public function generateTransactionNumber(): string
    {
        $date = date('Ymd');
        $last = $this->db->query("SELECT transaction_number FROM cash_transactions WHERE transaction_number LIKE 'TXN-{$date}-%' ORDER BY id DESC LIMIT 1")->getRowArray();
        $seq  = $last ? ((int) substr($last['transaction_number'], -5)) + 1 : 1;
        return 'TXN-' . $date . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function getLedger(array $filters = []): array
    {
        $builder = $this->db->table('cash_transactions ct')
            ->select('ct.*, fa.name as from_account_name, ta.name as to_account_name, u.name as created_by_name, ap.name as approved_by_name')
            ->join('cash_accounts fa', 'fa.id = ct.from_account_id', 'left')
            ->join('cash_accounts ta', 'ta.id = ct.to_account_id', 'left')
            ->join('users u', 'u.id = ct.created_by', 'left')
            ->join('users ap', 'ap.id = ct.approved_by', 'left');

        if (!empty($filters['status'])) {
            $builder->where('ct.status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $builder->where('ct.type', $filters['type']);
        }
        if (!empty($filters['account_id'])) {
            $builder->groupStart()
                ->where('ct.from_account_id', $filters['account_id'])
                ->orWhere('ct.to_account_id', $filters['account_id'])
            ->groupEnd();
        }

        return $builder->orderBy('ct.id', 'DESC')->get()->getResultArray();
    }
}
