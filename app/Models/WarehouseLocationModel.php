<?php
namespace App\Models;
use CodeIgniter\Model;
class WarehouseLocationModel extends Model
{
    protected $table         = 'warehouse_locations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['warehouse_id','code','name','description','is_active','created_at','updated_at'];
    protected $useTimestamps  = true;

    public function getByWarehouse($warehouseId, $activeOnly = true)
    {
        $builder = $this->where('warehouse_id', $warehouseId);
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }
        return $builder->orderBy('name', 'ASC')->findAll();
    }
}
