<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\InventoryHeaderModel;
use App\Models\InventoryLineModel;
use App\Models\PartsDetailModel;
use App\Models\PartModel;
use App\Models\PartVariantModel;
use App\Models\WarehouseModel;
use App\Models\WarehouseLocationModel;
use App\Models\AuditLogModel;

class InventoryController extends BaseController
{
    public function index()
    {
        $headerModel = new InventoryHeaderModel();
        $headers = $headerModel->db->query("
            SELECT ih.*, u.name as created_by_name, w.name as warehouse_name
            FROM inventory_headers ih
            JOIN users u ON u.id = ih.created_by
            LEFT JOIN warehouses w ON w.id = ih.warehouse_id
            ORDER BY ih.id DESC
        ")->getResultArray();

        $data = [
            'pageTitle'  => 'Inventory',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Inventory', null]],
            'headers'    => $headers,
        ];
        return view('layouts/main', $data + ['content' => view('inventory/index', $data)]);
    }

    public function create()
    {
        $data = [
            'pageTitle'  => 'Add Inventory',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Inventory', base_url('inventory')], ['Add', null]],
            'parts'      => (new PartModel())->where('is_active', 1)->orderBy('name')->findAll(),
            'warehouses' => (new WarehouseModel())->getActive(),
        ];
        return view('layouts/main', $data + ['content' => view('inventory/create', $data)]);
    }

    public function store()
    {
        $rules = [
            'warehouse_id' => 'required|integer',
            'lines'        => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please select a warehouse and add at least one line.');
        }

        $headerModel    = new InventoryHeaderModel();
        $lineModel      = new InventoryLineModel();
        $partModel      = new PartModel();
        $variantModel   = new PartVariantModel();
        $detailModel    = new PartsDetailModel();
        $auditModel     = new AuditLogModel();

        $refNo = $headerModel->generateReferenceNo();
        $headerId = $headerModel->insert([
            'reference_no' => $refNo,
            'source'       => 'manual',
            'warehouse_id' => $this->request->getPost('warehouse_id'),
            'remarks'      => $this->request->getPost('remarks'),
            'created_by'   => session()->get('user_id'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $lines = json_decode($this->request->getPost('lines'), true) ?? [];
        foreach ($lines as $line) {
            $part      = $partModel->find($line['part_id']);
            $quantity  = (int)($line['quantity'] ?? 1);
            $unitCost  = (float)($line['unit_cost'] ?? 0);
            $warehouseId = $line['warehouse_id'] ?? $this->request->getPost('warehouse_id');
            $locationId  = $line['warehouse_location_id'] ?? null;

            $lineId = $lineModel->insert([
                'inventory_header_id'   => $headerId,
                'part_id'               => $line['part_id'],
                'variant_id'            => $line['variant_id'] ?? null,
                'warehouse_id'          => $warehouseId,
                'warehouse_location_id' => $locationId ?: null,
                'quantity'              => $part['type'] === 'non_quantity' ? $quantity : $quantity,
                'acquisition_cost'      => $unitCost,
                'total_cost'            => $unitCost * $quantity,
                'remarks'               => $line['remarks'] ?? null,
                'created_at'            => date('Y-m-d H:i:s'),
            ]);

            // For non_quantity parts, auto-generate individual tracked units
            if ($part['type'] === 'non_quantity') {
                for ($i = 1; $i <= $quantity; $i++) {
                    $uniqueQr = $detailModel->generateUniqueQr($part['sku'], $i);
                    $detailModel->insert([
                        'inventory_header_id'   => $headerId,
                        'inventory_line_id'     => $lineId,
                        'part_id'               => $line['part_id'],
                        'variant_id'            => $line['variant_id'] ?? null,
                        'warehouse_id'          => $warehouseId,
                        'warehouse_location_id' => $locationId ?: null,
                        'unique_qr_code'        => $uniqueQr,
                        'status'                => 'available',
                        'consumed'              => 0,
                        'created_date'          => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $auditModel->log('inventory', 'create', $headerId, "Created inventory entry {$refNo}");
        return redirect()->to(base_url("inventory/{$headerId}"))->with('success', "Inventory {$refNo} recorded.");
    }

    public function show(int $id)
    {
        $headerModel = new InventoryHeaderModel();
        $lineModel   = new InventoryLineModel();

        $header = $headerModel->getWithDetails($id);
        if (! $header) return redirect()->to(base_url('inventory'))->with('error', 'Inventory not found.');

        $data = [
            'pageTitle'  => $header['reference_no'],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Inventory', base_url('inventory')], [$header['reference_no'], null]],
            'header'     => $header,
            'lines'      => $lineModel->getByHeader($id),
        ];
        return view('layouts/main', $data + ['content' => view('inventory/show', $data)]);
    }
}
