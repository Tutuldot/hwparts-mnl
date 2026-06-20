<?php /** @var array $users */ ?>
<div class="page-header d-flex align-items-center justify-content-between">
    <div><h1 class="page-title">User Management</h1><p class="page-subtitle"><?= count($users) ?> users registered</p></div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#userLevelDetailsModal"><i class="fas fa-circle-info"></i> View User Level Details</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus"></i> Add User</button>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="usersTable">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td class="fw-500"><?= esc($u['name']) ?></td>
                <td><?= esc($u['email']) ?></td>
                <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'submitted' : 'draft' ?>"><?= ucfirst($u['role']) ?></span></td>
                <td class="text-center"><span class="badge badge-<?= $u['is_active'] ? 'active' : 'inactive' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td class="text-center d-flex gap-1 justify-content-center">
                    <button class="btn btn-sm btn-outline-secondary btn-edit-user"
                            data-id="<?= $u['id'] ?>" data-name="<?= esc($u['name']) ?>"
                            data-email="<?= esc($u['email']) ?>" data-role="<?= esc($u['role']) ?>">
                        <i class="fas fa-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning btn-reset-pw" data-id="<?= $u['id'] ?>" data-name="<?= esc($u['name']) ?>">
                        <i class="fas fa-key"></i>
                    </button>
                    <form action="<?= base_url("admin/users/{$u['id']}/toggle") ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-<?= $u['is_active'] ? 'danger' : 'success' ?>" onclick="return confirm('Toggle user status?')">
                            <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= base_url('admin/users/store') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" minlength="8" required></div>
            <div class="mb-3"><label class="form-label">Role *</label>
                <select name="role" class="form-select" required>
                    <option value="warehouse">Warehouse</option>
                    <option value="purchasing">Purchasing</option>
                    <option value="approver">Approver</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create</button></div>
    </form>
</div></div></div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="editUserForm" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" id="editUserName" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" id="editUserEmail" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Role *</label>
                <select name="role" id="editUserRole" class="form-select" required>
                    <option value="warehouse">Warehouse</option>
                    <option value="purchasing">Purchasing</option>
                    <option value="approver">Approver</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
</div></div></div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPwModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="resetPwTitle">Reset Password</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="resetPwForm" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">New Password *</label><input type="password" name="new_password" class="form-control" minlength="8" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Reset</button></div>
    </form>
</div></div></div>

<!-- User Level Details Modal -->
<div class="modal fade" id="userLevelDetailsModal" tabindex="-1" aria-labelledby="userLevelDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userLevelDetailsModalLabel">User Role Capabilities &amp; Access Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:25%">Role</th>
                                <th>Permissions &amp; Capabilities</th>
                                <th style="width:25%">Supplier Visibility</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="badge badge-submitted fs-6">Admin</span>
                                </td>
                                <td>
                                    <ul class="mb-0 ps-3">
                                        <li>Full administrative access across all modules</li>
                                        <li>Manage system users and reset passwords</li>
                                        <li>Set low-stock alert thresholds</li>
                                        <li>Create, submit, approve/reject purchase orders &amp; transfers</li>
                                        <li>View system-wide audit logs</li>
                                    </ul>
                                </td>
                                <td>
                                    <span class="text-success fw-600"><i class="fas fa-eye"></i> Full Access</span><br>
                                    <small class="text-muted">Sees all notice emails &amp; hidden contacts</small>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-success text-white fs-6">Approver</span>
                                </td>
                                <td>
                                    <ul class="mb-0 ps-3">
                                        <li>Approve or reject submitted purchase orders (PO)</li>
                                        <li>Approve or reject inventory transfers between warehouses</li>
                                        <li>View inventory dashboard and standard stocks</li>
                                    </ul>
                                </td>
                                <td>
                                    <span class="text-success fw-600"><i class="fas fa-eye"></i> Full Access</span><br>
                                    <small class="text-muted">Sees all notice emails &amp; hidden contacts</small>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-primary text-white fs-6">Purchasing</span>
                                </td>
                                <td>
                                    <ul class="mb-0 ps-3">
                                        <li>Create, edit and manage parts/variants catalogue</li>
                                        <li>Create purchase orders (draft, submit)</li>
                                        <li>Record PO item arrivals (Receiving Workflow) into warehouses</li>
                                        <li>Manage suppliers database</li>
                                    </ul>
                                </td>
                                <td>
                                    <span class="text-success fw-600"><i class="fas fa-eye"></i> Full Access</span><br>
                                    <small class="text-muted">Sees all notice emails &amp; hidden contacts</small>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary text-white fs-6">Warehouse</span>
                                </td>
                                <td>
                                    <ul class="mb-0 ps-3">
                                        <li>Manage warehouses and sub-locations (Shelves/Bins)</li>
                                        <li>Manually record and adjust warehouse inventory stocks</li>
                                        <li>Create transfers and record partial delivery of transferred lines</li>
                                        <li>Consume tracked units (QR scanner logic)</li>
                                    </ul>
                                </td>
                                <td>
                                    <span class="text-danger fw-600"><i class="fas fa-eye-slash"></i> Restricted Access</span><br>
                                    <small class="text-muted">Notice emails &amp; contacts marked "Visible = No" are masked as <code>*******</code></small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initDataTable('#usersTable');
    document.querySelectorAll('.btn-edit-user').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('editUserName').value  = btn.dataset.name;
            document.getElementById('editUserEmail').value = btn.dataset.email;
            document.getElementById('editUserRole').value  = btn.dataset.role;
            document.getElementById('editUserForm').action = `<?= base_url('admin/users/') ?>${btn.dataset.id}/update`;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        });
    });
    document.querySelectorAll('.btn-reset-pw').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('resetPwTitle').textContent = 'Reset: ' + btn.dataset.name;
            document.getElementById('resetPwForm').action = `<?= base_url('admin/users/') ?>${btn.dataset.id}/reset-password`;
            new bootstrap.Modal(document.getElementById('resetPwModal')).show();
        });
    });
});
</script>
