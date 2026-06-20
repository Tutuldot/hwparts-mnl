<?php /** @var array $transfers */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Inventory Transfers</h1><p class="page-subtitle"><?= count($transfers) ?> transfers total</p></div>
    <a href="<?= base_url('transfers/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> New Transfer</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="transfersTable">
            <thead><tr><th>Transfer #</th><th>From</th><th>To</th><th>Date</th><th>Created By</th><th>Status</th><th class="text-center">Action</th></tr></thead>
            <tbody>
            <?php foreach ($transfers as $t): ?>
            <tr>
                <td><a href="<?= base_url("transfers/{$t['id']}") ?>" class="mono fw-500"><?= esc($t['transfer_no']) ?></a></td>
                <td><?= esc($t['from_warehouse_name']) ?></td>
                <td><?= esc($t['to_warehouse_name']) ?></td>
                <td><?= $t['transfer_date'] ? date('M d, Y', strtotime($t['transfer_date'])) : '—' ?></td>
                <td><?= esc($t['created_by_name']) ?></td>
                <td><span class="badge badge-<?= esc($t['status']) ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
                <td class="text-center"><a href="<?= base_url("transfers/{$t['id']}") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded', () => initDataTable('#transfersTable', {order:[[3,'desc']]}));</script>
