<?php /** @var array $supplier */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Edit Supplier</h1>
        <p class="page-subtitle">Update supplier partner details</p>
    </div>
    <a href="<?= base_url('suppliers') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form action="<?= base_url("suppliers/{$supplier['id']}/update") ?>" method="POST" id="editSupplierForm">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Supplier Information</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= esc($supplier['name']) ?>" required placeholder="e.g. Acme Auto Parts Trading">
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">Notice Emails <small class="text-muted">(Separate multiple with semicolon ";")</small></label>
                        <input type="text" name="emails_for_notice" class="form-control mono" value="<?= esc($supplier['emails_for_notice']) ?>" placeholder="e.g. sales@acme.com; notices@acme.com">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Tags <small class="text-muted">(Separate multiple with commas ",")</small></label>
                        <input type="text" name="tags" class="form-control" value="<?= esc($supplier['tags']) ?>" placeholder="e.g. local, oem, suspension">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="e.g. 123 Quezon Ave, Quezon City, Metro Manila"><?= esc($supplier['address']) ?></textarea>
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
                <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save"></i> Save Changes</button>
                <a href="<?= base_url('suppliers') ?>" class="btn btn-outline-secondary w-100">Cancel</a>
            </div>
        </div>
    </div>
</div>
</form>

<script>
let contacts = <?= json_encode(array_map(fn($c) => ['name' => $c['name'], 'email' => $c['email'] ?? '', 'mobile' => $c['mobile'] ?? '', 'role_or_title' => $c['role_or_title'] ?? '', 'is_visible' => (int)($c['is_visible'] ?? 1)], $contacts)) ?>;

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
                <input type="text" class="form-control form-control-sm" placeholder="Contact Name" value="${c.name || ''}"
                       onchange="contacts[${idx}].name = this.value; updateHidden()" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Email</label>
                <input type="email" class="form-control form-control-sm mono" placeholder="Email" value="${c.email || ''}"
                       onchange="contacts[${idx}].email = this.value; updateHidden()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Mobile</label>
                <input type="text" class="form-control form-control-sm" placeholder="Mobile Number" value="${c.mobile || ''}"
                       onchange="contacts[${idx}].mobile = this.value; updateHidden()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Role / Title</label>
                <input type="text" class="form-control form-control-sm" placeholder="e.g. Sales" value="${c.role_or_title || ''}"
                       onchange="contacts[${idx}].role_or_title = this.value; updateHidden()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Visible</label>
                <select class="form-select form-select-sm" onchange="contacts[${idx}].is_visible = +this.value; updateHidden()">
                    <option value="1" ${(c.is_visible ?? 1) == 1 ? 'selected' : ''}>Yes</option>
                    <option value="0" ${(c.is_visible ?? 1) == 0 ? 'selected' : ''}>No</option>
                </select>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeContact(${idx})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>`;
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
    contacts.push({name: '', email: '', mobile: '', role_or_title: '', is_visible: 1}); 
    renderContacts();
});

// Initial load
renderContacts();

document.getElementById('editSupplierForm').addEventListener('submit', function(e) {
    for (let i = 0; i < contacts.length; i++) {
        if (!contacts[i].name || !contacts[i].name.trim()) {
            e.preventDefault();
            toastr.error('Contact name cannot be empty.');
            return;
        }
    }
});
</script>
