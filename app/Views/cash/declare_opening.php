<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8 col-sm-12">
        <div class="card shadow-lg border-0 my-5">
            <div class="card-header bg-primary text-white py-3 text-center">
                <i class="fas fa-lock fa-3x mb-3 text-light"></i>
                <h4 class="font-weight-black mb-1 text-white">Start-of-Day Cash Declaration</h4>
                <p class="mb-0 small text-white-50">Enforced security and reconciliation mandate</p>
            </div>
            
            <div class="card-body p-4">
                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i><?= session()->getFlashdata('info') ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($declarations)): ?>
                    <p class="text-muted text-center mb-4 small">
                        Please count the physical balances in your drawer, bank balances, or GCash wallets, and input the exact opening cash levels for <strong class="text-dark"><?= date('F d, Y') ?></strong> below.
                    </p>

                    <form action="<?= base_url('admin/cash/submit-opening') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="list-group list-group-flush mb-4">
                            <?php foreach ($accounts as $a): ?>
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <h6 class="font-weight-bold text-dark mb-0"><?= esc($a['name']) ?></h6>
                                            <span class="text-muted small">
                                                <?php if ($a['type'] === 'bank'): ?>
                                                    Bank (Acct: <?= esc($a['account_number']) ?>)
                                                <?php elseif ($a['type'] === 'digital_wallet'): ?>
                                                    Wallet (Acct: <?= esc($a['account_number']) ?>)
                                                <?php else: ?>
                                                    Physical Cash register
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div style="width: 180px;">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">₱</span>
                                                <input type="number" 
                                                       name="opening_balance_<?= $a['id'] ?>" 
                                                       class="form-control form-control-lg text-end font-weight-bold" 
                                                       step="0.01" 
                                                       value="0.00" 
                                                       min="0"
                                                       required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 font-weight-bold shadow-sm">
                            <i class="fas fa-check-double me-2"></i> Submit and Unlock System
                        </button>
                    </form>
                <?php else: ?>
                    <!-- If already declared, show the logs of declarations -->
                    <div class="alert alert-success border-0 shadow-sm mb-4 text-center" role="alert">
                        <i class="fas fa-unlock-alt me-2 fs-5"></i> <strong>System Unlocked!</strong><br>
                        Opening balances for today (<?= date('Y-m-d') ?>) have already been verified.
                    </div>
                    
                    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">Today's Declared Balances</h6>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($declarations as $d): ?>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark"><?= esc($d['account_name']) ?></strong>
                                    <div class="text-muted small">Declared by <?= esc($d['declared_by_name']) ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="font-weight-bold text-success">₱<?= number_format($d['opening_balance'], 2) ?></div>
                                    <?php if ((float)$d['discrepancy'] != 0.00): ?>
                                        <span class="badge bg-danger">Diff: ₱<?= number_format($d['discrepancy'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-success border">Matched</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary w-100 mt-4">
                        Go to Dashboard <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
