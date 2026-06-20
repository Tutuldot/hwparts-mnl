<?php /** @var array $po @var array $lines @var array $approvals @var string $role */ ?>
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title"><?= esc($po['po_number']) ?></h1>
        <p class="page-subtitle">Supplier: <?= esc($po['supplier_name']) ?> &middot; Created by <?= esc($po['created_by_name']) ?> on <?= date('M d, Y', strtotime($po['created_at'])) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <span class="badge badge-<?= esc($po['status']) ?> fs-6"><?= ucfirst(str_replace('_', ' ', $po['status'])) ?></span>
        <a href="<?= base_url('purchase-orders') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <?php if ($po['status'] === 'draft'): ?>
            <a href="<?= base_url("purchase-orders/{$po['id']}/edit") ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-pencil"></i> Edit</a>
            <form action="<?= base_url("purchase-orders/{$po['id']}/submit") ?>" method="POST" class="d-inline">
                <?= csrf_field() ?><button class="btn btn-primary btn-sm" onclick="return confirm('Submit for approval?')"><i class="fas fa-paper-plane"></i> Submit</button>
            </form>
            <form action="<?= base_url("purchase-orders/{$po['id']}/cancel") ?>" method="POST" class="d-inline">
                <?= csrf_field() ?><button class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this PO?')"><i class="fas fa-ban"></i> Cancel</button>
            </form>
        <?php elseif ($po['status'] === 'submitted' && in_array($role, ['admin','approver'])): ?>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal"><i class="fas fa-check"></i> Approve</button>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times"></i> Reject</button>
        <?php elseif (in_array($po['status'], ['approved','partially_received'])): ?>
            <a href="<?= base_url("purchase-orders/{$po['id']}/receive") ?>" class="btn btn-success btn-sm"><i class="fas fa-boxes-stacking"></i> Receive Items</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <!-- Line Items -->
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Line Items</span></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Part</th><th>SKU</th><th>Type</th><th class="text-center">Ordered</th><th class="text-center">Received</th><th class="text-end">Unit Cost</th><th class="text-end">Total</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($lines as $l): ?>
                    <tr class="<?= $l['is_received'] ? 'table-success' : '' ?>">
                        <td><?= esc($l['part_name']) ?><?php if ($l['variant_name']): ?><br><small class="text-muted"><?= esc($l['variant_name']) ?></small><?php endif; ?></td>
                        <td><span class="mono small"><?= esc($l['sku']) ?></span></td>
                        <td><span class="badge badge-<?= esc($l['part_type']) ?>"><?= $l['part_type'] === 'quantity' ? 'Qty' : 'Non-Qty' ?></span></td>
                        <td class="text-center"><?= $l['quantity_ordered'] ?></td>
                        <td class="text-center"><?= $l['quantity_received'] ?></td>
                        <td class="text-end">₱<?= number_format($l['unit_cost'], 2) ?></td>
                        <td class="text-end">₱<?= number_format($l['total_cost'], 2) ?></td>
                        <td><span class="badge badge-<?= $l['is_received'] ? 'approved' : 'submitted' ?>"><?= $l['is_received'] ? 'Done' : 'Pending' ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr>
                        <td colspan="6" class="text-end fw-600">Total Amount:</td>
                        <td class="text-end fw-700 text-primary">₱<?= number_format($po['amount'], 2) ?></td>
                        <td></td>
                    </tr></tfoot>
                </table>
            </div>
        </div>

        <!-- Approval History -->
        <div class="card">
            <div class="card-header"><span class="card-title">Approval History</span></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Action</th><th>By</th><th>Date</th><th>Notes</th></tr></thead>
                    <tbody>
                    <?php foreach ($approvals as $a): ?>
                    <tr><td><span class="badge badge-<?= esc($a['action']) ?>"><?= ucfirst($a['action']) ?></span></td>
                        <td><?= esc($a['action_by_name'] ?? '—') ?></td>
                        <td><?= date('M d, Y H:i', strtotime($a['action_at'])) ?></td>
                        <td class="text-muted"><?= esc($a['notes'] ?? '—') ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Order Summary</span></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">PO Number</td><td class="fw-500 mono"><?= esc($po['po_number']) ?></td></tr>
                    <tr><td class="text-muted">Supplier</td><td class="fw-500"><?= esc($po['supplier_name']) ?></td></tr>
                    <tr><td class="text-muted">Payment</td><td><?= ucfirst(str_replace('_',' ',$po['payment_type'])) ?></td></tr>
                    <tr><td class="text-muted">Amount</td><td class="fw-700 text-primary">₱<?= number_format($po['amount'],2) ?></td></tr>
                    <tr><td class="text-muted">Status</td><td><span class="badge badge-<?= esc($po['status']) ?>"><?= ucfirst(str_replace('_',' ',$po['status'])) ?></span></td></tr>
                    <?php if ($po['proof_of_payment']): ?>
                    <tr><td class="text-muted">Proof</td><td><a href="<?= base_url($po['proof_of_payment']) ?>" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-file"></i> View</a></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Approve Purchase Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="<?= base_url("purchase-orders/{$po['id']}/approve") ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <p>Are you sure you want to approve <strong><?= esc($po['po_number']) ?></strong>?</p>
                <label class="form-label">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Approve</button></div>
        </form>
    </div></div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Reject Purchase Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="<?= base_url("purchase-orders/{$po['id']}/reject") ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <label class="form-label">Rejection Reason *</label>
                <textarea name="reason" class="form-control" rows="3" required></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject</button></div>
        </form>
    </div></div>
</div>
