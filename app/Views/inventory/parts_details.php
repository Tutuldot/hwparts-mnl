<?php /** @var array $details */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Tracked Units</h1><p class="page-subtitle"><?= number_format(count($details)) ?> individual units tracked</p></div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="detailsTable">
            <thead><tr><th>QR Code</th><th>Part</th><th>Warehouse</th><th>Location</th><th>Status</th><th>Date</th><th class="text-center">Action</th></tr></thead>
            <tbody>
            <?php foreach ($details as $d): ?>
            <tr>
                <td><span class="mono small"><?= esc($d['unique_qr_code']) ?></span></td>
                <td><?= esc($d['part_name']) ?><?php if ($d['variant_name']): ?><br><small class="text-muted"><?= esc($d['variant_name']) ?></small><?php endif; ?></td>
                <td><?= esc($d['warehouse_name'] ?? '—') ?></td>
                <td><?= esc($d['location_name'] ?? '—') ?></td>
                <td><span class="badge badge-<?= esc($d['status']) ?>"><?= ucfirst($d['status']) ?></span></td>
                <td><?= date('M d, Y', strtotime($d['created_date'])) ?></td>
                <td class="text-center"><a href="<?= base_url("parts-details/{$d['id']}") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>initDataTable('#detailsTable',{order:[[5,'desc']]}));</script>
