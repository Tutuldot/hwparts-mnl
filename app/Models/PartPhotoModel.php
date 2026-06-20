<?php

namespace App\Models;

use CodeIgniter\Model;

class PartPhotoModel extends Model
{
    protected $table         = 'part_photos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['part_id', 'photo_path', 'is_primary', 'created_at'];
    protected $useTimestamps  = false;

    public function getByPart(int $partId): array
    {
        return $this->where('part_id', $partId)->orderBy('is_primary', 'DESC')->orderBy('id', 'ASC')->findAll();
    }

    public function getPrimaryPhoto(int $partId): ?array
    {
        return $this->where('part_id', $partId)->where('is_primary', 1)->first();
    }

    public function setPrimary(int $partId, int $photoId): void
    {
        $this->db->transStart();
        $this->where('part_id', $partId)->update(null, ['is_primary' => 0]);
        $this->update($photoId, ['is_primary' => 1]);
        $this->db->transComplete();
    }
}
