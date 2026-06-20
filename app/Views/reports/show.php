<?php /** @var string $key @var array $def @var array $rows @var array $columns @var array $filters */ ?>
<div class="page-header d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="page-title"><i class="<?= esc($def[2]) ?> me-2 text-primary"></i><?= esc($def[0]) ?></h1>
        <p class="page-subtitle"><?= esc($def[1]) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>All Reports
        </a>
        <a href="<?= base_url("reports/{$key}/export?" . http_build_query($filters)) ?>" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Export Excel
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <?php
            // Show relevant filters per report type
            $showDate     = in_array($key, ['sales_summary','sales_by_part','po_summary','customer_ledger']);
            $showAsOf     = in_array($key, ['ar_aging','ap_aging']);
            $showStatus   = in_array($key, ['sales_summary','po_summary']);
            ?>
            <?php if ($showDate): ?>
                <div class="col-auto">
                    <label class="form-label small mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>">
                </div>
            <?php endif; ?>
            <?php if ($showAsOf): ?>
                <div class="col-auto">
                    <label class="form-label small mb-1">As of Date</label>
                    <input type="date" name="as_of" class="form-control form-control-sm" value="<?= esc($filters['as_of'] ?? date('Y-m-d')) ?>">
                </div>
            <?php endif; ?>
            <?php if ($showStatus && $key === 'sales_summary'): ?>
                <div class="col-auto">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($showStatus && $key === 'po_summary'): ?>
                <div class="col-auto">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['draft','submitted','approved','rejected','received','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i>Apply
                </button>
                <a href="<?= base_url("reports/{$key}") ?>" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <span class="text-muted small"><i class="fas fa-table me-1"></i><?= count($rows) ?> records found</span>
        </div>
        <?php if (empty($rows)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                No data found for the selected filters.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 small">
                <thead class="table-dark">
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th class="fw-semibold"><?= esc($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach (array_values($row) as $i => $cell): ?>
                                <td><?= esc((string)$cell) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
