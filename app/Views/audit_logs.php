<?php /** @var array $logs */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">Audit Logs</h1><p class="page-subtitle">Last <?= count($logs) ?> system events</p></div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0" id="auditTable">
            <thead><tr><th>Date/Time</th><th>User</th><th>Role</th><th>Module</th><th>Action</th><th>Record #</th><th>Description</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td class="mono small"><?= date('M d Y H:i:s', strtotime($log['created_at'])) ?></td>
                <td><?= esc($log['user_name']) ?></td>
                <td><span class="badge badge-draft"><?= esc($log['user_role']) ?></span></td>
                <td><span class="badge badge-submitted"><?= esc($log['module']) ?></span></td>
                <td><?= esc($log['action']) ?></td>
                <td class="text-muted">#<?= $log['record_id'] ?></td>
                <td><?= esc($log['description']) ?></td>
                <td class="mono small text-muted"><?= esc($log['ip_address']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>initDataTable('#auditTable',{order:[[0,'desc']],pageLength:50}));</script>
