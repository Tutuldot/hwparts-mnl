<?php

namespace App\Controllers\Parts;

use App\Controllers\BaseController;
use App\Models\PartModel;
use App\Models\PartCategoryModel;
use App\Models\PartCarTagModel;
use App\Models\PartVariantModel;
use App\Models\AuditLogModel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class PartsController extends BaseController
{
    protected PartModel $pm;
    protected AuditLogModel $audit;

    public function __construct()
    {
        $this->pm    = new PartModel();
        $this->audit = new AuditLogModel();
    }

    public function index()
    {
        $data = [
            'pageTitle'  => 'Parts',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', null]],
            'parts'      => $this->pm->getAllWithCategory(),
        ];
        return view('layouts/main', $data + ['content' => view('parts/index', $data)]);
    }

    public function create()
    {
        $catModel = new PartCategoryModel();
        $data = [
            'pageTitle'  => 'Add Part',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', base_url('parts')], ['Add', null]],
            'categories' => $catModel->getActive(),
        ];
        return view('layouts/main', $data + ['content' => view('parts/create', $data)]);
    }

    public function store()
    {
        $catModel = new PartCategoryModel();
        $rules = [
            'name'        => 'required|min_length[2]|max_length[200]',
            'category_id' => 'required|integer',
            'type'        => 'required|in_list[quantity,non_quantity]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $cat = $catModel->find($this->request->getPost('category_id'));
        $sku = $this->pm->generateSku($cat['code']);

        // Generate QR code
        $qrValue = $sku . '|' . $this->request->getPost('name');
        $qrImage = null;
        try {
            $options = new QROptions(['outputType' => \chillerlan\QRCode\Output\QRImage::class, 'scale' => 6, 'imageBase64' => false]);
            $qrPath  = FCPATH . 'assets/qrcodes/' . $sku . '.png';
            (new QRCode($options))->render($qrValue, $qrPath);
            $qrImage = 'assets/qrcodes/' . $sku . '.png';
        } catch (\Throwable $e) {
            // QR generation failed silently; continue
        }

        $partId = $this->pm->insert([
            'sku'             => $sku,
            'name'            => $this->request->getPost('name'),
            'category_id'     => $this->request->getPost('category_id'),
            'type'            => $this->request->getPost('type'),
            'oem'             => $this->request->getPost('oem') ? 1 : 0,
            'brand'           => $this->request->getPost('brand') ? strtoupper(trim($this->request->getPost('brand'))) : null,
            'description'     => $this->request->getPost('description'),
            'unit_of_measure' => $this->request->getPost('unit_of_measure') ?: 'pcs',
            'min_stock_level' => (int)$this->request->getPost('min_stock_level'),
            'barcode_value'   => $this->request->getPost('barcode_value') ?: $sku,
            'qr_code_value'   => $qrValue,
            'qr_code_image'   => $qrImage,
            'is_active'       => 1,
            'created_by'      => session()->get('user_id'),
        ]);

        // Sync car tags
        $tags = json_decode($this->request->getPost('car_tags') ?? '[]', true);
        if (!empty($tags)) {
            (new PartCarTagModel())->syncTags($partId, $tags);
        }

        $this->audit->log('parts', 'create', $partId, "Created part: {$sku} — " . $this->request->getPost('name'));
        return redirect()->to(base_url('parts/' . $partId))->with('success', "Part {$sku} created successfully.");
    }

    public function show(int $id)
    {
        $part = $this->pm->getWithCategory($id);
        if (! $part) return redirect()->to(base_url('parts'))->with('error', 'Part not found.');

        $variantModel = new PartVariantModel();
        $tagModel     = new PartCarTagModel();

        $data = [
            'pageTitle'  => $part['sku'],
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', base_url('parts')], [$part['sku'], null]],
            'part'       => $part,
            'variants'   => $variantModel->getByPart($id, false),
            'carTags'    => $tagModel->getByPart($id),
            'stock'      => $this->pm->getStockByWarehouse($id),
        ];
        return view('layouts/main', $data + ['content' => view('parts/show', $data)]);
    }

    public function edit(int $id)
    {
        $part = $this->pm->getWithCategory($id);
        if (! $part) return redirect()->to(base_url('parts'))->with('error', 'Part not found.');
        $catModel = new PartCategoryModel();
        $tagModel = new PartCarTagModel();
        $data = [
            'pageTitle'  => 'Edit ' . $part['sku'],
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', base_url('parts')], [$part['sku'], base_url('parts/' . $id)], ['Edit', null]],
            'part'       => $part,
            'categories' => $catModel->getActive(),
            'carTags'    => $tagModel->getByPart($id),
        ];
        return view('layouts/main', $data + ['content' => view('parts/edit', $data)]);
    }

    public function update(int $id)
    {
        $part = $this->pm->find($id);
        if (! $part) return redirect()->to(base_url('parts'))->with('error', 'Part not found.');

        $rules = [
            'name'        => 'required|min_length[2]|max_length[200]',
            'category_id' => 'required|integer',
            'type'        => 'required|in_list[quantity,non_quantity]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->pm->update($id, [
            'name'            => $this->request->getPost('name'),
            'category_id'     => $this->request->getPost('category_id'),
            'type'            => $this->request->getPost('type'),
            'oem'             => $this->request->getPost('oem') ? 1 : 0,
            'brand'           => $this->request->getPost('brand') ? strtoupper(trim($this->request->getPost('brand'))) : null,
            'description'     => $this->request->getPost('description'),
            'unit_of_measure' => $this->request->getPost('unit_of_measure') ?: 'pcs',
            'min_stock_level' => (int)$this->request->getPost('min_stock_level'),
            'barcode_value'   => $this->request->getPost('barcode_value') ?: $part['sku'],
        ]);

        $tags = json_decode($this->request->getPost('car_tags') ?? '[]', true);
        (new PartCarTagModel())->syncTags($id, $tags);

        $this->audit->log('parts', 'update', $id, "Updated part: {$part['sku']}");
        return redirect()->to(base_url('parts/' . $id))->with('success', 'Part updated.');
    }

    public function toggle(int $id)
    {
        $part = $this->pm->find($id);
        if ($part) $this->pm->update($id, ['is_active' => $part['is_active'] ? 0 : 1]);
        return redirect()->to(base_url('parts'))->with('success', 'Part status updated.');
    }

    public function printLabel(int $id)
    {
        $part = $this->pm->getWithCategory($id);
        if (! $part) return redirect()->to(base_url('parts'))->with('error', 'Part not found.');
        $data = ['part' => $part];
        return view('parts/label_pdf', $data);
    }

    public function ajaxBrandSuggestions()
    {
        $term    = trim($this->request->getGet('term') ?? '');
        $results = $this->pm->getBrandSuggestions($term);
        return $this->response->setJSON(array_column($results, 'brand'));
    }

    public function ajaxSkuPreview()
    {
        $categoryId = $this->request->getGet('category_id');
        if (! $categoryId) return $this->response->setJSON(['sku' => '—']);
        $cat = (new PartCategoryModel())->find($categoryId);
        if (! $cat) return $this->response->setJSON(['sku' => '—']);
        $sku = $this->pm->generateSku($cat['code']);
        return $this->response->setJSON(['sku' => $sku]);
    }

    public function ajaxCarSuggestions()
    {
        $type = $this->request->getGet('type');
        $term = $this->request->getGet('term') ?? '';
        $tagModel = new PartCarTagModel();
        if ($type === 'brand') {
            $results = $tagModel->getBrandSuggestions($term);
            return $this->response->setJSON(array_column($results, 'brand'));
        }
        $brand   = $this->request->getGet('brand') ?? '';
        $results = $tagModel->getModelSuggestions($brand, $term);
        return $this->response->setJSON(array_column($results, 'model'));
    }
}
