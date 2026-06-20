<?php /** @var array $header @var array $lines */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title"><?= esc($header['reference_no']) ?></h1>
        <p class="page-subtitle"><?= ucfirst(str_replace('_',' ',$header['source'])) ?> &middot; <?= esc($header['warehouse_name'] ?? '—') ?> &middot; <?= esc($header['created_by_name']) ?></p>
    </div>
    <a href="<?= base_url('inventory') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Part</th><th>SKU</th><th>Type</th><th>Warehouse</th><th>Location</th><th class="text-center">Qty</th><th class="text-end">Unit Cost</th><th class="text-end">Total</th></tr></thead>
            <tbody>
            <?php foreach ($lines as $l): ?>
            <tr>
                <td><?= esc($l['part_name']) ?><?php if ($l['variant_name']): ?><br><small class="text-muted"><?= esc($l['variant_name']) ?></small><?php endif; ?></td>
                <td><span class="mono small"><?= esc($l['sku']) ?></span></td>
                <td><span class="badge badge-<?= $l['part_type'] ?>"><?= $l['part_type']==='quantity'?'Qty':'Non-Qty' ?></span></td>
                <td><?= esc($l['warehouse_name']) ?></td>
                <td><?= esc($l['location_name'] ?? '—') ?></td>
                <td class="text-center"><?= number_format($l['quantity']) ?></td>
                <td class="text-end">₱<?= number_format($l['acquisition_cost'],2) ?></td>
                <td class="text-end">₱<?= number_format($l['total_cost'],2) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
