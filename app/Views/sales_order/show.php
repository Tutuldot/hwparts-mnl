<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Sales Order Details</h1>
        <p class="page-subtitle">View draft, approved, or cancelled customer POS sales orders</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('sales-orders') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
        
        <?php if ($order['status'] === 'draft'): ?>
            <form action="<?= base_url('sales-orders/' . $order['id'] . '/approve') ?>" method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this Sales Order? This will generate a BIR sequential Accounts Receivable Invoice.')">
                    <i class="fas fa-check-circle me-1"></i> Approve & Billing
                </button>
            </form>
            <form action="<?= base_url('sales-orders/' . $order['id'] . '/cancel') ?>" method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this Sales Order?')">
                    <i class="fas fa-times-circle me-1"></i> Cancel Order
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Summary Details Card -->
    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-file-invoice fa-3x text-primary"></i>
                </div>
                <h4 class="card-title mb-1 font-weight-bold"><?= esc($order['so_number']) ?></h4>
                <p class="text-muted small mb-3">POS SALES INVOICE DRAFT</p>
                <div class="d-flex justify-content-center">
                    <?php if ($order['status'] === 'approved'): ?>
                        <span class="badge bg-success px-3 py-2 fs-6">Approved</span>
                    <?php elseif ($order['status'] === 'cancelled'): ?>
                        <span class="badge bg-danger px-3 py-2 fs-6">Cancelled</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark px-3 py-2 fs-6">Draft</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="list-group list-group-flush border-top small">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Total Amount</span>
                    <span class="font-weight-bold text-primary">₱<?= number_format($order['amount'], 2) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Date Created</span>
                    <span class="font-weight-medium text-end"><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Created By</span>
                    <span class="font-weight-medium text-end"><?= esc($order['created_by_name'] ?: 'System') ?></span>
                </div>
                <?php if ($order['status'] === 'approved' && !empty($order['approved_by_name'])): ?>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Approved By</span>
                        <span class="font-weight-medium text-end text-success"><?= esc($order['approved_by_name']) ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Approved At</span>
                        <span class="font-weight-medium text-end"><?= date('M d, Y h:i A', strtotime($order['approved_at'])) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Associated AR Invoice Connection -->
        <?php if ($order['status'] === 'approved' && !empty($arRecord)): ?>
            <div class="card mb-4 shadow-sm border-0 border-start border-success border-4">
                <div class="card-header bg-white font-weight-bold text-success">Accounts Receivable Connected</div>
                <div class="card-body small">
                    <div class="mb-2">
                        <span class="text-muted d-block">BIR Invoice Number</span>
                        <span class="font-weight-bold text-dark fs-5 font-monospace"><?= esc($arRecord['invoice_number']) ?></span>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted d-block">AR Payment Due Date</span>
                        <span class="font-weight-medium text-danger"><?= date('M d, Y', strtotime($arRecord['due_date'])) ?></span>
                    </div>
                    <a href="<?= base_url('accounts-receivable/' . $arRecord['id']) ?>" class="btn btn-sm btn-success w-100 font-weight-bold">
                        <i class="fas fa-external-link-alt me-1"></i> Go to AR Settlement
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Customer Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold">Customer Contact Info</div>
            <div class="card-body small">
                <div class="mb-2">
                    <span class="text-muted d-block">Account Name</span>
                    <a href="<?= base_url('customers/' . $order['customer_id']) ?>" class="font-weight-bold text-dark">
                        <?= esc($order['customer_name']) ?>
                    </a>
                </div>
                <?php if ($order['company_name']): ?>
                    <div class="mb-2">
                        <span class="text-muted d-block">Company Name</span>
                        <span class="font-weight-medium text-dark"><?= esc($order['company_name']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="mb-2">
                    <span class="text-muted d-block">TIN</span>
                    <span class="font-weight-medium text-dark"><?= esc($order['tin'] ?: 'N/A') ?></span>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Billing Address</span>
                    <div class="bg-light p-2 rounded text-muted"><?= nl2br(esc($order['billing_address'])) ?></div>
                </div>
                <div>
                    <span class="text-muted d-block">Shipping Address</span>
                    <div class="bg-light p-2 rounded text-muted"><?= nl2br(esc($order['shipping_address'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Listing Table -->
    <div class="col-lg-8">
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold">Sales Order Item Details</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 small">
                        <thead class="table-light text-uppercase font-weight-bold">
                            <tr>
                                <th>Item / Part Name</th>
                                <th>SKU</th>
                                <th class="text-center">Quantity</th>
                                <th>Unit Price</th>
                                <th class="text-end">Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lines as $line): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= esc($line['part_name']) ?></div>
                                        <?php if (!empty($line['variant_name'])): ?>
                                            <span class="badge bg-light text-dark font-weight-normal"><?= esc($line['variant_name']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-monospace text-muted"><?= esc($line['sku']) ?></td>
                                    <td class="text-center font-weight-medium"><?= esc($line['quantity']) ?></td>
                                    <td class="font-weight-medium">₱<?= number_format($line['unit_price'], 2) ?></td>
                                    <td class="text-end font-weight-bold text-dark">₱<?= number_format($line['total_price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light">
                                <td colspan="4" class="text-end font-weight-bold">Order Summary Amount:</td>
                                <td class="text-end font-weight-black text-primary fs-5">₱<?= number_format($order['amount'], 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($order['remarks'])): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white font-weight-bold">Order Notes / Remarks</div>
                <div class="card-body small text-muted">
                    <?= nl2br(esc($order['remarks'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
