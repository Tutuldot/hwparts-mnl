<?php /** @var array $po @var array $lines @var array $warehouses */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Receive Items — <?= esc($po['po_number']) ?></h1>
        <p class="page-subtitle">Select quantity, warehouse &amp; location for each line item</p>
    </div>
    <a href="<?= base_url("purchase-orders/{$po['id']}") ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to PO</a>
</div>

<?php foreach ($lines as $line): ?>
<?php $remaining = $line['quantity_ordered'] - $line['quantity_received']; ?>
<div class="card mb-3">
    <div class="card-header">
        <div>
            <span class="fw-600"><?= esc($line['part_name']) ?></span>
            <?php if ($line['variant_name']): ?><small class="text-muted ms-1">(<?= esc($line['variant_name']) ?>)</small><?php endif; ?>
            <span class="mono text-muted ms-2"><?= esc($line['sku']) ?></span>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-muted small">Ordered: <?= $line['quantity_ordered'] ?> | Received: <?= $line['quantity_received'] ?> | Remaining: <strong><?= $remaining ?></strong></span>
            <?php if ($line['is_received']): ?>
                <span class="badge badge-approved">Fully Received</span>
            <?php endif; ?>
        </div>
    </div>
    <?php if (! $line['is_received'] && $remaining > 0): ?>
    <div class="card-body">
        <form action="<?= base_url("purchase-orders/{$po['id']}/receive-line") ?>" method="POST" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <input type="hidden" name="line_id" value="<?= $line['id'] ?>">
            <div class="col-md-2">
                <label class="form-label">Qty to Receive *</label>
                <input type="number" name="qty_received" class="form-control" value="<?= $remaining ?>" min="1" max="<?= $remaining ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Warehouse *</label>
                <select name="warehouse_id" class="form-select wh-select" required>
                    <option value="">— Select Warehouse —</option>
                    <?php foreach ($warehouses as $wh): ?>
                        <option value="<?= $wh['id'] ?>"><?= esc($wh['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sub-Location</label>
                <select name="warehouse_location_id" class="form-select loc-select">
                    <option value="">— Select Location (optional) —</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100"><i class="fas fa-check"></i> Receive</button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="card-body text-muted py-2"><i class="fas fa-check-circle text-success me-1"></i> All items received.</div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<script>
document.querySelectorAll('.wh-select').forEach(sel => {
    sel.addEventListener('change', function() {
        const locSelect = this.closest('.row').querySelector('.loc-select');
        const whId = this.value;
        if (!whId) { locSelect.innerHTML = '<option value="">— Select Location (optional) —</option>'; return; }
        fetch(`<?= base_url('warehouses/ajax/locations') ?>?warehouse_id=${whId}`)
            .then(r => r.json())
            .then(locs => {
                locSelect.innerHTML = '<option value="">— None —</option>' +
                    locs.map(l => `<option value="${l.id}">[${l.code}] ${l.name}</option>`).join('');
            });
    });
});
</script>
