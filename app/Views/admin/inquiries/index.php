<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Customer Support Inquiries</h1>
        <p class="page-subtitle">Respond to customer messages, upload images, close inquiries, and associate Sales Orders</p>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <script>document.addEventListener('DOMContentLoaded',()=>toastr.success(<?= json_encode(session()->getFlashdata('success')) ?>));</script>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <script>document.addEventListener('DOMContentLoaded',()=>toastr.error(<?= json_encode(session()->getFlashdata('error')) ?>));</script>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="card-title fw-bold mb-0 text-primary"><i class="fas fa-list-check me-2 text-muted"></i>Inquiry Management</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="inquiriesTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 100px;">Inquiry ID</th>
                        <th>Customer / Client Name</th>
                        <th>Created Date</th>
                        <th class="text-center" style="width: 150px;">Status</th>
                        <th>Associated Sales Order</th>
                        <th class="text-center pe-4" style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiries)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-comments fa-3x mb-3 text-light"></i>
                                <p class="mb-0 fw-bold">No customer inquiries found.</p>
                                <p class="text-muted small">Inquiries will appear here when submitted from the customer portal.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $inq): ?>
                            <tr class="<?= $inq['status'] === 'open' ? 'fw-600' : '' ?>">
                                <td class="ps-4">
                                    <span class="mono text-primary fw-bold">#<?= $inq['id'] ?></span>
                                </td>
                                <td>
                                    <div><?= esc($inq['customer_name']) ?></div>
                                    <?php if ($inq['company_name']): ?>
                                        <div class="small text-muted"><?= esc($inq['company_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="mono small">
                                    <?= date('Y-m-d h:i A', strtotime($inq['created_at'])) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($inq['status'] === 'open'): ?>
                                        <span class="badge badge-submitted"><i class="fas fa-envelope-open me-1"></i>Open</span>
                                    <?php else: ?>
                                        <span class="badge badge-draft"><i class="fas fa-check-circle me-1"></i>Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($inq['so_number']): ?>
                                        <a href="<?= base_url('sales-orders/' . $inq['sales_order_id']) ?>" class="fw-bold text-decoration-none">
                                            <i class="fas fa-receipt me-1 text-success"></i><?= esc($inq['so_number']) ?>
                                        </a>
                                        <span class="badge badge-<?= $inq['so_status'] ?> btn-xs py-0 px-1 font-weight-normal"><?= ucfirst($inq['so_status']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="<?= base_url('admin/inquiries/' . $inq['id']) ?>" class="btn btn-primary btn-sm btn-icon" title="View thread and reply">
                                        <i class="fas fa-reply"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->section('extraJs') ?>
<script>
    $(document).ready(function() {
        if ($('#inquiriesTable tbody tr').length > 1 || !$('#inquiriesTable tbody tr td').hasClass('text-center')) {
            initDataTable('#inquiriesTable', {
                order: [[3, 'desc'], [0, 'desc']], // Default sorting: status open first (or custom), then ID desc
                columnDefs: [
                    { orderable: false, targets: [4, 5] }
                ]
            });
        }
    });
</script>
<?= $this->endSection() ?>
