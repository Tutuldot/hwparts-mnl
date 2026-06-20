<?php /** @var array $suppliers */ 
$role = session()->get('user_role');
$canSeeSensitive = in_array($role, ['admin', 'approver', 'purchasing']);
?>
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title">Suppliers</h1>
        <p class="page-subtitle">Manage supply chain partners, contact details and email notifications</p>
    </div>
    <a href="<?= base_url('suppliers/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Supplier</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" id="suppliersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Notice Emails</th>
                        <th>Contact Persons</th>
                        <th>Tags</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td class="fw-600"><?= esc($s['name']) ?></td>
                            <td>
                                <?php if ($s['emails_for_notice']): ?>
                                    <?php if ($canSeeSensitive): ?>
                                        <?php 
                                        $emails = explode(';', $s['emails_for_notice']);
                                        foreach ($emails as $email): 
                                        ?>
                                            <span class="badge badge-submitted text-lowercase mono mb-1"><?= esc(trim($email)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted mono" style="font-size:0.8rem">*******</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($s['contacts'])): ?>
                                    <?php foreach ($s['contacts'] as $c): ?>
                                        <div class="mb-2 last-mb-0">
                                            <span class="fw-600 text-dark"><?= esc($c['name']) ?></span>
                                            <?php if ($c['role_or_title']): ?>
                                                <span class="badge bg-light text-muted border py-0 px-1 ms-1" style="font-size:0.65rem"><?= esc($c['role_or_title']) ?></span>
                                            <?php endif; ?>
                                            <?php if (($c['is_visible'] ?? 1) == 0): ?>
                                                <span class="badge bg-warning text-dark border py-0 px-1 ms-1" style="font-size:0.65rem" title="Hidden from standard users"><i class="fas fa-eye-slash"></i> Hidden</span>
                                            <?php endif; ?>
                                            <div class="small text-muted" style="line-height: 1.25;">
                                                <?php if ($c['email']): ?>
                                                    <i class="far fa-envelope me-1" style="font-size:0.75rem"></i>
                                                    <span class="mono"><?= ($canSeeSensitive || ($c['is_visible'] ?? 1) == 1) ? esc($c['email']) : '*******' ?></span><br>
                                                <?php endif; ?>
                                                <?php if ($c['mobile']): ?>
                                                    <i class="fas fa-phone me-1" style="font-size:0.75rem"></i>
                                                    <span><?= ($canSeeSensitive || ($c['is_visible'] ?? 1) == 1) ? esc($c['mobile']) : '*******' ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['tags']): ?>
                                    <?php 
                                    $tags = explode(',', $s['tags']);
                                    foreach ($tags as $tag): 
                                    ?>
                                        <span class="badge bg-secondary text-uppercase mb-1" style="font-size:0.65rem"><?= esc(trim($tag)) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $s['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url("suppliers/{$s['id']}/edit") ?>" class="btn btn-xs btn-outline-secondary btn-icon"><i class="fas fa-pencil"></i></a>
                                <form action="<?= base_url("suppliers/{$s['id']}/toggle") ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-xs btn-icon btn-outline-<?= $s['is_active'] ? 'danger' : 'success' ?>" title="<?= $s['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                        <i class="fas fa-<?= $s['is_active'] ? 'ban' : 'check' ?>"></i>
                                    </button>
                                </form>
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
    initDataTable('#suppliersTable');
});
</script>
