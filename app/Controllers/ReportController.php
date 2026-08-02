<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportController extends BaseController
{
    // Report definitions: key => [label, description, icon, color]
    protected array $reportDefs = [
        'sales_summary'   => ['Sales Summary',             'All sales orders with totals, discounts, and status',      'fas fa-file-invoice-dollar', 'primary'],
        'sales_by_part'   => ['Sales by Part',             'Quantity sold and revenue breakdown per part/variant',      'fas fa-boxes',               'success'],
        'ar_aging'        => ['Accounts Receivable Aging', 'Outstanding AR grouped by age (0-30, 31-60, 61-90, 90+)',  'fas fa-user-clock',          'warning'],
        'ap_aging'        => ['Accounts Payable Aging',    'Outstanding AP grouped by age (0-30, 31-60, 61-90, 90+)',  'fas fa-building',            'danger'],
        'po_summary'      => ['Purchase Orders Summary',   'All POs with supplier, amounts, and approval status',       'fas fa-truck',               'info'],
        'inventory_stock' => ['Inventory Stock Levels',    'Current stock per part per warehouse with low-stock flags', 'fas fa-warehouse',           'secondary'],
        'price_list'      => ['Price List',                'All parts and variants with current selling prices',        'fas fa-tags',                'dark'],
        'customer_ledger' => ['Cash Ledger',               'All cash transactions, transfers, advances, and adjustments across accounts', 'fas fa-money-bill-wave', 'primary'],
        'cogs_report'     => ['Cost of Goods Sold (COGS)',  'Per-line COGS, gross profit, and gross margin based on weighted average inventory cost', 'fas fa-calculator', 'danger'],
    ];

    protected array $allRoles = ['admin', 'warehouse', 'purchasing', 'approver'];

    public function index()
    {
        $allowed = $this->getAllowedReports();
        $filters = array_filter([
            'date_from' => trim((string) $this->request->getGet('date_from')),
            'date_to'   => trim((string) $this->request->getGet('date_to')),
        ]);
        $data = [
            'pageTitle'   => 'Reports Center',
            'breadcrumb'  => [['HW Trucks MNL', base_url('dashboard')], ['Reports', null]],
            'reportDefs'  => array_intersect_key($this->reportDefs, array_flip($allowed)),
            'isAdmin'     => session()->get('user_role') === 'admin',
            'filters'     => $filters,
        ];
        return view('layouts/main', $data + ['content' => view('reports/index', $data)]);
    }

    public function show(string $key)
    {
        $this->checkAccess($key);
        $def = $this->reportDefs[$key] ?? null;
        if (!$def) return redirect()->to(base_url('reports'))->with('error', 'Report not found.');

        $filters    = $this->request->getGet() ?? [];
        $rows       = $this->fetchData($key, $filters);
        $db         = \Config\Database::connect();
        $warehouses = $db->table('warehouses')->where('is_active', 1)->orderBy('name')->get()->getResultArray();
        $categories = $db->table('part_categories')->orderBy('name')->get()->getResultArray();

        $data = [
            'pageTitle'  => $def[0],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Reports', base_url('reports')], [$def[0], null]],
            'key'        => $key,
            'def'        => $def,
            'rows'       => $rows,
            'filters'    => $filters,
            'columns'    => $this->getColumns($key),
            'warehouses' => $warehouses,
            'categories' => $categories,
        ];
        return view('layouts/main', $data + ['content' => view('reports/show', $data)]);
    }

    public function export(string $key)
    {
        $this->checkAccess($key);
        $def = $this->reportDefs[$key] ?? null;
        if (!$def) return redirect()->to(base_url('reports'))->with('error', 'Report not found.');

        $filters = $this->request->getGet() ?? [];
        $rows    = $this->fetchData($key, $filters);
        $columns = $this->getColumns($key);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($def[0], 0, 31));

        // Header row styling
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];

        // Title row
        $colCountStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns));
        $sheet->setCellValue('A1', $def[0] . ' — Generated: ' . date('Y-m-d H:i'));
        $sheet->mergeCells('A1:' . $colCountStr . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1E3A5F']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Filter info string
        $filterStr = '';
        foreach ($filters as $fk => $fv) {
            if ($fv !== '') $filterStr .= ucfirst(str_replace('_', ' ', $fk)) . ': ' . $fv . '  |  ';
        }
        if ($filterStr) {
            $sheet->setCellValue('A2', 'Applied Filters: ' . rtrim($filterStr, '  |  '));
            $sheet->mergeCells('A2:' . $colCountStr . '2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '555555']],
            ]);
        }

        // Column headers (row 3)
        $colIdx = 1;
        foreach ($columns as $colLabel) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->setCellValue($colLetter . '3', $colLabel);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            $colIdx++;
        }
        $sheet->getStyle('A3:' . $colCountStr . '3')->applyFromArray($headerStyle);

        // Data rows
        $rowNum = 4;
        $altStyle = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']]];
        foreach ($rows as $row) {
            $colIdx = 1;
            foreach (array_values($row) as $val) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue($colLetter . $rowNum, $val);
                $colIdx++;
            }
            if ($rowNum % 2 === 0) {
                $sheet->getStyle('A' . $rowNum . ':' . $colCountStr . $rowNum)->applyFromArray($altStyle);
            }
            $rowNum++;
        }

        // Total count footer
        $sheet->setCellValue('A' . $rowNum, 'Total Records: ' . count($rows));
        $sheet->getStyle('A' . $rowNum)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
        ]);

        // Output Excel response
        $filename = str_replace(' ', '_', $def[0]) . '_' . date('Ymd_Hi') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function accessMatrix()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to(base_url('reports'))->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        $grants = $db->table('report_permissions')->get()->getResultArray();

        $matrix = [];
        foreach ($grants as $g) {
            $matrix[$g['report_key']][$g['role']] = true;
        }

        $data = [
            'pageTitle'  => 'Report Access Matrix',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Reports', base_url('reports')], ['Access Matrix', null]],
            'reportDefs' => $this->reportDefs,
            'roles'      => $this->allRoles,
            'matrix'     => $matrix,
        ];
        return view('layouts/main', $data + ['content' => view('reports/access_matrix', $data)]);
    }

    public function saveAccess()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to(base_url('reports'))->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        $db->table('report_permissions')->truncate();

        $now = date('Y-m-d H:i:s');
        $submitted = $this->request->getPost('access') ?? [];
        foreach ($submitted as $reportKey => $roles) {
            foreach ($roles as $role => $val) {
                if (isset($this->reportDefs[$reportKey]) && in_array($role, $this->allRoles)) {
                    $db->table('report_permissions')->insert([
                        'report_key' => $reportKey,
                        'role'       => $role,
                        'created_at' => $now,
                    ]);
                }
            }
        }

        return redirect()->to(base_url('reports/access-matrix'))->with('success', 'Report access permissions updated.');
    }

    // ----------------------------------------------------------------
    //  Private helpers & Data Fetchers
    // ----------------------------------------------------------------

    private function getAllowedReports(): array
    {
        // Allow all reports for all users regardless of role
        return array_keys($this->reportDefs);
    }

    private function checkAccess(string $key): void
    {
        if (!isset($this->reportDefs[$key])) {
            redirect()->to(base_url('reports'))->with('error', 'Report not found.')->send();
            exit;
        }
    }

    private function getColumns(string $key): array
    {
        return match($key) {
            'sales_summary'   => ['SO #', 'Customer', 'Date', 'Status', 'Gross Amount', 'Total Discount', 'Net Amount', 'Approved By', 'Approved At'],
            'sales_by_part'   => ['Part Name', 'SKU', 'Variant', 'Total Qty Sold', 'Total Revenue (Net)', 'Avg Unit Price'],
            'ar_aging'        => ['Invoice #', 'BIR Invoice #', 'Customer', 'SO #', 'Invoice Amount', 'Amount Paid', 'Balance', 'Due Date', 'Days Overdue', 'Aging Bucket', 'Status'],
            'ap_aging'        => ['AP #', 'PO #', 'Supplier', 'Amount', 'Amount Paid', 'Balance', 'Due Date', 'Days Overdue', 'Aging Bucket', 'Status'],
            'po_summary'      => ['PO #', 'Supplier', 'Payment Due', 'Status', 'Total Amount', 'Created By', 'Created At', 'Approved At'],
            'inventory_stock' => ['Part Name', 'SKU', 'Type', 'Warehouse', 'Location', 'Qty On Hand', 'Consumed Qty', 'Available', 'Min Stock', 'Status'],
            'price_list'      => ['Part Name', 'SKU', 'Variant', 'Variant SKU', 'Selling Price', 'Min Selling Price', 'Notes', 'Last Updated'],
            'customer_ledger' => ['Txn #', 'Date & Time', 'Type', 'From Account', 'To Account', 'Debit (DR)', 'Credit (CR)', 'Ref #', 'Remarks', 'Created By', 'Approved By', 'Status'],
            'cogs_report'     => ['SO #', 'SO Date', 'SO Status', 'Customer', 'Part Name', 'SKU', 'Variant', 'Qty', 'Sell Price', 'Discount', 'Net Revenue', 'Unit Cost (WAC)', 'COGS', 'Gross Profit', 'Gross Margin %', 'Cost Flag'],
        };
    }

    private function fetchData(string $key, array $filters): array
    {
        $db = \Config\Database::connect();

        $dateFrom = $filters['date_from'] ?? '';
        $dateTo   = $filters['date_to']   ?? '';
        $asOf     = $filters['as_of']     ?? date('Y-m-d');

        return match($key) {
            'sales_summary'   => $this->fetchSalesSummary($db, $dateFrom, $dateTo, $filters),
            'sales_by_part'   => $this->fetchSalesByPart($db, $dateFrom, $dateTo),
            'ar_aging'        => $this->fetchArAging($db, $dateFrom, $dateTo, $asOf),
            'ap_aging'        => $this->fetchApAging($db, $dateFrom, $dateTo, $asOf),
            'po_summary'      => $this->fetchPoSummary($db, $dateFrom, $dateTo, $filters),
            'inventory_stock' => $this->fetchInventoryStock($db, $dateFrom, $dateTo, $filters),
            'price_list'      => $this->fetchPriceList($db, $dateFrom, $dateTo, $filters),
            'customer_ledger' => $this->fetchCustomerLedger($db, $dateFrom, $dateTo),
            'cogs_report'     => $this->fetchCogsReport($db, $dateFrom, $dateTo, $filters),
        };
    }

    private function formatDate(?string $date): string
    {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return '—';
        }
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : '—';
    }

    private function fetchSalesSummary($db, string $dateFrom, string $dateTo, array $f): array
    {
        $q = $db->table('sales_orders so')
            ->select('so.so_number, c.name as customer, so.created_at, so.status,
                      COALESCE(SUM(sol.quantity * sol.unit_price), 0) as gross_amount,
                      COALESCE(SUM(sol.line_discount), 0) as total_discount,
                      so.amount as net_amount,
                      u.name as approved_by, so.approved_at')
            ->join('customers c', 'c.id = so.customer_id')
            ->join('sales_order_lines sol', 'sol.so_id = so.id', 'left')
            ->join('users u', 'u.id = so.approved_by', 'left')
            ->groupBy(['so.id', 'so.so_number', 'c.name', 'so.created_at', 'so.status', 'so.amount', 'u.name', 'so.approved_at']);

        if ($dateFrom) $q->where('so.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo)   $q->where('so.created_at <=', $dateTo . ' 23:59:59');
        if (!empty($f['status'])) $q->where('so.status', $f['status']);

        $rows = $q->orderBy('so.created_at', 'DESC')->get()->getResultArray();

        return array_map(function($r) {
            $net = (float)$r['net_amount'];
            $disc = (float)$r['total_discount'];
            $gross = (float)$r['gross_amount'];
            if ($gross <= 0) {
                $gross = $net + $disc;
            }
            return [
                $r['so_number'],
                $r['customer'],
                $this->formatDate($r['created_at']),
                strtoupper((string)$r['status']),
                number_format($gross, 2),
                number_format($disc, 2),
                number_format($net, 2),
                $r['approved_by'] ?? '—',
                $this->formatDate($r['approved_at']),
            ];
        }, $rows);
    }

    private function fetchSalesByPart($db, string $dateFrom, string $dateTo): array
    {
        $q = $db->table('sales_order_lines sol')
            ->select('p.name as part_name, p.sku, v.variant_name,
                      SUM(sol.quantity) as total_qty,
                      SUM(sol.total_price) as total_revenue,
                      ROUND(AVG(sol.unit_price), 2) as avg_price')
            ->join('parts p', 'p.id = sol.part_id')
            ->join('part_variants v', 'v.id = sol.variant_id', 'left')
            ->join('sales_orders so', 'so.id = sol.so_id')
            ->groupBy(['sol.part_id', 'sol.variant_id', 'p.name', 'p.sku', 'v.variant_name']);

        if ($dateFrom) $q->where('so.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo)   $q->where('so.created_at <=', $dateTo . ' 23:59:59');

        $rows = $q->orderBy('total_revenue', 'DESC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['part_name'], $r['sku'], $r['variant_name'] ?? '—',
            (int)$r['total_qty'],
            number_format((float)$r['total_revenue'], 2),
            number_format((float)$r['avg_price'], 2),
        ], $rows);
    }

    private function fetchArAging($db, string $dateFrom, string $dateTo, string $asOf): array
    {
        $q = $db->table('accounts_receivable ar')
            ->select('ar.invoice_number, ar.bir_invoice_number, c.name as customer, so.so_number,
                      ar.amount, ar.amount_paid, ar.due_date, ar.status, ar.created_at')
            ->join('customers c', 'c.id = ar.customer_id')
            ->join('sales_orders so', 'so.id = ar.so_id');

        if ($dateFrom) $q->where('ar.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo)   $q->where('ar.created_at <=', $dateTo . ' 23:59:59');

        $rows = $q->orderBy('ar.due_date', 'ASC')->get()->getResultArray();

        return array_map(function($r) use ($asOf) {
            $balance = (float)$r['amount'] - (float)($r['amount_paid'] ?? 0);
            $due = $r['due_date'] ?? $asOf;
            $days = $r['status'] === 'paid' ? 0 : max(0, (int)((strtotime($asOf) - strtotime($due)) / 86400));
            $bucket = $days === 0 ? 'Current' : ($days <= 30 ? '1-30 days' : ($days <= 60 ? '31-60 days' : ($days <= 90 ? '61-90 days' : '90+ days')));
            return [
                $r['invoice_number'],
                $r['bir_invoice_number'] ?? '—',
                $r['customer'],
                $r['so_number'],
                number_format((float)$r['amount'], 2),
                number_format((float)($r['amount_paid'] ?? 0), 2),
                number_format($balance, 2),
                $due, $days, $bucket, strtoupper((string)$r['status'])
            ];
        }, $rows);
    }

    private function fetchApAging($db, string $dateFrom, string $dateTo, string $asOf): array
    {
        $q = $db->table('accounts_payable ap')
            ->select('ap.id, po.po_number, s.name as supplier,
                      ap.amount, ap.amount_paid, ap.due_date, ap.status, ap.created_at')
            ->join('purchase_orders po', 'po.id = ap.po_id')
            ->join('suppliers s', 's.id = ap.supplier_id');

        if ($dateFrom) $q->where('ap.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo)   $q->where('ap.created_at <=', $dateTo . ' 23:59:59');

        $rows = $q->orderBy('ap.due_date', 'ASC')->get()->getResultArray();

        return array_map(function($r) use ($asOf) {
            $balance = (float)$r['amount'] - (float)($r['amount_paid'] ?? 0);
            $due = $r['due_date'] ?? $asOf;
            $days = $r['status'] === 'paid' ? 0 : max(0, (int)((strtotime($asOf) - strtotime($due)) / 86400));
            $bucket = $days === 0 ? 'Current' : ($days <= 30 ? '1-30 days' : ($days <= 60 ? '31-60 days' : ($days <= 90 ? '61-90 days' : '90+ days')));
            return [
                'AP-' . str_pad($r['id'], 6, '0', STR_PAD_LEFT),
                $r['po_number'], $r['supplier'],
                number_format((float)$r['amount'], 2),
                number_format((float)($r['amount_paid'] ?? 0), 2),
                number_format($balance, 2),
                $due, $days, $bucket, strtoupper((string)$r['status'])
            ];
        }, $rows);
    }

    private function fetchPoSummary($db, string $dateFrom, string $dateTo, array $f): array
    {
        $q = $db->table('purchase_orders po')
            ->select('po.po_number, po.supplier_name as supplier, po.payment_due_date, po.status,
                      po.amount as total_amount, u.name as created_by, po.created_at, po.approved_at')
            ->join('users u', 'u.id = po.created_by', 'left');

        if ($dateFrom) $q->where('po.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo)   $q->where('po.created_at <=', $dateTo . ' 23:59:59');
        if (!empty($f['status'])) $q->where('po.status', $f['status']);

        $rows = $q->orderBy('po.created_at', 'DESC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['po_number'], $r['supplier'],
            $r['payment_due_date'] ?? '—',
            strtoupper((string)$r['status']),
            number_format((float)$r['total_amount'], 2),
            $r['created_by'] ?? 'System',
            $this->formatDate($r['created_at']),
            $this->formatDate($r['approved_at']),
        ], $rows);
    }

    private function fetchInventoryStock($db, string $dateFrom, string $dateTo, array $f): array
    {
        $q = $db->table('inventory_lines il')
            ->select('p.name as part_name, p.sku, p.type,
                      w.name as warehouse, wl.name as location,
                      SUM(il.quantity) as qty_on_hand,
                      SUM(il.consumed_qty) as consumed_qty,
                      (SUM(il.quantity) - SUM(il.consumed_qty)) as available,
                      p.min_stock_level')
            ->join('parts p', 'p.id = il.part_id')
            ->join('warehouses w', 'w.id = il.warehouse_id')
            ->join('warehouse_locations wl', 'wl.id = il.warehouse_location_id', 'left')
            ->groupBy(['il.part_id', 'il.warehouse_id', 'il.warehouse_location_id', 'p.name', 'p.sku', 'p.type', 'w.name', 'wl.name', 'p.min_stock_level']);

        if ($dateFrom) $q->where('il.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo)   $q->where('il.created_at <=', $dateTo . ' 23:59:59');
        if (!empty($f['warehouse_id'])) $q->where('il.warehouse_id', $f['warehouse_id']);

        $rows = $q->orderBy('p.name', 'ASC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['part_name'], $r['sku'], $r['type'],
            $r['warehouse'], $r['location'] ?? '—',
            (int)$r['qty_on_hand'], (int)$r['consumed_qty'], (int)$r['available'],
            $r['min_stock_level'],
            ((int)$r['available'] <= (int)$r['min_stock_level']) ? 'LOW STOCK' : 'OK',
        ], $rows);
    }

    private function fetchPriceList($db, string $dateFrom, string $dateTo, array $f): array
    {
        $q = $db->table('part_prices pp')
            ->select('p.name as part_name, p.sku, v.variant_name, v.variant_sku,
                      pp.selling_price, pp.min_selling_price, pp.notes, pp.updated_at')
            ->join('parts p', 'p.id = pp.part_id')
            ->join('part_variants v', 'v.id = pp.variant_id', 'left')
            ->join('part_categories c', 'c.id = p.category_id', 'left');

        if ($dateFrom) $q->where('pp.updated_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo)   $q->where('pp.updated_at <=', $dateTo . ' 23:59:59');
        if (!empty($f['category_id'])) $q->where('p.category_id', $f['category_id']);

        $rows = $q->orderBy('p.name', 'ASC')->orderBy('v.variant_name', 'ASC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['part_name'], $r['sku'],
            $r['variant_name'] ?? '—', $r['variant_sku'] ?? '—',
            number_format((float)$r['selling_price'], 2),
            $r['min_selling_price'] !== null ? number_format((float)$r['min_selling_price'], 2) : '—',
            $r['notes'] ?? '',
            $this->formatDate($r['updated_at']),
        ], $rows);
    }

    private function fetchCustomerLedger($db, string $dateFrom, string $dateTo): array
    {
        // Mirrors /admin/cash/ledger — queries cash_transactions with account & user joins
        $q = $db->table('cash_transactions ct')
            ->select('ct.transaction_number, ct.created_at, ct.type,
                      fa.name as from_account, ta.name as to_account,
                      ct.amount, ct.from_account_id, ct.to_account_id,
                      ct.reference_number, ct.remarks,
                      u.name  as created_by_name,
                      ap.name as approved_by_name,
                      ct.status')
            ->join('cash_accounts fa', 'fa.id = ct.from_account_id', 'left')
            ->join('cash_accounts ta', 'ta.id = ct.to_account_id',   'left')
            ->join('users u',          'u.id  = ct.created_by',       'left')
            ->join('users ap',         'ap.id = ct.approved_by',      'left');

        if ($dateFrom) $q->where('ct.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo)   $q->where('ct.created_at <=', $dateTo   . ' 23:59:59');

        $rows = $q->orderBy('ct.id', 'DESC')->get()->getResultArray();

        // Debit types (money IN to an account)
        $debitTypes  = ['deposit', 'income', 'initial_adjustment'];
        // Credit types (money OUT of an account)
        $creditTypes = ['withdrawal', 'expense', 'advance'];

        return array_map(function ($r) use ($debitTypes, $creditTypes) {
            $amount = (float)$r['amount'];
            $isDebit = in_array($r['type'], $debitTypes)
                || ($r['type'] === 'transfer'    && $r['to_account_id'])
                || ($r['type'] === 'adjustment'  && $r['to_account_id'] && !$r['from_account_id']);
            $isCredit = in_array($r['type'], $creditTypes)
                || ($r['type'] === 'transfer'    && $r['from_account_id'])
                || ($r['type'] === 'adjustment'  && $r['from_account_id'] && !$r['to_account_id']);

            $typeLabel = match($r['type']) {
                'deposit'            => 'Deposit',
                'withdrawal'         => 'Withdrawal',
                'transfer'           => 'Transfer',
                'income'             => 'Sales Payment',
                'expense'            => 'PO Payment',
                'advance'            => 'Cash Advance',
                'adjustment'         => 'Adjustment',
                'initial_adjustment' => 'Initial Adjustment',
                default              => ucfirst((string)$r['type']),
            };

            return [
                $r['transaction_number'],
                date('Y-m-d H:i', strtotime($r['created_at'])),
                $typeLabel,
                $r['from_account'] ?? '—',
                $r['to_account']   ?? '—',
                $isDebit  ? number_format($amount, 2) : '—',
                $isCredit ? number_format($amount, 2) : '—',
                $r['reference_number'] ?? '—',
                $r['remarks']          ?? '—',
                $r['created_by_name']  ?? '—',
                $r['approved_by_name'] ?? '—',
                strtoupper((string)$r['status']),
            ];
        }, $rows);
    }

    private function fetchCogsReport($db, string $dateFrom, string $dateTo, array $f): array
    {
        // Default to approved SOs only (can be overridden via ?status=all or ?status=cancelled etc.)
        $statusFilter = $f['status'] ?? 'approved';

        // Weighted Average Cost subquery from inventory_lines
        // Uses only positive quantities and positive acquisition_cost entries (ignores transfer-out rows)
        $wacSql = "
            SELECT part_id, variant_id,
                   SUM(quantity * acquisition_cost) / NULLIF(SUM(quantity), 0) AS weighted_avg_cost
            FROM inventory_lines
            WHERE acquisition_cost > 0 AND quantity > 0
            GROUP BY part_id, variant_id
        ";

        // CI4's query builder escapes <=> (NULL-safe equality) incorrectly, so we use raw SQL
        // with bound parameters for safety.
        $sql = "
            SELECT
                so.so_number,
                DATE(so.created_at)                                                          AS so_date,
                so.status                                                                    AS so_status,
                c.name                                                                       AS customer,
                p.name                                                                       AS part_name,
                p.sku,
                COALESCE(v.variant_name, '')                                                 AS variant,
                sol.quantity,
                sol.unit_price                                                               AS sell_price,
                sol.line_discount,
                sol.total_price                                                              AS net_revenue,
                COALESCE(wac.weighted_avg_cost, 0)                                          AS unit_cost,
                ROUND(sol.quantity * COALESCE(wac.weighted_avg_cost, 0), 2)                 AS cogs,
                ROUND(sol.total_price - sol.quantity * COALESCE(wac.weighted_avg_cost, 0), 2) AS gross_profit,
                CASE WHEN COALESCE(wac.weighted_avg_cost, 0) = 0 THEN 'MISSING' ELSE 'OK' END AS cost_flag
            FROM  sales_order_lines sol
            JOIN  sales_orders  so  ON so.id  = sol.so_id
            JOIN  customers     c   ON c.id   = so.customer_id
            JOIN  parts         p   ON p.id   = sol.part_id
            LEFT JOIN part_variants v ON v.id = sol.variant_id
            LEFT JOIN (
                SELECT part_id, variant_id,
                       SUM(quantity * acquisition_cost) / NULLIF(SUM(quantity), 0) AS weighted_avg_cost
                FROM   inventory_lines
                WHERE  acquisition_cost > 0 AND quantity > 0
                GROUP  BY part_id, variant_id
            ) wac ON wac.part_id = sol.part_id
                 AND wac.variant_id <=> sol.variant_id
            WHERE 1=1
        ";

        $binds = [];

        if ($dateFrom) {
            $sql    .= " AND so.created_at >= ?";
            $binds[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $sql    .= " AND so.created_at <= ?";
            $binds[] = $dateTo . ' 23:59:59';
        }
        if ($statusFilter && $statusFilter !== 'all') {
            $sql    .= " AND so.status = ?";
            $binds[] = $statusFilter;
        }

        $sql .= " ORDER BY so.created_at DESC, sol.id ASC";

        $rows = $db->query($sql, $binds)->getResultArray();

        // Post-filter by cost_flag if requested (calculated field, not filterable in SQL directly)
        if (!empty($f['cost_flag'])) {
            $flagFilter = strtoupper($f['cost_flag']);
            $rows = array_filter($rows, fn($r) => strtoupper($r['cost_flag']) === $flagFilter);
        }

        return array_map(function ($r) {
            $rev      = (float)$r['net_revenue'];
            $cogs     = (float)$r['cogs'];
            $gp       = (float)$r['gross_profit'];
            $margin   = ($rev > 0) ? round($gp / $rev * 100, 2) : 0;

            return [
                $r['so_number'],
                $r['so_date'],
                strtoupper((string)$r['so_status']),
                $r['customer'],
                $r['part_name'],
                $r['sku'],
                $r['variant'] ?: '—',
                (int)$r['quantity'],
                number_format((float)$r['sell_price'], 2),
                number_format((float)$r['line_discount'], 2),
                number_format($rev,  2),
                number_format((float)$r['unit_cost'], 4),   // 4 decimals — WAC can be fractional
                number_format($cogs, 2),
                number_format($gp,   2),
                $margin . '%',
                $r['cost_flag'],
            ];
        }, $rows);
    }
}
