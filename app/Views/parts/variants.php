<?php /** @var array $part @var array $variants */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Variants — <span class="mono"><?= esc($part['sku']) ?></span></h1>
        <p class="page-subtitle"><?= esc($part['name']) ?> &middot; <?= count($variants) ?> variants</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url("parts/{$part['id']}") ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i></a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVariantModal"><i class="fas fa-plus"></i> Add Variant</button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="variantsTable">
            <thead><tr><th>Variant Name</th><th>SKU</th><th>Barcode</th><th>Notes</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($variants as $v): ?>
            <tr>
                <td class="fw-500"><?= esc($v['variant_name']) ?></td>
                <td class="mono small"><?= esc($v['variant_sku']) ?></td>
                <td class="mono small"><?= esc($v['barcode_value'] ?? '—') ?></td>
                <td class="text-muted"><?= esc($v['additional_notes'] ?? '—') ?></td>
                <td class="text-center"><span class="badge badge-<?= $v['is_active']?'active':'inactive' ?>"><?= $v['is_active']?'Active':'Inactive' ?></span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-secondary btn-edit-v"
                            data-id="<?= $v['id'] ?>" data-name="<?= esc($v['variant_name']) ?>"
                            data-barcode="<?= esc($v['barcode_value']??'') ?>" data-notes="<?= esc($v['additional_notes']??'') ?>">
                        <i class="fas fa-pencil"></i>
                    </button>
                    <form action="<?= base_url("variants/{$v['id']}/toggle") ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-<?= $v['is_active']?'danger':'success' ?>" onclick="return confirm('Toggle?')"><i class="fas fa-<?= $v['is_active']?'ban':'check' ?>"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addVariantModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Variant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= base_url("parts/{$part['id']}/variants/store") ?>" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Variant Name *</label><input type="text" name="variant_name" class="form-control" required placeholder="e.g. Red / Large"></div>
            <div class="mb-3"><label class="form-label">Barcode</label><input type="text" name="barcode_value" class="form-control mono"></div>
            <div class="mb-3"><label class="form-label">Notes</label><textarea name="additional_notes" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editVariantModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit Variant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="editVariantForm" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Variant Name *</label><input type="text" name="variant_name" id="editVName" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Barcode</label><input type="text" name="barcode_value" id="editVBarcode" class="form-control mono"></div>
            <div class="mb-3"><label class="form-label">Notes</label><textarea name="additional_notes" id="editVNotes" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
</div></div></div>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    initDataTable('#variantsTable');
    document.querySelectorAll('.btn-edit-v').forEach(btn=>{
        btn.addEventListener('click',()=>{
            document.getElementById('editVName').value    = btn.dataset.name;
            document.getElementById('editVBarcode').value = btn.dataset.barcode;
            document.getElementById('editVNotes').value   = btn.dataset.notes;
            document.getElementById('editVariantForm').action = `<?= base_url('variants/') ?>${btn.dataset.id}/update`;
            new bootstrap.Modal(document.getElementById('editVariantModal')).show();
        });
    });
});
</script>
