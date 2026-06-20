<?php /** @var array $warehouses */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Warehouses</h1>
        <p class="page-subtitle"><?= count($warehouses) ?> warehouses configured</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
        <i class="fas fa-plus"></i> Add Warehouse
    </button>
</div>

<div class="row g-3">
    <?php foreach ($warehouses as $wh): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <div>
                    <span class="mono fw-600 text-primary"><?= esc($wh['code']) ?></span>
                    <span class="badge badge-<?= $wh['is_active'] ? 'active' : 'inactive' ?> ms-2"><?= $wh['is_active'] ? 'Active' : 'Inactive' ?></span>
                    <div class="fw-600 mt-1"><?= esc($wh['name']) ?></div>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary btn-edit-wh"
                            data-id="<?= $wh['id'] ?>"
                            data-code="<?= esc($wh['code']) ?>"
                            data-name="<?= esc($wh['name']) ?>"
                            data-address="<?= esc($wh['address'] ?? '') ?>"
                            data-contact_person="<?= esc($wh['contact_person'] ?? '') ?>"
                            data-contact_number="<?= esc($wh['contact_number'] ?? '') ?>">
                        <i class="fas fa-pencil"></i>
                    </button>
                    <form action="<?= base_url("warehouses/{$wh['id']}/toggle") ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-<?= $wh['is_active'] ? 'danger' : 'success' ?>"
                                onclick="return confirm('Toggle status?')">
                            <i class="fas fa-<?= $wh['is_active'] ? 'ban' : 'check' ?>"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center g-2 mb-3">
                    <div class="col-6">
                        <div class="fw-700 fs-4"><?= number_format($wh['location_count'] ?? 0) ?></div>
                        <div class="text-muted" style="font-size:.75rem">Sub-Locations</div>
                    </div>
                </div>
                <?php if ($wh['address']): ?>
                    <div class="text-muted small mb-1"><i class="fas fa-location-dot me-1"></i><?= esc($wh['address']) ?></div>
                <?php endif; ?>
                <?php if ($wh['contact_person']): ?>
                    <div class="text-muted small"><i class="fas fa-user me-1"></i><?= esc($wh['contact_person']) ?>
                    <?php if ($wh['contact_number']): ?> &mdash; <?= esc($wh['contact_number']) ?><?php endif; ?></div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent border-top-0 pt-0">
                <a href="<?= base_url("warehouses/{$wh['id']}/locations") ?>" class="btn btn-sm btn-outline-primary w-100">
                    <i class="fas fa-map-pin me-1"></i> Manage Locations
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($warehouses)): ?>
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">
        <i class="fas fa-building fa-3x mb-3 opacity-25"></i><p>No warehouses yet. Add your first one!</p>
    </div></div></div>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addWarehouseModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Warehouse</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="<?= base_url('warehouses/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-4"><label class="form-label">Code *</label><input type="text" name="code" class="form-control" placeholder="WH-A" maxlength="20" required style="text-transform:uppercase"></div>
                    <div class="col-8"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" placeholder="Main Warehouse" required></div>
                    <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" placeholder="Full address"></div>
                    <div class="col-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control"></div>
                    <div class="col-6"><label class="form-label">Contact Number</label><input type="text" name="contact_number" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div></div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editWarehouseModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Warehouse</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="editWhForm" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-4"><label class="form-label">Code *</label><input type="text" name="code" id="editWhCode" class="form-control" style="text-transform:uppercase" required></div>
                    <div class="col-8"><label class="form-label">Name *</label><input type="text" name="name" id="editWhName" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" id="editWhAddress" class="form-control"></div>
                    <div class="col-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" id="editWhContactPerson" class="form-control"></div>
                    <div class="col-6"><label class="form-label">Contact Number</label><input type="text" name="contact_number" id="editWhContactNumber" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div></div>
</div>

<script>
document.querySelectorAll('.btn-edit-wh').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('editWhCode').value = btn.dataset.code;
        document.getElementById('editWhName').value = btn.dataset.name;
        document.getElementById('editWhAddress').value = btn.dataset.address;
        document.getElementById('editWhContactPerson').value = btn.dataset.contact_person;
        document.getElementById('editWhContactNumber').value = btn.dataset.contact_number;
        document.getElementById('editWhForm').action = `<?= base_url('warehouses/') ?>${id}/update`;
        new bootstrap.Modal(document.getElementById('editWarehouseModal')).show();
    });
});
</script>
