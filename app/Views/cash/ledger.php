<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Cash Ledger & Movements</h1>
        <p class="page-subtitle">Track, filter, and record all cash transactions, transfers, adjustments, and advances</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordTxnModal">
        <i class="fas fa-plus-circle me-1"></i> Record Manual Cash Flow
    </button>
</div>

<!-- Filter Bar -->
<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body py-3">
        <form action="<?= base_url('admin/cash/ledger') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">-- All Accounts --</option>
                    <?php foreach ($accounts as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $filters['account_id'] == $a['id'] ? 'selected' : '' ?>>
                            <?= esc($a['name']) ?> (₱<?= number_format($a['balance'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select form-select-sm">
                    <option value="">-- All Transaction Types --</option>
                    <option value="deposit" <?= $filters['type'] === 'deposit' ? 'selected' : '' ?>>Deposit / Deposit Receipt</option>
                    <option value="withdrawal" <?= $filters['type'] === 'withdrawal' ? 'selected' : '' ?>>Withdrawal / Expense</option>
                    <option value="transfer" <?= $filters['type'] === 'transfer' ? 'selected' : '' ?>>Inter-Account Transfer</option>
                    <option value="income" <?= $filters['type'] === 'income' ? 'selected' : '' ?>>Sales Income Collection</option>
                    <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : '' ?>>Procurement Payment</option>
                    <option value="adjustment" <?= $filters['type'] === 'adjustment' ? 'selected' : '' ?>>Balance Adjustment</option>
                    <option value="advance" <?= $filters['type'] === 'advance' ? 'selected' : '' ?>>Cash Advance</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- All Approval Statuses --</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending Approval</option>
                    <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-flex g-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= base_url('admin/cash/ledger') ?>" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Ledger Listing -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 small">
                <thead class="table-light text-uppercase font-weight-bold">
                    <tr>
                        <th>Txn Number</th>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>From Account</th>
                        <th>To Account</th>
                        <th class="text-end" style="width: 130px;">Debit (DR) (+)</th>
                        <th class="text-end" style="width: 130px;">Credit (CR) (-)</th>
                        <th>Ref #</th>
                        <th>Evidence</th>
                        <th>Created By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice fa-3x mb-3 text-light"></i>
                                <p class="mb-0 font-weight-bold">No cash transactions found matching criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="font-weight-bold text-dark"><?= esc($t['transaction_number']) ?></td>
                                <td class="text-muted"><?= date('M d, Y h:i A', strtotime($t['created_at'])) ?></td>
                                <td>
                                    <?php if ($t['type'] === 'deposit'): ?>
                                        <span class="badge bg-success-light text-success"><i class="fas fa-plus-circle me-1"></i>Deposit</span>
                                    <?php elseif ($t['type'] === 'withdrawal'): ?>
                                        <span class="badge bg-danger-light text-danger"><i class="fas fa-minus-circle me-1"></i>Withdrawal</span>
                                    <?php elseif ($t['type'] === 'transfer'): ?>
                                        <span class="badge bg-primary-light text-primary"><i class="fas fa-exchange-alt me-1"></i>Transfer</span>
                                    <?php elseif ($t['type'] === 'income'): ?>
                                        <span class="badge bg-success"><i class="fas fa-cash-register me-1"></i>Sales Payment</span>
                                    <?php elseif ($t['type'] === 'expense'): ?>
                                        <span class="badge bg-danger"><i class="fas fa-shopping-cart me-1"></i>PO Payment</span>
                                    <?php elseif ($t['type'] === 'advance'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-hand-holding-usd me-1"></i>Cash Advance</span>
                                    <?php elseif ($t['type'] === 'initial_adjustment'): ?>
                                        <span class="badge bg-info text-dark"><i class="fas fa-sliders-h me-1"></i>Initial Adjustment</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white"><i class="fas fa-tools me-1"></i>Adjustment</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-dark font-weight-medium"><?= $t['from_account_name'] ? esc($t['from_account_name']) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-dark font-weight-medium"><?= $t['to_account_name'] ? esc($t['to_account_name']) : '<span class="text-muted">—</span>' ?></td>
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
                                        <a href="<?= base_url($t['evidence_path']) ?>" target="_blank" class="btn btn-xs btn-outline-info">
                                            <i class="fas fa-image me-1"></i>View Image
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">No Evidence</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc($t['created_by_name']) ?>
                                </td>
                                <td>
                                    <?php if ($t['status'] === 'approved'): ?>
                                        <span class="badge bg-success" title="Approved by <?= esc($t['approved_by_name']) ?> on <?= $t['approved_at'] ?>">Approved</span>
                                    <?php elseif ($t['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger" title="Rejected by <?= esc($t['approved_by_name']) ?>. Reason: <?= esc($t['remarks']) ?>">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                             <?php if ($t['remarks']): ?>
                                <tr class="table-light">
                                    <td colspan="11" class="text-muted small border-top-0 py-1">
                                        <i class="fas fa-comment-alt text-muted ms-2 me-1"></i>
                                        <strong>Memo / Reason:</strong> <?= esc($t['remarks']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Record Transaction Modal -->
<div class="modal fade" id="recordTxnModal" tabindex="-1" aria-labelledby="recordTxnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= base_url('admin/cash/ledger/store') ?>" method="POST" enctype="multipart/form-data" id="manualTxnForm">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="recordTxnModalLabel"><i class="fas fa-plus-circle me-1"></i> Record Manual Cash Flow</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-medium">Transaction Type *</label>
                        <select name="type" id="txnType" class="form-select" required>
                            <option value="deposit">Deposit (Receive External Income/Investment)</option>
                            <option value="withdrawal">Withdrawal (Record External General Expense)</option>
                            <option value="transfer">Transfer (Move Cash Between Internal Accounts)</option>
                            <option value="advance">Cash Advance (Release funds for employee travel/expense)</option>
                            <option value="adjustment">Balance Adjustment (Correct bank audit discrepancy)</option>
                            <option value="initial_adjustment">Initial Amount Adjustment (Force set starting balance)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6" id="fromAccountGroup">
                            <label class="form-label font-weight-medium">From Account (Source) *</label>
                            <select name="from_account_id" id="fromAccount" class="form-select">
                                <option value="">-- Select Source Account --</option>
                                <?php foreach ($accounts as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= esc($a['name']) ?> (₱<?= number_format($a['balance'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6" id="toAccountGroup">
                            <label class="form-label font-weight-medium">To Account (Destination) *</label>
                            <select name="to_account_id" id="toAccount" class="form-select">
                                <option value="">-- Select Destination Account --</option>
                                <?php foreach ($accounts as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= esc($a['name']) ?> (₱<?= number_format($a['balance'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-medium">Amount (₱) *</label>
                            <input type="number" name="amount" step="0.01" class="form-control font-weight-bold" min="0.01" required placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-medium">Reference Number (If any)</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="e.g. Bank Ref ID, GCash ID">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-medium" id="remarksLabel">Remarks / Memo</label>
                        <textarea name="remarks" id="remarksInput" class="form-control" rows="2" placeholder="Describe the purpose of this cash movement..."></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label font-weight-medium">Evidence Attachment (Picture/Slip/Receipt) *</label>
                        <input type="file" name="evidence" class="form-control" accept="image/*" required>
                        <small class="text-muted">Upload a photo of the receipt, Gcash receipt, or deposit slip.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Submit Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const txnType = document.getElementById('txnType');
        const fromGroup = document.getElementById('fromAccountGroup');
        const toGroup = document.getElementById('toAccountGroup');
        const fromSelect = document.getElementById('fromAccount');
        const toSelect = document.getElementById('toAccount');
        const remarksLabel = document.getElementById('remarksLabel');
        const remarksInput = document.getElementById('remarksInput');

        function updateFields() {
            const val = txnType.value;

            // Reset selects required
            fromSelect.removeAttribute('required');
            toSelect.removeAttribute('required');
            remarksLabel.textContent = "Remarks / Memo";
            remarksInput.removeAttribute('required');
            remarksInput.placeholder = "Describe the purpose of this cash movement...";

            if (val === 'transfer') {
                fromGroup.style.display = 'block';
                toGroup.style.display = 'block';
                fromSelect.setAttribute('required', 'required');
                toSelect.setAttribute('required', 'required');
            } else if (val === 'withdrawal' || val === 'advance') {
                fromGroup.style.display = 'block';
                toGroup.style.display = 'none';
                fromSelect.setAttribute('required', 'required');
                toSelect.value = '';
                
                if (val === 'advance') {
                    remarksLabel.innerHTML = 'Remarks / Explanation of Cash Advance * <span class="badge bg-warning text-dark">Required</span>';
                    remarksInput.setAttribute('required', 'required');
                    remarksInput.placeholder = "REQUIRED: Explain what this Cash Advance is for (who, purpose)...";
                }
            } else if (val === 'deposit') {
                fromGroup.style.display = 'none';
                toGroup.style.display = 'block';
                toSelect.setAttribute('required', 'required');
                fromSelect.value = '';
            } else if (val === 'adjustment') {
                fromGroup.style.display = 'block';
                toGroup.style.display = 'block';
                remarksLabel.innerHTML = 'Remarks / Explanation of Audit Discrepancy * <span class="badge bg-warning text-dark">Required</span>';
                remarksInput.setAttribute('required', 'required');
                remarksInput.placeholder = "REQUIRED: Explain the details of this audit alignment / discrepancy correction...";
            } else if (val === 'initial_adjustment') {
                fromGroup.style.display = 'none';
                toGroup.style.display = 'block';
                toSelect.setAttribute('required', 'required');
                fromSelect.value = '';
                remarksLabel.innerHTML = 'Remarks / Purpose of Starting Balance * <span class="badge bg-warning text-dark">Required</span>';
                remarksInput.setAttribute('required', 'required');
                remarksInput.placeholder = "REQUIRED: Explain why you are adjusting the initial starting balance of this account...";
            }
        }

        txnType.addEventListener('change', updateFields);
        updateFields(); // run initially
    });
</script>
