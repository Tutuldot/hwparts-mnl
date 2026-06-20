<?php

namespace App\Models;

use CodeIgniter\Model;

class PartCarTagModel extends Model
{
    protected $table         = 'part_car_tags';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['part_id', 'brand', 'model'];
    protected $useTimestamps  = false;

    public function getByPart(int $partId): array
    {
        return $this->where('part_id', $partId)->findAll();
    }

    public function getBrandSuggestions(string $term): array
    {
        return $this->db->query("SELECT DISTINCT brand FROM part_car_tags WHERE brand LIKE ? ORDER BY brand LIMIT 10", ['%' . $term . '%'])->getResultArray();
    }

    public function getModelSuggestions(string $brand, string $term): array
    {
        return $this->db->query("SELECT DISTINCT model FROM part_car_tags WHERE brand = ? AND model LIKE ? ORDER BY model LIMIT 10", [$brand, '%' . $term . '%'])->getResultArray();
    }

    public function syncTags(int $partId, array $tags): void
    {
        $this->where('part_id', $partId)->delete();
        if (!empty($tags)) {
            $rows = array_map(fn($t) => ['part_id' => $partId, 'brand' => $t['brand'], 'model' => $t['model']], $tags);
            $this->insertBatch($rows);
        }
    }
}
