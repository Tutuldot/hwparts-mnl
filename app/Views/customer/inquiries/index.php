<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Inquiries — HW Trucks MNL</title>
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
        .badge-open { background-color: #d1fae5; color: #065f46; }
        .badge-closed { background-color: #e5e7eb; color: #374151; }
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
                    <a class="nav-link" href="<?= base_url('customer/orders') ?>"><i class="fas fa-list-check me-1"></i>My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?= base_url('customer/inquiries') ?>"><i class="fas fa-question-circle me-1"></i>Inquiries</a>
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
            <h2 class="fw-bold mb-0">My Support Inquiries</h2>
            <p class="text-muted mb-0">Send messages or upload photos to ask admins about parts and orders</p>
        </div>
        <div>
            <?php if ($hasOpenInquiry): ?>
                <button class="btn btn-primary" disabled data-bs-toggle="tooltip" data-bs-placement="left" title="You must resolve your open inquiry before starting a new one.">
                    <i class="fas fa-plus me-1"></i>New Inquiry
                </button>
            <?php else: ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newInquiryModal">
                    <i class="fas fa-plus me-1"></i>New Inquiry
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($hasOpenInquiry): ?>
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <i class="fas fa-info-circle me-2 text-warning"></i>
            <strong>Active Inquiry Open:</strong> You have an open support request. To start a new one, please close or complete the existing thread.
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 100px;">Inquiry ID</th>
                            <th>Date Created</th>
                            <th class="text-center">Status</th>
                            <th>Linked Sales Order</th>
                            <th class="text-center pe-4" style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inquiries)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-comments fa-3x mb-3 text-light"></i>
                                    <p class="mb-0 fw-bold">No inquiries yet.</p>
                                    <p class="text-muted small">If you have any questions or need parts support, click "New Inquiry" above.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inquiries as $inq): ?>
                                <tr>
                                    <td class="ps-4 fw-bold">#<?= $inq['id'] ?></td>
                                    <td><?= date('M d, Y h:i A', strtotime($inq['created_at'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $inq['status'] ?>">
                                            <?= ucfirst($inq['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($inq['so_number']): ?>
                                            <a href="<?= base_url('customer/orders/' . $inq['sales_order_id']) ?>" class="fw-bold text-decoration-none">
                                                <i class="fas fa-file-invoice-dollar me-1"></i><?= esc($inq['so_number']) ?>
                                            </a>
                                            <span class="text-muted small">(<?= ucfirst($inq['so_status']) ?>)</span>
                                        <?php else: ?>
                                            <span class="text-muted">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="<?= base_url('customer/inquiries/' . $inq['id']) ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Thread
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

<!-- New Inquiry Modal -->
<div class="modal fade" id="newInquiryModal" tabindex="-1" aria-labelledby="newInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-0 bg-light" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <h5 class="modal-title fw-bold" id="newInquiryModalLabel"><i class="fas fa-plus-circle text-primary me-2"></i>Create New Support Inquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('customer/inquiries/create') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="text-muted small">Please describe your question or list the parts you are looking for. You can optionally attach a reference photo (which will be optimized automatically).</p>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label fw-600">Message / Description *</label>
                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter details about your inquiry..." required></textarea>
                    </div>

                    <div class="mb-0">
                        <label for="photo" class="form-label fw-600">Attach Photo (Optional)</label>
                        <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
                        <div class="form-text text-muted">Supports JPG, PNG, GIF files. The image will be compressed automatically.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane me-1"></i>Submit Inquiry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
</body>
</html>
