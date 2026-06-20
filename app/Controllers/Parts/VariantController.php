<?php

namespace App\Controllers\Parts;

use App\Controllers\BaseController;
use App\Models\PartVariantModel;
use App\Models\PartModel;
use App\Models\AuditLogModel;

class VariantController extends BaseController
{
    public function index(int $partId)
    {
        $partModel = new PartModel();
        $part = $partModel->getWithCategory($partId);
        if (! $part) return redirect()->to(base_url('parts'))->with('error', 'Part not found.');

        $variantModel = new PartVariantModel();
        $data = [
            'pageTitle'  => $part['sku'] . ' — Variants',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', base_url('parts')], [$part['sku'], base_url('parts/' . $partId)], ['Variants', null]],
            'part'       => $part,
            'variants'   => $variantModel->getByPart($partId, false),
        ];
        return view('layouts/main', $data + ['content' => view('parts/variants', $data)]);
    }

    public function store(int $partId)
    {
        $partModel    = new PartModel();
        $variantModel = new PartVariantModel();

        $part = $partModel->find($partId);
        if (! $part) return redirect()->to(base_url('parts'))->with('error', 'Part not found.');

        $variantSku = $variantModel->generateVariantSku($part['sku'], $partId);

        $id = $variantModel->insert([
            'part_id'          => $partId,
            'variant_name'     => $this->request->getPost('variant_name'),
            'variant_sku'      => $variantSku,
            'barcode_value'    => $this->request->getPost('barcode_value') ?: $variantSku,
            'additional_notes' => $this->request->getPost('additional_notes'),
            'is_active'        => 1,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        (new AuditLogModel())->log('variants', 'create', $id, "Added variant {$variantSku} to part {$part['sku']}");
        return redirect()->to(base_url("parts/{$partId}/variants"))->with('success', "Variant {$variantSku} added.");
    }

    public function update(int $variantId)
    {
        $variantModel = new PartVariantModel();
        $variant = $variantModel->find($variantId);
        if (! $variant) return redirect()->back()->with('error', 'Variant not found.');

        $variantModel->update($variantId, [
            'variant_name'     => $this->request->getPost('variant_name'),
            'barcode_value'    => $this->request->getPost('barcode_value'),
            'additional_notes' => $this->request->getPost('additional_notes'),
        ]);
        return redirect()->to(base_url("parts/{$variant['part_id']}/variants"))->with('success', 'Variant updated.');
    }

    public function toggle(int $variantId)
    {
        $variantModel = new PartVariantModel();
        $variant = $variantModel->find($variantId);
        if ($variant) $variantModel->update($variantId, ['is_active' => $variant['is_active'] ? 0 : 1]);
        return redirect()->to(base_url("parts/{$variant['part_id']}/variants"))->with('success', 'Variant status toggled.');
    }
}
