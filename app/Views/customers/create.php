<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Add Customer Profile</h1>
        <p class="page-subtitle">Create a new customer profile and portal access</p>
    </div>
    <a href="<?= base_url('customers') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form action="<?= base_url('customers/store') ?>" method="POST" id="customerForm">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <!-- Customer Details -->
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Customer Profile Info</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Profile Type *</label>
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

                    <div class="col-md-6">
                        <label class="form-label">TIN Number (Optional)</label>
                        <input type="text" name="tin" class="form-control" placeholder="e.g. 123-456-789-000" value="<?= old('tin') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Payment Terms (Days) *</label>
                        <input type="number" name="payment_terms" id="paymentTermsInput" class="form-control" required min="0" value="<?= old('payment_terms', 0) ?>" placeholder="Maximum allowed days before payment">
                        <small class="text-muted d-block mt-1">Minimum: 0 (Immediate Cash). Over 30 days will require confirmation warning.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Billing Address *</label>
                        <textarea name="billing_address" class="form-control" rows="2" required placeholder="Full billing billing address"><?= old('billing_address') ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Shipping Address *</label>
                        <textarea name="shipping_address" class="form-control" rows="2" required placeholder="Full shipping delivery address"><?= old('shipping_address') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacts Card -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Customer Contacts</span>
                <button type="button" class="btn btn-xs btn-outline-primary" id="addContactBtn">
                    <i class="fas fa-plus me-1"></i>Add Contact
                </button>
            </div>
            <div class="card-body p-3">
                <p class="text-muted small mb-3">At least 1 valid contact method (email or mobile) must be saved.</p>
                <div id="contactsContainer">
                    <!-- Dynamic Rows Appended Here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Credentials & Save -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Portal Access Login</span></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control form-control-sm" required placeholder="Portal login username" value="<?= old('username') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control form-control-sm" required placeholder="Portal login password">
                </div>
            </div>
        </div>

        <div class="card sticky-top" style="top:80px">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save me-1"></i>Save Profile</button>
                <a href="<?= base_url('customers') ?>" class="btn btn-outline-secondary w-100">Cancel</a>
            </div>
        </div>
    </div>
</div>
</form>

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
        row.className = 'row g-2 align-items-center contact-row mb-2';
        row.innerHTML = `
            <div class="col-sm-3">
                <select name="contacts[${index}][contact_type]" class="form-select form-select-sm">
                    <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                    <option value="mobile" ${type === 'mobile' ? 'selected' : ''}>Mobile No.</option>
                </select>
            </div>
            <div class="col-sm-5">
                <input type="text" name="contacts[${index}][value]" class="form-control form-control-sm" required placeholder="Contact endpoint" value="${value}">
            </div>
            <div class="col-sm-3">
                <input type="text" name="contacts[${index}][remarks]" class="form-control form-control-sm" placeholder="Remarks" value="${remarks}">
            </div>
            <div class="col-sm-1 text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.contact-row').remove()"><i class="fas fa-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
    }

    document.getElementById('addContactBtn').addEventListener('click', () => addContactRow());

    // Add first row on load
    addContactRow('email');

    // Trigger warning if payment terms is more than 30 days
    document.getElementById('customerForm').addEventListener('submit', function(e) {
        const termsInput = document.getElementById('paymentTermsInput');
        const terms = parseInt(termsInput.value) || 0;
        
        if (terms > 30) {
            const confirmed = confirm(`Warning: You have set the payment terms to ${terms} days, which exceeds the standard limit of 30 days. Are you sure this value is correct?`);
            if (!confirmed) {
                e.preventDefault(); // Stop form submission
            }
        }
    });

    // Trigger change if corporate old value exists
    if (document.getElementById('customerTypeSelect').value === 'corporate') {
        document.getElementById('corporateField').classList.remove('d-none');
        document.getElementById('companyNameInput').setAttribute('required', 'required');
    }
</script>
