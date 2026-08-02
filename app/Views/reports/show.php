<?php
/** @var string $key @var array $def @var array $rows @var array $columns @var array $filters @var array $warehouses @var array $categories */
$dateParams  = array_filter(['date_from' => $filters['date_from'] ?? '', 'date_to' => $filters['date_to'] ?? '']);
$dateQs      = $dateParams ? '?' . http_build_query($dateParams) : '';
$allFiltersQs = $filters   ? '?' . http_build_query($filters)    : '';
?>
<div class="page-header d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="page-title"><i class="<?= esc($def[2]) ?> me-2 text-primary"></i><?= esc($def[0]) ?></h1>
        <p class="page-subtitle"><?= esc($def[1]) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('reports') . $dateQs ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>All Reports
        </a>
        <a href="<?= base_url("reports/{$key}/export") . $allFiltersQs ?>" class="btn btn-success btn-sm font-weight-bold">
            <i class="fas fa-file-excel me-1"></i>Export Excel
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <!-- Date Range Filters (Available for Extraction across all reports) -->
            <div class="col-md-3">
                <label class="form-label font-weight-bold small mb-1"><i class="fas fa-calendar-alt text-primary me-1"></i>Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label font-weight-bold small mb-1"><i class="fas fa-calendar-check text-primary me-1"></i>Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>

            <?php if (in_array($key, ['ar_aging', 'ap_aging'])): ?>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold small mb-1"><i class="fas fa-clock text-warning me-1"></i>As of Date</label>
                    <input type="date" name="as_of" class="form-control form-control-sm" value="<?= esc($filters['as_of'] ?? date('Y-m-d')) ?>">
                </div>
            <?php endif; ?>

            <?php if ($key === 'sales_summary'): ?>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold small mb-1">SO Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($key === 'po_summary'): ?>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold small mb-1">PO Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <?php foreach (['draft','submitted','approved','rejected','received','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($key === 'cogs_report'): ?>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold small mb-1"><i class="fas fa-check-circle text-success me-1"></i>SO Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="approved" <?= ($filters['status'] ?? 'approved') === 'approved' ? 'selected' : '' ?>>Approved Only</option>
                        <option value="all"      <?= ($filters['status'] ?? '') === 'all'      ? 'selected' : '' ?>>All Statuses</option>
                        <option value="draft"    <?= ($filters['status'] ?? '') === 'draft'    ? 'selected' : '' ?>>Draft</option>
                        <option value="cancelled"<?= ($filters['status'] ?? '') === 'cancelled'? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold small mb-1"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Cost Flag</label>
                    <select name="cost_flag" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="MISSING" <?= ($filters['cost_flag'] ?? '') === 'MISSING' ? 'selected' : '' ?>>Missing Cost Only</option>
                        <option value="OK"      <?= ($filters['cost_flag'] ?? '') === 'OK'      ? 'selected' : '' ?>>OK Only</option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($key === 'inventory_stock' && !empty($warehouses)): ?>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold small mb-1">Warehouse</label>
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">All Warehouses</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>" <?= ((string)($filters['warehouse_id'] ?? '') === (string)$wh['id']) ? 'selected' : '' ?>>
                                <?= esc($wh['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($key === 'price_list' && !empty($categories)): ?>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold small mb-1">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ((string)($filters['category_id'] ?? '') === (string)$cat['id']) ? 'selected' : '' ?>>
                                <?= esc($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                    <i class="fas fa-filter me-1"></i>Filter &amp; Extract
                </button>
                <a href="<?= base_url("reports/{$key}") . $dateQs ?>" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
        </form>
        <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
            <div class="mt-2 pt-2 border-top d-flex align-items-center text-primary small">
                <i class="fas fa-calendar-check me-2"></i>
                <span>Date Range: <strong><?= esc($filters['date_from'] ?: 'Beginning') ?></strong> to <strong><?= esc($filters['date_to'] ?: 'Today') ?></strong></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Data Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-light">
            <span class="text-dark fw-bold small"><i class="fas fa-table me-1 text-primary"></i><?= count($rows) ?> records extracted</span>
            <a href="<?= base_url("reports/{$key}/export") . $allFiltersQs ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-download me-1"></i>Download Excel (.xlsx)
            </a>
        </div>
        <?php if (empty($rows)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary opacity-50"></i>
                <h6 class="fw-bold">No Records Extracted</h6>
                <p class="small mb-0">No data found matching the selected date range and filters.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 small">
                <thead class="table-dark">
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th class="fw-semibold text-nowrap"><?= esc($col) ?></th>
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
