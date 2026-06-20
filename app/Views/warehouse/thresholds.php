<?php /** @var array $thresholds @var array $parts @var array $warehouses */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Stock Thresholds</h1><p class="page-subtitle">Per-warehouse minimum stock levels</p></div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addThresholdModal"><i class="fas fa-plus"></i> Add Threshold</button>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0" id="thresholdTable">
            <thead><tr><th>Part</th><th>SKU</th><th>Warehouse</th><th class="text-center">Min Stock</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($thresholds as $t): ?>
            <tr>
                <td><?= esc($t['part_name']) ?></td>
                <td class="mono small"><?= esc($t['sku']) ?></td>
                <td><span class="badge badge-draft"><?= esc($t['warehouse_code']) ?></span> <?= esc($t['warehouse_name']) ?></td>
                <td class="text-center fw-600"><?= $t['min_stock_level'] ?></td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-sm btn-outline-secondary btn-edit-threshold"
                                data-id="<?= $t['id'] ?>" data-min="<?= $t['min_stock_level'] ?>">
                            <i class="fas fa-pencil"></i>
                        </button>
                        <form action="<?= base_url("thresholds/{$t['id']}/delete") ?>" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove threshold?')"><i class="fas fa-trash"></i></button>
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

<!-- Add Modal -->
<div class="modal fade" id="addThresholdModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Stock Threshold</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= base_url('thresholds/store') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Part *</label>
                <select name="part_id" class="form-select" required>
                    <option value="">— Select Part —</option>
                    <?php foreach ($parts as $p): ?><option value="<?= $p['id'] ?>">[<?= esc($p['sku']) ?>] <?= esc($p['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Warehouse *</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">— Select Warehouse —</option>
                    <?php foreach ($warehouses as $wh): ?><option value="<?= $wh['id'] ?>"><?= esc($wh['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Minimum Stock Level *</label>
                <input type="number" name="min_stock_level" class="form-control" min="1" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editThresholdModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit Threshold</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="editThresholdForm" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Minimum Stock Level *</label>
                <input type="number" name="min_stock_level" id="editThresholdMin" class="form-control" min="1" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
</div></div></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initDataTable('#thresholdTable');
    document.querySelectorAll('.btn-edit-threshold').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editThresholdMin').value = btn.dataset.min;
            document.getElementById('editThresholdForm').action = `<?= base_url('thresholds/') ?>${btn.dataset.id}/update`;
            new bootstrap.Modal(document.getElementById('editThresholdModal')).show();
        });
    });
});
</script>
