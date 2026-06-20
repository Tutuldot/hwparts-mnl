<?php /** @var array $parts @var array $warehouses */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Add Inventory</h1><p class="page-subtitle">Manual inventory entry</p></div>
    <a href="<?= base_url('inventory') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form action="<?= base_url('inventory/store') ?>" method="POST" id="invForm">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Header</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Default Warehouse *</label>
                        <select name="warehouse_id" id="headerWarehouse" class="form-select" required>
                            <option value="">— Select Warehouse —</option>
                            <?php foreach ($warehouses as $wh): ?><option value="<?= $wh['id'] ?>"><?= esc($wh['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional notes"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">Line Items</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn"><i class="fas fa-plus"></i> Add Line</button>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0"><thead><tr><th>Part</th><th>Warehouse</th><th>Location</th><th class="text-center">Qty</th><th>Unit Cost</th><th></th></tr></thead>
                    <tbody id="lineRows"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px"><div class="card-body">
            <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save"></i> Save Inventory</button>
            <a href="<?= base_url('inventory') ?>" class="btn btn-outline-secondary w-100">Cancel</a>
        </div></div>
    </div>
</div>
<input type="hidden" name="lines" id="linesJson" value="[]">
</form>

<script>
const partsData = <?= json_encode(array_map(fn($p)=>['id'=>$p['id'],'name'=>$p['name'],'sku'=>$p['sku'],'type'=>$p['type']], $parts)) ?>;
const warehousesData = <?= json_encode($warehouses) ?>;
let lines = [];

function fetchLocations(whId, selectEl) {
    if (!whId) { selectEl.innerHTML = '<option value="">— Location —</option>'; return; }
    fetch(`<?= base_url('warehouses/ajax/locations') ?>?warehouse_id=${whId}`)
        .then(r=>r.json()).then(locs => {
            selectEl.innerHTML = '<option value="">— Location (opt) —</option>' +
                locs.map(l=>`<option value="${l.id}">[${l.code}] ${l.name}</option>`).join('');
        });
}

function renderLines() {
    const tbody = document.getElementById('lineRows');
    tbody.innerHTML = '';
    lines.forEach((line, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select class="form-select form-select-sm" onchange="updateLine(${i},'part_id',this.value,this)">
                    <option value="">— Part —</option>
                    ${partsData.map(p=>`<option value="${p.id}" data-type="${p.type}" ${line.part_id==p.id?'selected':''}>[${p.sku}] ${p.name}</option>`).join('')}
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm wh-line-sel" id="wh_${i}"
                    onchange="updateLine(${i},'warehouse_id',this.value); fetchLocations(this.value, document.getElementById('loc_${i}'))">
                    ${warehousesData.map(w=>`<option value="${w.id}" ${line.warehouse_id==w.id?'selected':''}>${w.name}</option>`).join('')}
                </select>
            </td>
            <td><select class="form-select form-select-sm" id="loc_${i}" onchange="updateLine(${i},'warehouse_location_id',this.value)">
                <option value="">— Location —</option></select></td>
            <td style="width:70px"><input type="number" class="form-control form-control-sm text-center" value="${line.quantity||1}" min="1"
                onchange="updateLine(${i},'quantity',+this.value)"></td>
            <td style="width:110px"><input type="number" class="form-control form-control-sm" value="${line.unit_cost||0}" min="0" step="0.01"
                onchange="updateLine(${i},'unit_cost',+this.value)" placeholder="0.00"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(${i})"><i class="fas fa-trash"></i></button></td>`;
        tbody.appendChild(tr);
        if (line.warehouse_id) fetchLocations(line.warehouse_id, document.getElementById(`loc_${i}`));
    });
    document.getElementById('linesJson').value = JSON.stringify(lines);
}

function updateLine(i, key, val, sel=null) {
    lines[i][key] = val;
    if (sel && key==='part_id') { const o=sel.options[sel.selectedIndex]; lines[i].part_type=o.dataset.type||'quantity'; }
    renderLines();
}
function removeLine(i) { lines.splice(i,1); renderLines(); }
document.getElementById('addLineBtn').addEventListener('click', () => {
    const whId = document.getElementById('headerWarehouse').value;
    lines.push({part_id:'', warehouse_id: whId, warehouse_location_id:'', quantity:1, unit_cost:0, part_type:'quantity'});
    renderLines();
});
document.getElementById('invForm').addEventListener('submit', e => {
    if (!document.getElementById('headerWarehouse').value) { e.preventDefault(); toastr.error('Select a warehouse.'); return; }
    if (lines.length===0) { e.preventDefault(); toastr.error('Add at least one line.'); }
});
</script>
