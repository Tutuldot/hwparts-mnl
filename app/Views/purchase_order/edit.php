<?php /** Use same template as create but pre-populated */ ?>
<?php /** @var array $po @var array $lines @var array $parts */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Edit <?= esc($po['po_number']) ?></h1><p class="page-subtitle">Modify draft purchase order</p></div>
    <a href="<?= base_url("purchase-orders/{$po['id']}") ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<form action="<?= base_url("purchase-orders/{$po['id']}/update") ?>" method="POST" id="poForm">
<?= csrf_field() ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Order Details</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Supplier *</label>
                        <select name="supplier_name" class="form-select" required>
                            <option value="">— Select Supplier —</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= esc($s['name']) ?>" <?= $po['supplier_name'] === $s['name'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
                            <?php endforeach; ?>
                            <?php if (!in_array($po['supplier_name'], array_column($suppliers, 'name'))): ?>
                                <option value="<?= esc($po['supplier_name']) ?>" selected><?= esc($po['supplier_name']) ?> (Legacy)</option>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted" style="font-size: 0.75rem;">Manage this list in <a href="<?= base_url('suppliers') ?>" target="_blank">Suppliers <i class="fas fa-external-link small"></i></a>.</small>
                    </div>
                    <div class="col-md-4"><label class="form-label">Payment Type *</label>
                        <select name="payment_type" class="form-select" required>
                            <?php foreach (['cash','cheque','bank_transfer','credit_card','terms'] as $pt): ?>
                                <option value="<?= $pt ?>" <?= $po['payment_type'] === $pt ? 'selected':'' ?>><?= ucfirst(str_replace('_',' ',$pt)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Due Date *</label>
                        <input type="date" name="payment_due_date" class="form-control" value="<?= esc($po['payment_due_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-md-8"><label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"><?= esc($po['remarks']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Line Items</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn"><i class="fas fa-plus"></i> Add</button>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Part</th><th class="text-center">Qty</th><th>Unit Cost (₱)</th><th>Total (₱)</th><th></th></tr></thead>
                    <tbody id="lineRows"></tbody>
                    <tfoot><tr><td colspan="3" class="text-end fw-600">Total:</td><td class="fw-700 text-primary" id="grandTotal">₱0.00</td><td></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px"><div class="card-body">
            <div class="mb-3 text-center"><div class="text-muted small">Order Total</div>
                <div class="fs-2 fw-700 text-primary" id="sideTotal">₱0.00</div></div>
            <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save"></i> Save Changes</button>
            <a href="<?= base_url("purchase-orders/{$po['id']}") ?>" class="btn btn-outline-secondary w-100">Cancel</a>
        </div></div>
    </div>
</div>
<input type="hidden" name="lines" id="linesJson" value="[]">
</form>
<script>
const partsData = <?= json_encode(array_map(fn($p)=>['id'=>$p['id'],'name'=>$p['name'],'sku'=>$p['sku'],'type'=>$p['type']], $parts)) ?>;
let lines = <?= json_encode(array_map(fn($l)=>['part_id'=>$l['part_id'],'part_type'=>$l['part_type'],'quantity'=>$l['quantity_ordered'],'unit_cost'=>$l['unit_cost'],'total_cost'=>$l['total_cost']], $lines)) ?>;

function renderLines(){
    const tbody=document.getElementById('lineRows'); tbody.innerHTML='';
    let total=0;
    lines.forEach((line,i)=>{
        const row=document.createElement('tr');
        row.innerHTML=`<td><select class="form-select form-select-sm" onchange="updateLine(${i},'part_id',this.value)">
            <option value="">—</option>${partsData.map(p=>`<option value="${p.id}" ${line.part_id==p.id?'selected':''}>[${p.sku}] ${p.name}</option>`).join('')}</select></td>
            <td style="width:80px"><input type="number" class="form-control form-control-sm text-center" value="${line.quantity}" min="1" onchange="updateLine(${i},'quantity',+this.value)"></td>
            <td style="width:120px"><input type="number" class="form-control form-control-sm" value="${line.unit_cost}" min="0" step="0.01" onchange="updateLine(${i},'unit_cost',+this.value)"></td>
            <td class="fw-500">₱${((line.quantity||1)*(line.unit_cost||0)).toFixed(2)}</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="lines.splice(${i},1);renderLines()"><i class="fas fa-trash"></i></button></td>`;
        tbody.appendChild(row);
        total+=(line.quantity||1)*(line.unit_cost||0);
    });
    document.getElementById('grandTotal').textContent='₱'+total.toFixed(2);
    document.getElementById('sideTotal').textContent='₱'+total.toFixed(2);
    document.getElementById('linesJson').value=JSON.stringify(lines);
}
function updateLine(i,k,v){lines[i][k]=v;lines[i].total_cost=(lines[i].quantity||1)*(lines[i].unit_cost||0);renderLines();}
document.getElementById('addLineBtn').addEventListener('click',()=>{lines.push({part_id:'',quantity:1,unit_cost:0,total_cost:0});renderLines();});
renderLines();
</script>
