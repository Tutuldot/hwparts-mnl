<?php

namespace App\Models;

use CodeIgniter\Model;

class PartVariantModel extends Model
{
    protected $table         = 'part_variants';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['part_id', 'variant_name', 'variant_sku', 'barcode_value', 'qr_code_value', 'qr_code_image', 'additional_notes', 'is_active', 'created_at'];
    protected $useTimestamps  = false;

    public function getByPart(int $partId, bool $activeOnly = true): array
    {
        $builder = $this->where('part_id', $partId);
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }
        return $builder->findAll();
    }

    public function generateVariantSku(string $parentSku, int $partId): string
    {
        $count = $this->where('part_id', $partId)->countAllResults();
        return $parentSku . '-V' . ($count + 1);
    }
}
