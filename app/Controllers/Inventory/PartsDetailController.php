<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\PartsDetailModel;
use App\Models\AuditLogModel;

class PartsDetailController extends BaseController
{
    public function index()
    {
        $model   = new PartsDetailModel();
        $details = $model->db->query("
            SELECT pd.*, p.name as part_name, p.sku, pv.variant_name,
                   w.name as warehouse_name, w.code as warehouse_code,
                   wl.name as location_name
            FROM parts_details pd
            JOIN parts p ON p.id = pd.part_id
            LEFT JOIN part_variants pv ON pv.id = pd.variant_id
            LEFT JOIN warehouses w ON w.id = pd.warehouse_id
            LEFT JOIN warehouse_locations wl ON wl.id = pd.warehouse_location_id
            ORDER BY pd.id DESC
        ")->getResultArray();

        $data = [
            'pageTitle'  => 'Tracked Units',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Tracked Units', null]],
            'details'    => $details,
        ];
        return view('layouts/main', $data + ['content' => view('inventory/parts_details', $data)]);
    }

    public function show(int $id)
    {
        $model = new PartsDetailModel();
        $detail = $model->getWithDetails($id);
        if (! $detail) return redirect()->to(base_url('parts-details'))->with('error', 'Unit not found.');

        $data = [
            'pageTitle'  => 'Unit #' . $id,
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Tracked Units', base_url('parts-details')], ['#' . $id, null]],
            'detail'     => $detail,
        ];
        return view('layouts/main', $data + ['content' => view('inventory/parts_detail_show', $data)]);
    }

    public function consume(int $id)
    {
        $model = new PartsDetailModel();
        $detail = $model->find($id);
        if (! $detail || $detail['consumed']) {
            return redirect()->back()->with('error', 'Unit not found or already consumed.');
        }

        $model->update($id, [
            'status'        => 'consumed',
            'consumed'      => 1,
            'consumed_date' => date('Y-m-d H:i:s'),
            'consumed_by'   => session()->get('user_id'),
            'remarks'       => $this->request->getPost('remarks'),
        ]);

        (new AuditLogModel())->log('parts_details', 'consume', $id, "Consumed unit #{$id}");
        return redirect()->to(base_url("parts-details/{$id}"))->with('success', 'Unit marked as consumed.');
    }
}
