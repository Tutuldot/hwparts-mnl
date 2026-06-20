<?php

namespace App\Controllers\Parts;

use App\Controllers\BaseController;
use App\Models\PartModel;
use App\Models\PartCategoryModel;
use App\Models\PartCarTagModel;
use App\Models\PartVariantModel;
use App\Models\PartPhotoModel;
use App\Models\PartPriceModel;
use App\Models\SupplierModel;
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
        $parts = $this->pm->getAllWithCategory();
        $photoModel = new PartPhotoModel();
        
        foreach ($parts as &$p) {
            $p['primary_photo'] = $photoModel->getPrimaryPhoto($p['id']);
        }

        $data = [
            'pageTitle'  => 'Parts',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', null]],
            'parts'      => $parts,
        ];
        return view('layouts/main', $data + ['content' => view('parts/index', $data)]);
    }

    public function create()
    {
        $catModel = new PartCategoryModel();
        $supModel = new SupplierModel();
        $data = [
            'pageTitle'  => 'Add Part',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', base_url('parts')], ['Add', null]],
            'categories' => $catModel->getActive(),
            'suppliers'  => $supModel->where('is_active', 1)->orderBy('name')->findAll(),
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
            if (!is_dir(dirname($qrPath))) {
                mkdir(dirname($qrPath), 0777, true);
            }
            (new QRCode($options))->render($qrValue, $qrPath);
            $qrImage = 'assets/qrcodes/' . $sku . '.png';
        } catch (\Throwable $e) {
            // QR generation failed silently
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

        // Sync suppliers
        $suppliers = $this->request->getPost('suppliers') ?? [];
        $this->pm->syncSuppliers($partId, $suppliers);

        // Upload photos
        $files = $this->request->getFiles();
        if (isset($files['photos'])) {
            $photoModel = new PartPhotoModel();
            $isFirst = true;
            foreach ($files['photos'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $uploadPath = FCPATH . 'uploads/parts/';
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }
                    $file->move($uploadPath, $newName);
                    $photoModel->insert([
                        'part_id'    => $partId,
                        'photo_path' => 'uploads/parts/' . $newName,
                        'is_primary' => $isFirst ? 1 : 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $isFirst = false;
                }
            }
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
        $photoModel   = new PartPhotoModel();
        $priceModel   = new PartPriceModel();

        $data = [
            'pageTitle'  => $part['sku'],
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', base_url('parts')], [$part['sku'], null]],
            'part'       => $part,
            'variants'   => $variantModel->getByPart($id, false),
            'carTags'    => $tagModel->getByPart($id),
            'stock'      => $this->pm->getStockByWarehouse($id),
            'photos'     => $photoModel->getByPart($id),
            'suppliers'  => $this->pm->getSuppliers($id),
            'prices'     => $priceModel->getPricesForPart($id),
        ];
        return view('layouts/main', $data + ['content' => view('parts/show', $data)]);
    }

    public function edit(int $id)
    {
        $part = $this->pm->getWithCategory($id);
        if (! $part) return redirect()->to(base_url('parts'))->with('error', 'Part not found.');
        
        $catModel      = new PartCategoryModel();
        $tagModel      = new PartCarTagModel();
        $supModel      = new SupplierModel();
        $photoModel    = new PartPhotoModel();
        $variantModel  = new PartVariantModel();
        $priceModel    = new PartPriceModel();

        $data = [
            'pageTitle'       => 'Edit ' . $part['sku'],
            'breadcrumb'      => [['HWParts MNL', base_url('dashboard')], ['Parts', base_url('parts')], [$part['sku'], base_url('parts/' . $id)], ['Edit', null]],
            'part'            => $part,
            'categories'      => $catModel->getActive(),
            'carTags'         => $tagModel->getByPart($id),
            'suppliers'       => $supModel->where('is_active', 1)->orderBy('name')->findAll(),
            'linkedSuppliers' => $this->pm->getSuppliers($id),
            'photos'          => $photoModel->getByPart($id),
            'variants'        => $variantModel->getByPart($id, false),
            'prices'          => $priceModel->getPricesForPart($id),
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

        // Sync suppliers
        $suppliers = $this->request->getPost('suppliers') ?? [];
        $this->pm->syncSuppliers($id, $suppliers);

        // Save pricing
        $priceData = $this->request->getPost('prices') ?? [];
        if (!empty($priceData)) {
            $priceModel = new PartPriceModel();
            foreach ($priceData as $priceRow) {
                $variantId  = !empty($priceRow['variant_id']) ? (int)$priceRow['variant_id'] : null;
                $selling    = (float)($priceRow['selling_price'] ?? 0);
                $minSelling = isset($priceRow['min_selling_price']) && $priceRow['min_selling_price'] !== '' ? (float)$priceRow['min_selling_price'] : null;
                $notes      = $priceRow['notes'] ?? null;
                $priceModel->upsertPrice($id, $variantId, $selling, $minSelling, $notes, session()->get('user_id'));
            }
        }

        // Upload photos
        $files = $this->request->getFiles();
        if (isset($files['photos'])) {
            $photoModel = new PartPhotoModel();
            // check if there's currently a primary photo
            $hasPrimary = $photoModel->getPrimaryPhoto($id) !== null;
            $isFirst = !$hasPrimary;
            foreach ($files['photos'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $uploadPath = FCPATH . 'uploads/parts/';
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }
                    $file->move($uploadPath, $newName);
                    $photoModel->insert([
                        'part_id'    => $id,
                        'photo_path' => 'uploads/parts/' . $newName,
                        'is_primary' => $isFirst ? 1 : 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $isFirst = false;
                }
            }
        }

        $this->audit->log('parts', 'update', $id, "Updated part: {$part['sku']}");
        return redirect()->to(base_url('parts/' . $id))->with('success', 'Part updated.');
    }

    public function toggle(int $id)
    {
        $part = $this->pm->find($id);
        if ($part) $this->pm->update($id, ['is_active' => $part['is_active'] ? 0 : 1]);
        return redirect()->to(base_url('parts'))->with('success', 'Part status updated.');
    }

    public function deletePhoto(int $partId, int $photoId)
    {
        $photoModel = new PartPhotoModel();
        $photo = $photoModel->find($photoId);
        if ($photo && $photo['part_id'] == $partId) {
            $filePath = FCPATH . $photo['photo_path'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            $photoModel->delete($photoId);

            // If we deleted the primary photo, assign primary status to another photo of this part
            if ($photo['is_primary']) {
                $next = $photoModel->where('part_id', $partId)->first();
                if ($next) {
                    $photoModel->update($next['id'], ['is_primary' => 1]);
                }
            }
            return redirect()->to(base_url("parts/{$partId}/edit"))->with('success', 'Photo deleted successfully.');
        }
        return redirect()->to(base_url("parts/{$partId}/edit"))->with('error', 'Photo not found.');
    }

    public function setPrimaryPhoto(int $partId, int $photoId)
    {
        $photoModel = new PartPhotoModel();
        $photo = $photoModel->find($photoId);
        if ($photo && $photo['part_id'] == $partId) {
            $photoModel->setPrimary($partId, $photoId);
            return redirect()->to(base_url("parts/{$partId}/edit"))->with('success', 'Primary photo updated.');
        }
        return redirect()->to(base_url("parts/{$partId}/edit"))->with('error', 'Photo not found.');
    }

    public function template()
    {
        $filename = "parts_import_template_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        fputcsv($output, [
            'SKU', 
            'Name', 
            'Category Code', 
            'Type', 
            'OEM', 
            'Brand', 
            'Description', 
            'Unit of Measure', 
            'Min Stock Level', 
            'Barcode Value'
        ]);
        
        // Example Row 1 (Auto SKU generation)
        fputcsv($output, [
            '', // Leave blank for auto generation
            'Spark Plug',
            'ENG', // Example category code
            'quantity',
            '0',
            'BOSCH',
            'Super plus spark plug replacement',
            'pcs',
            '10',
            'SPK-BOSCH-123'
        ]);

        // Example Row 2 (Custom SKU)
        fputcsv($output, [
            'HWP-ALT-CUSTOM-999',
            'Alternator Assembly',
            'ALT',
            'non_quantity',
            '1',
            'DENSO',
            'High output replacement alternator',
            'pcs',
            '1',
            ''
        ]);
        
        fclose($output);
        exit;
    }

    public function upload()
    {
        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Please upload a valid CSV file.');
        }

        $filePath = $file->getTempName();
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'Could not read the uploaded CSV file.');
        }

        // Get headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file is empty.');
        }

        // Map column indexes case-insensitively
        $headerMap = array_flip(array_map('strtolower', array_map('trim', $headers)));
        
        // Required mapping fields
        $reqFields = [
            'name' => ['name', 'part name', 'part_name'],
            'category_code' => ['category code', 'category_code', 'category'],
            'type' => ['type'],
            'oem' => ['oem'],
        ];

        $colIndexes = [];
        foreach ($reqFields as $key => $synonyms) {
            $found = false;
            foreach ($synonyms as $syn) {
                if (isset($headerMap[$syn])) {
                    $colIndexes[$key] = $headerMap[$syn];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                fclose($handle);
                return redirect()->back()->with('error', "Missing required column in CSV. We require: Name, Category Code, Type, OEM.");
            }
        }

        // Optional fields mapping
        $optFields = [
            'sku' => ['sku'],
            'brand' => ['brand'],
            'description' => ['description', 'desc'],
            'unit_of_measure' => ['unit of measure', 'unit_of_measure', 'uom'],
            'min_stock_level' => ['min stock level', 'min_stock_level', 'min_stock'],
            'barcode_value' => ['barcode value', 'barcode_value', 'barcode'],
        ];

        foreach ($optFields as $key => $synonyms) {
            $colIndexes[$key] = -1;
            foreach ($synonyms as $syn) {
                if (isset($headerMap[$syn])) {
                    $colIndexes[$key] = $headerMap[$syn];
                    break;
                }
            }
        }

        $catModel = new PartCategoryModel();
        // Load all active categories code mapped to id and values
        $allCats = $catModel->findAll();
        $categoriesByCode = [];
        foreach ($allCats as $cat) {
            $categoriesByCode[strtoupper(trim($cat['code']))] = $cat;
        }

        // Track rows, duplicate check containers
        $errors = [];
        $csvSkus = [];
        $csvNames = [];
        $rowsToInsert = [];
        $rowNum = 1; // row 1 was header

        while (($data = fgetcsv($handle)) !== false) {
            $rowNum++;
            // Skip empty rows
            if (empty($data) || (count($data) === 1 && $data[0] === null)) {
                continue;
            }

            // Extract fields based on mapped columns
            $sku = $colIndexes['sku'] !== -1 && isset($data[$colIndexes['sku']]) ? trim($data[$colIndexes['sku']]) : '';
            $name = isset($data[$colIndexes['name']]) ? trim($data[$colIndexes['name']]) : '';
            $categoryCode = strtoupper(isset($data[$colIndexes['category_code']]) ? trim($data[$colIndexes['category_code']]) : '');
            $type = strtolower(isset($data[$colIndexes['type']]) ? trim($data[$colIndexes['type']]) : '');
            $oem = isset($data[$colIndexes['oem']]) ? trim($data[$colIndexes['oem']]) : '0';
            $brand = $colIndexes['brand'] !== -1 && isset($data[$colIndexes['brand']]) ? trim($data[$colIndexes['brand']]) : '';
            $description = $colIndexes['description'] !== -1 && isset($data[$colIndexes['description']]) ? trim($data[$colIndexes['description']]) : '';
            $uom = $colIndexes['unit_of_measure'] !== -1 && isset($data[$colIndexes['unit_of_measure']]) ? trim($data[$colIndexes['unit_of_measure']]) : 'pcs';
            $minStock = $colIndexes['min_stock_level'] !== -1 && isset($data[$colIndexes['min_stock_level']]) ? trim($data[$colIndexes['min_stock_level']]) : '0';
            $barcode = $colIndexes['barcode_value'] !== -1 && isset($data[$colIndexes['barcode_value']]) ? trim($data[$colIndexes['barcode_value']]) : '';

            $rowErrors = [];

            // 1. Validate Name
            if (empty($name)) {
                $rowErrors[] = "Part Name is empty.";
            } elseif (strlen($name) < 2 || strlen($name) > 200) {
                $rowErrors[] = "Part Name must be between 2 and 200 characters.";
            } else {
                // Check duplicate in CSV
                $normName = strtoupper($name);
                if (isset($csvNames[$normName])) {
                    $rowErrors[] = "Part Name '{$name}' is duplicated in the CSV (see Row {$csvNames[$normName]}).";
                } else {
                    $csvNames[$normName] = $rowNum;
                }

                // Check duplicate in DB
                $existingName = $this->pm->where('name', $name)->first();
                if ($existingName) {
                    $rowErrors[] = "Part Name '{$name}' already exists in database (SKU: {$existingName['sku']}).";
                }
            }

            // 2. Validate Category Code
            if (empty($categoryCode)) {
                $rowErrors[] = "Category Code is empty.";
            } elseif (!isset($categoriesByCode[$categoryCode])) {
                $rowErrors[] = "Category Code '{$categoryCode}' does not exist in database.";
            } elseif ($categoriesByCode[$categoryCode]['is_active'] != 1) {
                $rowErrors[] = "Category '{$categoryCode}' is inactive.";
            }

            // 3. Validate Type
            if ($type !== 'quantity' && $type !== 'non_quantity') {
                $rowErrors[] = "Type must be either 'quantity' or 'non_quantity' (found '{$type}').";
            }

            // 4. Validate OEM
            if ($oem !== '1' && $oem !== '0') {
                $rowErrors[] = "OEM must be either '1' (Yes) or '0' (No) (found '{$oem}').";
            }

            // 5. Validate SKU if provided
            if (!empty($sku)) {
                if (!preg_match('/^[A-Za-z0-9\-\_]+$/', $sku)) {
                    $rowErrors[] = "SKU '{$sku}' contains invalid characters. Use alphanumeric, dashes, and underscores only.";
                } else {
                    $normSku = strtoupper($sku);
                    // Check duplicate in CSV
                    if (isset($csvSkus[$normSku])) {
                        $rowErrors[] = "SKU '{$sku}' is duplicated in the CSV (see Row {$csvSkus[$normSku]}).";
                    } else {
                        $csvSkus[$normSku] = $rowNum;
                    }

                    // Check duplicate in DB
                    $existingSku = $this->pm->where('sku', $sku)->first();
                    if ($existingSku) {
                        $rowErrors[] = "SKU '{$sku}' already exists in database.";
                    }
                }
            }

            if (!empty($rowErrors)) {
                $errors[$rowNum] = $rowErrors;
            } else {
                $rowsToInsert[] = [
                    'row_num'         => $rowNum,
                    'sku'             => $sku,
                    'name'            => $name,
                    'category_id'     => $categoriesByCode[$categoryCode]['id'],
                    'category_code'   => $categoryCode,
                    'type'            => $type,
                    'oem'             => (int)$oem,
                    'brand'           => !empty($brand) ? strtoupper($brand) : null,
                    'description'     => !empty($description) ? $description : null,
                    'unit_of_measure' => !empty($uom) ? $uom : 'pcs',
                    'min_stock_level' => (int)$minStock,
                    'barcode_value'   => !empty($barcode) ? $barcode : null
                ];
            }
        }
        fclose($handle);

        if (!empty($errors)) {
            $data = [
                'pageTitle'  => 'Import Errors',
                'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Parts', base_url('parts')], ['Import Errors', null]],
                'errors'     => $errors
            ];
            return view('layouts/main', $data + ['content' => view('parts/import_errors', $data)]);
        }

        if (empty($rowsToInsert)) {
            return redirect()->back()->with('error', 'No valid rows found in the CSV.');
        }

        // Insert parts in a transaction
        $this->pm->db->transStart();
        $importedCount = 0;
        foreach ($rowsToInsert as $row) {
            $sku = $row['sku'];
            if (empty($sku)) {
                $sku = $this->pm->generateSku($row['category_code']);
            }

            // Generate QR code
            $qrValue = $sku . '|' . $row['name'];
            $qrImage = null;
            try {
                $options = new QROptions(['outputType' => \chillerlan\QRCode\Output\QRImage::class, 'scale' => 6, 'imageBase64' => false]);
                $qrPath  = FCPATH . 'assets/qrcodes/' . $sku . '.png';
                if (!is_dir(dirname($qrPath))) {
                    mkdir(dirname($qrPath), 0777, true);
                }
                (new QRCode($options))->render($qrValue, $qrPath);
                $qrImage = 'assets/qrcodes/' . $sku . '.png';
            } catch (\Throwable $e) {
                // Ignore silently
            }

            $partId = $this->pm->insert([
                'sku'             => $sku,
                'name'            => $row['name'],
                'category_id'     => $row['category_id'],
                'type'            => $row['type'],
                'oem'             => $row['oem'],
                'brand'           => $row['brand'],
                'description'     => $row['description'],
                'unit_of_measure' => $row['unit_of_measure'],
                'min_stock_level' => $row['min_stock_level'],
                'barcode_value'   => $row['barcode_value'] ?: $sku,
                'qr_code_value'   => $qrValue,
                'qr_code_image'   => $qrImage,
                'is_active'       => 1,
                'created_by'      => session()->get('user_id'),
            ]);

            $this->audit->log('parts', 'create', $partId, "Bulk imported part: {$sku} — " . $row['name']);
            $importedCount++;
        }
        $this->pm->db->transComplete();

        if ($this->pm->db->transStatus() === false) {
            return redirect()->to(base_url('parts'))->with('error', 'Database transaction failed during bulk import.');
        }

        return redirect()->to(base_url('parts'))->with('success', "Imported {$importedCount} parts successfully.");
    }

    /**
     * AJAX: Return current selling price for a part/variant combo.
     * Used by the POS (sales order create) to auto-fill unit price.
     */
    public function ajaxGetPrice()
    {
        $partId    = (int)($this->request->getGet('part_id') ?? 0);
        $variantId = $this->request->getGet('variant_id');
        $variantId = ($variantId !== null && $variantId !== '') ? (int)$variantId : null;

        if (!$partId) {
            return $this->response->setJSON(['selling_price' => 0, 'min_selling_price' => null]);
        }

        $priceModel = new PartPriceModel();
        $price = $priceModel->getPriceForPart($partId, $variantId);

        return $this->response->setJSON([
            'selling_price'     => $price ? (float)$price['selling_price'] : 0,
            'min_selling_price' => $price ? $price['min_selling_price'] : null,
        ]);
    }
}
