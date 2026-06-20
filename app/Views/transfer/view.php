<?php /** @var array $transfer @var array $lines @var string $role @var array $toLocations */ ?>
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title"><?= esc($transfer['transfer_no']) ?></h1>
        <p class="page-subtitle">
            <strong><?= esc($transfer['from_warehouse_name']) ?></strong>
            <i class="fas fa-arrow-right mx-1 text-muted"></i>
            <strong><?= esc($transfer['to_warehouse_name']) ?></strong>
            &middot; Created by <?= esc($transfer['created_by_name']) ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <span class="badge badge-<?= esc($transfer['status']) ?> fs-6"><?= ucfirst(str_replace('_',' ',$transfer['status'])) ?></span>
        <a href="<?= base_url('transfers') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>

        <?php if ($transfer['status'] === 'draft'): ?>
            <form action="<?= base_url("transfers/{$transfer['id']}/submit") ?>" method="POST" class="d-inline">
                <?= csrf_field() ?><button class="btn btn-primary btn-sm" onclick="return confirm('Submit for approval?')"><i class="fas fa-paper-plane"></i> Submit</button>
            </form>
            <form action="<?= base_url("transfers/{$transfer['id']}/cancel") ?>" method="POST" class="d-inline">
                <?= csrf_field() ?><button class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel?')"><i class="fas fa-ban"></i> Cancel</button>
            </form>
        <?php elseif ($transfer['status'] === 'submitted' && in_array($role, ['admin','approver'])): ?>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal"><i class="fas fa-check"></i> Approve</button>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times"></i> Reject</button>
        <?php elseif ($transfer['status'] === 'approved'): ?>
            <form action="<?= base_url("transfers/{$transfer['id']}/transit") ?>" method="POST" class="d-inline">
                <?= csrf_field() ?><button class="btn btn-warning btn-sm" onclick="return confirm('Mark as In Transit?')"><i class="fas fa-truck"></i> Mark In Transit</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span class="card-title">Transfer Lines</span></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Part</th><th>Type</th><th class="text-center">Requested</th><th class="text-center">Transferred</th><th>Status</th><th>Dest. Location</th>
                    <?php if (in_array($transfer['status'], ['in_transit','partially_transferred'])): ?><th class="text-center">Action</th><?php endif; ?>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($lines as $l): ?>
                    <tr class="<?= $l['status'] === 'transferred' ? 'table-success' : '' ?>">
                        <td>
                            <?= esc($l['part_name']) ?>
                            <?php if ($l['variant_name']): ?><br><small class="text-muted"><?= esc($l['variant_name']) ?></small><?php endif; ?>
                            <?php if ($l['unique_qr_code']): ?><br><small class="mono text-muted"><?= esc($l['unique_qr_code']) ?></small><?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= $l['part_type'] ?>"><?= $l['part_type'] === 'non_quantity' ? 'Non-Qty' : 'Qty' ?></span></td>
                        <td class="text-center"><?= $l['part_type'] === 'non_quantity' ? '1 unit' : $l['quantity_requested'] ?></td>
                        <td class="text-center"><?= $l['part_type'] === 'non_quantity' ? ($l['status'] === 'transferred' ? '1' : '0') : $l['quantity_transferred'] ?></td>
                        <td><span class="badge badge-<?= esc($l['status']) ?>"><?= ucfirst(str_replace('_',' ',$l['status'])) ?></span></td>
                        <td class="text-muted small"><?= esc($l['to_location_name'] ?? '—') ?></td>
                        <?php if (in_array($transfer['status'], ['in_transit','partially_transferred']) && $l['status'] !== 'transferred'): ?>
                        <td class="text-center">
                            <button class="btn btn-xs btn-outline-success" onclick="openDeliverModal(<?= $l['id'] ?>, '<?= esc($l['part_name']) ?>', '<?= $l['part_type'] ?>',
                                <?= $l['quantity_requested'] - $l['quantity_transferred'] ?>)">
                                <i class="fas fa-check"></i> Deliver
                            </button>
                        </td>
                        <?php elseif (in_array($transfer['status'], ['in_transit','partially_transferred'])): ?>
                        <td></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><span class="card-title">Transfer Info</span></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Transfer #</td><td class="mono fw-500"><?= esc($transfer['transfer_no']) ?></td></tr>
                    <tr><td class="text-muted">From</td><td><?= esc($transfer['from_warehouse_name']) ?></td></tr>
                    <tr><td class="text-muted">To</td><td><?= esc($transfer['to_warehouse_name']) ?></td></tr>
                    <tr><td class="text-muted">Date</td><td><?= $transfer['transfer_date'] ? date('M d, Y', strtotime($transfer['transfer_date'])) : '—' ?></td></tr>
                    <tr><td class="text-muted">Status</td><td><span class="badge badge-<?= esc($transfer['status']) ?>"><?= ucfirst(str_replace('_',' ',$transfer['status'])) ?></span></td></tr>
                    <?php if ($transfer['submitted_by_name']): ?><tr><td class="text-muted">Submitted</td><td><?= esc($transfer['submitted_by_name']) ?></td></tr><?php endif; ?>
                    <?php if ($transfer['approved_by_name']): ?><tr><td class="text-muted">Approved</td><td><?= esc($transfer['approved_by_name']) ?></td></tr><?php endif; ?>
                    <?php if ($transfer['remarks']): ?><tr><td class="text-muted">Remarks</td><td><?= esc($transfer['remarks']) ?></td></tr><?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Deliver Modal -->
<div class="modal fade" id="deliverModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="deliverModalTitle">Record Delivery</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="deliverForm" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div id="qtyField" class="mb-3">
                    <label class="form-label">Quantity to Deliver *</label>
                    <input type="number" name="qty_deliver" id="qtyDeliver" class="form-control" min="1" value="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Destination Sub-Location</label>
                    <select name="to_warehouse_location_id" class="form-select">
                        <option value="">— None —</option>
                        <?php foreach ($toLocations as $loc): ?>
                            <option value="<?= $loc['id'] ?>">[<?= esc($loc['code']) ?>] <?= esc($loc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Confirm Delivery</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Approve Transfer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="<?= base_url("transfers/{$transfer['id']}/approve") ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body"><p>Approve <strong><?= esc($transfer['transfer_no']) ?></strong>?</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Approve</button></div>
        </form>
    </div></div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Reject Transfer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="<?= base_url("transfers/{$transfer['id']}/reject") ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <label class="form-label">Reason *</label>
                <textarea name="reason" class="form-control" rows="3" required></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject</button></div>
        </form>
    </div></div>
</div>

<script>
function openDeliverModal(lineId, partName, partType, remaining) {
    document.getElementById('deliverModalTitle').textContent = 'Deliver: ' + partName;
    document.getElementById('deliverForm').action = `<?= base_url("transfers/{$transfer['id']}/lines/") ?>${lineId}/deliver`;
    const qtyField = document.getElementById('qtyField');
    if (partType === 'non_quantity') {
        qtyField.style.display = 'none';
    } else {
        qtyField.style.display = '';
        const inp = document.getElementById('qtyDeliver');
        inp.max = remaining; inp.value = remaining;
    }
    new bootstrap.Modal(document.getElementById('deliverModal')).show();
}
</script>
