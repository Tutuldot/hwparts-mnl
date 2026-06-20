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
        'customer_ledger' => ['Customer Ledger',           'Per-customer SO count, billed amount, paid, and balance',   'fas fa-address-book',        'primary'],
    ];

    protected array $allRoles = ['admin', 'warehouse', 'purchasing', 'approver'];

    public function index()
    {
        $allowed = $this->getAllowedReports();
        $data = [
            'pageTitle'   => 'Reports',
            'breadcrumb'  => [['HWParts MNL', base_url('dashboard')], ['Reports', null]],
            'reportDefs'  => array_intersect_key($this->reportDefs, array_flip($allowed)),
            'isAdmin'     => session()->get('user_role') === 'admin',
        ];
        return view('layouts/main', $data + ['content' => view('reports/index', $data)]);
    }

    public function show(string $key)
    {
        $this->checkAccess($key);
        $def = $this->reportDefs[$key] ?? null;
        if (!$def) return redirect()->to(base_url('reports'))->with('error', 'Report not found.');

        $filters = $this->request->getGet() ?? [];
        $rows    = $this->fetchData($key, $filters);

        $data = [
            'pageTitle'  => $def[0],
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Reports', base_url('reports')], [$def[0], null]],
            'key'        => $key,
            'def'        => $def,
            'rows'       => $rows,
            'filters'    => $filters,
            'columns'    => $this->getColumns($key),
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
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];

        // Title row
        $sheet->setCellValue('A1', $def[0] . ' — Generated: ' . date('Y-m-d H:i'));
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns)) . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1e3a5f']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Filter info
        $filterStr = '';
        foreach ($filters as $fk => $fv) {
            if ($fv !== '') $filterStr .= ucfirst(str_replace('_', ' ', $fk)) . ': ' . $fv . '  ';
        }
        if ($filterStr) {
            $sheet->setCellValue('A2', 'Filters: ' . trim($filterStr));
            $sheet->mergeCells('A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns)) . '2');
        }

        // Column headers (row 3)
        $colIdx = 1;
        foreach ($columns as $colLabel) {
            $sheet->setCellValueByColumnAndRow($colIdx, 3, $colLabel);
            $sheet->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
            $colIdx++;
        }
        $sheet->getStyle('A3:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns)) . '3')
              ->applyFromArray($headerStyle);

        // Data rows
        $rowNum = 4;
        $altStyle = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F8FF']]];
        foreach ($rows as $row) {
            $colIdx = 1;
            foreach (array_values($row) as $val) {
                $sheet->setCellValueByColumnAndRow($colIdx, $rowNum, $val);
                $colIdx++;
            }
            if ($rowNum % 2 === 0) {
                $sheet->getStyle('A' . $rowNum . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns)) . $rowNum)
                      ->applyFromArray($altStyle);
            }
            $rowNum++;
        }

        // Row count
        $sheet->setCellValue('A' . $rowNum, 'Total Records: ' . count($rows));

        // Output
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
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Reports', base_url('reports')], ['Access Matrix', null]],
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
    //  Private helpers
    // ----------------------------------------------------------------

    private function getAllowedReports(): array
    {
        $role = session()->get('user_role') ?? 'warehouse';
        $db = \Config\Database::connect();
        $rows = $db->table('report_permissions')->where('role', $role)->get()->getResultArray();
        return array_column($rows, 'report_key');
    }

    private function checkAccess(string $key): void
    {
        $allowed = $this->getAllowedReports();
        if (!in_array($key, $allowed)) {
            redirect()->to(base_url('reports'))->with('error', 'You do not have access to this report.')->send();
            exit;
        }
    }

    private function getColumns(string $key): array
    {
        return match($key) {
            'sales_summary'   => ['SO #', 'Customer', 'Date', 'Status', 'Gross Amount', 'Total Discount', 'Net Amount', 'Approved By', 'Approved At'],
            'sales_by_part'   => ['Part Name', 'SKU', 'Variant', 'Total Qty Sold', 'Total Revenue (Net)', 'Avg Unit Price'],
            'ar_aging'        => ['Invoice #', 'Customer', 'SO #', 'Invoice Amount', 'Amount Paid', 'Balance', 'Due Date', 'Days Overdue', 'Aging Bucket', 'Status'],
            'ap_aging'        => ['AP #', 'PO #', 'Supplier', 'Amount', 'Amount Paid', 'Balance', 'Due Date', 'Days Overdue', 'Aging Bucket', 'Status'],
            'po_summary'      => ['PO #', 'Supplier', 'Payment Due', 'Status', 'Total Amount', 'Created By', 'Created At', 'Approved At'],
            'inventory_stock' => ['Part Name', 'SKU', 'Type', 'Warehouse', 'Location', 'Qty On Hand', 'Consumed Qty', 'Available', 'Min Stock', 'Status'],
            'price_list'      => ['Part Name', 'SKU', 'Variant', 'Variant SKU', 'Selling Price', 'Min Selling Price', 'Notes', 'Last Updated'],
            'customer_ledger' => ['Customer', 'Type', 'Company', 'Payment Terms', 'SO Count', 'Total Billed', 'Total Paid', 'Balance'],
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
            'ar_aging'        => $this->fetchArAging($db, $asOf),
            'ap_aging'        => $this->fetchApAging($db, $asOf),
            'po_summary'      => $this->fetchPoSummary($db, $dateFrom, $dateTo, $filters),
            'inventory_stock' => $this->fetchInventoryStock($db, $filters),
            'price_list'      => $this->fetchPriceList($db, $filters),
            'customer_ledger' => $this->fetchCustomerLedger($db, $dateFrom, $dateTo),
        };
    }

    private function fetchSalesSummary($db, string $dateFrom, string $dateTo, array $f): array
    {
        $q = $db->table('sales_orders so')
            ->select('so.so_number, c.name as customer, so.created_at, so.status,
                      SUM(sol.quantity * sol.unit_price) as gross_amount,
                      SUM(sol.line_discount) as total_discount,
                      so.amount as net_amount,
                      u.name as approved_by, so.approved_at')
            ->join('customers c', 'c.id = so.customer_id')
            ->join('sales_order_lines sol', 'sol.so_id = so.id', 'left')
            ->join('users u', 'u.id = so.approved_by', 'left')
            ->groupBy('so.id');

        if ($dateFrom) $q->where('DATE(so.created_at) >=', $dateFrom);
        if ($dateTo)   $q->where('DATE(so.created_at) <=', $dateTo);
        if (!empty($f['status'])) $q->where('so.status', $f['status']);
        if (!empty($f['customer_id'])) $q->where('so.customer_id', $f['customer_id']);

        $rows = $q->orderBy('so.created_at', 'DESC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['so_number'],
            $r['customer'],
            $r['created_at'] ? date('Y-m-d', strtotime($r['created_at'])) : '',
            strtoupper($r['status']),
            number_format((float)$r['gross_amount'], 2),
            number_format((float)$r['total_discount'], 2),
            number_format((float)$r['net_amount'], 2),
            $r['approved_by'] ?? '',
            $r['approved_at'] ? date('Y-m-d', strtotime($r['approved_at'])) : '',
        ], $rows);
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
            ->where('so.status', 'approved')
            ->groupBy('sol.part_id, sol.variant_id');

        if ($dateFrom) $q->where('DATE(so.approved_at) >=', $dateFrom);
        if ($dateTo)   $q->where('DATE(so.approved_at) <=', $dateTo);

        $rows = $q->orderBy('total_revenue', 'DESC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['part_name'], $r['sku'], $r['variant_name'] ?? '—',
            $r['total_qty'],
            number_format((float)$r['total_revenue'], 2),
            number_format((float)$r['avg_price'], 2),
        ], $rows);
    }

    private function fetchArAging($db, string $asOf): array
    {
        $rows = $db->table('accounts_receivable ar')
            ->select('ar.invoice_number, c.name as customer, so.so_number,
                      ar.amount, ar.amount_paid, ar.due_date, ar.status')
            ->join('customers c', 'c.id = ar.customer_id')
            ->join('sales_orders so', 'so.id = ar.so_id')
            ->orderBy('ar.due_date', 'ASC')
            ->get()->getResultArray();

        return array_map(function($r) use ($asOf) {
            $balance = (float)$r['amount'] - (float)($r['amount_paid'] ?? 0);
            $due = $r['due_date'] ?? $asOf;
            $days = $r['status'] === 'paid' ? 0 : max(0, (int)((strtotime($asOf) - strtotime($due)) / 86400));
            $bucket = $days === 0 ? 'Current' : ($days <= 30 ? '1-30 days' : ($days <= 60 ? '31-60 days' : ($days <= 90 ? '61-90 days' : '90+ days')));
            return [
                $r['invoice_number'], $r['customer'], $r['so_number'],
                number_format((float)$r['amount'], 2),
                number_format((float)($r['amount_paid'] ?? 0), 2),
                number_format($balance, 2),
                $due, $days, $bucket, strtoupper($r['status'])
            ];
        }, $rows);
    }

    private function fetchApAging($db, string $asOf): array
    {
        $rows = $db->table('accounts_payable ap')
            ->select('ap.id, po.po_number, s.name as supplier,
                      ap.amount, ap.amount_paid, ap.due_date, ap.status')
            ->join('purchase_orders po', 'po.id = ap.po_id')
            ->join('suppliers s', 's.id = ap.supplier_id')
            ->orderBy('ap.due_date', 'ASC')
            ->get()->getResultArray();

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
                $due, $days, $bucket, strtoupper($r['status'])
            ];
        }, $rows);
    }

    private function fetchPoSummary($db, string $dateFrom, string $dateTo, array $f): array
    {
        $q = $db->table('purchase_orders po')
            ->select('po.po_number, s.name as supplier, po.payment_due_date, po.status,
                      po.total_amount, u.name as created_by, po.created_at, po.approved_at')
            ->join('suppliers s', 's.id = po.supplier_id')
            ->join('users u', 'u.id = po.created_by', 'left');

        if ($dateFrom) $q->where('DATE(po.created_at) >=', $dateFrom);
        if ($dateTo)   $q->where('DATE(po.created_at) <=', $dateTo);
        if (!empty($f['status'])) $q->where('po.status', $f['status']);

        $rows = $q->orderBy('po.created_at', 'DESC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['po_number'], $r['supplier'],
            $r['payment_due_date'] ?? '',
            strtoupper($r['status']),
            number_format((float)$r['total_amount'], 2),
            $r['created_by'] ?? '',
            $r['created_at'] ? date('Y-m-d', strtotime($r['created_at'])) : '',
            $r['approved_at'] ? date('Y-m-d', strtotime($r['approved_at'])) : '',
        ], $rows);
    }

    private function fetchInventoryStock($db, array $f): array
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
            ->groupBy('il.part_id, il.warehouse_id');

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

    private function fetchPriceList($db, array $f): array
    {
        $q = $db->table('part_prices pp')
            ->select('p.name as part_name, p.sku, v.variant_name, v.variant_sku,
                      pp.selling_price, pp.min_selling_price, pp.notes, pp.updated_at')
            ->join('parts p', 'p.id = pp.part_id')
            ->join('part_variants v', 'v.id = pp.variant_id', 'left')
            ->join('part_categories c', 'c.id = p.category_id', 'left');

        if (!empty($f['category_id'])) $q->where('p.category_id', $f['category_id']);

        $rows = $q->orderBy('p.name', 'ASC')->orderBy('v.variant_name', 'ASC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['part_name'], $r['sku'],
            $r['variant_name'] ?? '—', $r['variant_sku'] ?? '—',
            number_format((float)$r['selling_price'], 2),
            $r['min_selling_price'] !== null ? number_format((float)$r['min_selling_price'], 2) : '—',
            $r['notes'] ?? '',
            $r['updated_at'] ? date('Y-m-d', strtotime($r['updated_at'])) : '',
        ], $rows);
    }

    private function fetchCustomerLedger($db, string $dateFrom, string $dateTo): array
    {
        $q = $db->table('customers c')
            ->select('c.name, c.type, c.company_name, c.payment_terms,
                      COUNT(DISTINCT so.id) as so_count,
                      COALESCE(SUM(so.amount), 0) as total_billed,
                      COALESCE(SUM(ar.amount_paid), 0) as total_paid')
            ->join('sales_orders so', 'so.customer_id = c.id AND so.status = "approved"', 'left')
            ->join('accounts_receivable ar', 'ar.so_id = so.id', 'left')
            ->groupBy('c.id');

        if ($dateFrom) $q->where('DATE(so.created_at) >=', $dateFrom);
        if ($dateTo)   $q->where('DATE(so.created_at) <=', $dateTo);

        $rows = $q->orderBy('c.name', 'ASC')->get()->getResultArray();

        return array_map(fn($r) => [
            $r['name'], ucfirst($r['type']),
            $r['company_name'] ?? '—',
            $r['payment_terms'] . ' days',
            (int)$r['so_count'],
            number_format((float)$r['total_billed'], 2),
            number_format((float)$r['total_paid'], 2),
            number_format((float)$r['total_billed'] - (float)$r['total_paid'], 2),
        ], $rows);
    }
}
