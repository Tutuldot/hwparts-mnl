<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Add Supplier</h1>
        <p class="page-subtitle">Register a new supply chain partner</p>
    </div>
    <a href="<?= base_url('suppliers') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form action="<?= base_url('suppliers/store') ?>" method="POST" id="createSupplierForm">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Supplier Information</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= esc(old('name')) ?>" required placeholder="e.g. Acme Auto Parts Trading">
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">Notice Emails <small class="text-muted">(Separate multiple with semicolon ";")</small></label>
                        <input type="text" name="emails_for_notice" class="form-control mono" value="<?= esc(old('emails_for_notice')) ?>" placeholder="e.g. sales@acme.com; notices@acme.com">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Tags <small class="text-muted">(Separate multiple with commas ",")</small></label>
                        <input type="text" name="tags" class="form-control" value="<?= esc(old('tags')) ?>" placeholder="e.g. local, oem, suspension">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="e.g. 123 Quezon Ave, Quezon City, Metro Manila"><?= esc(old('address')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Persons Card -->
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">Contact Persons</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addContactBtn"><i class="fas fa-plus"></i> Add Contact</button>
            </div>
            <div class="card-body">
                <div id="contactsContainer"></div>
                <input type="hidden" name="contacts" id="contactsInput" value="[]">
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save"></i> Save Supplier</button>
                <a href="<?= base_url('suppliers') ?>" class="btn btn-outline-secondary w-100">Cancel</a>
            </div>
        </div>
    </div>
</div>
</form>

<script>
let contacts = [];

function renderContacts() {
    const container = document.getElementById('contactsContainer');
    container.innerHTML = '';
    
    if (contacts.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-3 small">No contacts added yet. Click "Add Contact" to add one.</div>';
        updateHidden();
        return;
    }
    
    contacts.forEach((c, idx) => {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 p-2 border rounded align-items-end position-relative bg-light';
        row.innerHTML = `
            <div class="col-md-2">
                <label class="form-label small mb-1">Name *</label>
                <input type="text" class="form-control form-control-sm contact-name-input" placeholder="Contact Name" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Email</label>
                <input type="email" class="form-control form-control-sm mono contact-email-input" placeholder="Email">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Mobile</label>
                <input type="text" class="form-control form-control-sm contact-mobile-input" placeholder="Mobile Number">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Remarks</label>
                <input type="text" class="form-control form-control-sm contact-remarks-input" placeholder="e.g. Viber only">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Role / Title</label>
                <input type="text" class="form-control form-control-sm contact-role-input" placeholder="e.g. Sales">
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">Visible</label>
                <select class="form-select form-select-sm contact-visible-select">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-contact-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>`;
            
        // Populate inputs safely
        row.querySelector('.contact-name-input').value = c.name || '';
        row.querySelector('.contact-email-input').value = c.email || '';
        row.querySelector('.contact-mobile-input').value = c.mobile || '';
        row.querySelector('.contact-remarks-input').value = c.remarks || '';
        row.querySelector('.contact-role-input').value = c.role_or_title || '';
        row.querySelector('.contact-visible-select').value = c.is_visible !== undefined ? c.is_visible : 1;

        // Bind event listeners dynamically
        row.querySelector('.contact-name-input').addEventListener('input', function() {
            contacts[idx].name = this.value;
            updateHidden();
        });
        row.querySelector('.contact-email-input').addEventListener('input', function() {
            contacts[idx].email = this.value;
            updateHidden();
        });
        row.querySelector('.contact-mobile-input').addEventListener('input', function() {
            contacts[idx].mobile = this.value;
            updateHidden();
        });
        row.querySelector('.contact-remarks-input').addEventListener('input', function() {
            contacts[idx].remarks = this.value;
            updateHidden();
        });
        row.querySelector('.contact-role-input').addEventListener('input', function() {
            contacts[idx].role_or_title = this.value;
            updateHidden();
        });
        row.querySelector('.contact-visible-select').addEventListener('change', function() {
            contacts[idx].is_visible = +this.value;
            updateHidden();
        });
        row.querySelector('.remove-contact-btn').addEventListener('click', function() {
            removeContact(idx);
        });

        container.appendChild(row);
    });
    updateHidden();
}

function updateHidden() { 
    document.getElementById('contactsInput').value = JSON.stringify(contacts); 
}

function removeContact(idx) { 
    contacts.splice(idx, 1); 
    renderContacts(); 
}

document.getElementById('addContactBtn').addEventListener('click', () => {
    contacts.push({name: '', email: '', mobile: '', remarks: '', role_or_title: '', is_visible: 1}); 
    renderContacts();
});

// Initial load
renderContacts();

document.getElementById('createSupplierForm').addEventListener('submit', function(e) {
    for (let i = 0; i < contacts.length; i++) {
        if (!contacts[i].name || !contacts[i].name.trim()) {
            e.preventDefault();
            toastr.error('Contact name cannot be empty.');
            return;
        }
    }
});
</script>
