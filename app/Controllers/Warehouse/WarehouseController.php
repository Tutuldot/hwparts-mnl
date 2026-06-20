<?php

namespace App\Controllers\Warehouse;

use App\Controllers\BaseController;
use App\Models\WarehouseModel;
use App\Models\WarehouseLocationModel;
use App\Models\AuditLogModel;

class WarehouseController extends BaseController
{
    protected WarehouseModel $wm;
    protected WarehouseLocationModel $wlm;
    protected AuditLogModel $audit;

    public function __construct()
    {
        $this->wm    = new WarehouseModel();
        $this->wlm   = new WarehouseLocationModel();
        $this->audit = new AuditLogModel();
    }

    public function index()
    {
        $data = [
            'pageTitle'  => 'Warehouses',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Warehouses', null]],
            'warehouses' => $this->wm->getWithLocationCount(),
        ];
        return view('layouts/main', $data + ['content' => view('warehouse/index', $data)]);
    }

    public function store()
    {
        $rules = [
            'code' => 'required|min_length[2]|max_length[20]|is_unique[warehouses.code]',
            'name' => 'required|min_length[2]|max_length[150]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $id = $this->wm->insert([
            'code'           => strtoupper($this->request->getPost('code')),
            'name'           => $this->request->getPost('name'),
            'address'        => $this->request->getPost('address'),
            'contact_person' => $this->request->getPost('contact_person'),
            'contact_number' => $this->request->getPost('contact_number'),
            'is_active'      => 1,
            'created_by'     => session()->get('user_id'),
        ]);

        $this->audit->log('warehouses', 'create', $id, 'Created warehouse: ' . $this->request->getPost('name'));
        return redirect()->to(base_url('warehouses'))->with('success', 'Warehouse created successfully.');
    }

    public function update(int $id)
    {
        $wh = $this->wm->find($id);
        if (! $wh) return redirect()->back()->with('error', 'Warehouse not found.');

        $rules = [
            'code' => "required|min_length[2]|max_length[20]|is_unique[warehouses.code,id,{$id}]",
            'name' => 'required|min_length[2]|max_length[150]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->wm->update($id, [
            'code'           => strtoupper($this->request->getPost('code')),
            'name'           => $this->request->getPost('name'),
            'address'        => $this->request->getPost('address'),
            'contact_person' => $this->request->getPost('contact_person'),
            'contact_number' => $this->request->getPost('contact_number'),
        ]);

        $this->audit->log('warehouses', 'update', $id, 'Updated warehouse: ' . $wh['name']);
        return redirect()->to(base_url('warehouses'))->with('success', 'Warehouse updated.');
    }

    public function toggle(int $id)
    {
        $wh = $this->wm->find($id);
        if (! $wh) return redirect()->back()->with('error', 'Warehouse not found.');
        $this->wm->update($id, ['is_active' => $wh['is_active'] ? 0 : 1]);
        $this->audit->log('warehouses', 'toggle', $id, 'Toggled warehouse: ' . $wh['name']);
        return redirect()->to(base_url('warehouses'))->with('success', 'Warehouse status updated.');
    }

    public function locations(int $warehouseId)
    {
        $wh = $this->wm->find($warehouseId);
        if (! $wh) return redirect()->to(base_url('warehouses'))->with('error', 'Warehouse not found.');

        $data = [
            'pageTitle'  => $wh['name'] . ' — Locations',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Warehouses', base_url('warehouses')], [$wh['name'], null]],
            'warehouse'  => $wh,
            'locations'  => $this->wlm->getByWarehouse($warehouseId, false),
        ];
        return view('layouts/main', $data + ['content' => view('warehouse/locations', $data)]);
    }

    public function storeLocation(int $warehouseId)
    {
        $rules = [
            'code' => "required|max_length[30]",
            'name' => 'required|max_length[100]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // Check unique code within warehouse
        $existing = $this->wlm->where('warehouse_id', $warehouseId)
            ->where('code', strtoupper($this->request->getPost('code')))->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Location code already exists in this warehouse.');
        }

        $id = $this->wlm->insert([
            'warehouse_id' => $warehouseId,
            'code'         => strtoupper($this->request->getPost('code')),
            'name'         => $this->request->getPost('name'),
            'description'  => $this->request->getPost('description'),
            'is_active'    => 1,
        ]);

        $this->audit->log('warehouse_locations', 'create', $id, "Added location to warehouse #{$warehouseId}");
        return redirect()->to(base_url("warehouses/{$warehouseId}/locations"))->with('success', 'Location added.');
    }

    public function updateLocation(int $locationId)
    {
        $loc = $this->wlm->find($locationId);
        if (! $loc) return redirect()->back()->with('error', 'Location not found.');

        $this->wlm->update($locationId, [
            'code'        => strtoupper($this->request->getPost('code')),
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        $this->audit->log('warehouse_locations', 'update', $locationId, 'Updated location: ' . $loc['name']);
        return redirect()->to(base_url("warehouses/{$loc['warehouse_id']}/locations"))->with('success', 'Location updated.');
    }

    public function toggleLocation(int $locationId)
    {
        $loc = $this->wlm->find($locationId);
        if (! $loc) return redirect()->back()->with('error', 'Location not found.');
        $this->wlm->update($locationId, ['is_active' => $loc['is_active'] ? 0 : 1]);
        return redirect()->to(base_url("warehouses/{$loc['warehouse_id']}/locations"))->with('success', 'Location status updated.');
    }

    public function ajaxLocations()
    {
        $warehouseId = $this->request->getGet('warehouse_id');
        if (! $warehouseId) {
            return $this->response->setJSON([]);
        }
        $locations = $this->wlm->getByWarehouse((int)$warehouseId);
        return $this->response->setJSON($locations);
    }
}
