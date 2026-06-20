<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fas fa-users text-primary me-2" style="font-size:1.25rem"></i>Customers</h1>
        <p class="page-subtitle">Manage customer profiles, payment terms, and credentials</p>
    </div>
    <a href="<?= base_url('customers/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus me-1"></i>Add Customer
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="customersTable">
                <thead>
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Type</th>
                        <th>Company Name</th>
                        <th>TIN</th>
                        <th>Terms (Days)</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td class="ps-4">
                                <a href="<?= base_url("customers/{$c['id']}") ?>" class="fw-bold text-decoration-none">
                                    <?= esc($c['name']) ?>
                                </a>
                                <div class="text-muted small font-monospace">@<?= esc($c['username']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $c['type'] === 'corporate' ? 'primary' : 'info' ?> text-capitalize">
                                    <?= esc($c['type']) ?>
                                </span>
                            </td>
                            <td class="text-dark fw-500"><?= esc($c['company_name'] ?: '—') ?></td>
                            <td class="mono small"><?= esc($c['tin'] ?: '—') ?></td>
                            <td class="fw-600 text-center"><?= $c['payment_terms'] ?></td>
                            <td class="text-center">
                                <?php if ($c['is_active']): ?>
                                    <span class="badge badge-approved"><i class="fas fa-circle-check me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected"><i class="fas fa-circle-xmark me-1"></i>Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <a href="<?= base_url("customers/{$c['id']}") ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url("customers/{$c['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-pencil"></i>
                                    </a>
                                    <form action="<?= base_url("customers/{$c['id']}/toggle") ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-<?= $c['is_active'] ? 'danger' : 'success' ?>" 
                                            title="<?= $c['is_active'] ? 'Deactivate' : 'Activate' ?>"
                                            onclick="return confirm('Are you sure you want to change this customer status?')">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </form>
                                </div>
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
    initDataTable('#customersTable', {
        order: [[0, 'asc']]
    });
});
</script>
