<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SalesOrderModel;
use App\Models\SalesOrderLineModel;
use App\Models\CustomerModel;
use App\Models\AccountsReceivableModel;
use App\Models\PartModel;
use App\Models\PartPriceModel;
use App\Models\AuditLogModel;

class SalesOrderController extends BaseController
{
    protected SalesOrderModel     $soModel;
    protected SalesOrderLineModel $soLineModel;
    protected CustomerModel       $customerModel;
    protected AuditLogModel       $audit;

    public function __construct()
    {
        $this->soModel       = new SalesOrderModel();
        $this->soLineModel      = new SalesOrderLineModel();
        $this->customerModel = new CustomerModel();
        $this->audit         = new AuditLogModel();
    }

    public function index()
    {
        $orders = $this->soModel->getAllWithDetails();

        $data = [
            'pageTitle'  => 'Sales Orders',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Sales Orders', null]],
            'orders'     => $orders,
        ];
        return view('layouts/main', $data + ['content' => view('sales_order/index', $data)]);
    }

    public function create()
    {
        $customers = $this->customerModel->where('is_active', 1)->orderBy('name')->findAll();

        $inquiryId = $this->request->getGet('inquiry_id');
        $preselectedCustomerId = null;
        if ($inquiryId) {
            $inquiryModel = new \App\Models\InquiryModel();
            $inquiry = $inquiryModel->find($inquiryId);
            if ($inquiry) {
                $preselectedCustomerId = $inquiry['customer_id'];
            }
        }

        $data = [
            'pageTitle'  => 'New Sales Order (POS)',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Sales Orders', base_url('sales-orders')], ['POS Create', null]],
            'customers'  => $customers,
            'inquiry_id' => $inquiryId,
            'preselected_customer_id' => $preselectedCustomerId,
        ];
        return view('layouts/main', $data + ['content' => view('sales_order/create', $data)]);
    }

    public function store()
    {
        $customerId = $this->request->getPost('customer_id');
        $remarks    = $this->request->getPost('remarks');
        $linesJson  = $this->request->getPost('lines');
        $inquiryId  = $this->request->getPost('inquiry_id');

        if (empty($customerId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please select a customer.'])->setStatusCode(400);
        }

        $customer = $this->customerModel->find($customerId);
        if (!$customer || !$customer['is_active']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid or inactive customer selected.'])->setStatusCode(400);
        }

        $lines = json_decode($linesJson, true);
        if (empty($lines)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please add at least one item to the cart.'])->setStatusCode(400);
        }

        $totalAmount = 0.00;
        $validLines = [];

        foreach ($lines as $line) {
            $partId       = (int)($line['part_id'] ?? 0);
            $qty          = (int)($line['quantity'] ?? 0);
            $unitPrice    = (float)($line['unit_price'] ?? 0);
            $variantId    = !empty($line['variant_id']) ? (int)$line['variant_id'] : null;
            $discountType = in_array($line['discount_type'] ?? 'none', ['none','percent','amount']) ? $line['discount_type'] : 'none';
            $discountVal  = (float)($line['discount_value'] ?? 0);

            if ($partId <= 0 || $qty <= 0 || $unitPrice < 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid item quantity or price.'])->setStatusCode(400);
            }

            $grossLine = $qty * $unitPrice;
            $lineDiscount = 0.00;
            if ($discountType === 'percent' && $discountVal > 0) {
                $lineDiscount = round($grossLine * ($discountVal / 100), 2);
            } elseif ($discountType === 'amount' && $discountVal > 0) {
                $lineDiscount = min($discountVal * $qty, $grossLine); // cap at line gross
            }
            $lineTotal = $grossLine - $lineDiscount;
            $totalAmount += $lineTotal;

            $validLines[] = [
                'part_id'        => $partId,
                'variant_id'     => $variantId,
                'quantity'       => $qty,
                'unit_price'     => $unitPrice,
                'discount_type'  => $discountType,
                'discount_value' => $discountVal,
                'line_discount'  => $lineDiscount,
                'total_price'    => $lineTotal
            ];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $soNumber = $this->soModel->generateSoNumber();
        $soId = $this->soModel->insert([
            'so_number'   => $soNumber,
            'customer_id' => $customerId,
            'amount'      => $totalAmount,
            'status'      => 'draft',
            'remarks'     => $remarks ?: null,
            'created_by'  => session()->get('user_id'),
        ]);

        foreach ($validLines as $vl) {
            $this->soLineModel->insert([
                'so_id'          => $soId,
                'part_id'        => $vl['part_id'],
                'variant_id'     => $vl['variant_id'],
                'quantity'       => $vl['quantity'],
                'unit_price'     => $vl['unit_price'],
                'discount_type'  => $vl['discount_type'],
                'discount_value' => $vl['discount_value'],
                'line_discount'  => $vl['line_discount'],
                'total_price'    => $vl['total_price']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save sales order. Database transaction failed.'])->setStatusCode(500);
        }

        $this->audit->log('sales_orders', 'create', $soId, "Created Sales Order POS Draft: {$soNumber}");

        if ($inquiryId) {
            $inquiryModel = new \App\Models\InquiryModel();
            $inquiryModel->update($inquiryId, ['sales_order_id' => $soId]);
            $this->audit->log('inquiries', 'assign_so', $inquiryId, "Automatically assigned new Sales Order {$soNumber} to Inquiry #{$inquiryId}");
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Sales Order draft created successfully.',
            'redirect' => base_url("sales-orders/{$soId}")
        ]);
    }

    public function show(int $id)
    {
        $order = $this->soModel->getWithDetails($id);
        if (!$order) {
            return redirect()->to(base_url('sales-orders'))->with('error', 'Sales Order not found.');
        }

        $lines = $this->soLineModel->getBySo($id);

        $arRecord = null;
        if ($order['status'] === 'approved') {
            $arModel = new AccountsReceivableModel();
            $arRecord = $arModel->where('so_id', $id)->first();
        }

        $data = [
            'pageTitle'  => 'Sales Order: ' . $order['so_number'],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Sales Orders', base_url('sales-orders')], [$order['so_number'], null]],
            'order'      => $order,
            'lines'      => $lines,
            'arRecord'   => $arRecord,
        ];
        return view('layouts/main', $data + ['content' => view('sales_order/show', $data)]);
    }

    public function approve(int $id)
    {
        $order = $this->soModel->find($id);
        if (!$order) {
            return redirect()->to(base_url('sales-orders'))->with('error', 'Sales Order not found.');
        }

        if ($order['status'] !== 'draft') {
            return redirect()->to(base_url("sales-orders/{$id}"))->with('error', 'Only draft Sales Orders can be approved.');
        }

        $customer = $this->customerModel->find($order['customer_id']);
        if (!$customer) {
            return redirect()->to(base_url("sales-orders/{$id}"))->with('error', 'Customer associated with this order no longer exists.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Update SO status
        $this->soModel->update($id, [
            'status'      => 'approved',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        // 2. Automatically generate Accounts Receivable record
        $arModel = new AccountsReceivableModel();
        $invoiceNumber = $arModel->generateInvoiceNumber();
        
        $paymentTerms = (int)$customer['payment_terms'];
        $dueDate = date('Y-m-d', strtotime("+{$paymentTerms} days"));

        $arModel->insert([
            'so_id'            => $id,
            'customer_id'      => $order['customer_id'],
            'invoice_number'   => $invoiceNumber,
            'amount'           => $order['amount'],
            'amount_paid'      => 0.00,
            'due_date'         => $dueDate,
            'status'           => 'unpaid',
            'payment_reference'=> null,
            'payment_type'     => null,
            'proof_of_payment' => null,
            'paid_at'          => null,
            'paid_by'          => null,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(base_url("sales-orders/{$id}"))->with('error', 'Failed to approve Sales Order.');
        }

        // FIFO COGS: consume inventory batches per line (outside transaction to avoid lock issues)
        $priceModel = new PartPriceModel();
        $lines = $this->soLineModel->getBySo($id);
        foreach ($lines as $line) {
            $soLineId  = (int)$line['id'];
            $partId    = (int)$line['part_id'];
            $variantId = $line['variant_id'] ? (int)$line['variant_id'] : null;
            $qty       = (int)$line['quantity'];
            // Consume FIFO batches (non-fatal if insufficient stock recorded)
            try { $priceModel->consumeFIFO($partId, $variantId, $qty, $soLineId); } catch (\Throwable $e) { /* log silently */ }
        }

        $this->audit->log('sales_orders', 'approve', $id, "Approved Sales Order {$order['so_number']}. Generated AR Invoice {$invoiceNumber}.");

        return redirect()->to(base_url("sales-orders/{$id}"))->with('success', 'Sales Order approved successfully and Accounts Receivable record created.');
    }

    public function cancel(int $id)
    {
        $order = $this->soModel->find($id);
        if (!$order) {
            return redirect()->to(base_url('sales-orders'))->with('error', 'Sales Order not found.');
        }

        if ($order['status'] !== 'draft') {
            return redirect()->to(base_url("sales-orders/{$id}"))->with('error', 'Only draft Sales Orders can be cancelled.');
        }

        $this->soModel->update($id, [
            'status' => 'cancelled'
        ]);

        $this->audit->log('sales_orders', 'cancel', $id, "Cancelled Sales Order {$order['so_number']}");

        return redirect()->to(base_url("sales-orders/{$id}"))->with('success', 'Sales Order has been cancelled.');
    }

    public function ajaxSearchParts()
    {
        $q = trim($this->request->getGet('q') ?? '');
        if ($q === '') {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();

        // Query active parts matching name, sku, barcode_value
        $partsQuery = $db->table('parts p')
                         ->select('p.id as part_id, NULL as variant_id, p.name as part_name, NULL as variant_name, p.sku, p.barcode_value, p.type')
                         ->where('p.is_active', 1)
                         ->groupStart()
                             ->like('p.name', $q)
                             ->orLike('p.sku', $q)
                             ->orLike('p.barcode_value', $q)
                         ->groupEnd()
                         ->limit(10)
                         ->get()
                         ->getResultArray();

        // Query active part variants matching name, sku, barcode_value, parent name
        $variantsQuery = $db->table('part_variants pv')
                            ->select('p.id as part_id, pv.id as variant_id, p.name as part_name, pv.variant_name, pv.variant_sku as sku, pv.barcode_value, p.type')
                            ->join('parts p', 'p.id = pv.part_id')
                            ->where('p.is_active', 1)
                            ->where('pv.is_active', 1)
                            ->groupStart()
                                ->like('pv.variant_name', $q)
                                ->orLike('pv.variant_sku', $q)
                                ->orLike('pv.barcode_value', $q)
                                ->orLike('p.name', $q)
                            ->groupEnd()
                            ->limit(10)
                            ->get()
                            ->getResultArray();

        $merged = array_merge($partsQuery, $variantsQuery);

        $priceModel = new PartPriceModel();
        $results = [];
        foreach ($merged as $item) {
            $displayName = $item['part_name'];
            if (!empty($item['variant_name'])) {
                $displayName .= ' (' . $item['variant_name'] . ')';
            }
            $displayName .= ' [' . $item['sku'] . ']';

            $varId = $item['variant_id'] ? (int)$item['variant_id'] : null;
            $price = $priceModel->getPriceForPart((int)$item['part_id'], $varId);

            $results[] = [
                'part_id'         => (int)$item['part_id'],
                'variant_id'      => $varId,
                'part_name'       => $item['part_name'],
                'variant_name'    => $item['variant_name'],
                'sku'             => $item['sku'],
                'barcode_value'   => $item['barcode_value'],
                'type'            => $item['type'],
                'display_name'    => $displayName,
                'suggested_price' => $price ? (float)$price['selling_price'] : 0,
                'min_price'       => $price ? $price['min_selling_price'] : null,
            ];
        }

        return $this->response->setJSON($results);
    }
}
