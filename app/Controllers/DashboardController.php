<?php

namespace App\Controllers;

use App\Models\PartModel;
use App\Models\WarehouseModel;
use App\Models\PurchaseOrderModel;
use App\Models\InventoryTransferModel;
use App\Models\PartStockThresholdModel;
use App\Models\AuditLogModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $partModel     = new PartModel();
        $warehouseModel = new WarehouseModel();
        $poModel       = new PurchaseOrderModel();
        $transferModel = new InventoryTransferModel();
        $thresholdModel = new PartStockThresholdModel();
        $auditModel    = new AuditLogModel();

        $lowStockAlerts = $thresholdModel->getLowStockAlerts();
        session()->set('low_stock_count', count($lowStockAlerts));

        $data = [
            'pageTitle'  => 'Dashboard',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Dashboard', null]],
            'stats' => [
                'total_parts'      => $partModel->where('is_active', 1)->countAllResults(),
                'total_warehouses' => $warehouseModel->where('is_active', 1)->countAllResults(),
                'pending_pos'      => $poModel->whereIn('status', ['submitted'])->countAllResults(),
                'pending_transfers'=> $transferModel->whereIn('status', ['submitted', 'approved', 'in_transit', 'partially_transferred'])->countAllResults(),
                'low_stock'        => count($lowStockAlerts),
            ],
            'warehouses'       => $warehouseModel->getWithLocationCount(),
            'lowStockAlerts'   => array_slice($lowStockAlerts, 0, 10),
            'recentAudit'      => $auditModel->getRecent(8),
            'recentPos'        => $poModel->orderBy('created_at', 'DESC')->limit(5)->findAll(),
            'recentTransfers'  => array_slice($transferModel->listWithWarehouses(), 0, 5),
        ];

        // Pre-load stock summaries so the view doesn't instantiate models in a loop
        $warehouseStocks = [];
        foreach ($data['warehouses'] as $wh) {
            $warehouseStocks[$wh['id']] = $warehouseModel->getStockSummary($wh['id']);
        }
        $data['warehouseStocks'] = $warehouseStocks;

        return view('layouts/main', $data + ['content' => view('dashboard/index', $data)]);
    }
}
