<?php /** @var array $part @var array $categories @var array $carTags */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Edit <?= esc($part['sku']) ?></h1><p class="page-subtitle"><?= esc($part['name']) ?></p></div>
    <a href="<?= base_url("parts/{$part['id']}") ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form action="<?= base_url("parts/{$part['id']}/update") ?>" method="POST" id="editPartForm" enctype="multipart/form-data">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Edit Part Information</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12"><div class="alert alert-secondary py-2 mb-0">
                         <i class="fas fa-info-circle me-1"></i> SKU: <strong class="mono"><?= esc($part['sku']) ?></strong> (cannot be changed)
                    </div></div>
                    <div class="col-md-8"><label class="form-label">Part Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= esc($part['name']) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="quantity" <?= $part['type'] === 'quantity' ? 'selected' : '' ?>>Quantity (bulk)</option>
                            <option value="non_quantity" <?= $part['type'] === 'non_quantity' ? 'selected' : '' ?>>Non-Quantity (tracked)</option>
                        </select>
                        <small class="text-danger d-block mt-1" style="font-size:0.75rem;"><i class="fas fa-exclamation-triangle"></i> Changing type may affect stock calculation!</small>
                    </div>
                    <div class="col-md-6"><label class="form-label">Category *</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $part['category_id'] ? 'selected' : '' ?>>[<?= esc($cat['code']) ?>] <?= esc($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Unit of Measure</label>
                        <input type="text" name="unit_of_measure" class="form-control" value="<?= esc($part['unit_of_measure']) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Min Stock Level</label>
                        <input type="number" name="min_stock_level" class="form-control" value="<?= $part['min_stock_level'] ?>" min="0"></div>
                    <div class="col-md-6">
                        <label class="form-label">Barcode Value</label>
                        <div class="input-group">
                            <input type="text" name="barcode_value" id="barcodeValueInput" class="form-control mono" value="<?= esc($part['barcode_value']) ?>">
                            <button class="btn btn-outline-primary" type="button" id="scanBarcodeBtn"><i class="fas fa-camera"></i> Scan</button>
                        </div>
                    </div>
                    <!-- Brand -->
                    <div class="col-md-3 position-relative">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" id="brandInput" class="form-control"
                               value="<?= esc($part['brand'] ?? '') ?>" placeholder="e.g. TOYOTA"
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
                                       <?= $part['oem'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-500" for="oemYes">
                                    <span class="badge" style="background:#16a34a">Yes — OEM</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="oem" id="oemNo" value="0"
                                       <?= !$part['oem'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="oemNo">No</label>
                            </div>
                        </div>
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
                                                       <?= in_array($s['id'], $linkedSupplierIds) ? 'checked' : '' ?>>
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

                    <!-- Photo Upload -->
                    <div class="col-12">
                        <label class="form-label font-weight-bold">Upload More Photos</label>
                        <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Upload any additional photos for this part.</small>
                    </div>

                    <div class="col-12"><label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= esc($part['description']) ?></textarea></div>
                </div>
            </div>
        </div>
        
        <!-- Manage Photos Gallery -->
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Manage Photos</span></div>
            <div class="card-body">
                <?php if (empty($photos)): ?>
                    <div class="text-center text-muted py-3 small">No photos uploaded yet. Use the upload field above to add some.</div>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($photos as $photo): ?>
                            <div class="col-6 col-sm-4 col-md-3">
                                <div class="card h-100 position-relative shadow-none border">
                                    <img src="<?= base_url($photo['photo_path']) ?>" class="card-img-top" style="height: 120px; object-fit: cover;" alt="Part Photo">
                                    <div class="card-body p-2 d-flex flex-column gap-1">
                                        <?php if ($photo['is_primary']): ?>
                                            <span class="badge bg-success text-white w-100 text-center py-1"><i class="fas fa-star me-1"></i>Primary</span>
                                        <?php else: ?>
                                            <button type="submit" form="setPrimaryPhotoForm_<?= $photo['id'] ?>" class="btn btn-xs btn-outline-primary w-100 justify-content-center">Set Primary</button>
                                        <?php endif; ?>
                                        <button type="submit" form="deletePhotoForm_<?= $photo['id'] ?>" class="btn btn-xs btn-outline-danger w-100 justify-content-center" onclick="return confirm('Delete this photo?')">Delete</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Vehicle Compatibility Tags</span></div>
            <div class="card-body">
                <div id="carTagsContainer"></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addTagBtn"><i class="fas fa-plus"></i> Add Tag</button>
                <input type="hidden" name="car_tags" id="carTagsInput" value="[]">
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px"><div class="card-body">
            <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save"></i> Save Changes</button>
            <a href="<?= base_url("parts/{$part['id']}") ?>" class="btn btn-outline-secondary w-100">Cancel</a>
        </div></div>
    </div>
</div>
</form>

<!-- Helper forms for photo management -->
<?php foreach ($photos as $photo): ?>
    <?php if (!$photo['is_primary']): ?>
        <form action="<?= base_url("parts/{$part['id']}/set-primary-photo/{$photo['id']}") ?>" method="POST" id="setPrimaryPhotoForm_<?= $photo['id'] ?>"><?= csrf_field() ?></form>
    <?php endif; ?>
    <form action="<?= base_url("parts/{$part['id']}/delete-photo/{$photo['id']}") ?>" method="POST" id="deletePhotoForm_<?= $photo['id'] ?>"><?= csrf_field() ?></form>
<?php endforeach; ?>m>

<style>.tag-row{display:flex;gap:.5rem;margin-bottom:.5rem;align-items:center}.tag-row input{flex:1}</style>
<script>
let carTags = <?= json_encode(array_map(fn($t) => ['brand' => $t['brand'], 'model' => $t['model']], $carTags)) ?>;
function renderTags(){
    const c=document.getElementById('carTagsContainer'); c.innerHTML='';
    carTags.forEach((t,i)=>{
        const row=document.createElement('div'); row.className='tag-row';
        row.innerHTML=`<input type="text" class="form-control form-control-sm" placeholder="Brand" value="${t.brand}" onchange="carTags[${i}].brand=this.value;updateHidden()">
            <input type="text" class="form-control form-control-sm" placeholder="Model" value="${t.model}" onchange="carTags[${i}].model=this.value;updateHidden()">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="carTags.splice(${i},1);renderTags()"><i class="fas fa-trash"></i></button>`;
        c.appendChild(row);
    });
}
function updateHidden(){document.getElementById('carTagsInput').value=JSON.stringify(carTags);}
document.getElementById('addTagBtn').addEventListener('click',()=>{carTags.push({brand:'',model:''});renderTags();});
renderTags(); updateHidden();

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

<!-- Scanner Modal -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scannerModalLabel">Scan Barcode / QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reader" style="width:100%; min-height:250px; background:#f8f9fa; border:1px dashed #ccc; border-radius:4px; overflow:hidden;"></div>
                <div id="scanFeedback" class="mt-2 text-center text-muted" style="font-size:0.85rem;">Position the barcode/QR inside the frame.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let html5QrCode = null;
    const scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));

    document.getElementById('scanBarcodeBtn').addEventListener('click', () => {
        scannerModal.show();
        setTimeout(() => {
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 15, qrbox: { width: 250, height: 200 } };
            
            html5QrCode.start(
                { facingMode: "environment" }, 
                config,
                (decodedText, decodedResult) => {
                    if (html5QrCode) {
                        html5QrCode.stop().then(() => {
                            html5QrCode = null;
                            scannerModal.hide();
                            if (confirm(`Scanned Barcode: "${decodedText}"\n\nDo you want to save this barcode and update the part?`)) {
                                document.getElementById('barcodeValueInput').value = decodedText;
                                document.getElementById('editPartForm').submit();
                            }
                        }).catch(err => {
                            console.error(err);
                            scannerModal.hide();
                        });
                    }
                },
                (errorMessage) => {
                    // ignore
                }
            ).catch(err => {
                console.error("Camera error", err);
                document.getElementById('reader').innerHTML = `
                    <div class="alert alert-danger m-3 text-center" role="alert">
                        <i class="fas fa-exclamation-circle d-block fs-3 mb-2"></i>
                        <strong>Camera Error</strong><br>
                        <span style="font-size:0.85rem">${err}</span>
                    </div>`;
            });
        }, 450);
    });

    document.getElementById('scannerModal').addEventListener('hidden.bs.modal', () => {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode = null;
            }).catch(err => {
                console.error(err);
                html5QrCode = null;
            });
        }
        document.getElementById('reader').innerHTML = '';
    });
});
</script>
