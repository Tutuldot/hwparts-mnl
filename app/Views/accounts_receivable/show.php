<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Accounts Receivable Settlement</h1>
        <p class="page-subtitle">Manage customer payments, upload transaction proofs, and send billing notice alerts</p>
    </div>
    <a href="<?= base_url('accounts-receivable') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Receivables
    </a>
</div>

<div class="row g-4">
    <!-- Invoice Details Panel -->
    <div class="col-lg-5">
        <!-- Invoice Info Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-file-invoice-dollar fa-3x text-primary"></i>
                </div>
                <h4 class="card-title mb-1 font-weight-bold font-monospace text-dark"><?= esc($ar['invoice_number']) ?></h4>
                <p class="text-muted small mb-3">BIR STANDARD SALES INVOICE</p>
                <div class="d-flex justify-content-center">
                    <?php if ($ar['status'] === 'paid'): ?>
                        <span class="badge bg-success px-3 py-2 fs-6">Invoice Paid</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark px-3 py-2 fs-6">Unpaid Outstanding</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="list-group list-group-flush border-top small">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Invoice Amount</span>
                    <span class="font-weight-bold text-dark">₱<?= number_format($ar['amount'], 2) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Due Date</span>
                    <span class="font-weight-bold text-danger"><?= date('M d, Y', strtotime($ar['due_date'])) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Payment Terms</span>
                    <span class="font-weight-medium"><?= esc($ar['payment_terms']) ?> Days</span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Linked Sales Order</span>
                    <span class="font-weight-medium font-monospace">
                        <a href="<?= base_url('sales-orders/' . $ar['so_id']) ?>"><?= esc($ar['so_number']) ?></a>
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Generated Date</span>
                    <span class="font-weight-medium"><?= date('M d, Y h:i A', strtotime($ar['created_at'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Profile Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold">Customer Details</div>
            <div class="card-body small">
                <div class="mb-2">
                    <span class="text-muted d-block">Account Name</span>
                    <a href="<?= base_url('customers/' . $ar['customer_id']) ?>" class="font-weight-bold text-dark">
                        <?= esc($ar['customer_name']) ?>
                    </a>
                </div>
                <?php if ($ar['company_name']): ?>
                    <div class="mb-2">
                        <span class="text-muted d-block">Company Name</span>
                        <span class="font-weight-medium text-dark"><?= esc($ar['company_name']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="mb-2">
                    <span class="text-muted d-block">Billing Address</span>
                    <div class="bg-light p-2 rounded text-muted"><?= nl2br(esc($ar['billing_address'])) ?></div>
                </div>
                <div>
                    <span class="text-muted d-block">Shipping Address</span>
                    <div class="bg-light p-2 rounded text-muted"><?= nl2br(esc($ar['shipping_address'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Forms & Notices -->
    <div class="col-lg-7">
        <?php if ($ar['status'] === 'unpaid'): ?>
            <!-- Trigger Billing Notices Card -->
            <div class="card mb-4 shadow-sm border-0 border-start border-4 border-primary bg-light">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold text-primary mb-2"><i class="fas fa-paper-plane me-1"></i> Send Customer Payment Reminders</h5>
                    <p class="text-muted small mb-3">Transmit HTML email notices and SMS alerts to all registered customer contact points instantly.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <form action="<?= base_url('accounts-receivable/' . $ar['id'] . '/notice') ?>" method="POST" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="notice_type" value="1st_notice">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-bell me-1"></i> Send 1st Notice
                            </button>
                        </form>
                        <form action="<?= base_url('accounts-receivable/' . $ar['id'] . '/notice') ?>" method="POST" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="notice_type" value="2nd_notice">
                            <button type="submit" class="btn btn-sm btn-warning text-dark">
                                <i class="fas fa-exclamation-triangle me-1"></i> Send 2nd Notice
                            </button>
                        </form>
                        <form action="<?= base_url('accounts-receivable/' . $ar['id'] . '/notice') ?>" method="POST" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="notice_type" value="final_notice">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-skull-crossbones me-1"></i> Send Final Notice
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if (!empty($pendingTxn)): ?>
                <!-- Pending Cash Clearance Notification -->
                <div class="card mb-4 shadow-sm border-0 border-start border-4 border-warning bg-warning-light">
                    <div class="card-body">
                        <h5 class="font-weight-bold text-dark mb-2">
                            <i class="fas fa-clock text-warning me-2"></i> Payment Pending Clearance
                        </h5>
                        <p class="mb-3 text-dark small">
                            A settlement of <strong>₱<?= number_format($pendingTxn['amount'], 2) ?></strong> was registered for this invoice and is currently awaiting clearance by an administrator.
                        </p>
                        <div class="bg-white p-3 rounded shadow-xs mb-3 border small">
                            <div class="row g-2">
                                <div class="col-sm-6"><strong>Txn Number:</strong> <span class="text-primary font-monospace"><?= esc($pendingTxn['transaction_number']) ?></span></div>
                                <div class="col-sm-6"><strong>Reference ID:</strong> <?= esc($pendingTxn['reference_number']) ?></div>
                                <div class="col-sm-6"><strong>Payment Type:</strong> <?= esc($ar['payment_type']) ?></div>
                                <div class="col-sm-6"><strong>Submitted:</strong> <?= date('M d, Y h:i A', strtotime($pendingTxn['created_at'])) ?></div>
                            </div>
                            <?php if ($pendingTxn['evidence_path']): ?>
                                <div class="mt-3">
                                    <a href="<?= base_url($pendingTxn['evidence_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-image me-1"></i> View Submitted Proof Image
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="alert alert-warning mb-0 border-0 p-2 small">
                            <i class="fas fa-info-circle me-1"></i> Running account balances will update once this transaction is approved on the Cash Approvals dashboard.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Settle Payment Form -->
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white font-weight-bold">Register Payment Settlement</div>
                    <div class="card-body">
                        <form action="<?= base_url('accounts-receivable/' . $ar['id'] . '/pay') ?>" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label font-weight-medium small">Deposit to Cash Account *</label>
                                    <select name="cash_account_id" class="form-select form-select-sm" required>
                                        <option value="">-- Select Destination Account --</option>
                                        <?php foreach ($cashAccounts as $ca): ?>
                                            <option value="<?= $ca['id'] ?>"><?= esc($ca['name']) ?> (₱<?= number_format($ca['balance'], 2) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label font-weight-medium small">Payment Method *</label>
                                    <select name="payment_type" id="arPaymentType" class="form-select form-select-sm" required>
                                        <option value="GCASH">GCASH</option>
                                        <option value="BANK TRANSFER">BANK TRANSFER</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Cash via Transmittal">Cash via Transmittal</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label font-weight-medium small">Actual Amount Paid (₱) *</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" step="0.01" min="0.01" name="amount_paid" id="arAmountPaidInput" class="form-control" required value="<?= old('amount_paid', $ar['amount']) ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="arMaxAmountBtn">Max</button>
                                    </div>
                                </div>

                                <!-- Cheque Fields -->
                                <div class="col-12 d-none" id="arChequeDetailsRow">
                                    <div class="row g-2 bg-light p-2 rounded">
                                        <div class="col-md-6">
                                            <label class="form-label font-weight-medium small">Bank Name *</label>
                                            <input type="text" name="cheque_bank" id="arChequeBank" class="form-control form-control-sm" placeholder="e.g. BDO, BPI">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label font-weight-medium small">Check Number *</label>
                                            <input type="text" name="cheque_number" id="arChequeNumber" class="form-control form-control-sm" placeholder="e.g. 12345678">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label font-weight-medium small">Payment Transaction Reference / Control ID *</label>
                                    <input type="text" name="payment_reference" class="form-control form-control-sm" required placeholder="e.g. GCash Ref No, Bank Inst. Code" value="<?= old('payment_reference') ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label font-weight-medium small">Upload Proof of Payment (Image) *</label>
                                    <input type="file" name="proof_of_payment" class="form-control form-control-sm" accept="image/*" required>
                                    <small class="text-muted d-block mt-1">Image scan or screenshot confirmation. Required for BIR transaction auditing.</small>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-success w-100 font-weight-bold py-2"><i class="fas fa-check-circle me-1"></i> Confirm Settlement & Settle AR</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Settled Info Details Card -->
            <div class="card mb-4 shadow-sm border-0 border-start border-4 border-success">
                <div class="card-header bg-white font-weight-bold text-success">Settlement Payment Details</div>
                <div class="card-body small">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted d-block">Settlement Date</span>
                            <span class="font-weight-bold text-dark"><?= date('M d, Y h:i A', strtotime($ar['paid_at'])) ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Amount Settled</span>
                            <span class="font-weight-bold text-success fs-5">₱<?= number_format($ar['amount_paid'] ?: $ar['amount'], 2) ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Payment Method</span>
                            <span class="font-weight-bold text-dark"><?= esc($ar['payment_type']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Control Reference</span>
                            <span class="font-weight-bold font-monospace text-dark"><?= esc($ar['payment_reference']) ?></span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block mb-2">Attached Proof of Payment</span>
                            <?php if (!empty($ar['proof_of_payment'])): ?>
                                <div class="p-2 border rounded bg-light text-center">
                                    <img src="<?= base_url($ar['proof_of_payment']) ?>" alt="Proof of Payment" class="img-fluid rounded" style="max-height: 400px; display: block; margin: 0 auto;">
                                    <a href="<?= base_url($ar['proof_of_payment']) ?>" target="_blank" class="btn btn-xs btn-outline-primary mt-2">
                                        <i class="fas fa-search-plus me-1"></i> View Full Image
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No proof of payment file attached.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Notice & Delivery Logs Card -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold">Notice & Delivery History Logs</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Notice Type</th>
                                <th>Medium</th>
                                <th>Recipient Contact</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No payment notice reminders sent yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase text-light small">
                                                <?= str_replace('_', ' ', $log['notice_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $log['type'] === 'email' ? 'bg-info' : 'bg-primary' ?> text-uppercase small">
                                                <?= esc($log['type']) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($log['recipient']) ?></td>
                                        <td>
                                            <?php if ($log['status'] === 'sent'): ?>
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Sent</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Max Amount Button Handler
    const maxAmountBtn = document.getElementById('arMaxAmountBtn');
    const amountPaidInput = document.getElementById('arAmountPaidInput');
    if (maxAmountBtn && amountPaidInput) {
        maxAmountBtn.addEventListener('click', function() {
            amountPaidInput.value = '<?= $ar['amount'] ?>';
        });
    }

    // Toggle Cheque inputs row based on selected payment type
    const arPaymentType = document.getElementById('arPaymentType');
    const arChequeDetailsRow = document.getElementById('arChequeDetailsRow');
    const arChequeBank = document.getElementById('arChequeBank');
    const arChequeNumber = document.getElementById('arChequeNumber');

    if (arPaymentType && arChequeDetailsRow) {
        arPaymentType.addEventListener('change', function() {
            if (this.value === 'Cheque') {
                arChequeDetailsRow.classList.remove('d-none');
                arChequeBank.setAttribute('required', 'required');
                arChequeNumber.setAttribute('required', 'required');
            } else {
                arChequeDetailsRow.classList.add('d-none');
                arChequeBank.removeAttribute('required');
                arChequeNumber.removeAttribute('required');
                arChequeBank.value = '';
                arChequeNumber.value = '';
            }
        });
    }
</script>
