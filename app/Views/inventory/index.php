<?php /** @var array $headers */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Inventory</h1><p class="page-subtitle"><?= count($headers) ?> inventory entries</p></div>
    <a href="<?= base_url('inventory/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Inventory</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="inventoryTable">
            <thead><tr><th>Reference</th><th>Source</th><th>Warehouse</th><th>Created By</th><th>Date</th><th class="text-center">Action</th></tr></thead>
            <tbody>
            <?php foreach ($headers as $h): ?>
            <tr>
                <td><a href="<?= base_url("inventory/{$h['id']}") ?>" class="mono fw-500"><?= esc($h['reference_no']) ?></a></td>
                <td><span class="badge badge-<?= $h['source'] === 'manual' ? 'draft' : ($h['source'] === 'purchase_order' ? 'approved' : 'submitted') ?>"><?= ucfirst(str_replace('_',' ',$h['source'])) ?></span></td>
                <td><?= esc($h['warehouse_name'] ?? '—') ?></td>
                <td><?= esc($h['created_by_name']) ?></td>
                <td><?= date('M d, Y H:i', strtotime($h['created_at'])) ?></td>
                <td class="text-center"><a href="<?= base_url("inventory/{$h['id']}") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>initDataTable('#inventoryTable',{order:[[4,'desc']]}));</script>
