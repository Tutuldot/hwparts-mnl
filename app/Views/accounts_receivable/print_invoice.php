<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice - <?= esc(!empty($ar['bir_invoice_number']) ? $ar['bir_invoice_number'] : $ar['invoice_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 10mm 10mm;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Courier New', Courier, monospace, 'Segoe UI', sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .invoice-card {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px 25px;
            border: 2px solid #000;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        .invoice-header-title {
            font-weight: 900;
            font-size: 1.5rem;
            letter-spacing: 1px;
            line-height: 1.1;
        }
        .invoice-num {
            color: #dc3545;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .table-invoice-items {
            border: 2px solid #000 !important;
        }
        .table-invoice-items th, .table-invoice-items td {
            border: 1px solid #000 !important;
            padding: 4px 6px;
        }
        .summary-box {
            border: 2px solid #000;
            page-break-inside: avoid;
        }
        .summary-box td {
            border: 1px solid #000;
            padding: 3px 6px;
        }
        .signature-box {
            border: 1px solid #000;
            min-height: 45px;
        }
        .summary-container {
            page-break-inside: avoid;
        }
        @media print {
            .no-print { display: none !important; }
            html, body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
            .invoice-card { 
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
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 800px;">
        <a href="<?= base_url('accounts-receivable/' . $ar['id']) ?>" class="btn btn-outline-light btn-sm py-1">
            <i class="fas fa-arrow-left me-1"></i> Back to Settlement
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-warning text-dark font-weight-bold btn-sm py-1">
                <i class="fas fa-print me-1"></i> Print Invoice
            </button>
            <a href="<?= base_url('accounts-receivable/' . $ar['id'] . '/pdf') ?>" class="btn btn-danger font-weight-bold btn-sm py-1">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<div class="invoice-card">
    <!-- Header Section -->
    <div class="row align-items-start mb-2">
        <div class="col-8">
            <div class="invoice-header-title text-uppercase"><?= esc($companySettings['company_name'] ?? 'HW TRUCK PARTS TRADING') ?></div>
            <div class="small fw-semibold" style="font-size: 0.8rem;"><?= nl2br(esc($companySettings['company_address'] ?? '')) ?></div>
            <div class="small text-dark font-italic" style="font-size: 0.8rem;"><?= esc($companySettings['company_tagline'] ?? '') ?></div>
            <div class="small fw-bold mt-1" style="font-size: 0.8rem;">VAT REG. TIN: <?= esc($companySettings['company_tin'] ?? '') ?></div>
        </div>
        <div class="col-4 text-end">
            <h3 class="fw-black mb-0" style="font-size: 1.5rem;">SALES INVOICE</h3>
            <div class="invoice-num">№ <?= esc(!empty($ar['bir_invoice_number']) ? $ar['bir_invoice_number'] : $ar['invoice_number']) ?></div>
            <?php if (!empty($ar['bir_invoice_number'])): ?>
                <div class="text-muted font-monospace" style="font-size: 0.65rem;">(Ref: <?= esc($ar['invoice_number']) ?>)</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sales Type & Customer Info -->
    <div class="border-top border-bottom border-2 border-dark py-1 mb-2 small" style="font-size: 0.8rem;">
        <div class="d-flex justify-content-between mb-1">
            <div>
                <?php $isCharge = ($so['payment_terms'] ?? 0) > 0; ?>
                <span class="me-4"><i class="<?= !$isCharge ? 'fas fa-check-square' : 'far fa-square' ?>"></i> Cash Sales</span>
                <span><i class="<?= $isCharge ? 'fas fa-check-square' : 'far fa-square' ?>"></i> Charge Sales</span>
            </div>
            <div class="fw-bold">
                DATE: <span class="border-bottom border-dark d-inline-block text-center px-2" style="min-width: 120px;"><?= date('M d, Y', strtotime($so['created_at'])) ?></span>
            </div>
        </div>
        <div class="mb-1">
            <strong>Registered Name:</strong> 
            <span class="border-bottom border-dark d-inline-block px-1" style="width: calc(100% - 150px);"><?= esc($ar['company_name'] ?: $ar['customer_name']) ?></span>
        </div>
        <div class="d-flex justify-content-between">
            <div style="width: 70%;">
                <strong>Business Address:</strong> 
                <span class="border-bottom border-dark d-inline-block px-1" style="width: calc(100% - 140px);"><?= esc($ar['billing_address'] ?: ($ar['shipping_address'] ?: '—')) ?></span>
            </div>
            <div style="width: 28%;" class="text-end">
                <strong>TIN:</strong> 
                <span class="border-bottom border-dark d-inline-block px-1" style="min-width: 100px;"><?= esc($ar['tin'] ?: '—') ?></span>
            </div>
        </div>
    </div>

    <!-- Item Description Table -->
    <table class="table table-invoice-items align-middle mb-2 small" style="font-size: 0.8rem;">
        <thead class="table-light text-uppercase fw-bold text-center">
            <tr>
                <th style="width: 48%;">Item Description/Nature of Service</th>
                <th style="width: 12%;">Qty.</th>
                <th style="width: 18%;">Unit Cost</th>
                <th style="width: 22%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grossSales    = (float)$so['amount'];
            $vatRate       = (float)($so['vat_rate'] ?? 12.00);
            $whtRate       = (float)($so['withholding_tax_rate'] ?? 0.00);
            $netOfVat      = (float)($so['net_of_vat_amount'] > 0 ? $so['net_of_vat_amount'] : ($vatRate > 0 ? round($grossSales / (1 + ($vatRate / 100)), 2) : $grossSales));
            $vatAmount     = (float)($so['vat_amount'] > 0 ? $so['vat_amount'] : round($grossSales - $netOfVat, 2));
            $whtAmount     = (float)($so['withholding_tax_amount'] >= 0 && isset($so['net_of_vat_amount']) ? $so['withholding_tax_amount'] : round($netOfVat * ($whtRate / 100), 2));
            $totalAmountDue= (float)($so['total_amount_due'] > 0 ? $so['total_amount_due'] : round($grossSales - $whtAmount, 2));

            $totalDiscount = 0;
            $rowCount = 0;
            foreach ($lines as $line): 
                $rowCount++;
                $disc = (float)($line['line_discount'] ?? 0);
                $totalDiscount += $disc;
            ?>
            <tr>
                <td>
                    <div class="fw-bold"><?= esc($line['part_name']) ?></div>
                    <?php if (!empty($line['variant_name'])): ?>
                        <div class="text-muted" style="font-size:0.72rem;">Variant: <?= esc($line['variant_name']) ?></div>
                    <?php endif; ?>
                    <div class="text-muted font-monospace" style="font-size:0.72rem;">SKU: <?= esc($line['sku']) ?></div>
                </td>
                <td class="text-center fw-bold"><?= esc($line['quantity']) ?></td>
                <td class="text-end">₱<?= number_format($line['unit_price'], 2) ?></td>
                <td class="text-end fw-bold">₱<?= number_format($line['total_price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>

            <!-- Compact padding for empty rows to maintain single-page fit -->
            <?php 
            $maxPad = ($rowCount < 3) ? (3 - $rowCount) : 0;
            for ($i = 0; $i < $maxPad; $i++): 
            ?>
            <tr>
                <td style="height: 24px;">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- BIR Tax Computation & Total Amount Due Summary -->
    <div class="summary-container">
        <div class="row g-0 mb-2 small" style="font-size: 0.8rem;">
            <!-- Left Tax Details -->
            <div class="col-5">
                <table class="table table-borderless mb-0 border border-dark border-2">
                    <tbody>
                        <tr class="border-bottom border-dark">
                            <td class="fw-bold py-1">Vatable Sales</td>
                            <td class="text-end py-1">₱<?= number_format($netOfVat, 2) ?></td>
                        </tr>
                        <tr class="border-bottom border-dark">
                            <td class="fw-bold py-1">VAT (<?= number_format($vatRate, 2) ?>%)</td>
                            <td class="text-end py-1">₱<?= number_format($vatAmount, 2) ?></td>
                        </tr>
                        <tr class="border-bottom border-dark">
                            <td class="py-1">Zero-Rated Sales</td>
                            <td class="text-end py-1">₱0.00</td>
                        </tr>
                        <tr>
                            <td class="py-1">VAT-Exempt Sales</td>
                            <td class="text-end py-1">₱0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Right Grand Total Calculation -->
            <div class="col-7 ps-2">
                <table class="table table-borderless mb-0 border border-dark border-2">
                    <tbody>
                        <tr class="border-bottom border-dark">
                            <td class="fw-bold py-1">Total Sales (VAT Inclusive)</td>
                            <td class="text-end fw-bold py-1">₱<?= number_format($grossSales, 2) ?></td>
                        </tr>
                        <tr class="border-bottom border-dark">
                            <td class="text-muted py-1">Less: VAT</td>
                            <td class="text-end text-danger py-1">₱<?= number_format($vatAmount, 2) ?></td>
                        </tr>
                        <tr class="border-bottom border-dark bg-light">
                            <td class="fw-bold py-1">Amount Net of VAT</td>
                            <td class="text-end fw-bold py-1">₱<?= number_format($netOfVat, 2) ?></td>
                        </tr>
                        <?php if ($totalDiscount > 0): ?>
                        <tr class="border-bottom border-dark">
                            <td class="text-muted py-1">Less: Discount</td>
                            <td class="text-end text-danger py-1">-₱<?= number_format($totalDiscount, 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="border-bottom border-dark">
                            <td class="text-muted py-1">Less: W/Tax (<?= number_format($whtRate, 2) ?>%)</td>
                            <td class="text-end text-danger py-1">₱<?= number_format($whtAmount, 2) ?></td>
                        </tr>
                        <tr class="table-primary border-top border-dark border-2">
                            <td class="fw-black text-uppercase py-1 fs-6">TOTAL AMOUNT DUE</td>
                            <td class="text-end fw-black fs-5 text-primary py-1">₱<?= number_format($totalAmountDue, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Signatures Footer -->
        <div class="row g-2 align-items-end small pt-1" style="font-size: 0.78rem;">
            <div class="col-7">
                <div class="mb-1">
                    <strong>Received the amount of:</strong> 
                    <span class="border-bottom border-dark d-inline-block px-2" style="width: 55%;">_________________________</span>
                </div>
            </div>
            <div class="col-5">
                <div class="signature-box p-1 text-center small">
                    <div class="text-muted mb-3" style="font-size:0.65rem;">SC/PWD/NAAC/MOV Sol Parent Id No. / Signature</div>
                    <div class="border-top border-dark pt-1 fw-bold">Authorized Representative Signature</div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
