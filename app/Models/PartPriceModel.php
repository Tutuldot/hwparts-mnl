<?php

namespace App\Models;

use CodeIgniter\Model;

class PartPriceModel extends Model
{
    protected $table      = 'part_prices';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'part_id', 'variant_id', 'selling_price', 'min_selling_price',
        'notes', 'updated_by',
    ];

    /**
     * Get the current price for a specific part/variant combination.
     * Pass variant_id = null for the base part price.
     */
    public function getPriceForPart(int $partId, ?int $variantId = null): ?array
    {
        if ($variantId === null) {
            return $this->where('part_id', $partId)->where('variant_id IS NULL', null, false)->first();
        }
        return $this->where('part_id', $partId)->where('variant_id', $variantId)->first();
    }

    /**
     * Get all price rows for a part (base + all variants).
     * Returns array keyed by variant_id (null key = base price).
     */
    public function getPricesForPart(int $partId): array
    {
        $rows = $this->where('part_id', $partId)->findAll();
        $map = [];
        foreach ($rows as $row) {
            $key = $row['variant_id'] ?? 'base';
            $map[$key] = $row;
        }
        return $map;
    }

    /**
     * Insert or update a single price record.
     */
    public function upsertPrice(int $partId, ?int $variantId, float $sellingPrice, ?float $minPrice, ?string $notes, int $userId): bool
    {
        $existing = $this->getPriceForPart($partId, $variantId);

        $data = [
            'part_id'           => $partId,
            'variant_id'        => $variantId,
            'selling_price'     => $sellingPrice,
            'min_selling_price' => $minPrice,
            'notes'             => $notes ?: null,
            'updated_by'        => $userId,
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        return (bool)$this->insert($data);
    }

    /**
     * FIFO COGS consumption.
     * Allocates qty sold to oldest inventory_line batches (by created_at ASC).
     * Updates consumed_qty on each inventory_line.
     * Inserts records into so_line_cogs.
     *
     * Returns total acquisition cost consumed.
     */
    public function consumeFIFO(int $partId, ?int $variantId, int $qtyNeeded, int $soLineId): float
    {
        $db = \Config\Database::connect();

        // Fetch available inventory lines (oldest first), where remaining qty > 0
        $builder = $db->table('inventory_lines il')
            ->join('inventory_headers ih', 'ih.id = il.inventory_header_id')
            ->select('il.id, il.quantity, il.consumed_qty, il.acquisition_cost')
            ->where('il.part_id', $partId)
            ->orderBy('il.created_at', 'ASC');

        if ($variantId) {
            $builder->where('il.variant_id', $variantId);
        } else {
            $builder->where('il.variant_id IS NULL', null, false);
        }

        $batches = $builder->get()->getResultArray();

        $totalCogs = 0.0;
        $remaining = $qtyNeeded;

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $available = (int)$batch['quantity'] - (int)$batch['consumed_qty'];
            if ($available <= 0) continue;

            $take = min($available, $remaining);
            $costPerUnit = (float)$batch['acquisition_cost'];
            $lineCost = $take * $costPerUnit;

            // Update consumed_qty on inventory_line
            $db->table('inventory_lines')
                ->where('id', $batch['id'])
                ->update(['consumed_qty' => (int)$batch['consumed_qty'] + $take]);

            // Record COGS link
            $db->table('so_line_cogs')->insert([
                'so_line_id'        => $soLineId,
                'inventory_line_id' => $batch['id'],
                'qty_consumed'      => $take,
                'acquisition_cost'  => $costPerUnit,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            $totalCogs += $lineCost;
            $remaining -= $take;
        }

        return $totalCogs;
    }
}
