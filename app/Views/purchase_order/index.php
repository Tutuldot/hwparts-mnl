<?php /** @var array $pos */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Purchase Orders</h1><p class="page-subtitle"><?= count($pos) ?> purchase orders total</p></div>
    <a href="<?= base_url('purchase-orders/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> New PO</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="poTable">
            <thead><tr><th>PO Number</th><th>Supplier</th><th>Amount</th><th>Payment</th><th>Created By</th><th>Date</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($pos as $po): ?>
            <tr>
                <td><a href="<?= base_url("purchase-orders/{$po['id']}") ?>" class="mono fw-500"><?= esc($po['po_number']) ?></a></td>
                <td><?= esc($po['supplier_name']) ?></td>
                <td>₱<?= number_format($po['amount'], 2) ?></td>
                <td><span class="badge badge-draft"><?= ucfirst(str_replace('_', ' ', $po['payment_type'])) ?></span></td>
                <td><?= esc($po['created_by_name']) ?></td>
                <td><?= date('M d, Y', strtotime($po['created_at'])) ?></td>
                <td><span class="badge badge-<?= esc($po['status']) ?>"><?= ucfirst(str_replace('_', ' ', $po['status'])) ?></span></td>
                <td class="text-center">
                    <a href="<?= base_url("purchase-orders/{$po['id']}") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    <?php if ($po['status'] === 'draft'): ?>
                    <a href="<?= base_url("purchase-orders/{$po['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pencil"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded', () => initDataTable('#poTable', {order: [[5,'desc']]}));</script>
