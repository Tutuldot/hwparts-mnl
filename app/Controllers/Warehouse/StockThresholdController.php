<?php

namespace App\Controllers\Warehouse;

use App\Controllers\BaseController;
use App\Models\PartStockThresholdModel;
use App\Models\PartModel;
use App\Models\WarehouseModel;

class StockThresholdController extends BaseController
{
    public function index()
    {
        $model     = new PartStockThresholdModel();
        $partModel = new PartModel();
        $whModel   = new WarehouseModel();

        $data = [
            'pageTitle'  => 'Stock Thresholds',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Stock Thresholds', null]],
            'thresholds' => $model->db->query("
                SELECT pst.*, p.name as part_name, p.sku, w.name as warehouse_name, w.code as warehouse_code
                FROM part_stock_thresholds pst
                JOIN parts p ON p.id = pst.part_id
                JOIN warehouses w ON w.id = pst.warehouse_id
                WHERE pst.is_active = 1
                ORDER BY p.name, w.name
            ")->getResultArray(),
            'parts'      => $partModel->where('is_active', 1)->orderBy('name')->findAll(),
            'warehouses' => $whModel->getActive(),
        ];
        return view('layouts/main', $data + ['content' => view('warehouse/thresholds', $data)]);
    }

    public function store()
    {
        $model = new PartStockThresholdModel();
        $model->insert([
            'part_id'         => $this->request->getPost('part_id'),
            'variant_id'      => $this->request->getPost('variant_id') ?: null,
            'warehouse_id'    => $this->request->getPost('warehouse_id'),
            'min_stock_level' => (int)$this->request->getPost('min_stock_level'),
            'is_active'       => 1,
            'created_by'      => session()->get('user_id'),
        ]);
        return redirect()->to(base_url('thresholds'))->with('success', 'Threshold saved.');
    }

    public function update(int $id)
    {
        $model = new PartStockThresholdModel();
        $model->update($id, ['min_stock_level' => (int)$this->request->getPost('min_stock_level')]);
        return redirect()->to(base_url('thresholds'))->with('success', 'Threshold updated.');
    }

    public function delete(int $id)
    {
        $model = new PartStockThresholdModel();
        $model->update($id, ['is_active' => 0]);
        return redirect()->to(base_url('thresholds'))->with('success', 'Threshold removed.');
    }
}
