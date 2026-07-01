<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — HW Trucks MNL</title>
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
        .table th {
            font-weight: 700;
            color: #4b5563;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 600;
        }
        .badge-draft { background-color: #e5e7eb; color: #374151; }
        .badge-approved { background-color: #d1fae5; color: #065f46; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-cubes me-2"></i>HW Trucks MNL Customer Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPortal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPortal">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="<?= base_url('customer/orders') ?>"><i class="fas fa-list-check me-1"></i>My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('customer/inquiries') ?>"><i class="fas fa-question-circle me-1"></i>Inquiries</a>
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
            <h2 class="fw-bold mb-0">My Sales Orders</h2>
            <p class="text-muted mb-0">Review the status and breakdown of your orders</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">SO Number</th>
                            <th>Order Date</th>
                            <th>Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-shopping-bag fa-3x mb-3 text-secondary"></i>
                                    <p class="mb-0">You have no orders placed yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td class="ps-4"><span class="fw-600 text-primary font-monospace"><?= esc($o['so_number']) ?></span></td>
                                    <td><?= date('M d, Y H:i A', strtotime($o['created_at'])) ?></td>
                                    <td class="fw-600">₱<?= number_format($o['amount'], 2) ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= esc($o['status']) ?>">
                                            <?= ucfirst($o['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="<?= base_url("customer/orders/{$o['id']}") ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View Details
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
