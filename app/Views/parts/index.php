<?php /** @var array $parts */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Parts</h1>
        <p class="page-subtitle"><?= count($parts) ?> parts in catalogue</p>
    </div>
    <a href="<?= base_url('parts/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Part</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="partsTable">
            <thead><tr><th>SKU</th><th>Name</th><th>Brand</th><th>Category</th><th>Type</th><th>OEM</th><th>UOM</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($parts as $p): ?>
            <tr>
                <td><span class="mono text-primary fw-500"><?= esc($p['sku']) ?></span></td>
                <td>
                    <a href="<?= base_url("parts/{$p['id']}") ?>" class="fw-500 text-decoration-none text-dark"><?= esc($p['name']) ?></a>
                    <?php if ($p['description']): ?><div class="text-muted small"><?= esc(substr($p['description'], 0, 60)) ?>...</div><?php endif; ?>
                </td>
                <td><?= $p['brand'] ? '<span class="mono fw-500">'.esc($p['brand']).'</span>' : '<span class="text-muted">—</span>' ?></td>
                <td><span class="badge badge-draft"><?= esc($p['category_code']) ?> &middot; <?= esc($p['category_name']) ?></span></td>
                <td><span class="badge badge-<?= $p['type'] ?>"><?= $p['type'] === 'quantity' ? 'Qty' : 'Non-Qty' ?></span></td>
                <td class="text-center">
                    <?php if ($p['oem']): ?>
                        <span class="badge" style="background:#16a34a">OEM</span>
                    <?php else: ?>
                        <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
                <td class="text-muted"><?= esc($p['unit_of_measure']) ?></td>
                <td class="text-center"><span class="badge badge-<?= $p['is_active'] ? 'active' : 'inactive' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td class="text-center">
                    <a href="<?= base_url("parts/{$p['id']}") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    <a href="<?= base_url("parts/{$p['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pencil"></i></a>
                    <form action="<?= base_url("parts/{$p['id']}/toggle") ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-<?= $p['is_active'] ? 'danger' : 'success' ?>" onclick="return confirm('Toggle status?')">
                            <i class="fas fa-<?= $p['is_active'] ? 'ban' : 'check' ?>"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>document.addEventListener('DOMContentLoaded', () => initDataTable('#partsTable'));</script>
