<?php
namespace App\Models;
use CodeIgniter\Model;
class PartCategoryModel extends Model
{
    protected $table         = 'part_categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['code','name','description','is_active','created_at'];
    protected $useTimestamps  = false;

    public function getActive()
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
    }
}
