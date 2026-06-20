<?php
/**
 * Dashboard View
 * @var array $stats
 * @var array $warehouses
 * @var array $lowStockAlerts
 * @var array $recentAudit
 * @var array $recentPos
 * @var array $recentTransfers
 */
?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Good <?= (date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening')) ?>, <?= esc(explode(' ', session()->get('user_name'))[0]) ?>! Here's your supply chain overview.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('inventory/create') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Inventory
        </a>
        <a href="<?= base_url('purchase-orders/create') ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-file-invoice"></i> New PO
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-gears"></i></div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_parts']) ?></div>
                <div class="stat-label">Parts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon indigo"><i class="fas fa-building"></i></div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_warehouses']) ?></div>
                <div class="stat-label">Warehouses</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-file-invoice"></i></div>
            <div>
                <div class="stat-value"><?= number_format($stats['pending_pos']) ?></div>
                <div class="stat-label">Pending POs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-right-left"></i></div>
            <div>
                <div class="stat-value"><?= number_format($stats['pending_transfers']) ?></div>
                <div class="stat-label">Active Transfers</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div>
            <div>
                <div class="stat-value"><?= number_format($stats['low_stock']) ?></div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Warehouse Stock Summary -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-building text-primary me-2"></i> Warehouses</span>
                <a href="<?= base_url('warehouses') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Warehouse</th><th class="text-center">SKUs</th><th class="text-center">Units</th><th class="text-center">Locations</th></tr></thead>
                    <tbody>
                    <?php foreach ($warehouses as $wh):
                        $summary = $warehouseStocks[$wh['id']] ?? ['total_skus' => 0, 'total_units' => 0];
                    ?>
                    <tr>
                        <td>
                            <div class="fw-600"><?= esc($wh['name']) ?></div>
                            <small class="text-muted mono"><?= esc($wh['code']) ?></small>
                        </td>
                        <td class="text-center"><?= number_format($summary['total_skus'] ?? 0) ?></td>
                        <td class="text-center"><?= number_format($summary['total_units'] ?? 0) ?></td>
                        <td class="text-center"><?= number_format($wh['location_count'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($warehouses)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No warehouses found. <a href="<?= base_url('warehouses') ?>">Add one</a>.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-triangle-exclamation text-danger me-2"></i> Low Stock Alerts</span>
                <a href="<?= base_url('thresholds') ?>" class="btn btn-sm btn-outline-danger">Manage</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockAlerts)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="mb-0">All stock levels are healthy!</p>
                    </div>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Part / SKU</th><th>Warehouse</th><th class="text-end">Stock</th><th class="text-end">Min</th></tr></thead>
                    <tbody>
                    <?php foreach ($lowStockAlerts as $alert): ?>
                    <tr>
                        <td>
                            <div><?= esc($alert['part_name']) ?></div>
                            <small class="mono text-muted"><?= esc($alert['sku']) ?></small>
                        </td>
                        <td><span class="badge badge-draft"><?= esc($alert['warehouse_code']) ?></span></td>
                        <td class="text-end"><span class="fw-600 text-danger"><?= number_format($alert['current_stock']) ?></span></td>
                        <td class="text-end text-muted"><?= number_format($alert['threshold']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Recent POs -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-file-invoice me-2"></i> Recent Purchase Orders</span>
                <a href="<?= base_url('purchase-orders') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>PO #</th><th>Supplier</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentPos as $po): ?>
                    <tr>
                        <td><a href="<?= base_url('purchase-orders/' . $po['id']) ?>" class="mono"><?= esc($po['po_number']) ?></a></td>
                        <td><?= esc($po['supplier_name']) ?></td>
                        <td>₱<?= number_format($po['amount'], 2) ?></td>
                        <td><span class="badge badge-<?= esc($po['status']) ?>"><?= ucfirst(str_replace('_', ' ', $po['status'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentPos)): ?><tr><td colspan="4" class="text-center text-muted py-3">No purchase orders yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Transfers -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-right-left me-2"></i> Recent Transfers</span>
                <a href="<?= base_url('transfers') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Transfer #</th><th>Route</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentTransfers as $t): ?>
                    <tr>
                        <td><a href="<?= base_url('transfers/' . $t['id']) ?>" class="mono"><?= esc($t['transfer_no']) ?></a></td>
                        <td>
                            <small class="badge badge-draft"><?= esc($t['from_warehouse_name']) ?></small>
                            <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.7rem"></i>
                            <small class="badge badge-approved"><?= esc($t['to_warehouse_name']) ?></small>
                        </td>
                        <td><span class="badge badge-<?= esc($t['status']) ?>"><?= ucfirst(str_replace('_', ' ', $t['status'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentTransfers)): ?><tr><td colspan="3" class="text-center text-muted py-3">No transfers yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
