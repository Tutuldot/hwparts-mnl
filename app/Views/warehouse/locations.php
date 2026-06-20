<?php /** @var array $warehouse @var array $locations */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><?= esc($warehouse['name']) ?> — Locations</h1>
        <p class="page-subtitle"><span class="mono"><?= esc($warehouse['code']) ?></span> &middot; <?= count($locations) ?> sub-locations</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('warehouses') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
            <i class="fas fa-plus"></i> Add Location
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="locationsTable">
            <thead><tr><th>Code</th><th>Name</th><th>Description</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($locations as $loc): ?>
            <tr>
                <td><span class="mono badge badge-draft"><?= esc($loc['code']) ?></span></td>
                <td class="fw-500"><?= esc($loc['name']) ?></td>
                <td class="text-muted"><?= esc($loc['description'] ?? '—') ?></td>
                <td class="text-center"><span class="badge badge-<?= $loc['is_active'] ? 'active' : 'inactive' ?>"><?= $loc['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-secondary btn-edit-loc"
                            data-id="<?= $loc['id'] ?>"
                            data-code="<?= esc($loc['code']) ?>"
                            data-name="<?= esc($loc['name']) ?>"
                            data-description="<?= esc($loc['description'] ?? '') ?>">
                        <i class="fas fa-pencil"></i>
                    </button>
                    <form action="<?= base_url("warehouses/locations/{$loc['id']}/toggle") ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-<?= $loc['is_active'] ? 'danger' : 'success' ?>" onclick="return confirm('Toggle?')">
                            <i class="fas fa-<?= $loc['is_active'] ? 'ban' : 'check' ?>"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($locations)): ?><tr><td colspan="5" class="text-center text-muted py-4">No locations yet. Add a shelf, bin, or row.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Location</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="<?= base_url("warehouses/{$warehouse['id']}/locations/store") ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Code *</label><input type="text" name="code" class="form-control" placeholder="SHF-A" maxlength="30" required style="text-transform:uppercase"></div>
                <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" placeholder="Shelf A" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div></div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Location</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="editLocForm" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Code *</label><input type="text" name="code" id="editLocCode" class="form-control" style="text-transform:uppercase" required></div>
                <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" id="editLocName" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="editLocDesc" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initDataTable('#locationsTable');
    document.querySelectorAll('.btn-edit-loc').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editLocCode').value = btn.dataset.code;
            document.getElementById('editLocName').value = btn.dataset.name;
            document.getElementById('editLocDesc').value = btn.dataset.description;
            document.getElementById('editLocForm').action = `<?= base_url('warehouses/locations/') ?>${btn.dataset.id}/update`;
            new bootstrap.Modal(document.getElementById('editLocationModal')).show();
        });
    });
});
</script>
