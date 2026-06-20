<?php /** @var array $part @var array $categories @var array $carTags */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Edit <?= esc($part['sku']) ?></h1><p class="page-subtitle"><?= esc($part['name']) ?></p></div>
    <a href="<?= base_url("parts/{$part['id']}") ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form action="<?= base_url("parts/{$part['id']}/update") ?>" method="POST" id="editPartForm">
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
                    <div class="col-md-5 position-relative">
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
                    <div class="col-12"><label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= esc($part['description']) ?></textarea></div>
                </div>
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
