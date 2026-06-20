<?php /** @var array $categories */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Add Part</h1><p class="page-subtitle">Create a new part in the catalogue</p></div>
    <a href="<?= base_url('parts') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form action="<?= base_url('parts/store') ?>" method="POST" id="createPartForm" enctype="multipart/form-data">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Part Information</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Part Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= esc(old('name')) ?>" required placeholder="e.g. ABS Speed Sensor">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="quantity" <?= old('type') === 'quantity' ? 'selected' : '' ?>>Quantity (bulk)</option>
                            <option value="non_quantity" <?= old('type') === 'non_quantity' ? 'selected' : '' ?>>Non-Quantity (tracked)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="">— Select Category —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                                    [<?= esc($cat['code']) ?>] <?= esc($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit of Measure</label>
                        <input type="text" name="unit_of_measure" class="form-control" value="<?= esc(old('unit_of_measure', 'pcs')) ?>" placeholder="pcs">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Min Stock Level</label>
                        <input type="number" name="min_stock_level" class="form-control" value="<?= esc(old('min_stock_level', '0')) ?>" min="0">
                    </div>
                    <!-- Brand -->
                    <div class="col-md-5 position-relative">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" id="brandInput" class="form-control"
                               value="<?= esc(old('brand')) ?>" placeholder="e.g. TOYOTA"
                               autocomplete="off" style="text-transform:uppercase">
                        <ul id="brandSuggestions" class="list-group position-absolute shadow-sm"
                            style="z-index:999;width:100%;top:100%;display:none;"></ul>
                    </div>
                    <!-- OEM -->
                    <div class="col-md-3">
                        <label class="form-label d-block">OEM Part?</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="oem" id="oemYes" value="1"
                                       <?= old('oem') == '1' ? 'checked' : '' ?>>
                                <label class="form-check-label fw-500" for="oemYes">
                                    <span class="badge" style="background:#16a34a">Yes — OEM</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="oem" id="oemNo" value="0"
                                       <?= old('oem') != '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="oemNo">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Barcode Value</label>
                        <input type="text" name="barcode_value" class="form-control mono" value="<?= esc(old('barcode_value')) ?>" placeholder="Leave blank to auto-use SKU">
                    </div>
                    
                    <!-- Suppliers Search, Link & Quick Add -->
                    <div class="col-12">
                        <label class="form-label font-weight-bold">Suppliers</label>
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-sm-4">
                                <input type="text" id="supplierSearch" class="form-control form-control-sm" placeholder="Type to search suppliers...">
                            </div>
                            <div class="col-sm-5">
                                <select id="supplierSelect" class="form-select form-select-sm">
                                    <option value="">— Select Supplier to Link —</option>
                                    <?php foreach ($suppliers as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-3 d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary w-50 justify-content-center" type="button" id="addSelectedSupplierBtn"><i class="fas fa-plus"></i> Link</button>
                                <button class="btn btn-sm btn-primary w-50 justify-content-center" type="button" data-bs-toggle="modal" data-bs-target="#quickAddSupplierModal"><i class="fas fa-plus-circle"></i> Quick Add</button>
                            </div>
                        </div>
                        
                        <!-- Selected suppliers chips container -->
                        <div class="border rounded p-3 bg-light">
                            <div class="text-muted small mb-2"><i class="fas fa-link me-1"></i>Linked Sourcing Partners:</div>
                            <div id="linkedSuppliersList" class="d-flex flex-wrap gap-2">
                                <span class="text-muted small">No suppliers linked to this part yet.</span>
                            </div>
                            <!-- Holds dynamic hidden inputs -->
                            <div id="hiddenSuppliersContainer"></div>
                        </div>
                        <small class="text-muted">Select active suppliers to link or click Quick Add to create a new one instantly.</small>
                    </div>

                    <!-- Upload Photos -->
                    <div class="col-12">
                        <label class="form-label font-weight-bold">Part Photos</label>
                        <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted d-block mt-1">You can select multiple photos. The first uploaded photo will be marked as primary.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= esc(old('description')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Compatibility Tags -->
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Vehicle Compatibility Tags</span></div>
            <div class="card-body">
                <div id="carTagsContainer"></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addTagBtn">
                    <i class="fas fa-plus"></i> Add Vehicle Tag
                </button>
                <input type="hidden" name="car_tags" id="carTagsInput" value="[]">
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- SKU Preview -->
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Auto-Generated SKU</span></div>
            <div class="card-body text-center">
                <div id="skuPreview" class="mono fw-700 fs-4 text-primary">—</div>
                <small class="text-muted">Generated on save based on category</small>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-save"></i> Create Part
                </button>
                <a href="<?= base_url('parts') ?>" class="btn btn-outline-secondary w-100">Cancel</a>
            </div>
        </div>
    </div>
</div>
</form>

<style>
.tag-row { display: flex; gap: .5rem; margin-bottom: .5rem; align-items: center; }
.tag-row input { flex: 1; }
</style>

<script>
let carTags = [];

function renderTags() {
    const container = document.getElementById('carTagsContainer');
    container.innerHTML = '';
    carTags.forEach((tag, idx) => {
        const row = document.createElement('div');
        row.className = 'tag-row';
        row.innerHTML = `
            <input type="text" class="form-control form-control-sm" placeholder="Brand" value="${tag.brand}"
                   onchange="carTags[${idx}].brand = this.value; updateHidden()">
            <input type="text" class="form-control form-control-sm" placeholder="Model" value="${tag.model}"
                   onchange="carTags[${idx}].model = this.value; updateHidden()">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTag(${idx})">
                <i class="fas fa-trash"></i>
            </button>`;
        container.appendChild(row);
    });
}

function updateHidden() { document.getElementById('carTagsInput').value = JSON.stringify(carTags); }
function removeTag(idx) { carTags.splice(idx, 1); renderTags(); updateHidden(); }
document.getElementById('addTagBtn').addEventListener('click', () => {
    carTags.push({brand: '', model: ''}); renderTags();
});

// SKU Preview
document.getElementById('categorySelect').addEventListener('change', function() {
    const catId = this.value;
    if (!catId) { document.getElementById('skuPreview').textContent = '—'; return; }
    fetch(`<?= base_url('parts/ajax/sku-preview') ?>?category_id=${catId}`)
        .then(r => r.json())
        .then(d => document.getElementById('skuPreview').textContent = d.sku);
});

// Brand autocomplete
const brandInput = document.getElementById('brandInput');
const brandSugg  = document.getElementById('brandSuggestions');
if (brandInput) {
    brandInput.addEventListener('input', function() {
        const term = this.value.trim();
        this.value = term.toUpperCase();
        if (term.length < 1) { brandSugg.style.display = 'none'; return; }
        fetch(`<?= base_url('parts/ajax/brand-suggestions') ?>?term=${encodeURIComponent(term)}`)
            .then(r => r.json())
            .then(brands => {
                if (!brands.length) { brandSugg.style.display = 'none'; return; }
                brandSugg.innerHTML = brands.map(b =>
                    `<li class="list-group-item list-group-item-action py-1 mono" style="cursor:pointer">${b}</li>`
                ).join('');
                brandSugg.style.display = 'block';
                brandSugg.querySelectorAll('li').forEach(li => {
                    li.addEventListener('mousedown', () => {
                        brandInput.value = li.textContent;
                        brandSugg.style.display = 'none';
                    });
                });
            });
    });
    brandInput.addEventListener('blur', () => setTimeout(() => brandSugg.style.display = 'none', 150));
}

// Suppliers Search, Selection & AJAX Adding
let allSuppliers = <?= json_encode(array_map(fn($s) => ['id' => (int)$s['id'], 'name' => $s['name']], $suppliers)) ?>;
let selectedSuppliers = [];

function populateSupplierSelect(filterTerm = '') {
    const select = document.getElementById('supplierSelect');
    select.innerHTML = '<option value="">— Select Supplier to Link —</option>';
    allSuppliers.forEach(sup => {
        if (!filterTerm || sup.name.toLowerCase().includes(filterTerm.toLowerCase())) {
            const opt = document.createElement('option');
            opt.value = sup.id;
            opt.textContent = sup.name;
            select.appendChild(opt);
        }
    });
}

function renderLinkedSuppliers() {
    const container = document.getElementById('linkedSuppliersList');
    const hiddenContainer = document.getElementById('hiddenSuppliersContainer');
    container.innerHTML = '';
    hiddenContainer.innerHTML = '';

    if (selectedSuppliers.length === 0) {
        container.innerHTML = '<span class="text-muted small">No suppliers linked to this part yet.</span>';
        return;
    }

    selectedSuppliers.forEach(sup => {
        // Chip
        const chip = document.createElement('span');
        chip.className = 'badge badge-submitted d-inline-flex align-items-center gap-2 p-2';
        chip.style.fontSize = '0.825rem';
        chip.innerHTML = `${sup.name} <i class="fas fa-times text-danger" style="cursor:pointer;" onclick="removeLinkedSupplier(${sup.id})"></i>`;
        container.appendChild(chip);

        // Input
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'suppliers[]';
        input.value = sup.id;
        hiddenContainer.appendChild(input);
    });
}

window.removeLinkedSupplier = function(id) {
    selectedSuppliers = selectedSuppliers.filter(s => s.id !== id);
    renderLinkedSuppliers();
};

document.getElementById('addSelectedSupplierBtn').addEventListener('click', () => {
    const select = document.getElementById('supplierSelect');
    const id = parseInt(select.value);
    if (!id) return;
    const name = select.options[select.selectedIndex].text;

    if (!selectedSuppliers.some(s => s.id === id)) {
        selectedSuppliers.push({id: id, name: name});
        renderLinkedSuppliers();
    }
    select.value = '';
});

document.getElementById('supplierSearch').addEventListener('input', function() {
    populateSupplierSelect(this.value);
});

// Quick Add Supplier via AJAX
document.getElementById('saveQuickSupplierBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const nameInput = document.getElementById('quickSupName');
    const name = nameInput.value.trim();
    if (!name) {
        toastr.error('Supplier name is required.');
        return;
    }

    const form = document.getElementById('quickAddSupplierForm');
    const formData = new FormData(form);

    fetch('<?= base_url('suppliers/ajax-store') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Response error');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            allSuppliers.push({id: parseInt(data.id), name: data.name});
            allSuppliers.sort((a,b) => a.name.localeCompare(b.name));
            
            if (!selectedSuppliers.some(s => s.id === data.id)) {
                selectedSuppliers.push({id: parseInt(data.id), name: data.name});
            }
            
            populateSupplierSelect(document.getElementById('supplierSearch').value);
            renderLinkedSuppliers();

            const modalEl = document.getElementById('quickAddSupplierModal');
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.hide();
            
            form.reset();
            toastr.success('Supplier registered and linked successfully.');
        } else {
            toastr.error(data.error || 'Failed to create supplier.');
        }
    })
    .catch(err => {
        console.error(err);
        toastr.error('An error occurred while creating supplier.');
    });
});

populateSupplierSelect();
renderLinkedSuppliers();
</script>

<!-- Quick Add Supplier Modal -->
<div class="modal fade" id="quickAddSupplierModal" tabindex="-1" aria-labelledby="quickAddSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddSupplierModalLabel"><i class="fas fa-truck-field text-primary me-2"></i>Quick Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddSupplierForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Supplier Name *</label>
                        <input type="text" name="name" id="quickSupName" class="form-control" required placeholder="e.g. Nippon Parts Distributor">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notice Emails <small class="text-muted">(Semicolon ";" separated)</small></label>
                        <input type="text" name="emails_for_notice" class="form-control mono" placeholder="e.g. sales@nippon.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tags <small class="text-muted">(Comma "," separated)</small></label>
                        <input type="text" name="tags" class="form-control" placeholder="e.g. local, suspension">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="e.g. Quezon City, Manila"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" id="saveQuickSupplierBtn">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
