<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Customer Profile</h1>
        <p class="page-subtitle">View and manage customer account information, contacts, and orders</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('customers') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
        <a href="<?= base_url('customers/edit/' . $customer['id']) ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Edit Profile
        </a>
        <form action="<?= base_url('customers/toggle/' . $customer['id']) ?>" method="POST" style="display:inline;">
            <?= csrf_field() ?>
            <?php if ($customer['is_active']): ?>
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to deactivate this customer account?')">
                    <i class="fas fa-ban me-1"></i> Deactivate
                </button>
            <?php else: ?>
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to activate this customer account?')">
                    <i class="fas fa-check-circle me-1"></i> Activate
                </button>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Profile Info Card -->
    <div class="col-lg-4">
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fas <?= $customer['type'] === 'corporate' ? 'fa-building text-primary' : 'fa-user text-success' ?> fa-3x"></i>
                </div>
                <h4 class="card-title mb-1 font-weight-bold"><?= esc($customer['name']) ?></h4>
                <p class="text-muted text-uppercase small mb-3"><?= esc($customer['type']) ?></p>
                <div class="d-flex justify-content-center">
                    <span class="badge <?= $customer['is_active'] ? 'bg-success' : 'bg-danger' ?> px-3 py-2 fs-6">
                        <?= $customer['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
            </div>
            <div class="list-group list-group-flush border-top small">
                <?php if ($customer['type'] === 'corporate' && !empty($customer['company_name'])): ?>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Company Name</span>
                        <span class="font-weight-medium text-end"><?= esc($customer['company_name']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">TIN</span>
                    <span class="font-weight-medium text-end"><?= esc($customer['tin'] ?: 'N/A') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Payment Terms</span>
                    <span class="font-weight-medium text-end"><?= esc($customer['payment_terms']) ?> Days</span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Portal Username</span>
                    <span class="font-weight-medium text-end text-primary"><?= esc($customer['username']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Created At</span>
                    <span class="font-weight-medium text-end"><?= date('M d, Y h:i A', strtotime($customer['created_at'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Address Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold">Address Information</div>
            <div class="card-body small">
                <div class="mb-3">
                    <span class="text-muted d-block mb-1"><i class="fas fa-file-invoice-dollar me-1"></i> Billing Address</span>
                    <div class="bg-light p-2 rounded"><?= nl2br(esc($customer['billing_address'])) ?></div>
                </div>
                <div>
                    <span class="text-muted d-block mb-1"><i class="fas fa-truck me-1"></i> Shipping Address</span>
                    <div class="bg-light p-2 rounded"><?= nl2br(esc($customer['shipping_address'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact & Orders Tab Content -->
    <div class="col-lg-8">
        <!-- Contacts List Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-address-book text-muted me-2"></i>Contact Points</span>
                <span class="badge bg-secondary"><?= count($contacts) ?> Saved</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Contact Value</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contacts)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No contact points registered.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contacts as $contact): ?>
                                    <tr>
                                        <td>
                                            <span class="badge <?= $contact['contact_type'] === 'email' ? 'bg-info' : 'bg-primary' ?> text-uppercase">
                                                <?= esc($contact['contact_type']) ?>
                                            </span>
                                        </td>
                                        <td class="font-weight-medium">
                                            <?php if ($contact['contact_type'] === 'email'): ?>
                                                <a href="mailto:<?= esc($contact['value']) ?>"><i class="fas fa-envelope me-1"></i><?= esc($contact['value']) ?></a>
                                            <?php else: ?>
                                                <a href="tel:<?= esc($contact['value']) ?>"><i class="fas fa-phone-alt me-1"></i><?= esc($contact['value']) ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($contact['remarks'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sales Orders Listing Card -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-shopping-cart text-muted me-2"></i>Sales Order History</span>
                <span class="badge bg-secondary"><?= count($orders) ?> Orders</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>SO Number</th>
                                <th>Order Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No sales orders found for this customer.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="font-weight-bold">
                                            <a href="<?= base_url('sales-orders/' . $order['id']) ?>">
                                                <?= esc($order['so_number']) ?>
                                            </a>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
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
                                        <td class="text-end">
                                            <a href="<?= base_url('sales-orders/' . $order['id']) ?>" class="btn btn-xs btn-outline-primary">
                                                <i class="fas fa-eye"></i> View Detail
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
    </div>
</div>
