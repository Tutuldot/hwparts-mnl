<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryTransferLineModel extends Model
{
    protected $table         = 'inventory_transfer_lines';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['transfer_id', 'part_id', 'variant_id', 'part_type', 'quantity_requested', 'quantity_transferred', 'parts_detail_id', 'to_warehouse_location_id', 'status', 'transferred_at', 'transferred_by', 'remarks'];
    protected $useTimestamps  = false;

    public function getByTransfer(int $transferId): array
    {
        return $this->db->query("
            SELECT tl.*, p.name as part_name, p.sku, pv.variant_name,
                   pd.unique_qr_code, wl.name as to_location_name,
                   u.name as transferred_by_name
            FROM inventory_transfer_lines tl
            JOIN parts p ON p.id = tl.part_id
            LEFT JOIN part_variants pv ON pv.id = tl.variant_id
            LEFT JOIN parts_details pd ON pd.id = tl.parts_detail_id
            LEFT JOIN warehouse_locations wl ON wl.id = tl.to_warehouse_location_id
            LEFT JOIN users u ON u.id = tl.transferred_by
            WHERE tl.transfer_id = ?
            ORDER BY tl.id ASC
        ", [$transferId])->getResultArray();
    }

    public function recalcTransferStatus(int $transferId): string
    {
        $lines      = $this->where('transfer_id', $transferId)->findAll();
        $statuses   = array_column($lines, 'status');
        $hasPending = in_array('pending', $statuses) || in_array('partially_transferred', $statuses);
        $hasDone    = in_array('transferred', $statuses);

        if (!$hasPending && $hasDone) {
            return 'completed';
        }
        if ($hasPending && $hasDone) {
            return 'partially_transferred';
        }
        return 'in_transit';
    }
}
