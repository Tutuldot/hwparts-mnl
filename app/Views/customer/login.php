<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login — HWParts MNL</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            background-color: #ffffff;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);
            padding: 30px;
            text-align: center;
            color: #ffffff;
            border: none;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
        }
        .form-control:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }
        .btn-primary {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card-header">
        <h3 class="mb-1 fw-bold">Customer Portal</h3>
        <p class="mb-0 text-white-50">Sign in to view your orders & invoices</p>
    </div>
    <div class="card-body p-4 p-md-5">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-circle-check me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('customer/login') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control" required placeholder="Enter username" value="<?= old('username') ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" required placeholder="Enter password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-2"></i>Sign In</button>
        </form>

        <div class="mt-4 text-center">
            <p class="mb-0 text-muted small">Not enrolled yet? <a href="<?= base_url('customer-enrollment') ?>" class="text-decoration-none fw-bold">Enroll now</a></p>
            <p class="mt-2 mb-0 small"><a href="<?= base_url('/') ?>" class="text-decoration-none text-secondary"><i class="fas fa-chevron-left me-1"></i>Back to Main Login</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
