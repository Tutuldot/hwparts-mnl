<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Sales Orders</h1>
        <p class="page-subtitle">Manage customer draft and approved sales orders</p>
    </div>
    <a href="<?= base_url('sales-orders/create') ?>" class="btn btn-primary">
        <i class="fas fa-cash-register me-1"></i> Open POS Cash Register
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-nowrap align-middle small" id="salesOrdersTable">
                <thead class="table-light text-uppercase font-weight-bold">
                    <tr>
                        <th>SO Number</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Order Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-shopping-basket fa-3x mb-3 text-light"></i>
                                <p class="mb-0 font-weight-bold">No sales orders found.</p>
                                <p class="text-muted small">Open the POS cash register to create your first order.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="font-weight-bold">
                                    <a href="<?= base_url('sales-orders/' . $order['id']) ?>">
                                        <?= esc($order['so_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= base_url('customers/' . $order['customer_id']) ?>" class="font-weight-medium text-dark">
                                        <?= esc($order['customer_name']) ?>
                                    </a>
                                </td>
                                <td class="font-weight-medium">₱<?= number_format($order['amount'], 2) ?></td>
                                <td>
                                    <?php if ($order['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php elseif ($order['status'] === 'cancelled'): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($order['created_by_name'] ?: 'System') ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('sales-orders/' . $order['id']) ?>" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View Detail
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
