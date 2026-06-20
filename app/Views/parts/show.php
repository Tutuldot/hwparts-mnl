<?php /** @var array $part @var array $variants @var array $carTags @var array $stock */ ?>
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title"><span class="mono"><?= esc($part['sku']) ?></span></h1>
        <p class="page-subtitle"><?= esc($part['name']) ?> &middot; <?= esc($part['category_name']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url("parts/{$part['id']}/print-label") ?>" class="btn btn-outline-secondary btn-sm" target="_blank"><i class="fas fa-print"></i> Label</a>
        <a href="<?= base_url("parts/{$part['id']}/edit") ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-pencil"></i> Edit</a>
        <a href="<?= base_url('parts') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <!-- Details -->
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Part Details</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small">SKU</div><div class="mono fw-600"><?= esc($part['sku']) ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Type</div><span class="badge badge-<?= $part['type'] ?>"><?= ucfirst(str_replace('_',' ',$part['type'])) ?></span></div>
                    <div class="col-md-4"><div class="text-muted small">Status</div><span class="badge badge-<?= $part['is_active'] ? 'active' : 'inactive' ?>"><?= $part['is_active'] ? 'Active' : 'Inactive' ?></span></div>
                    <div class="col-md-4"><div class="text-muted small">Category</div><div><?= esc($part['category_name']) ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Unit</div><div><?= esc($part['unit_of_measure']) ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Min Stock</div><div><?= $part['min_stock_level'] ?></div></div>
                    <!-- Brand + OEM -->
                    <div class="col-md-4">
                        <div class="text-muted small">Brand</div>
                        <div class="mono fw-600"><?= $part['brand'] ? esc($part['brand']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">OEM Part</div>
                        <div>
                            <?php if ($part['oem']): ?>
                                <span class="badge" style="background:#16a34a">Yes — OEM</span>
                            <?php else: ?>
                                <span class="text-muted">No</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($part['description']): ?>
                    <div class="col-12"><div class="text-muted small">Description</div><div><?= esc($part['description']) ?></div></div>
                    <?php endif; ?>
                    <?php if ($part['barcode_value']): ?>
                    <div class="col-12"><div class="text-muted small">Barcode</div><div class="mono"><?= esc($part['barcode_value']) ?></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stock by Warehouse -->
        <div class="card mb-3">
            <div class="card-header">
                <span class="card-title">Stock by Warehouse</span>
                <a href="<?= base_url("inventory/create") ?>" class="btn btn-sm btn-outline-primary">Add Inventory</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Warehouse</th><th class="text-center">Stock</th></tr></thead>
                    <tbody>
                    <?php if (empty($stock)): ?><tr><td colspan="2" class="text-muted text-center py-3">No stock recorded.</td></tr><?php endif; ?>
                    <?php foreach ($stock as $s): ?>
                    <tr><td><?= esc($s['warehouse_name']) ?> <small class="mono text-muted">(<?= esc($s['warehouse_code']) ?>)</small></td>
                        <td class="text-center fw-600 <?= $s['stock'] <= $part['min_stock_level'] && $s['stock'] > 0 ? 'text-warning' : ($s['stock'] == 0 ? 'text-danger' : 'text-success') ?>"><?= number_format($s['stock']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title"><i class="fas fa-tags text-success me-2"></i>Selling Prices</span>
                <a href="<?= base_url("parts/{$part['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-pencil me-1"></i>Edit Prices
                </a>
            </div>
            <div class="card-body p-0">
                <?php
                $basePrice = $prices['base'] ?? null;
                $hasPrices = $basePrice || !empty(array_filter($prices, fn($k) => $k !== 'base', ARRAY_FILTER_USE_KEY));
                ?>
                <?php if (!$hasPrices): ?>
                    <div class="text-center text-muted py-4 small">
                        <i class="fas fa-tags fa-2x opacity-25 d-block mb-2"></i>
                        No selling prices set. <a href="<?= base_url("parts/{$part['id']}/edit") ?>">Edit part</a> to add prices.
                    </div>
                <?php else: ?>
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Part / Variant</th>
                            <th class="text-end">Selling Price</th>
                            <th class="text-end">Min. Price</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Base row -->
                        <tr class="<?= $basePrice ? '' : 'text-muted' ?>">
                            <td>
                                <i class="fas fa-box me-1 text-primary"></i>
                                <strong><?= esc($part['name']) ?></strong>
                                <span class="badge bg-primary ms-1">Base</span>
                            </td>
                            <td class="text-end fw-bold <?= $basePrice ? 'text-success' : 'text-muted' ?>">
                                <?= $basePrice ? '₱' . number_format((float)$basePrice['selling_price'], 2) : '—' ?>
                            </td>
                            <td class="text-end text-muted small">
                                <?= ($basePrice && $basePrice['min_selling_price'] !== null)
                                    ? '₱' . number_format((float)$basePrice['min_selling_price'], 2)
                                    : '—' ?>
                            </td>
                            <td class="text-muted small"><?= esc($basePrice['notes'] ?? '') ?></td>
                        </tr>
                        <!-- Variant rows -->
                        <?php foreach ($variants as $v): ?>
                            <?php $vp = $prices[$v['id']] ?? null; ?>
                            <tr class="<?= $vp ? '' : 'text-muted' ?>">
                                <td class="ps-4">
                                    <i class="fas fa-code-branch me-1 text-muted"></i>
                                    <?= esc($v['variant_name']) ?>
                                    <span class="badge bg-secondary ms-1 font-monospace small"><?= esc($v['variant_sku']) ?></span>
                                </td>
                                <td class="text-end fw-bold <?= $vp ? 'text-success' : 'text-muted' ?>">
                                    <?= $vp ? '₱' . number_format((float)$vp['selling_price'], 2) : '—' ?>
                                </td>
                                <td class="text-end text-muted small">
                                    <?= ($vp && $vp['min_selling_price'] !== null)
                                        ? '₱' . number_format((float)$vp['min_selling_price'], 2)
                                        : '—' ?>
                                </td>
                                <td class="text-muted small"><?= esc($vp['notes'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sourcing Suppliers -->

        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-truck-field text-primary me-2"></i>Sourcing Suppliers</span></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead><tr><th>Supplier Name</th><th class="text-center">Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($suppliers)): ?>
                        <tr><td colspan="2" class="text-muted text-center py-3">No sourcing suppliers linked. <a href="<?= base_url("parts/{$part['id']}/edit") ?>">Edit to link one</a>.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($suppliers as $sup): ?>
                    <tr>
                        <td class="fw-500"><a href="<?= base_url("suppliers/{$sup['id']}") ?>" class="text-decoration-none"><?= esc($sup['name']) ?></a></td>
                        <td class="text-center"><span class="badge badge-<?= $sup['is_active'] ? 'active' : 'inactive' ?>"><?= $sup['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Car Tags -->
        <?php if (!empty($carTags)): ?>
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Vehicle Compatibility</span></div>
            <div class="card-body">
                <?php foreach ($carTags as $tag): ?>
                <span class="badge badge-draft me-1 mb-1"><?= esc($tag['brand']) ?> <?= esc($tag['model']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <!-- Photo Gallery Card -->
        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-image text-primary me-2"></i>Part Photos</span></div>
            <div class="card-body">
                <?php 
                $primary = null;
                foreach ($photos as $p) { if ($p['is_primary']) { $primary = $p; break; } }
                if (!$primary && !empty($photos)) { $primary = $photos[0]; }
                ?>
                <?php if ($primary): ?>
                    <div class="text-center border rounded p-2 bg-light mb-2">
                        <img id="mainGalleryImage" src="<?= base_url($primary['photo_path']) ?>" alt="Part Image" class="img-fluid rounded" style="max-height: 220px; object-fit: contain; cursor: zoom-in;" onclick="window.open(this.src, '_blank')">
                    </div>
                    <?php if (count($photos) > 1): ?>
                        <div class="d-flex gap-1 overflow-x-auto pb-1" style="max-width: 100%;">
                            <?php foreach ($photos as $photo): ?>
                                <img src="<?= base_url($photo['photo_path']) ?>" class="rounded border gallery-thumb" 
                                     style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; opacity: <?= $photo['is_primary'] ? '1' : '0.6' ?>;" 
                                     onclick="document.getElementById('mainGalleryImage').src = this.src; document.querySelectorAll('.gallery-thumb').forEach(el => el.style.opacity = '0.6'); this.style.opacity = '1';">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4 border rounded bg-light">
                        <i class="fas fa-image fa-3x opacity-25 mb-2"></i>
                        <p class="small mb-0">No photos uploaded.<br><a href="<?= base_url("parts/{$part['id']}/edit") ?>">Edit part</a> to add photos.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Variants -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Variants (<?= count($variants) ?>)</span>
                <a href="<?= base_url("parts/{$part['id']}/variants") ?>" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($variants)): ?>
                <div class="text-muted text-center py-3">No variants.</div>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Variant</th><th>SKU</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($variants as $v): ?>
                    <tr><td><?= esc($v['variant_name']) ?></td><td class="mono small"><?= esc($v['variant_sku']) ?></td>
                        <td><span class="badge badge-<?= $v['is_active'] ? 'active' : 'inactive' ?>"><?= $v['is_active']?'On':'Off' ?></span></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
