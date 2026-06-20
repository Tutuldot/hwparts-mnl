<?php /** @var array $reportDefs @var array $roles @var array $matrix */ ?>
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-shield-alt me-2 text-primary"></i>Report Access Matrix</h1>
        <p class="page-subtitle">Control which roles can view and export each report</p>
    </div>
    <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back to Reports
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="alert alert-info py-2 small mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Check the box to grant access. Changes take effect immediately after saving.
            <strong>Admins always see the matrix management page regardless of their assigned reports.</strong>
        </div>

        <form action="<?= base_url('reports/access-matrix/save') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-3">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:35%">Report</th>
                            <?php foreach ($roles as $role): ?>
                                <th class="text-center text-capitalize" style="width:16%"><?= esc($role) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportDefs as $key => [$label, $desc, $icon, $color]): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="<?= esc($icon) ?> me-2 text-muted"></i>
                                    <div>
                                        <div class="fw-semibold small"><?= esc($label) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem"><?= esc($desc) ?></div>
                                    </div>
                                </div>
                            </td>
                            <?php foreach ($roles as $role): ?>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center m-0">
                                        <input class="form-check-input" type="checkbox"
                                               name="access[<?= $key ?>][<?= $role ?>]" value="1"
                                               id="perm_<?= $key ?>_<?= $role ?>"
                                               <?= !empty($matrix[$key][$role]) ? 'checked' : '' ?>
                                               <?= $role === 'admin' ? 'checked disabled title="Admin always has access"' : '' ?>>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Re-check admin boxes via hidden inputs since disabled checkboxes don't POST -->
            <?php foreach ($reportDefs as $key => $_): ?>
                <input type="hidden" name="access[<?= $key ?>][admin]" value="1">
            <?php endforeach; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Access Matrix
                </button>
                <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
