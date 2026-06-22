<?php

namespace App\Controllers\PurchaseOrder;

use App\Controllers\BaseController;
use App\Models\PurchaseOrderModel;
use App\Models\POLineModel;
use App\Models\POApprovalModel;
use App\Models\InventoryHeaderModel;
use App\Models\InventoryLineModel;
use App\Models\PartsDetailModel;
use App\Models\PartModel;
use App\Models\WarehouseModel;
use App\Models\WarehouseLocationModel;
use App\Models\AuditLogModel;

class POController extends BaseController
{
    protected PurchaseOrderModel $poModel;
    protected POLineModel        $lineModel;
    protected POApprovalModel    $approvalModel;
    protected AuditLogModel      $audit;

    public function __construct()
    {
        $this->poModel       = new PurchaseOrderModel();
        $this->lineModel     = new POLineModel();
        $this->approvalModel = new POApprovalModel();
        $this->audit         = new AuditLogModel();
    }

    public function index()
    {
        $pos = $this->poModel->db->query("
            SELECT po.*, u.name as created_by_name
            FROM purchase_orders po JOIN users u ON u.id = po.created_by
            ORDER BY po.id DESC
        ")->getResultArray();

        $data = [
            'pageTitle'  => 'Purchase Orders',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Purchase Orders', null]],
            'pos'        => $pos,
        ];
        return view('layouts/main', $data + ['content' => view('purchase_order/index', $data)]);
    }

    public function create()
    {
        $data = [
            'pageTitle'  => 'New Purchase Order',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Purchase Orders', base_url('purchase-orders')], ['New', null]],
            'parts'      => (new PartModel())->where('is_active', 1)->orderBy('name')->findAll(),
            'suppliers'  => (new \App\Models\SupplierModel())->getActive(),
        ];
        return view('layouts/main', $data + ['content' => view('purchase_order/create', $data)]);
    }

    public function store()
    {
        $rules = [
            'supplier_name'    => 'required|max_length[200]',
            'payment_type'     => 'required',
            'payment_due_date' => 'required|valid_date[Y-m-d]'
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $poNumber = $this->poModel->generatePoNumber();
        $lines    = json_decode($this->request->getPost('lines'), true) ?? [];
        $amount   = array_sum(array_column($lines, 'total_cost'));

        // Handle payment proof upload
        $proofFile = $this->request->getFile('proof_of_payment');
        $proofPath = null;
        if ($proofFile && $proofFile->isValid() && ! $proofFile->hasMoved()) {
            $newName  = $proofFile->getRandomName();
            $proofFile->move(FCPATH . 'assets/uploads', $newName);
            $proofPath = 'assets/uploads/' . $newName;
        }

        $poId = $this->poModel->insert([
            'po_number'        => $poNumber,
            'supplier_name'    => $this->request->getPost('supplier_name'),
            'payment_type'     => $this->request->getPost('payment_type'),
            'payment_due_date' => $this->request->getPost('payment_due_date') ?: date('Y-m-d'),
            'proof_of_payment' => $proofPath,
            'amount'           => $amount,
            'status'           => 'draft',
            'remarks'          => $this->request->getPost('remarks'),
            'created_by'       => session()->get('user_id'),
        ]);

        foreach ($lines as $line) {
            $this->lineModel->insert([
                'po_id'            => $poId,
                'part_id'          => $line['part_id'],
                'variant_id'       => $line['variant_id'] ?? null,
                'quantity_ordered'  => (int)$line['quantity'],
                'unit_cost'        => (float)$line['unit_cost'],
                'total_cost'       => (float)$line['total_cost'],
            ]);
        }

        $this->approvalModel->log($poId, 'created', session()->get('user_id'));
        $this->audit->log('purchase_orders', 'create', $poId, "Created PO {$poNumber}");
        return redirect()->to(base_url("purchase-orders/{$poId}"))->with('success', "Purchase Order {$poNumber} created.");
    }

    public function view(int $id)
    {
        $po = $this->poModel->getWithDetails($id);
        if (! $po) return redirect()->to(base_url('purchase-orders'))->with('error', 'PO not found.');

        $apModel = new \App\Models\AccountsPayableModel();
        $ap = $apModel->where('po_id', $id)->first();

        $data = [
            'pageTitle'  => $po['po_number'],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Purchase Orders', base_url('purchase-orders')], [$po['po_number'], null]],
            'po'         => $po,
            'lines'      => $this->lineModel->getByPo($id),
            'approvals'  => $this->approvalModel->getByPo($id),
            'role'       => session()->get('user_role'),
            'ap'         => $ap,
        ];
        return view('layouts/main', $data + ['content' => view('purchase_order/view', $data)]);
    }

    public function edit(int $id)
    {
        $po = $this->poModel->find($id);
        if (! $po || $po['status'] !== 'draft') return redirect()->to(base_url("purchase-orders/{$id}"))->with('error', 'Only draft POs can be edited.');
        $data = [
            'pageTitle'  => 'Edit ' . $po['po_number'],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Purchase Orders', base_url('purchase-orders')], [$po['po_number'], base_url("purchase-orders/{$id}")], ['Edit', null]],
            'po'         => $po,
            'lines'      => $this->lineModel->getByPo($id),
            'parts'      => (new PartModel())->where('is_active', 1)->orderBy('name')->findAll(),
            'suppliers'  => (new \App\Models\SupplierModel())->getActive(),
        ];
        return view('layouts/main', $data + ['content' => view('purchase_order/edit', $data)]);
    }

    public function update(int $id)
    {
        $po = $this->poModel->find($id);
        if (! $po || $po['status'] !== 'draft') return redirect()->back()->with('error', 'Cannot edit this PO.');

        $rules = [
            'supplier_name'    => 'required|max_length[200]',
            'payment_type'     => 'required',
            'payment_due_date' => 'required|valid_date[Y-m-d]'
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $lines  = json_decode($this->request->getPost('lines'), true) ?? [];
        $amount = array_sum(array_column($lines, 'total_cost'));

        $this->poModel->update($id, [
            'supplier_name'    => $this->request->getPost('supplier_name'),
            'payment_type'     => $this->request->getPost('payment_type'),
            'payment_due_date' => $this->request->getPost('payment_due_date') ?: date('Y-m-d'),
            'amount'           => $amount,
            'remarks'          => $this->request->getPost('remarks'),
        ]);

        // Replace lines
        $this->lineModel->where('po_id', $id)->delete();
        foreach ($lines as $line) {
            $this->lineModel->insert([
                'po_id'           => $id,
                'part_id'         => $line['part_id'],
                'variant_id'      => $line['variant_id'] ?? null,
                'quantity_ordered' => (int)$line['quantity'],
                'unit_cost'       => (float)$line['unit_cost'],
                'total_cost'      => (float)$line['total_cost'],
            ]);
        }

        $this->audit->log('purchase_orders', 'update', $id, "Updated PO {$po['po_number']}");
        return redirect()->to(base_url("purchase-orders/{$id}"))->with('success', 'PO updated.');
    }

    public function submit(int $id)
    {
        $po = $this->poModel->find($id);
        if (! $po || $po['status'] !== 'draft') return redirect()->back()->with('error', 'Only draft POs can be submitted.');
        $this->poModel->update($id, ['status' => 'submitted', 'submitted_at' => date('Y-m-d H:i:s')]);
        $this->approvalModel->log($id, 'submitted', session()->get('user_id'));
        $this->audit->log('purchase_orders', 'submit', $id, "Submitted PO {$po['po_number']}");
        return redirect()->to(base_url("purchase-orders/{$id}"))->with('success', 'PO submitted for approval.');
    }

    public function approve(int $id)
    {
        if (! in_array(session()->get('user_role'), ['admin', 'approver'])) {
            return redirect()->back()->with('error', 'Insufficient permissions.');
        }
        $po = $this->poModel->find($id);
        if (! $po || $po['status'] !== 'submitted') return redirect()->back()->with('error', 'PO cannot be approved at this stage.');

        $supplierModel = new \App\Models\SupplierModel();
        $supplier = $supplierModel->where('name', $po['supplier_name'])->first();
        if (! $supplier) {
            return redirect()->back()->with('error', 'Supplier "' . esc($po['supplier_name']) . '" not found in Suppliers list. Please create/edit the supplier before approving.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->poModel->update($id, [
            'status'      => 'approved',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $apModel = new \App\Models\AccountsPayableModel();
        $existingAp = $apModel->where('po_id', $po['id'])->first();
        if (! $existingAp) {
            $apModel->insert([
                'po_id'       => $po['id'],
                'supplier_id' => $supplier['id'],
                'amount'      => $po['amount'],
                'due_date'    => $po['payment_due_date'] ?: date('Y-m-d'),
                'status'      => 'unpaid',
            ]);
        }

        $notes = $this->request instanceof \CodeIgniter\HTTP\IncomingRequest ? $this->request->getPost('notes') : null;
        $this->approvalModel->log($id, 'approved', session()->get('user_id'), $notes);
        $this->audit->log('purchase_orders', 'approve', $id, "Approved PO {$po['po_number']}");

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to approve Purchase Order and create Accounts Payable.');
        }

        return redirect()->to(base_url("purchase-orders/{$id}"))->with('success', 'PO approved and Accounts Payable entry created.');
    }

    public function reject(int $id)
    {
        if (! in_array(session()->get('user_role'), ['admin', 'approver'])) {
            return redirect()->back()->with('error', 'Insufficient permissions.');
        }
        $po = $this->poModel->find($id);
        if (! $po || $po['status'] !== 'submitted') return redirect()->back()->with('error', 'PO cannot be rejected at this stage.');

        $this->poModel->update($id, [
            'status'           => 'rejected',
            'rejected_by'      => session()->get('user_id'),
            'rejected_at'      => date('Y-m-d H:i:s'),
            'rejection_reason' => $this->request->getPost('reason'),
        ]);
        $this->approvalModel->log($id, 'rejected', session()->get('user_id'), $this->request->getPost('reason'));
        $this->audit->log('purchase_orders', 'reject', $id, "Rejected PO {$po['po_number']}");
        return redirect()->to(base_url("purchase-orders/{$id}"))->with('success', 'PO rejected.');
    }

    public function receive(int $id)
    {
        $po = $this->poModel->find($id);
        if (! $po || ! in_array($po['status'], ['approved', 'partially_received'])) {
            return redirect()->back()->with('error', 'PO is not ready for receiving.');
        }
        $data = [
            'pageTitle'  => 'Receive ' . $po['po_number'],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Purchase Orders', base_url('purchase-orders')], [$po['po_number'], base_url("purchase-orders/{$id}")], ['Receive', null]],
            'po'         => $po,
            'lines'      => $this->lineModel->getByPo($id),
            'warehouses' => (new WarehouseModel())->getActive(),
        ];
        return view('layouts/main', $data + ['content' => view('purchase_order/receive', $data)]);
    }

    public function receiveLine(int $poId)
    {
        $po = $this->poModel->find($poId);
        if (! $po || ! in_array($po['status'], ['approved', 'partially_received'])) {
            return redirect()->back()->with('error', 'Cannot receive items for this PO.');
        }

        $lineId      = (int)$this->request->getPost('line_id');
        $qtyReceive  = (int)$this->request->getPost('qty_received');
        $warehouseId = (int)$this->request->getPost('warehouse_id');
        $locationId  = $this->request->getPost('warehouse_location_id') ?: null;

        $line = $this->lineModel->find($lineId);
        if (! $line || $line['is_received']) return redirect()->back()->with('error', 'Line not found or already received.');

        $remaining = $line['quantity_ordered'] - $line['quantity_received'];
        if ($qtyReceive > $remaining || $qtyReceive < 1) {
            return redirect()->back()->with('error', "Quantity must be between 1 and {$remaining}.");
        }

        $newQtyReceived = $line['quantity_received'] + $qtyReceive;
        $fullyReceived  = $newQtyReceived >= $line['quantity_ordered'];

        $this->lineModel->update($lineId, [
            'quantity_received' => $newQtyReceived,
            'is_received'       => $fullyReceived ? 1 : 0,
            'received_at'       => date('Y-m-d H:i:s'),
            'received_by'       => session()->get('user_id'),
        ]);

        // Create inventory entry for received items
        $headerModel = new InventoryHeaderModel();
        $lineModel2  = new InventoryLineModel();
        $detailModel = new PartsDetailModel();
        $partModel   = new PartModel();

        $refNo = $headerModel->generateReferenceNo();
        $headerId = $headerModel->insert([
            'reference_no' => $refNo,
            'source'       => 'purchase_order',
            'po_id'        => $poId,
            'warehouse_id' => $warehouseId,
            'remarks'      => "Received from PO {$po['po_number']}",
            'created_by'   => session()->get('user_id'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $part    = $partModel->find($line['part_id']);
        $invLine = $lineModel2->insert([
            'inventory_header_id'   => $headerId,
            'part_id'               => $line['part_id'],
            'variant_id'            => $line['variant_id'] ?? null,
            'warehouse_id'          => $warehouseId,
            'warehouse_location_id' => $locationId,
            'quantity'              => $qtyReceive,
            'acquisition_cost'      => $line['unit_cost'],
            'total_cost'            => $line['unit_cost'] * $qtyReceive,
            'created_at'            => date('Y-m-d H:i:s'),
        ]);

        if ($part['type'] === 'non_quantity') {
            for ($i = 1; $i <= $qtyReceive; $i++) {
                $detailModel->insert([
                    'inventory_header_id'   => $headerId,
                    'inventory_line_id'     => $invLine,
                    'part_id'               => $line['part_id'],
                    'variant_id'            => $line['variant_id'] ?? null,
                    'warehouse_id'          => $warehouseId,
                    'warehouse_location_id' => $locationId,
                    'unique_qr_code'        => $detailModel->generateUniqueQr($part['sku'], $i),
                    'status'                => 'available',
                    'consumed'              => 0,
                    'created_date'          => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Update PO status
        $allLines   = $this->lineModel->where('po_id', $poId)->findAll();
        $allDone    = count(array_filter($allLines, fn($l) => $l['is_received'])) === count($allLines);
        $anyDone    = count(array_filter($allLines, fn($l) => $l['quantity_received'] > 0)) > 0;
        $newStatus  = $allDone ? 'fully_received' : ($anyDone ? 'partially_received' : 'approved');
        $this->poModel->update($poId, ['status' => $newStatus]);

        $this->approvalModel->log($poId, 'received', session()->get('user_id'), "Received {$qtyReceive} units to warehouse #{$warehouseId}");
        $this->audit->log('purchase_orders', 'receive', $poId, "Received {$qtyReceive} units on PO {$po['po_number']}");

        if ($newStatus === 'fully_received') {
            return redirect()->to(base_url("purchase-orders/{$poId}"))->with('success', "All items fully received. Inventory updated as {$refNo}.");
        }
        return redirect()->to(base_url("purchase-orders/{$poId}/receive"))->with('success', "{$qtyReceive} unit(s) received to inventory as {$refNo}.");
    }

    public function cancel(int $id)
    {
        $po = $this->poModel->find($id);
        if (! $po || ! in_array($po['status'], ['draft', 'submitted'])) {
            return redirect()->back()->with('error', 'Only draft/submitted POs can be cancelled.');
        }
        $this->poModel->update($id, ['status' => 'cancelled']);
        $this->approvalModel->log($id, 'cancelled', session()->get('user_id'));
        $this->audit->log('purchase_orders', 'cancel', $id, "Cancelled PO {$po['po_number']}");
        return redirect()->to(base_url("purchase-orders/{$id}"))->with('success', 'PO cancelled.');
    }
}
