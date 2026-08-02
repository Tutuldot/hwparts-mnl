<?php /** @var array $parts */ ?>
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fas fa-gears text-primary me-2" style="font-size:1.25rem"></i>Parts</h1>
        <p class="page-subtitle"><?= count($parts) ?> parts in catalogue</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importPartsModal">
            <i class="fas fa-file-excel"></i> Import CSV
        </button>
        <a href="<?= base_url('parts/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Part</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" id="partsTable">
            <thead>
                <tr>
                    <th style="width: 60px;">Photo</th>
                    <th>Part Name / SKU</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th class="text-end">Selling Price</th>
                    <th class="text-end">Min Price</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($parts as $p): ?>
            <tr>
                <td>
                    <?php if (!empty($p['primary_photo'])): ?>
                        <img src="<?= base_url($p['primary_photo']['photo_path']) ?>" alt="Photo" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded border bg-light text-muted d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas fa-image" style="font-size: 0.85rem; opacity: 0.5;"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= base_url("parts/{$p['id']}") ?>" class="fw-bold text-decoration-none text-dark"><?= esc($p['name']) ?></a>
                    <div class="text-muted font-monospace small" style="font-size: 0.78rem;"><i class="fas fa-barcode me-1"></i>SKU: <?= esc($p['sku']) ?></div>
                    <?php if ($p['description']): ?><div class="text-muted small"><?= esc(substr($p['description'], 0, 60)) ?>...</div><?php endif; ?>
                </td>
                <td><?= $p['brand'] ? '<span class="mono fw-500">'.esc($p['brand']).'</span>' : '<span class="text-muted">—</span>' ?></td>
                <td><span class="badge badge-draft"><?= esc($p['category_code']) ?> &middot; <?= esc($p['category_name']) ?></span></td>
                <td><span class="badge badge-<?= $p['type'] ?>"><?= $p['type'] === 'quantity' ? 'Qty' : 'Non-Qty' ?></span></td>
                <td class="text-end font-weight-bold text-success">
                    <?= $p['selling_price'] !== null ? '₱' . number_format($p['selling_price'], 2) : '<span class="text-muted font-weight-normal">—</span>' ?>
                </td>
                <td class="text-end font-monospace text-dark">
                    <?= $p['min_selling_price'] !== null ? '₱' . number_format($p['min_selling_price'], 2) : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="text-center"><span class="badge badge-<?= $p['is_active'] ? 'active' : 'inactive' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="<?= base_url("parts/{$p['id']}") ?>" class="btn btn-sm btn-outline-primary" title="View Details"><i class="fas fa-eye"></i></a>
                        <a href="<?= base_url("parts/{$p['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary" title="Edit Part"><i class="fas fa-pencil"></i></a>
                        <form action="<?= base_url("parts/{$p['id']}/toggle") ?>" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-<?= $p['is_active'] ? 'danger' : 'success' ?>" onclick="return confirm('Toggle status?')" title="Toggle Status">
                                <i class="fas fa-<?= $p['is_active'] ? 'ban' : 'check' ?>"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div class="modal fade" id="importPartsModal" tabindex="-1" aria-labelledby="importPartsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importPartsModalLabel"><i class="fas fa-file-csv text-primary me-2"></i>Import Parts via CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('parts/upload') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="small text-muted">
                        Select a CSV file containing your parts list to upload in bulk. You can download our layout template below to ensure the formatting matches.
                    </p>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Select CSV File *</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-circle-info me-1 text-primary"></i> <strong>Note on duplicate SKUs:</strong> If any SKU or Name already exists in the system or is duplicated in your file, the entire import will fail with a list of corrections to perform first.
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <a href="<?= base_url('parts/template') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download me-1"></i>Download Template
                    </a>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Upload & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>document.addEventListener('DOMContentLoaded', () => initDataTable('#partsTable'));</script>
