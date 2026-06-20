<?php /** @var array $errors */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title text-danger"><i class="fas fa-triangle-exclamation me-2"></i>Import Failed</h1>
        <p class="page-subtitle">We found issues in your CSV file. Please resolve them and re-upload.</p>
    </div>
    <a href="<?= base_url('parts') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Parts</a>
</div>

<div class="card border-danger shadow-sm">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fas fa-file-excel me-2"></i>Validation Error Log</span>
        <span class="badge bg-white text-danger fw-bold"><?= count($errors) ?> rows affected</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;" class="text-center">CSV Row</th>
                        <th>Reasons / Corrective Action Required</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($errors as $rowNum => $rowErrors): ?>
                        <tr>
                            <td class="text-center font-monospace fw-bold text-muted"><?= $rowNum ?></td>
                            <td>
                                <ul class="list-unstyled mb-0 pl-0">
                                    <?php foreach ($rowErrors as $err): ?>
                                        <li class="text-danger py-1">
                                            <i class="fas fa-circle-exclamation me-2 small"></i><?= esc($err) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-center mt-4">
    <a href="<?= base_url('parts/template') ?>" class="btn btn-primary"><i class="fas fa-download me-2"></i>Download CSV Template</a>
</div>
