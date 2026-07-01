<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Cash & Bank Accounts</h1>
        <p class="page-subtitle">Manage payment accounts, banks, digital wallets, and physical cash registers</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
        <i class="fas fa-plus-circle me-1"></i> Register New Account
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-nowrap align-middle small">
                <thead class="table-light text-uppercase font-weight-bold">
                    <tr>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th>Account Number</th>
                        <th>Current Balance</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-university fa-3x mb-3 text-light"></i>
                                <p class="mb-0 font-weight-bold">No accounts registered yet.</p>
                                <p class="text-muted small">Register GCash, Bank accounts, or physical drawer cash limits.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $a): ?>
                            <tr>
                                <td class="font-weight-bold text-dark"><?= esc($a['name']) ?></td>
                                <td>
                                    <?php if ($a['type'] === 'bank'): ?>
                                        <span class="badge bg-primary"><i class="fas fa-university me-1"></i> Bank</span>
                                    <?php elseif ($a['type'] === 'digital_wallet'): ?>
                                        <span class="badge bg-info text-dark"><i class="fas fa-wallet me-1"></i> Digital Wallet</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-cash-register me-1"></i> Cash register</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-monospace text-muted"><?= $a['account_number'] ? esc($a['account_number']) : '—' ?></td>
                                <td class="font-weight-bold fs-6 <?= $a['balance'] < 0 ? 'text-danger' : 'text-success' ?>">
                                    ₱<?= number_format($a['balance'], 2) ?>
                                </td>
                                <td>
                                    <?php if ($a['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-xs btn-outline-warning text-dark edit-account-btn" 
                                            data-id="<?= $a['id'] ?>"
                                            data-name="<?= esc($a['name']) ?>"
                                            data-type="<?= $a['type'] ?>"
                                            data-number="<?= esc($a['account_number']) ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAccountModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="<?= base_url('admin/cash/accounts/' . $a['id'] . '/toggle') ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-xs <?= $a['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?> ms-1" onclick="return confirm('Are you sure you want to change status for <?= esc($a['name']) ?>?')">
                                            <?= $a['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
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

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= base_url('admin/cash/accounts/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addAccountModalLabel"><i class="fas fa-plus-circle me-1"></i> Register New Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-medium">Account Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. GCASH A, China Bank, Cash Drawer" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-medium">Account Type *</label>
                        <select name="type" id="addType" class="form-select" required>
                            <option value="bank">Bank Account</option>
                            <option value="digital_wallet">Digital Wallet / E-Wallet</option>
                            <option value="cash">Cash on Hand / Cash Box</option>
                        </select>
                    </div>
                    <div class="mb-3" id="addAccountNumberGroup">
                        <label class="form-label font-weight-medium">Account Number *</label>
                        <input type="text" name="account_number" id="addAccountNumber" class="form-control" placeholder="e.g. 123-456-7890">
                    </div>
                    <div class="mb-0">
                        <label class="form-label font-weight-medium">Initial Opening Balance (₱) *</label>
                        <input type="number" name="balance" step="0.01" class="form-control" value="0.00" min="0" required>
                        <small class="text-muted">Enter the initial balance currently in the account.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Register Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form id="editForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-warning">
                    <h5 class="modal-title font-weight-bold text-dark" id="editAccountModalLabel"><i class="fas fa-edit me-1"></i> Edit Account Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-medium">Account Name *</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-medium">Account Type *</label>
                        <select name="type" id="editType" class="form-select" required>
                            <option value="bank">Bank Account</option>
                            <option value="digital_wallet">Digital Wallet / E-Wallet</option>
                            <option value="cash">Cash on Hand / Cash Box</option>
                        </select>
                    </div>
                    <div class="mb-0" id="editAccountNumberGroup">
                        <label class="form-label font-weight-medium">Account Number *</label>
                        <input type="text" name="account_number" id="editAccountNumber" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning font-weight-bold text-dark">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle account number field based on type
        const addType = document.getElementById('addType');
        const addNumberGroup = document.getElementById('addAccountNumberGroup');
        const addNumberInput = document.getElementById('addAccountNumber');

        function toggleAddNumber() {
            if (addType.value === 'cash') {
                addNumberGroup.style.display = 'none';
                addNumberInput.removeAttribute('required');
            } else {
                addNumberGroup.style.display = 'block';
                addNumberInput.setAttribute('required', 'required');
            }
        }
        addType.addEventListener('change', toggleAddNumber);
        toggleAddNumber(); // init

        const editType = document.getElementById('editType');
        const editNumberGroup = document.getElementById('editAccountNumberGroup');
        const editNumberInput = document.getElementById('editAccountNumber');

        function toggleEditNumber() {
            if (editType.value === 'cash') {
                editNumberGroup.style.display = 'none';
                editNumberInput.removeAttribute('required');
            } else {
                editNumberGroup.style.display = 'block';
                editNumberInput.setAttribute('required', 'required');
            }
        }
        editType.addEventListener('change', toggleEditNumber);

        // Edit button handler
        const editButtons = document.querySelectorAll('.edit-account-btn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const type = this.getAttribute('data-type');
                const number = this.getAttribute('data-number');

                document.getElementById('editForm').action = `<?= base_url('admin/cash/accounts/') ?>/${id}/update`;
                document.getElementById('editName').value = name;
                document.getElementById('editType').value = type;
                document.getElementById('editAccountNumber').value = number;
                toggleEditNumber();
            });
        });
    });
</script>
