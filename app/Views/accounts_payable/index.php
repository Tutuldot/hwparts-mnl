<?php /** @var array $payables */ ?>
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fas fa-money-bill-wave text-primary me-2" style="font-size:1.25rem"></i>Accounts Payable</h1>
        <p class="page-subtitle">Manage supplier invoices and record remittances</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="payablesTable">
                <thead>
                    <tr>
                        <th>PO Reference</th>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payables as $ap): ?>
                        <tr>
                            <td><span class="mono fw-600 text-primary"><?= esc($ap['po_number']) ?></span></td>
                            <td class="fw-500"><?= esc($ap['supplier_name']) ?></td>
                            <td class="fw-600 text-dark">₱<?= number_format($ap['amount'], 2) ?></td>
                            <td class="mono small">
                                <?php 
                                $dueDateStr = date('M d, Y', strtotime($ap['due_date']));
                                $isOverdue = $ap['status'] === 'unpaid' && (strtotime($ap['due_date']) < strtotime(date('Y-m-d')));
                                ?>
                                <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                    <?= $dueDateStr ?>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge bg-danger text-uppercase ms-1" style="font-size: 0.55rem;">Overdue</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($ap['status'] === 'paid'): ?>
                                    <span class="badge badge-approved"><i class="fas fa-circle-check me-1"></i>Paid</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected"><i class="fas fa-circle-xmark me-1"></i>Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url("accounts-payable/{$ap['id']}") ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View & Pay
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initDataTable('#payablesTable', {
        order: [[3, 'asc']] // order by due date by default
    });
});
</script>
