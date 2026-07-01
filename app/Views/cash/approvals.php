<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Pending Cash Approvals</h1>
        <p class="page-subtitle">Review, approve, or reject pending cash transfers, deposits, AR/AP payments, and advances</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 small" id="pendingApprovalsTable">
                <thead class="table-light text-uppercase font-weight-bold">
                    <tr>
                        <th style="width: 150px;">Txn Number</th>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th class="text-end" style="width: 130px;">Debit (DR) (+)</th>
                        <th class="text-end" style="width: 130px;">Credit (CR) (-)</th>
                        <th>Reference #</th>
                        <th>Evidence Slip</th>
                        <th>Submitted By</th>
                        <th class="text-end" style="min-width: 250px;">Action Controls</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success-light"></i>
                                <p class="mb-0 font-weight-bold">No pending cash transaction approvals.</p>
                                <p class="text-muted small">All cash movements are reconciled and cleared.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="font-weight-bold text-dark"><?= esc($t['transaction_number']) ?></td>
                                <td class="text-muted"><?= date('M d, Y h:i A', strtotime($t['created_at'])) ?></td>
                                <td>
                                    <?php if ($t['type'] === 'deposit'): ?>
                                        <span class="badge bg-success-light text-success">Deposit</span>
                                    <?php elseif ($t['type'] === 'withdrawal'): ?>
                                        <span class="badge bg-danger-light text-danger">Withdrawal</span>
                                    <?php elseif ($t['type'] === 'transfer'): ?>
                                        <span class="badge bg-primary-light text-primary">Transfer</span>
                                    <?php elseif ($t['type'] === 'income'): ?>
                                        <span class="badge bg-success">Sales Payment</span>
                                    <?php elseif ($t['type'] === 'expense'): ?>
                                        <span class="badge bg-danger">PO Payment</span>
                                    <?php elseif ($t['type'] === 'advance'): ?>
                                        <span class="badge bg-warning text-dark">Advance</span>
                                    <?php elseif ($t['type'] === 'initial_adjustment'): ?>
                                        <span class="badge bg-info text-dark">Initial Adjustment</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white">Adjustment</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($t['type'] === 'transfer'): ?>
                                        Move from <strong class="text-dark"><?= esc($t['from_account_name']) ?></strong> to <strong class="text-dark"><?= esc($t['to_account_name']) ?></strong>
                                    <?php elseif ($t['from_account_id'] && $t['to_account_id']): ?>
                                        From <strong class="text-dark"><?= esc($t['from_account_name']) ?></strong> to <strong class="text-dark"><?= esc($t['to_account_name']) ?></strong>
                                    <?php elseif ($t['from_account_id']): ?>
                                        Draw from <strong class="text-dark"><?= esc($t['from_account_name']) ?></strong>
                                    <?php elseif ($t['to_account_id']): ?>
                                        Deposit to <strong class="text-dark"><?= esc($t['to_account_name']) ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end font-weight-bold text-success">
                                    <?php if (in_array($t['type'], ['deposit', 'income', 'initial_adjustment']) || ($t['type'] === 'transfer' && $t['to_account_id']) || ($t['type'] === 'adjustment' && $t['to_account_id'])): ?>
                                        ₱<?= number_format($t['amount'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end font-weight-bold text-danger">
                                    <?php if (in_array($t['type'], ['withdrawal', 'expense', 'advance']) || ($t['type'] === 'transfer' && $t['from_account_id']) || ($t['type'] === 'adjustment' && $t['from_account_id'] && !$t['to_account_id'])): ?>
                                        ₱<?= number_format($t['amount'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-monospace text-muted"><?= $t['reference_number'] ? esc($t['reference_number']) : '—' ?></td>
                                <td>
                                    <?php if ($t['evidence_path']): ?>
                                        <a href="<?= base_url($t['evidence_path']) ?>" target="_blank" class="btn btn-xs btn-outline-info" title="View Evidence Slip">
                                            <i class="fas fa-image"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-danger small" title="No Evidence!"><i class="fas fa-times-circle text-danger"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($t['created_by_name']) ?></td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <!-- Approve form -->
                                        <form action="<?= base_url('admin/cash/approvals/' . $t['id'] . '/approve') ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn btn-xs btn-success text-uppercase font-weight-bold px-3 approve-btn">
                                                <i class="fas fa-check-circle me-1"></i> Approve
                                            </button>
                                        </form>

                                        <!-- Reject control triggers inline expansion -->
                                        <button class="btn btn-xs btn-outline-danger text-uppercase font-weight-bold px-2 reject-toggle-btn" data-txn="<?= $t['id'] ?>">
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Inline Remarks / Reason -->
                             <?php if ($t['remarks']): ?>
                                <tr class="table-light">
                                    <td colspan="10" class="text-muted small border-top-0 py-1">
                                        <i class="fas fa-comment-alt text-muted ms-2 me-1"></i>
                                        <strong>Memo / Explanation:</strong> <?= esc($t['remarks']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <!-- Expandable Rejection Details -->
                             <tr id="reject-row-<?= $t['id'] ?>" class="table-danger" style="display:none;">
                                <td colspan="10" class="py-2 border-top-0">
                                    <form action="<?= base_url('admin/cash/approvals/' . $t['id'] . '/reject') ?>" method="POST" class="d-flex align-items-center justify-content-end gap-2 px-3">
                                        <?= csrf_field() ?>
                                        <label class="form-label mb-0 small text-danger font-weight-bold">Reason for Rejection *</label>
                                        <input type="text" name="reject_remarks" class="form-control form-control-sm" style="max-width: 400px;" placeholder="e.g. Image not clear, wrong account selected, missing ref ID..." required>
                                        <button type="submit" class="btn btn-sm btn-danger text-uppercase font-weight-bold py-1">Confirm Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButtons = document.querySelectorAll('.reject-toggle-btn');
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const txnId = this.getAttribute('data-txn');
                const row = document.getElementById('reject-row-' + txnId);
                
                if (row.style.display === 'none') {
                    row.style.display = 'table-row';
                    this.textContent = 'Cancel';
                    this.classList.replace('btn-outline-danger', 'btn-secondary');
                } else {
                    row.style.display = 'none';
                    this.textContent = 'Reject';
                    this.classList.replace('btn-secondary', 'btn-outline-danger');
                }
            });
        });

        // SweetAlert2 confirmation for Approve button
        const approveButtons = document.querySelectorAll('.approve-btn');
        approveButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Confirm Approval',
                    text: 'Confirm cash movement approval. This will update the ledger balances and mark invoices as Paid if applicable.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
