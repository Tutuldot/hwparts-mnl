<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Enrollment — HWParts MNL</title>
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
        .enroll-container {
            max-width: 750px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            background-color: #ffffff;
        }
        .brand-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }
        .btn-primary {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .contact-row {
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

<div class="container enroll-container">
    <div class="card">
        <div class="brand-header">
            <h2 class="mb-1 fw-bold">Customer Enrollment</h2>
            <p class="mb-0 text-white-50">Create your customer account with HWParts MNL</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('customer-enrollment') ?>" method="POST" id="enrollmentForm">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Customer Profile Type *</label>
                        <select name="type" id="customerTypeSelect" class="form-select" required>
                            <option value="individual">Individual</option>
                            <option value="corporate">Corporate (Company)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact / Account Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. John Doe" value="<?= old('name') ?>">
                    </div>

                    <div class="col-12 d-none" id="corporateField">
                        <label class="form-label">Company Name *</label>
                        <input type="text" name="company_name" id="companyNameInput" class="form-control" placeholder="e.g. Acme Corporation" value="<?= old('company_name') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">TIN Number (Optional)</label>
                        <input type="text" name="tin" class="form-control" placeholder="e.g. 123-456-789-000" value="<?= old('tin') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Billing Address *</label>
                        <textarea name="billing_address" class="form-control" rows="2" required placeholder="Street address, City, Province, Zip code"><?= old('billing_address') ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Shipping Address *</label>
                        <textarea name="shipping_address" class="form-control" rows="2" required placeholder="Shipping delivery destination address"><?= old('shipping_address') ?></textarea>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold"><i class="fas fa-address-book text-primary me-2"></i>Contact Information</h5>
                    <p class="text-muted small">At least 1 email or mobile number contact point is mandatory.</p>
                    
                    <div id="contactsContainer">
                        <!-- Dynamic Contact Rows Appended Here -->
                    </div>
                    
                    <div class="col-12">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="addContactBtn">
                            <i class="fas fa-plus me-1"></i>Add Contact Method
                        </button>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold"><i class="fas fa-key text-primary me-2"></i>Portal Login Credentials</h5>
                    <p class="text-muted small">Use these details to log in and check your orders and billing statements.</p>

                    <div class="col-md-6">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required placeholder="Choose a username" value="<?= old('username') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required placeholder="Choose a secure password">
                    </div>
                </div>

                <div class="mt-4 pt-3 text-center">
                    <button type="submit" class="btn btn-primary px-5"><i class="fas fa-paper-plane me-2"></i>Register Customer Account</button>
                    <p class="mt-3 mb-0 small text-muted">Already enrolled? <a href="<?= base_url('customer/login') ?>" class="text-decoration-none fw-bold">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Toggle Company Name Field
    document.getElementById('customerTypeSelect').addEventListener('change', function() {
        const corpDiv = document.getElementById('corporateField');
        const companyInput = document.getElementById('companyNameInput');
        if (this.value === 'corporate') {
            corpDiv.classList.remove('d-none');
            companyInput.setAttribute('required', 'required');
        } else {
            corpDiv.classList.add('d-none');
            companyInput.removeAttribute('required');
            companyInput.value = '';
        }
    });

    // Dynamic Contact Adding
    let contactCount = 0;
    const container = document.getElementById('contactsContainer');

    function addContactRow(type = 'email', value = '', remarks = '') {
        const index = contactCount++;
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-center contact-row';
        row.innerHTML = `
            <div class="col-md-3">
                <select name="contacts[${index}][contact_type]" class="form-select form-select-sm">
                    <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                    <option value="mobile" ${type === 'mobile' ? 'selected' : ''}>Mobile No.</option>
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="contacts[${index}][value]" class="form-control form-control-sm" required placeholder="Contact address/number" value="${value}">
            </div>
            <div class="col-md-3">
                <input type="text" name="contacts[${index}][remarks]" class="form-control form-control-sm" placeholder="Remarks (e.g. Sales, Urgent)" value="${remarks}">
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.contact-row').remove()"><i class="fas fa-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
    }

    document.getElementById('addContactBtn').addEventListener('click', () => addContactRow());

    // Add first row on load
    addContactRow('email');

    // Trigger change if corporate old value exists
    if (document.getElementById('customerTypeSelect').value === 'corporate') {
        document.getElementById('corporateField').classList.remove('d-none');
        document.getElementById('companyNameInput').setAttribute('required', 'required');
    }
</script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
