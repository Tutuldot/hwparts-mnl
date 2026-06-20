<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'module', 'action', 'record_id', 'description', 'ip_address', 'created_at'];
    protected $useTimestamps  = false;

    public function log(string $module, string $action, int $recordId, string $description): void
    {
        $this->insert([
            'user_id'     => session()->get('user_id') ?? 0,
            'module'      => $module,
            'action'      => $action,
            'record_id'   => $recordId,
            'description' => $description,
            'ip_address'  => service('request')->getIPAddress(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getRecent(int $limit = 100): array
    {
        return $this->db->query("
            SELECT al.*, u.name as user_name, u.role as user_role
            FROM audit_logs al JOIN users u ON u.id = al.user_id
            ORDER BY al.id DESC LIMIT ?
        ", [$limit])->getResultArray();
    }
}
