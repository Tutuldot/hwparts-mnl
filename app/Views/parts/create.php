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
                    
                    <!-- Suppliers (Multi-select) -->
                    <div class="col-12">
                        <label class="form-label font-weight-bold">Suppliers</label>
                        <div class="border rounded p-3 bg-light" style="max-height: 150px; overflow-y: auto;">
                            <?php if (empty($suppliers)): ?>
                                <div class="text-muted small">No active suppliers found. <a href="<?= base_url('suppliers/create') ?>" target="_blank">Add one</a>.</div>
                            <?php else: ?>
                                <div class="row g-2">
                                    <?php foreach ($suppliers as $s): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="suppliers[]" value="<?= $s['id'] ?>" id="supplier_<?= $s['id'] ?>"
                                                       <?= is_array(old('suppliers')) && in_array($s['id'], old('suppliers')) ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="supplier_<?= $s['id'] ?>">
                                                    <?= esc($s['name']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">Select all suppliers where this part can be sourced.</small>
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
</script>
