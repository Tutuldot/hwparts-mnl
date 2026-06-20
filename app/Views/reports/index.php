<?php /** @var array $reportDefs @var bool $isAdmin */ ?>
<div class="page-header d-flex align-items-center justify-content-between mb-4">
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

<?php if (empty($reportDefs)): ?>
    <div class="alert alert-warning text-center py-5">
        <i class="fas fa-lock fa-2x mb-3 d-block"></i>
        <strong>No Reports Available</strong><br>
        You do not have access to any reports. Contact your administrator.
    </div>
<?php else: ?>
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
                    <a href="<?= base_url("reports/{$key}") ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                        <i class="fas fa-eye me-1"></i>Preview
                    </a>
                    <a href="<?= base_url("reports/{$key}/export") ?>" class="btn btn-sm flex-fill text-white"
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
