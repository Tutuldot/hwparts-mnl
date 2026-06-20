<?php

namespace App\Models;

use CodeIgniter\Model;

class POApprovalModel extends Model
{
    protected $table         = 'po_approvals';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['po_id', 'action', 'action_by', 'action_at', 'notes'];
    protected $useTimestamps  = false;

    public function log(int $poId, string $action, int $userId, ?string $notes = null): void
    {
        $this->insert([
            'po_id'     => $poId,
            'action'    => $action,
            'action_by' => $userId,
            'action_at' => date('Y-m-d H:i:s'),
            'notes'     => $notes,
        ]);
    }

    public function getByPo(int $poId): array
    {
        return $this->db->query("
            SELECT pa.*, u.name as action_by_name
            FROM po_approvals pa JOIN users u ON u.id = pa.action_by
            WHERE pa.po_id = ? ORDER BY pa.action_at ASC
        ", [$poId])->getResultArray();
    }
}
