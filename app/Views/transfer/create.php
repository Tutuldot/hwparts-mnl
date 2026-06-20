<?php /** @var array $warehouses @var array $parts */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">New Inventory Transfer</h1><p class="page-subtitle">Move stock between warehouses</p></div>
    <a href="<?= base_url('transfers') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form action="<?= base_url('transfers/store') ?>" method="POST" id="transferForm">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Transfer Details</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">From Warehouse *</label>
                        <select name="from_warehouse_id" id="fromWarehouse" class="form-select" required>
                            <option value="">— Select Source —</option>
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= esc($wh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-center">
                        <i class="fas fa-arrow-right fs-4 text-muted mb-2"></i>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">To Warehouse *</label>
                        <select name="to_warehouse_id" id="toWarehouse" class="form-select" required>
                            <option value="">— Select Destination —</option>
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= esc($wh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Transfer Date</label>
                        <input type="date" name="transfer_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Reason for transfer">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">Items to Transfer</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn"><i class="fas fa-plus"></i> Add Item</button>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Part</th><th>Type</th><th>Stock Available</th><th>Qty / Unit</th><th></th></tr></thead>
                    <tbody id="transferLines"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px"><div class="card-body">
            <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save"></i> Create Transfer</button>
            <a href="<?= base_url('transfers') ?>" class="btn btn-outline-secondary w-100">Cancel</a>
        </div></div>
    </div>
</div>
<input type="hidden" name="lines" id="linesJson" value="[]">
</form>

<script>
const partsData = <?= json_encode(array_map(fn($p) => ['id'=>$p['id'],'name'=>$p['name'],'sku'=>$p['sku'],'type'=>$p['type']], $parts)) ?>;
let lines = [];

function getFromWh() { return document.getElementById('fromWarehouse').value; }

function renderLines() {
    const tbody = document.getElementById('transferLines');
    tbody.innerHTML = '';
    lines.forEach((line, i) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select class="form-select form-select-sm" onchange="updateLine(${i}, 'part_id', this.value, this)">
                    <option value="">— Select Part —</option>
                    ${partsData.map(p=>`<option value="${p.id}" data-type="${p.type}" ${line.part_id==p.id?'selected':''}>[${p.sku}] ${p.name}</option>`).join('')}
                </select>
            </td>
            <td><span class="badge badge-${line.part_type||'quantity'}">${line.part_type==='non_quantity'?'Non-Qty':'Qty'}</span></td>
            <td><span id="stock_${i}" class="fw-500 text-primary">—</span></td>
            <td style="width:100px">
                ${line.part_type === 'non_quantity'
                    ? `<select class="form-select form-select-sm" id="unitSel_${i}" onchange="updateLine(${i},'parts_detail_id',this.value)"><option value="">Loading...</option></select>`
                    : `<input type="number" class="form-control form-control-sm" value="${line.quantity||1}" min="1" onchange="updateLine(${i},'quantity',+this.value)">`
                }
            </td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(${i})"><i class="fas fa-trash"></i></button></td>`;
        tbody.appendChild(row);
        if (line.part_id && getFromWh()) updateStock(i, line.part_id, line.part_type);
    });
    document.getElementById('linesJson').value = JSON.stringify(lines);
}

function updateLine(i, key, val, sel=null) {
    lines[i][key] = val;
    if (sel && key === 'part_id') {
        const opt  = sel.options[sel.selectedIndex];
        lines[i].part_type = opt.dataset.type || 'quantity';
        lines[i].quantity  = 1;
        if (getFromWh() && val) updateStock(i, val, lines[i].part_type);
    }
    renderLines();
}

function updateStock(i, partId, type) {
    const whId = getFromWh();
    if (!whId || !partId) return;
    const url = type === 'non_quantity'
        ? `<?= base_url('transfers/ajax/available-units') ?>?part_id=${partId}&warehouse_id=${whId}`
        : `<?= base_url('transfers/ajax/available-stock') ?>?part_id=${partId}&warehouse_id=${whId}`;
    fetch(url).then(r=>r.json()).then(data => {
        const el = document.getElementById(`stock_${i}`);
        if (type === 'non_quantity') {
            if (el) el.textContent = `${data.length} units`;
            const sel = document.getElementById(`unitSel_${i}`);
            if (sel) sel.innerHTML = '<option value="">— Select Unit —</option>' +
                data.map(u=>`<option value="${u.id}">${u.unique_qr_code}</option>`).join('');
        } else {
            if (el) el.textContent = data.stock + ' in stock';
        }
    });
}

function removeLine(i) { lines.splice(i, 1); renderLines(); }
document.getElementById('addLineBtn').addEventListener('click', () => {
    lines.push({part_id:'', part_type:'quantity', quantity:1}); renderLines();
});
document.getElementById('fromWarehouse').addEventListener('change', renderLines);
document.getElementById('transferForm').addEventListener('submit', e => {
    if (!document.getElementById('fromWarehouse').value || !document.getElementById('toWarehouse').value) {
        e.preventDefault(); toastr.error('Select both warehouses.'); return;
    }
    if (document.getElementById('fromWarehouse').value === document.getElementById('toWarehouse').value) {
        e.preventDefault(); toastr.error('Source and destination warehouses must be different.'); return;
    }
    if (lines.length === 0) { e.preventDefault(); toastr.error('Add at least one item.'); }
});
renderLines();
</script>
