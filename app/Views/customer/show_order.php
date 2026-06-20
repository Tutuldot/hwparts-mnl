<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details — HWParts MNL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        .navbar {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 600;
        }
        .badge-draft { background-color: #e5e7eb; color: #374151; }
        .badge-approved { background-color: #d1fae5; color: #065f46; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
        .mono { font-family: monospace; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-cubes me-2"></i>HWParts MNL Customer Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPortal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPortal">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('customer/orders') ?>"><i class="fas fa-list-check me-1"></i>My Orders</a>
                </li>
            </ul>
            <div class="navbar-nav">
                <span class="nav-item nav-link me-3"><i class="fas fa-user-circle me-1"></i>Hello, <?= esc(session()->get('customer_name')) ?></span>
                <a class="nav-link text-warning" href="<?= base_url('customer/logout') ?>"><i class="fas fa-sign-out-alt me-1"></i>Sign Out</a>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0">Sales Order Details</h2>
            <p class="text-muted mb-0">Sales Order Reference: <span class="mono fw-bold"><?= esc($order['so_number']) ?></span></p>
        </div>
        <a href="<?= base_url('customer/orders') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
    </div>

    <div class="row g-3">
        <!-- Order Lines Card -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white"><span class="fw-bold"><i class="fas fa-cart-shopping me-1 text-primary"></i>Items Ordered</span></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Part Description</th>
                                    <th>SKU</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end pe-3">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lines as $l): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-600"><?= esc($l['part_name']) ?></span>
                                            <?php if ($l['variant_name']): ?>
                                                <div class="small text-muted"><?= esc($l['variant_name']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="mono small"><?= esc($l['sku']) ?></span></td>
                                        <td class="text-center"><?= $l['quantity'] ?></td>
                                        <td class="text-end">₱<?= number_format($l['unit_price'], 2) ?></td>
                                        <td class="text-end pe-3">₱<?= number_format($l['total_price'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white"><span class="fw-bold"><i class="fas fa-file-invoice me-1 text-primary"></i>Order Summary</span></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">SO Number</td><td class="fw-600 mono"><?= esc($order['so_number']) ?></td></tr>
                        <tr><td class="text-muted">Date Ordered</td><td><?= date('M d, Y', strtotime($order['created_at'])) ?></td></tr>
                        <tr><td class="text-muted">Total Amount</td><td class="fw-bold text-primary">₱<?= number_format($order['amount'], 2) ?></td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge badge-<?= esc($order['status']) ?>"><?= ucfirst($order['status']) ?></span></td></tr>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white"><span class="fw-bold"><i class="fas fa-truck me-1 text-primary"></i>Delivery Info</span></div>
                <div class="card-body small">
                    <div class="mb-2">
                        <div class="text-muted font-weight-bold">Billing Address:</div>
                        <div class="text-dark" style="white-space: pre-wrap;"><?= esc($order['billing_address']) ?></div>
                    </div>
                    <div>
                        <div class="text-muted font-weight-bold">Shipping Address:</div>
                        <div class="text-dark" style="white-space: pre-wrap;"><?= esc($order['shipping_address']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
