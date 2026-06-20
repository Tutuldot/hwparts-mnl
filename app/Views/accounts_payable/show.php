<?php /** @var array $payable */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title">Accounts Payable Settlement</h1>
        <p class="page-subtitle">Settle invoice for PO <span class="mono"><?= esc($payable['po_number']) ?></span></p>
    </div>
    <a href="<?= base_url('accounts-payable') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="row g-3">
    <!-- Left Column: Payable Details -->
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">Payable Details</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small">Purchase Order</div>
                        <div class="fw-600"><a href="<?= base_url('purchase-orders/' . $payable['po_id']) ?>" class="mono"><?= esc($payable['po_number']) ?></a></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Supplier</div>
                        <div class="fw-600"><a href="<?= base_url('suppliers/' . $payable['supplier_id']) ?>"><?= esc($payable['supplier_name']) ?></a></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Amount Due</div>
                        <div class="fs-4 fw-700 text-primary">₱<?= number_format($payable['amount'], 2) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Due Date</div>
                        <?php 
                        $dueDateStr = date('M d, Y', strtotime($payable['due_date']));
                        $isOverdue = $payable['status'] === 'unpaid' && (strtotime($payable['due_date']) < strtotime(date('Y-m-d')));
                        ?>
                        <div class="fw-600 <?= $isOverdue ? 'text-danger' : '' ?>">
                            <?= $dueDateStr ?>
                            <?php if ($isOverdue): ?>
                                <span class="badge bg-danger text-uppercase ms-1" style="font-size: 0.55rem;">Overdue</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Payment Status</div>
                        <div class="mt-1">
                            <?php if ($payable['status'] === 'paid'): ?>
                                <span class="badge bg-success text-white py-1.5 px-3"><i class="fas fa-circle-check me-1"></i>Settled / Paid</span>
                            <?php else: ?>
                                <span class="badge bg-danger text-white py-1.5 px-3"><i class="fas fa-circle-xmark me-1"></i>Awaiting Settlement</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($payable['status'] === 'paid'): ?>
                        <hr class="my-2">
                        <div class="col-sm-6">
                            <div class="text-muted small">Settled Date</div>
                            <div class="fw-600 small"><?= date('M d, Y H:i A', strtotime($payable['paid_at'])) ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Settled By</div>
                            <div class="fw-600 small"><?= esc($payable['paid_by_name'] ?? 'System') ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Settle Form or Payment Proof -->
    <div class="col-lg-6">
        <?php if ($payable['status'] === 'unpaid'): ?>
            <!-- Settle Form -->
            <div class="card">
                <div class="card-header bg-primary text-white"><span class="card-title text-white"><i class="fas fa-wallet me-2"></i>Record Remittance</span></div>
                <div class="card-body">
                    <form action="<?= base_url("accounts-payable/{$payable['id']}/pay") ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Payment Form / Channel *</label>
                            <select name="payment_type" id="paymentTypeSelect" class="form-select" required>
                                <option value="GCASH">GCASH</option>
                                <option value="BANK TRANSFER">BANK TRANSFER</option>
                                <option value="Cheque">Cheque (Check)</option>
                                <option value="Cash via Transmittal">Cash via Transmittal</option>
                            </select>
                        </div>

                        <!-- Cheque Details (Bank & Check Number) -->
                        <div class="row g-2 mb-3 d-none" id="chequeFields">
                            <div class="col-sm-6">
                                <label class="form-label font-weight-bold">Bank Name *</label>
                                <input type="text" name="cheque_bank" id="chequeBank" class="form-control form-control-sm" placeholder="e.g. BDO, Metrobank">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label font-weight-bold">Check Number *</label>
                                <input type="text" name="cheque_number" id="chequeNum" class="form-control form-control-sm" placeholder="e.g. 123456789">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Reference / Transaction Number *</label>
                            <input type="text" name="payment_reference" class="form-control" required placeholder="e.g. Ref# 987654321">
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Proof of Payment File (Required) *</label>
                            <input type="file" name="proof_of_payment" class="form-control" accept="image/*,.pdf" required>
                            <small class="text-muted d-block mt-1">Upload a clear photo/receipt (PDF/Image) representing the remittance transaction.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check-circle me-1"></i>Settle Accounts Payable</button>
                    </form>
                </div>
            </div>
            
            <script>
            document.getElementById('paymentTypeSelect').addEventListener('change', function() {
                const chequeDiv = document.getElementById('chequeFields');
                const chkBank = document.getElementById('chequeBank');
                const chkNum = document.getElementById('chequeNum');
                
                if (this.value === 'Cheque') {
                    chequeDiv.classList.remove('d-none');
                    chkBank.setAttribute('required', 'required');
                    chkNum.setAttribute('required', 'required');
                } else {
                    chequeDiv.classList.add('d-none');
                    chkBank.removeAttribute('required');
                    chkNum.removeAttribute('required');
                    chkBank.value = '';
                    chkNum.value = '';
                }
            });
            </script>

        <?php else: ?>
            <!-- Settled Details & Proof display -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white"><span class="card-title text-white"><i class="fas fa-check-circle me-2"></i>Remittance Details</span></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Payment Channel</div>
                            <div class="fw-600"><?= esc($payable['payment_type']) ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Reference No.</div>
                            <div class="fw-600 mono"><?= esc($payable['payment_reference']) ?></div>
                        </div>
                        <?php if ($payable['cheque_details']): ?>
                            <div class="col-12">
                                <div class="text-muted small">Cheque Info</div>
                                <div class="fw-600 text-info"><i class="fas fa-money-check me-1"></i><?= esc($payable['cheque_details']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span class="card-title"><i class="fas fa-file-invoice-dollar me-2"></i>Proof of Payment</span></div>
                <div class="card-body text-center p-2 bg-light border rounded">
                    <?php if (str_ends_with(strtolower($payable['proof_of_payment']), '.pdf')): ?>
                        <div class="py-4">
                            <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i>
                            <p class="small text-muted mb-2">PDF Document Payment Proof</p>
                            <a href="<?= base_url($payable['proof_of_payment']) ?>" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-external-link me-1"></i>Open PDF Receipt
                            </a>
                        </div>
                    <?php else: ?>
                        <img src="<?= base_url($payable['proof_of_payment']) ?>" alt="Proof of Payment" class="img-fluid rounded border" style="max-height: 380px; cursor: zoom-in;" onclick="window.open(this.src, '_blank')">
                        <small class="text-muted d-block mt-2">Click image to enlarge/open in a new window.</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
