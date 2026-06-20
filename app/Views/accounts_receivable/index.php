<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Accounts Receivable</h1>
        <p class="page-subtitle">Track and settle customer invoices, payments, and overdue billing accounts</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-nowrap align-middle small" id="receivablesTable">
                <thead class="table-light text-uppercase font-weight-bold">
                    <tr>
                        <th>Invoice Number</th>
                        <th>Sales Order</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($receivables)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-light"></i>
                                <p class="mb-0 font-weight-bold">No accounts receivable found.</p>
                                <p class="text-muted small">Create and approve sales orders to generate invoices.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($receivables as $ar): ?>
                            <?php 
                                $isOverdue = (strtotime($ar['due_date']) < time()) && ($ar['status'] === 'unpaid');
                            ?>
                            <tr class="<?= $isOverdue ? 'table-danger-light' : '' ?>">
                                <td class="font-weight-bold font-monospace">
                                    <a href="<?= base_url('accounts-receivable/' . $ar['id']) ?>">
                                        <?= esc($ar['invoice_number']) ?>
                                    </a>
                                </td>
                                <td class="font-monospace">
                                    <a href="<?= base_url('sales-orders/' . $ar['so_id']) ?>">
                                        <?= esc($ar['so_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= base_url('customers/' . $ar['customer_id']) ?>" class="font-weight-medium text-dark">
                                        <?= esc($ar['customer_name']) ?>
                                    </a>
                                </td>
                                <td class="font-weight-medium">₱<?= number_format($ar['amount'], 2) ?></td>
                                <td class="font-weight-medium <?= $isOverdue ? 'text-danger font-weight-bold' : '' ?>">
                                    <?= date('M d, Y', strtotime($ar['due_date'])) ?>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge bg-danger ms-1 small">Overdue</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ar['status'] === 'paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('accounts-receivable/' . $ar['id']) ?>" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Open Billing
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
