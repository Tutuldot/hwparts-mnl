<?php /** @var array $pos */ ?>
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Purchase Orders</h1>
        <p class="page-subtitle"><?= count($pos) ?> total purchase orders recorded</p>
    </div>
    <a href="<?= base_url('purchase-orders/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New Purchase Order
    </a>
</div>

<!-- Summary Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary">
            <div class="card-body py-3">
                <div class="text-muted small font-weight-bold text-uppercase">Total POs</div>
                <div class="fs-4 font-weight-black text-dark mt-1"><?= count($pos) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-warning">
            <div class="card-body py-3">
                <div class="text-muted small font-weight-bold text-uppercase">Unpaid / Pending</div>
                <div class="fs-4 font-weight-black text-warning mt-1">
                    <?= $unpaidCount ?>
                    <?php if ($totalUnpaidAmount > 0): ?>
                        <small class="fs-6 font-weight-normal text-muted ms-1">(₱<?= number_format($totalUnpaidAmount, 2) ?>)</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-danger">
            <div class="card-body py-3">
                <div class="text-muted small font-weight-bold text-uppercase">Already Due / Overdue</div>
                <div class="fs-4 font-weight-black text-danger mt-1">
                    <?= $overdueCount ?>
                    <?php if ($totalOverdueAmount > 0): ?>
                        <small class="fs-6 font-weight-normal text-muted ms-1">(₱<?= number_format($totalOverdueAmount, 2) ?>)</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-success">
            <div class="card-body py-3">
                <div class="text-muted small font-weight-bold text-uppercase">Fully Paid</div>
                <div class="fs-4 font-weight-black text-success mt-1"><?= $paidCount ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" id="poTable">
                <thead class="table-light">
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier Name</th>
                        <th class="text-end">Amount (₱)</th>
                        <th>PO Status</th>
                        <th>Due Date</th>
                        <th class="text-center">Payment Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $today = date('Y-m-d');
                foreach ($pos as $po): 
                    $isOverdue = ($po['payment_indicator'] === 'overdue');
                    $rowClass  = $isOverdue ? 'table-danger bg-danger bg-opacity-10' : '';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td>
                        <a href="<?= base_url("purchase-orders/{$po['id']}") ?>" class="font-monospace fw-bold text-decoration-none text-primary">
                            <?= esc($po['po_number']) ?>
                        </a>
                        <div class="text-muted small"><?= date('M d, Y', strtotime($po['created_at'])) ?></div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?= esc($po['supplier_name']) ?></div>
                        <span class="badge bg-light text-dark font-weight-normal"><?= ucfirst(str_replace('_', ' ', $po['payment_type'] ?? '')) ?></span>
                    </td>
                    <td class="text-end fw-bold text-dark">
                        ₱<?= number_format($po['amount'], 2) ?>
                    </td>
                    <td>
                        <?php
                        $st = $po['status'];
                        $stBadge = 'secondary';
                        if ($st === 'approved') $stBadge = 'primary';
                        elseif (in_array($st, ['received', 'fully_received'])) $stBadge = 'success';
                        elseif ($st === 'submitted') $stBadge = 'info';
                        elseif ($st === 'rejected' || $st === 'cancelled') $stBadge = 'danger';
                        ?>
                        <span class="badge bg-<?= $stBadge ?> text-capitalize">
                            <?= ucfirst(str_replace('_', ' ', $st)) ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($po['effective_due_date'])): 
                            $dueDateFormatted = date('M d, Y', strtotime($po['effective_due_date']));
                            if ($isOverdue): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger font-monospace px-2 py-1" title="Payment Due Exceeded!">
                                    <i class="fas fa-calendar-times me-1"></i><?= $dueDateFormatted ?> (Overdue)
                                </span>
                            <?php elseif ($po['effective_due_date'] === $today): ?>
                                <span class="badge bg-warning bg-opacity-20 text-dark border border-warning font-monospace px-2 py-1">
                                    <i class="fas fa-calendar-day me-1"></i>Due Today (<?= $dueDateFormatted ?>)
                                </span>
                            <?php else: ?>
                                <span class="text-dark font-monospace">
                                    <i class="fas fa-calendar-alt text-muted me-1"></i><?= $dueDateFormatted ?>
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($po['payment_indicator'] === 'overdue'): ?>
                            <span class="badge bg-danger text-white font-weight-bold px-2 py-1" title="Already due and not yet paid!">
                                <i class="fas fa-exclamation-triangle me-1"></i> OVERDUE
                            </span>
                        <?php elseif ($po['payment_indicator'] === 'unpaid'): ?>
                            <span class="badge bg-warning text-dark font-weight-bold px-2 py-1">
                                <i class="fas fa-hourglass-half me-1"></i> UNPAID
                            </span>
                        <?php elseif ($po['payment_indicator'] === 'partially_paid'): ?>
                            <span class="badge bg-info text-dark font-weight-bold px-2 py-1">
                                <i class="fas fa-adjust me-1"></i> PARTIAL
                            </span>
                        <?php elseif ($po['payment_indicator'] === 'paid'): ?>
                            <span class="badge bg-success text-white font-weight-bold px-2 py-1">
                                <i class="fas fa-check-circle me-1"></i> PAID
                            </span>
                        <?php else: ?>
                            <span class="text-muted small">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="<?= base_url("purchase-orders/{$po['id']}") ?>" class="btn btn-sm btn-outline-primary" title="View Purchase Order">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (!empty($po['ap_id'])): ?>
                                <a href="<?= base_url("accounts-payable/{$po['ap_id']}") ?>" class="btn btn-sm btn-outline-success" title="View / Pay Accounts Payable">
                                    <i class="fas fa-money-bill-wave"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($po['status'] === 'draft'): ?>
                                <a href="<?= base_url("purchase-orders/{$po['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary" title="Edit Draft PO">
                                    <i class="fas fa-pencil"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initDataTable('#poTable', { order: [[0, 'desc']] });
    });
</script>
