<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierContactModel extends Model
{
    protected $table         = 'supplier_contacts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'supplier_id',
        'name',
        'email',
        'mobile',
        'role_or_title',
        'is_visible',
        'created_at'
    ];
    protected $useTimestamps  = false;

    public function getBySupplier(int $supplierId): array
    {
        return $this->where('supplier_id', $supplierId)->orderBy('id', 'ASC')->findAll();
    }

    public function syncContacts(int $supplierId, array $contacts): void
    {
        $this->where('supplier_id', $supplierId)->delete();
        if (!empty($contacts)) {
            $rows = [];
            foreach ($contacts as $c) {
                if (empty(trim($c['name'] ?? ''))) continue;
                $rows[] = [
                    'supplier_id'   => $supplierId,
                    'name'          => trim($c['name']),
                    'email'         => !empty(trim($c['email'] ?? '')) ? trim($c['email']) : null,
                    'mobile'        => !empty(trim($c['mobile'] ?? '')) ? trim($c['mobile']) : null,
                    'role_or_title' => !empty(trim($c['role_or_title'] ?? '')) ? trim($c['role_or_title']) : null,
                    'is_visible'    => isset($c['is_visible']) ? (int)$c['is_visible'] : 1,
                    'created_at'    => date('Y-m-d H:i:s'),
                ];
            }
            if (!empty($rows)) {
                $this->insertBatch($rows);
            }
        }
    }
}
