<?php

namespace App\Controllers\Transfer;

use App\Controllers\BaseController;
use App\Models\InventoryTransferModel;
use App\Models\InventoryTransferLineModel;
use App\Models\InventoryHeaderModel;
use App\Models\InventoryLineModel;
use App\Models\PartsDetailModel;
use App\Models\PartModel;
use App\Models\WarehouseModel;
use App\Models\WarehouseLocationModel;
use App\Models\AuditLogModel;

class TransferController extends BaseController
{
    protected InventoryTransferModel     $tm;
    protected InventoryTransferLineModel $tlm;
    protected AuditLogModel              $audit;

    public function __construct()
    {
        $this->tm    = new InventoryTransferModel();
        $this->tlm   = new InventoryTransferLineModel();
        $this->audit = new AuditLogModel();
    }

    public function index()
    {
        $data = [
            'pageTitle'  => 'Inventory Transfers',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Transfers', null]],
            'transfers'  => $this->tm->listWithWarehouses(),
        ];
        return view('layouts/main', $data + ['content' => view('transfer/index', $data)]);
    }

    public function create()
    {
        $data = [
            'pageTitle'  => 'New Transfer',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Transfers', base_url('transfers')], ['New', null]],
            'warehouses' => (new WarehouseModel())->getActive(),
            'parts'      => (new PartModel())->where('is_active', 1)->orderBy('name')->findAll(),
        ];
        return view('layouts/main', $data + ['content' => view('transfer/create', $data)]);
    }

    public function store()
    {
        $rules = [
            'from_warehouse_id' => 'required|integer',
            'to_warehouse_id'   => 'required|integer|differs[from_warehouse_id]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $transferNo = $this->tm->generateTransferNo();
        $transferId = $this->tm->insert([
            'transfer_no'       => $transferNo,
            'from_warehouse_id' => $this->request->getPost('from_warehouse_id'),
            'to_warehouse_id'   => $this->request->getPost('to_warehouse_id'),
            'transfer_date'     => $this->request->getPost('transfer_date') ?: date('Y-m-d'),
            'status'            => 'draft',
            'remarks'           => $this->request->getPost('remarks'),
            'created_by'        => session()->get('user_id'),
        ]);

        $lines = json_decode($this->request->getPost('lines'), true) ?? [];
        foreach ($lines as $line) {
            $this->tlm->insert([
                'transfer_id'         => $transferId,
                'part_id'             => $line['part_id'],
                'variant_id'          => $line['variant_id'] ?? null,
                'part_type'           => $line['part_type'] ?? 'quantity',
                'quantity_requested'  => ($line['part_type'] ?? 'quantity') === 'quantity' ? (int)($line['quantity'] ?? 0) : null,
                'parts_detail_id'     => $line['parts_detail_id'] ?? null,
                'status'              => 'pending',
            ]);
        }

        $this->audit->log('transfers', 'create', $transferId, "Created transfer {$transferNo}");
        return redirect()->to(base_url("transfers/{$transferId}"))->with('success', "Transfer {$transferNo} created.");
    }

    public function view(int $id)
    {
        $transfer = $this->tm->getWithWarehouses($id);
        if (! $transfer) return redirect()->to(base_url('transfers'))->with('error', 'Transfer not found.');

        $data = [
            'pageTitle'  => $transfer['transfer_no'],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Transfers', base_url('transfers')], [$transfer['transfer_no'], null]],
            'transfer'   => $transfer,
            'lines'      => $this->tlm->getByTransfer($id),
            'role'       => session()->get('user_role'),
            'toLocations'=> (new WarehouseLocationModel())->getByWarehouse($transfer['to_warehouse_id']),
        ];
        return view('layouts/main', $data + ['content' => view('transfer/view', $data)]);
    }

    public function submit(int $id)
    {
        $transfer = $this->tm->find($id);
        if (! $transfer || $transfer['status'] !== 'draft') return redirect()->back()->with('error', 'Only draft transfers can be submitted.');

        $this->tm->update($id, [
            'status'       => 'submitted',
            'submitted_by' => session()->get('user_id'),
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit->log('transfers', 'submit', $id, "Submitted transfer {$transfer['transfer_no']}");
        return redirect()->to(base_url("transfers/{$id}"))->with('success', 'Transfer submitted for approval.');
    }

    public function approve(int $id)
    {
        if (! in_array(session()->get('user_role'), ['admin', 'approver'])) {
            return redirect()->back()->with('error', 'Insufficient permissions.');
        }
        $transfer = $this->tm->find($id);
        if (! $transfer || $transfer['status'] !== 'submitted') return redirect()->back()->with('error', 'Transfer cannot be approved at this stage.');

        $this->tm->update($id, [
            'status'      => 'approved',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit->log('transfers', 'approve', $id, "Approved transfer {$transfer['transfer_no']}");
        return redirect()->to(base_url("transfers/{$id}"))->with('success', 'Transfer approved.');
    }

    public function reject(int $id)
    {
        if (! in_array(session()->get('user_role'), ['admin', 'approver'])) {
            return redirect()->back()->with('error', 'Insufficient permissions.');
        }
        $transfer = $this->tm->find($id);
        if (! $transfer || $transfer['status'] !== 'submitted') return redirect()->back()->with('error', 'Transfer cannot be rejected at this stage.');

        $this->tm->update($id, [
            'status'           => 'rejected',
            'rejected_by'      => session()->get('user_id'),
            'rejected_at'      => date('Y-m-d H:i:s'),
            'rejection_reason' => $this->request->getPost('reason'),
        ]);
        $this->audit->log('transfers', 'reject', $id, "Rejected transfer {$transfer['transfer_no']}");
        return redirect()->to(base_url("transfers/{$id}"))->with('success', 'Transfer rejected.');
    }

    public function markInTransit(int $id)
    {
        $transfer = $this->tm->find($id);
        if (! $transfer || $transfer['status'] !== 'approved') return redirect()->back()->with('error', 'Transfer must be approved first.');

        $this->tm->update($id, ['status' => 'in_transit']);
        $this->audit->log('transfers', 'in_transit', $id, "Marked transfer {$transfer['transfer_no']} as in transit");
        return redirect()->to(base_url("transfers/{$id}"))->with('success', 'Transfer marked as in transit.');
    }

    /**
     * Record delivery for a single line (partial transfer support).
     */
    public function recordDelivery(int $transferId, int $lineId)
    {
        $transfer = $this->tm->find($transferId);
        if (! $transfer || ! in_array($transfer['status'], ['in_transit', 'partially_transferred'])) {
            return redirect()->back()->with('error', 'Transfer is not in transit.');
        }

        $line = $this->tlm->find($lineId);
        if (! $line || $line['transfer_id'] != $transferId || $line['status'] === 'transferred') {
            return redirect()->back()->with('error', 'Line not found or already fully transferred.');
        }

        $partModel    = new PartModel();
        $headerModel  = new InventoryHeaderModel();
        $lineModel    = new InventoryLineModel();
        $detailModel  = new PartsDetailModel();
        $part         = $partModel->find($line['part_id']);
        $toLocationId = $this->request->getPost('to_warehouse_location_id') ?: null;

        if ($part['type'] === 'quantity') {
            // Qty-based partial delivery
            $qtyDeliver = (int)$this->request->getPost('qty_deliver');
            $remaining  = ($line['quantity_requested'] ?? 0) - ($line['quantity_transferred'] ?? 0);

            if ($qtyDeliver < 1 || $qtyDeliver > $remaining) {
                return redirect()->back()->with('error', "Quantity must be between 1 and {$remaining}.");
            }

            $newQty    = ($line['quantity_transferred'] ?? 0) + $qtyDeliver;
            $lineFullyDone = $newQty >= $line['quantity_requested'];

            // Create inventory header for deduction (source warehouse)
            $refNoSource = $headerModel->generateReferenceNo();
            $headerIdSource = $headerModel->insert([
                'reference_no' => $refNoSource,
                'source'       => 'transfer',
                'transfer_id'  => $transferId,
                'warehouse_id' => $transfer['from_warehouse_id'],
                'remarks'      => "Transfer {$transfer['transfer_no']} line deduction",
                'created_by'   => session()->get('user_id'),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            // Deduct from source warehouse inventory
            \Config\Database::connect()->query("
                INSERT INTO inventory_lines (inventory_header_id, part_id, variant_id, warehouse_id, warehouse_location_id, transfer_id, quantity, acquisition_cost, total_cost, created_at)
                SELECT ?, il.part_id, il.variant_id, ?, ?, ?, -?, il.acquisition_cost, -(il.acquisition_cost * ?), NOW()
                FROM inventory_lines il WHERE il.part_id = ? AND il.warehouse_id = ? LIMIT 1
            ", [$headerIdSource, $transfer['from_warehouse_id'], null, $transferId, $qtyDeliver, $qtyDeliver, $line['part_id'], $transfer['from_warehouse_id']]);

            // Add to destination warehouse
            $refNo    = $headerModel->generateReferenceNo();
            $headerId = $headerModel->insert([
                'reference_no' => $refNo,
                'source'       => 'transfer',
                'transfer_id'  => $transferId,
                'warehouse_id' => $transfer['to_warehouse_id'],
                'remarks'      => "Transfer {$transfer['transfer_no']} line delivery",
                'created_by'   => session()->get('user_id'),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            $lineModel->insert([
                'inventory_header_id'   => $headerId,
                'part_id'               => $line['part_id'],
                'variant_id'            => $line['variant_id'] ?? null,
                'warehouse_id'          => $transfer['to_warehouse_id'],
                'warehouse_location_id' => $toLocationId,
                'transfer_id'           => $transferId,
                'quantity'              => $qtyDeliver,
                'acquisition_cost'      => 0,
                'total_cost'            => 0,
                'created_at'            => date('Y-m-d H:i:s'),
            ]);

            $this->tlm->update($lineId, [
                'quantity_transferred'     => $newQty,
                'to_warehouse_location_id' => $toLocationId,
                'status'                   => $lineFullyDone ? 'transferred' : 'partially_transferred',
                'transferred_at'           => date('Y-m-d H:i:s'),
                'transferred_by'           => session()->get('user_id'),
            ]);
        } else {
            // Non-qty: move the specific parts_detail unit
            $detailId = $line['parts_detail_id'];
            if (! $detailId) return redirect()->back()->with('error', 'No tracked unit associated with this line.');

            $detailModel->update($detailId, [
                'warehouse_id'          => $transfer['to_warehouse_id'],
                'warehouse_location_id' => $toLocationId,
                'updated_at'            => date('Y-m-d H:i:s'),
            ]);

            $this->tlm->update($lineId, [
                'to_warehouse_location_id' => $toLocationId,
                'status'                   => 'transferred',
                'transferred_at'           => date('Y-m-d H:i:s'),
                'transferred_by'           => session()->get('user_id'),
            ]);
        }

        // Recalculate transfer header status
        $newStatus = $this->tlm->recalcTransferStatus($transferId);
        $updates   = ['status' => $newStatus];
        if ($newStatus === 'completed') {
            $updates['completed_by'] = session()->get('user_id');
            $updates['completed_at'] = date('Y-m-d H:i:s');
        }
        $this->tm->update($transferId, $updates);

        $this->audit->log('transfers', 'deliver', $transferId, "Recorded delivery on transfer {$transfer['transfer_no']} line #{$lineId}");
        return redirect()->to(base_url("transfers/{$transferId}"))->with('success', 'Delivery recorded successfully.');
    }

    public function cancel(int $id)
    {
        $transfer = $this->tm->find($id);
        if (! $transfer || ! in_array($transfer['status'], ['draft', 'submitted'])) {
            return redirect()->back()->with('error', 'Only draft/submitted transfers can be cancelled.');
        }
        $this->tm->update($id, [
            'status'       => 'cancelled',
            'cancelled_by' => session()->get('user_id'),
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit->log('transfers', 'cancel', $id, "Cancelled transfer {$transfer['transfer_no']}");
        return redirect()->to(base_url("transfers/{$id}"))->with('success', 'Transfer cancelled.');
    }

    public function ajaxAvailableStock()
    {
        $partId      = (int)$this->request->getGet('part_id');
        $warehouseId = (int)$this->request->getGet('warehouse_id');
        $variantId   = $this->request->getGet('variant_id') ?: null;

        $lineModel = new InventoryLineModel();
        $stock     = $lineModel->getStockAtWarehouse($partId, $warehouseId, $variantId ? (int)$variantId : null);
        return $this->response->setJSON(['stock' => $stock]);
    }

    public function ajaxAvailableUnits()
    {
        $partId      = (int)$this->request->getGet('part_id');
        $warehouseId = (int)$this->request->getGet('warehouse_id');
        $variantId   = $this->request->getGet('variant_id') ?: null;

        $detailModel = new PartsDetailModel();
        $units       = $detailModel->getAvailableAtWarehouse($partId, $warehouseId, $variantId ? (int)$variantId : null);
        return $this->response->setJSON($units);
    }
}
