<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - <?= esc($po['po_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 10mm 10mm;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .po-card {
            max-width: 820px;
            margin: 20px auto;
            background: #fff;
            padding: 25px 30px;
            border: 2px solid #2563eb;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        .po-header-title {
            font-weight: 900;
            font-size: 1.5rem;
            color: #1e3a8a;
        }
        .po-num {
            color: #2563eb;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .table-po-items th, .table-po-items td {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 8px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 4px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .summary-container {
            page-break-inside: avoid;
        }
        @media print {
            .no-print { display: none !important; }
            html, body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
            .po-card { 
                border: 2px solid #000 !important; 
                box-shadow: none !important; 
                margin: 15px auto 0 auto !important; 
                width: 100% !important; 
                max-width: 100% !important; 
                padding: 18px 20px !important; 
            }
        }
    </style>
</head>
<body>

<!-- Navigation / Action Bar -->
<div class="no-print bg-dark text-white py-2 mb-2 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 820px;">
        <a href="<?= base_url('purchase-orders/' . $po['id']) ?>" class="btn btn-outline-light btn-sm py-1">
            <i class="fas fa-arrow-left me-1"></i> Back to Purchase Order
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-warning text-dark font-weight-bold btn-sm py-1">
                <i class="fas fa-print me-1"></i> Print PO
            </button>
            <a href="<?= base_url('purchase-orders/' . $po['id'] . '/pdf') ?>" class="btn btn-danger font-weight-bold btn-sm py-1">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<div class="po-card">
    <!-- Header Section -->
    <div class="row align-items-start mb-3">
        <div class="col-7">
            <div class="po-header-title text-uppercase"><?= esc($companySettings['company_name'] ?? 'HW TRUCK PARTS TRADING') ?></div>
            <div class="small fw-semibold text-secondary" style="font-size: 0.8rem;"><?= nl2br(esc($companySettings['company_address'] ?? '')) ?></div>
            <div class="small text-muted mt-1" style="font-size: 0.8rem;">
                <i class="fas fa-phone me-1"></i><?= esc($companySettings['company_phone'] ?? '') ?> &middot; 
                <i class="fas fa-envelope me-1"></i><?= esc($companySettings['company_email'] ?? '') ?>
            </div>
            <div class="small fw-bold mt-1" style="font-size: 0.8rem;">VAT REG. TIN: <?= esc($companySettings['company_tin'] ?? '') ?></div>
        </div>
        <div class="col-5 text-end">
            <h3 class="fw-black text-primary mb-0" style="font-size: 1.5rem;">PURCHASE ORDER</h3>
            <div class="po-num">№ <?= esc($po['po_number']) ?></div>
            <div class="mt-1">
                <span class="badge bg-primary text-uppercase px-2 py-1 fs-7">
                    <?= ucfirst(str_replace('_', ' ', $po['status'])) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Vendor & Order Info Table -->
    <div class="row g-2 mb-3 small" style="font-size: 0.8rem;">
        <div class="col-6">
            <div class="card bg-light border-0 p-2 h-100">
                <div class="fw-bold text-uppercase text-secondary mb-1" style="font-size:0.72rem;">Vendor / Supplier Information</div>
                <div class="fw-bold text-dark fs-6"><?= esc($po['supplier_name']) ?></div>
                <?php if (!empty($supplier['contact_person'])): ?>
                    <div class="text-muted">Contact Person: <?= esc($supplier['contact_person']) ?></div>
                <?php endif; ?>
                <?php if (!empty($supplier['phone'])): ?>
                    <div class="text-muted">Phone: <?= esc($supplier['phone']) ?></div>
                <?php endif; ?>
                <?php if (!empty($supplier['address'])): ?>
                    <div class="text-muted">Address: <?= esc($supplier['address']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-6">
            <div class="card bg-light border-0 p-2 h-100">
                <div class="fw-bold text-uppercase text-secondary mb-1" style="font-size:0.72rem;">Order & Payment Details</div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">PO Date:</span>
                    <span class="fw-bold"><?= date('M d, Y', strtotime($po['created_at'])) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Payment Type:</span>
                    <span class="fw-bold"><?= ucfirst(str_replace('_', ' ', $po['payment_type'])) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Payment Due Date:</span>
                    <span class="fw-bold text-danger"><?= !empty($po['payment_due_date']) ? date('M d, Y', strtotime($po['payment_due_date'])) : '—' ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Created By:</span>
                    <span class="fw-semibold"><?= esc($createdByName ?? 'Admin System') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <table class="table table-po-items align-middle mb-3 small" style="font-size: 0.8rem;">
        <thead class="table-primary text-uppercase fw-bold">
            <tr>
                <th style="width: 48%;">Part Description / SKU</th>
                <th style="width: 15%;" class="text-center">Qty Ordered</th>
                <th style="width: 17%;" class="text-end">Unit Cost (₱)</th>
                <th style="width: 20%;" class="text-end">Total Amount (₱)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalAmount = 0;
            foreach ($lines as $line): 
                $lineTotal = $line['quantity_ordered'] * $line['unit_cost'];
                $totalAmount += $lineTotal;
            ?>
            <tr>
                <td>
                    <div class="fw-bold text-dark"><?= esc($line['part_name']) ?></div>
                    <?php if (!empty($line['variant_name'])): ?>
                        <div class="text-muted small" style="font-size: 0.72rem;">Variant: <?= esc($line['variant_name']) ?></div>
                    <?php endif; ?>
                    <div class="text-muted font-monospace small" style="font-size: 0.72rem;">SKU: <?= esc($line['sku']) ?></div>
                </td>
                <td class="text-center fw-bold fs-6"><?= esc($line['quantity_ordered']) ?></td>
                <td class="text-end">₱<?= number_format($line['unit_cost'], 2) ?></td>
                <td class="text-end fw-bold text-dark">₱<?= number_format($lineTotal, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-light border-top border-2 border-dark">
                <td colspan="3" class="text-end fw-black fs-6 text-uppercase py-1">TOTAL ORDER AMOUNT:</td>
                <td class="text-end fw-black fs-5 text-primary py-1">₱<?= number_format($totalAmount, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary-container">
        <?php if (!empty($po['remarks'])): ?>
        <div class="card bg-light border-0 p-2 mb-3 small" style="font-size: 0.8rem;">
            <div class="fw-bold text-uppercase text-secondary mb-1" style="font-size:0.72rem;">Remarks & Instructions</div>
            <div><?= nl2br(esc($po['remarks'])) ?></div>
        </div>
        <?php endif; ?>

        <!-- Authorization Signatures -->
        <div class="row text-center mt-4">
            <div class="col-4">
                <div class="signature-line">Prepared By</div>
            </div>
            <div class="col-4">
                <div class="signature-line">Approved By</div>
            </div>
            <div class="col-4">
                <div class="signature-line">Supplier Confirmation</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
