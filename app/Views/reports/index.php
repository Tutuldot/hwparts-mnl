<?php /** @var array $reportDefs @var bool $isAdmin @var array $filters */ ?>
<div class="page-header d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="page-title"><i class="fas fa-chart-bar me-2 text-primary"></i>Reports Center</h1>
        <p class="page-subtitle">Download business reports as Excel files</p>
    </div>
    <?php if ($isAdmin): ?>
        <a href="<?= base_url('reports/access-matrix') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-shield-alt me-1"></i> Access Matrix
        </a>
    <?php endif; ?>
</div>

<!-- Global Date Range Filter Card -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body py-3">
        <form method="GET" action="<?= base_url('reports') ?>" id="reportFilterForm" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label font-weight-bold small mb-1">
                    <i class="fas fa-calendar-alt text-primary me-1"></i>Start Date (Date From)
                </label>
                <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label font-weight-bold small mb-1">
                    <i class="fas fa-calendar-check text-primary me-1"></i>End Date (Date To)
                </label>
                <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-6 d-flex flex-wrap align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                    <i class="fas fa-filter me-1"></i>Apply Filter
                </button>
                <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
                    <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Clear Filter
                    </a>
                <?php endif; ?>

                <div class="btn-group btn-group-sm ms-auto" role="group" aria-label="Date Presets">
                    <button type="button" class="btn btn-outline-secondary" onclick="setPreset('today')">Today</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setPreset('this_month')">This Month</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setPreset('last_30')">Last 30 Days</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setPreset('this_year')">This Year</button>
                </div>
            </div>
        </form>
        <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
            <div class="mt-2 pt-2 border-top d-flex align-items-center text-primary small">
                <i class="fas fa-info-circle me-2"></i>
                <span>Active Date Filter: <strong><?= esc($filters['date_from'] ?: 'Beginning') ?></strong> to <strong><?= esc($filters['date_to'] ?: 'Today') ?></strong></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($reportDefs)): ?>
    <div class="alert alert-warning text-center py-5">
        <i class="fas fa-lock fa-2x mb-3 d-block"></i>
        <strong>No Reports Available</strong><br>
        You do not have access to any reports. Contact your administrator.
    </div>
<?php else: ?>
<?php $queryParams = !empty($filters) ? '?' . http_build_query($filters) : ''; ?>
<div class="row g-3">
    <?php
    $colorMap = [
        'primary'   => ['bg' => '#1e3a5f', 'badge' => 'primary'],
        'success'   => ['bg' => '#166534', 'badge' => 'success'],
        'warning'   => ['bg' => '#92400e', 'badge' => 'warning'],
        'danger'    => ['bg' => '#991b1b', 'badge' => 'danger'],
        'info'      => ['bg' => '#155e75', 'badge' => 'info'],
        'secondary' => ['bg' => '#374151', 'badge' => 'secondary'],
        'dark'      => ['bg' => '#111827', 'badge' => 'dark'],
    ];
    foreach ($reportDefs as $key => [$label, $desc, $icon, $color]):
        $c = $colorMap[$color] ?? $colorMap['primary'];
    ?>
    <div class="col-lg-4 col-md-6">
        <div class="card h-100 shadow-sm border-0" style="border-left: 4px solid <?= $c['bg'] ?> !important;">
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                         style="width:46px;height:46px;background:<?= $c['bg'] ?>15;">
                        <i class="<?= $icon ?> fs-5" style="color:<?= $c['bg'] ?>"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1"><?= esc($label) ?></h6>
                        <p class="text-muted small mb-0"><?= esc($desc) ?></p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url("reports/{$key}") . $queryParams ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                        <i class="fas fa-eye me-1"></i>Preview
                    </a>
                    <a href="<?= base_url("reports/{$key}/export") . $queryParams ?>" class="btn btn-sm flex-fill text-white"
                       style="background:<?= $c['bg'] ?>">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function setPreset(type) {
    const today = new Date();
    const formatDate = (d) => d.toISOString().split('T')[0];
    let fromDate = '', toDate = formatDate(today);

    if (type === 'today') {
        fromDate = toDate;
    } else if (type === 'this_month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        fromDate = formatDate(firstDay);
    } else if (type === 'last_30') {
        const prior30 = new Date();
        prior30.setDate(today.getDate() - 30);
        fromDate = formatDate(prior30);
    } else if (type === 'this_year') {
        const firstDayYear = new Date(today.getFullYear(), 0, 1);
        fromDate = formatDate(firstDayYear);
    }

    document.getElementById('date_from').value = fromDate;
    document.getElementById('date_to').value = toDate;
    document.getElementById('reportFilterForm').submit();
}
</script>

