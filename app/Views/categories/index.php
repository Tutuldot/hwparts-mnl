<?php
/** @var array $categories */
?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Part Categories</h1>
        <p class="page-subtitle"><?= count($categories) ?> categories total</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> Add Category
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="categoriesTable">
            <thead><tr><th>Code</th><th>Name</th><th>Description</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><span class="badge badge-draft mono"><?= esc($cat['code']) ?></span></td>
                <td class="fw-500"><?= esc($cat['name']) ?></td>
                <td class="text-muted"><?= esc($cat['description'] ?? '—') ?></td>
                <td class="text-center">
                    <span class="badge badge-<?= $cat['is_active'] ? 'active' : 'inactive' ?>">
                        <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-secondary btn-edit-cat"
                            data-id="<?= $cat['id'] ?>"
                            data-code="<?= esc($cat['code']) ?>"
                            data-name="<?= esc($cat['name']) ?>"
                            data-description="<?= esc($cat['description'] ?? '') ?>">
                        <i class="fas fa-pencil"></i>
                    </button>
                    <form action="<?= base_url("categories/{$cat['id']}/toggle") ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-<?= $cat['is_active'] ? 'danger' : 'success' ?>"
                                onclick="return confirm('Toggle status?')">
                            <i class="fas fa-<?= $cat['is_active'] ? 'ban' : 'check' ?>"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="<?= base_url('categories/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. SEN" maxlength="10" required style="text-transform:uppercase"></div>
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Category name" required></div>
                    <div class="mb-3"><label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editCatForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Code</label>
                        <input type="text" name="code" id="editCode" class="form-control" maxlength="10" required style="text-transform:uppercase"></div>
                    <div class="mb-3"><label class="form-label">Name</label>
                        <input type="text" name="name" id="editName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Description</label>
                        <textarea name="description" id="editDesc" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initDataTable('#categoriesTable');
    document.querySelectorAll('.btn-edit-cat').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            document.getElementById('editCode').value = btn.dataset.code;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editDesc').value = btn.dataset.description;
            document.getElementById('editCatForm').action = `<?= base_url('categories/') ?>${id}/update`;
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        });
    });
});
</script>
