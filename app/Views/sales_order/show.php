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
            <a href="<?= base_url('sales-orders/' . $order['id'] . '/edit') ?>" class="btn btn-warning text-dark btn-sm">
                <i class="fas fa-edit me-1"></i> Edit Order
            </a>
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
        <?php elseif ($order['status'] === 'approved' && $arRecord && $arRecord['status'] === 'unpaid'): ?>
            <form action="<?= base_url('sales-orders/' . $order['id'] . '/cancel') ?>" method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this APPROVED Sales Order? This will cancel the associated Accounts Receivable Invoice and restore the stock levels in the warehouse.')">
                    <i class="fas fa-times-circle me-1"></i> Cancel Order & Restore Stock
                </button>
            </form>
        <?php endif; ?>

        <?php if (in_array(session()->get('user_role') ?: session()->get('role'), ['admin', 'superadmin'])): ?>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#overrideWhtModal">
                <i class="fas fa-percent me-1"></i> Override WHT Rate
            </button>
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
                    <span class="text-muted">Total Sales (Gross)</span>
                    <span class="font-weight-medium">₱<?= number_format($order['amount'], 2) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between bg-light">
                    <span class="font-weight-bold text-dark">Total Amount Due</span>
                    <span class="font-weight-black text-primary fs-6">₱<?= number_format(($order['total_amount_due'] > 0 ? $order['total_amount_due'] : $order['amount']), 2) ?></span>
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
                                <th>Item / Part Name & SKU</th>
                                <th class="text-center">Qty</th>
                                <th>Unit Price</th>
                                <th>Discount</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grossSales     = (float)$order['amount'];
                            $vatRate        = (float)($order['vat_rate'] ?? 12.00);
                            $whtRate        = (float)($order['withholding_tax_rate'] ?? 0.00);
                            $netOfVat       = (float)($order['net_of_vat_amount'] > 0 ? $order['net_of_vat_amount'] : ($vatRate > 0 ? round($grossSales / (1 + ($vatRate / 100)), 2) : $grossSales));
                            $vatAmount      = (float)($order['vat_amount'] > 0 ? $order['vat_amount'] : round($grossSales - $netOfVat, 2));
                            $whtAmount      = (float)($order['withholding_tax_amount'] >= 0 && isset($order['net_of_vat_amount']) ? $order['withholding_tax_amount'] : round($netOfVat * ($whtRate / 100), 2));
                            $totalAmountDue = (float)($order['total_amount_due'] > 0 ? $order['total_amount_due'] : round($grossSales - $whtAmount, 2));

                            $grossSubtotal  = 0;
                            $totalDiscount  = 0;
                            foreach ($lines as $line):
                                $gross = $line['quantity'] * $line['unit_price'];
                                $disc  = (float)($line['line_discount'] ?? 0);
                                $grossSubtotal += $gross;
                                $totalDiscount += $disc;
                            ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= esc($line['part_name']) ?></div>
                                        <?php if (!empty($line['variant_name'])): ?>
                                            <span class="badge bg-light text-dark font-weight-normal mb-1"><?= esc($line['variant_name']) ?></span>
                                        <?php endif; ?>
                                        <div class="text-muted small font-monospace"><i class="fas fa-barcode me-1"></i>SKU: <?= esc($line['sku']) ?></div>
                                    </td>
                                    <td class="text-center font-weight-medium"><?= esc($line['quantity']) ?></td>
                                    <td class="font-weight-medium">₱<?= number_format($line['unit_price'], 2) ?></td>
                                    <td>
                                        <?php if (($line['discount_type'] ?? 'none') !== 'none' && (float)($line['discount_value'] ?? 0) > 0): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                <?= $line['discount_type'] === 'percent'
                                                    ? esc($line['discount_value']) . '%'
                                                    : '₱' . number_format($line['discount_value'], 2) . '/unit' ?>
                                            </span>
                                            <div class="text-danger small">-₱<?= number_format($disc, 2) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end font-weight-bold text-dark">₱<?= number_format($line['total_price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($totalDiscount > 0): ?>
                            <tr class="table-warning">
                                <td colspan="4" class="text-end text-muted">Gross Subtotal:</td>
                                <td class="text-end">₱<?= number_format($grossSubtotal, 2) ?></td>
                            </tr>
                            <tr class="table-danger">
                                <td colspan="4" class="text-end text-danger font-weight-bold">Total Discount:</td>
                                <td class="text-end text-danger font-weight-bold">-₱<?= number_format($totalDiscount, 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-light">
                                <td colspan="4" class="text-end font-weight-bold">Total Sales (Gross Amount):</td>
                                <td class="text-end font-weight-bold text-dark fs-6">₱<?= number_format($grossSales, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end text-muted">Less VAT (<?= number_format($vatRate, 2) ?>%):</td>
                                <td class="text-end text-danger">-₱<?= number_format($vatAmount, 2) ?></td>
                            </tr>
                            <tr class="table-light">
                                <td colspan="4" class="text-end font-weight-bold text-dark">Amount Net of VAT:</td>
                                <td class="text-end font-weight-bold text-dark">₱<?= number_format($netOfVat, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end text-muted">
                                    Less Withholding Tax (<?= number_format($whtRate, 2) ?>%):
                                    <?php if (in_array(session()->get('user_role') ?: session()->get('role'), ['admin', 'superadmin'])): ?>
                                        <button type="button" class="btn btn-link p-0 ms-1 text-decoration-none small" data-bs-toggle="modal" data-bs-target="#overrideWhtModal" title="Override Withholding Tax Rate (Admin Only)">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-danger">-₱<?= number_format($whtAmount, 2) ?></td>
                            </tr>
                            <tr class="table-primary bg-primary bg-opacity-10">
                                <td colspan="4" class="text-end font-weight-black fs-5 text-dark">Total Amount Due:</td>
                                <td class="text-end font-weight-black text-primary fs-4">₱<?= number_format($totalAmountDue, 2) ?></td>
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

<!-- Admin Withholding Tax Override Modal -->
<?php if (in_array(session()->get('user_role') ?: session()->get('role'), ['admin', 'superadmin'])): ?>
<div class="modal fade" id="overrideWhtModal" tabindex="-1" aria-labelledby="overrideWhtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('sales-orders/' . $order['id'] . '/update-wht') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="overrideWhtModalLabel"><i class="fas fa-user-shield me-2"></i>Admin WHT Rate Override</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fas fa-info-circle me-1"></i> As an Administrator, you can adjust the withholding tax rate even after order approval. This will automatically recalculate the withholding tax amount, net amount due, and sync connected Accounts Receivable invoices.
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-medium small text-muted">Sales Order Number</label>
                        <input type="text" class="form-control form-control-sm font-monospace bg-light" value="<?= esc($order['so_number']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-medium small text-muted">Current Withholding Tax Rate</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="<?= number_format($order['withholding_tax_rate'] ?? 0, 2) ?>%" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">New Withholding Tax Rate (%) *</label>
                        <input type="number" step="0.01" min="0" max="100" name="withholding_tax_rate" class="form-control" value="<?= number_format($order['withholding_tax_rate'] ?? 1.00, 2) ?>" required placeholder="e.g. 1.00 or 2.00" autofocus>
                        <small class="text-muted d-block mt-1">Enter a rate percentage between 0% and 100%.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save me-1"></i> Save WHT Override</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

