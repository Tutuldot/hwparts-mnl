<?php /** @var array $supplier @var array $parts @var array $contacts */ ?>
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fas fa-truck-field text-primary me-2" style="font-size:1.25rem"></i><?= esc($supplier['name']) ?></h1>
        <p class="page-subtitle">Supplier Partner Details & Catalogue Sourcing</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url("suppliers/{$supplier['id']}/edit") ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-pencil"></i> Edit Supplier</a>
        <a href="<?= base_url('suppliers') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row g-3">
    <!-- Left Column: Supplier Information & Contact Persons -->
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Supplier Information</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="text-muted small">Supplier Name</div>
                        <div class="fw-600 fs-5"><?= esc($supplier['name']) ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Status</div>
                        <span class="badge badge-<?= $supplier['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $supplier['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Notice Emails</div>
                        <?php if ($supplier['emails_for_notice']): ?>
                            <?php 
                            $emails = explode(';', $supplier['emails_for_notice']);
                            foreach ($emails as $email): 
                            ?>
                                <span class="badge badge-submitted text-lowercase mono mb-1"><?= esc(trim($email)) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Tags</div>
                        <?php if ($supplier['tags']): ?>
                            <?php 
                            $tags = explode(',', $supplier['tags']);
                            foreach ($tags as $tag): 
                            ?>
                                <span class="badge bg-secondary text-uppercase mb-1" style="font-size:0.65rem"><?= esc(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Address</div>
                        <div class="small text-dark"><?= $supplier['address'] ? esc($supplier['address']) : '<span class="text-muted small">—</span>' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Contact Persons</span></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email / Mobile</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contacts)): ?>
                                <tr><td colspan="3" class="text-muted text-center py-3 small">No contacts registered.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($contacts as $c): ?>
                                <?php if ($c['is_visible']): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-600 small"><?= esc($c['name']) ?></div>
                                            <?php if ($c['remarks']): ?>
                                                <small class="text-muted bg-light border px-1 rounded" style="font-size: 0.7rem;"><?= esc($c['remarks']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="mono small"><?= esc($c['email'] ?: '—') ?></div>
                                            <div class="mono small text-muted"><?= esc($c['mobile'] ?: '—') ?></div>
                                        </td>
                                        <td class="small text-muted"><?= esc($c['role_or_title'] ?: '—') ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Sourced Parts list -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title"><i class="fas fa-gears text-primary me-2"></i>Sourced Parts list</span>
                <span class="badge bg-primary"><?= count($parts) ?> items</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="supplierPartsTable">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Part Name</th>
                                <th>Category</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($parts)): ?>
                                <tr><td colspan="4" class="text-muted text-center py-4">No parts are currently sourced from this supplier.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($parts as $p): ?>
                                <tr>
                                    <td><span class="mono text-primary fw-500"><?= esc($p['sku']) ?></span></td>
                                    <td>
                                        <a href="<?= base_url("parts/{$p['id']}") ?>" class="fw-600 text-decoration-none text-dark"><?= esc($p['name']) ?></a>
                                        <?php if ($p['brand']): ?>
                                            <div class="text-muted small font-monospace" style="font-size:0.75rem"><?= esc($p['brand']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-draft"><?= esc($p['category_code']) ?></span></td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $p['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#supplierPartsTable tbody tr td[colspan]')) return;
    initDataTable('#supplierPartsTable');
});
</script>
