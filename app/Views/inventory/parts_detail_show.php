<?php /** @var array $detail */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Unit #<?= $detail['id'] ?></h1>
        <p class="page-subtitle mono"><?= esc($detail['unique_qr_code']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('parts-details') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><span class="card-title">Unit Information</span></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Part</td><td class="fw-500"><?= esc($detail['part_name']) ?></td></tr>
                    <?php if ($detail['variant_name']): ?><tr><td class="text-muted">Variant</td><td><?= esc($detail['variant_name']) ?></td></tr><?php endif; ?>
                    <tr><td class="text-muted">QR Code</td><td class="mono"><?= esc($detail['unique_qr_code']) ?></td></tr>
                    <tr><td class="text-muted">Warehouse</td><td><?= esc($detail['warehouse_name'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted">Location</td><td><?= esc($detail['location_name'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted">Status</td><td><span class="badge badge-<?= esc($detail['status']) ?>"><?= ucfirst($detail['status']) ?></span></td></tr>
                    <tr><td class="text-muted">Created</td><td><?= date('M d, Y H:i', strtotime($detail['created_date'])) ?></td></tr>
                    <?php if ($detail['consumed']): ?>
                    <tr><td class="text-muted">Consumed</td><td><?= date('M d, Y H:i', strtotime($detail['consumed_date'])) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <?php if (! $detail['consumed']): ?>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><span class="card-title">Mark as Consumed</span></div>
            <div class="card-body">
                <form action="<?= base_url("parts-details/{$detail['id']}/consume") ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Where/how was this used?"></textarea></div>
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Mark this unit as consumed?')">
                        <i class="fas fa-check-circle"></i> Mark Consumed
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
