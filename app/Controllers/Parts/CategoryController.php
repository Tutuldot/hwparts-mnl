<?php

namespace App\Controllers\Parts;

use App\Controllers\BaseController;
use App\Models\PartCategoryModel;
use App\Models\AuditLogModel;

class CategoryController extends BaseController
{
    public function index()
    {
        $model = new PartCategoryModel();
        $data  = [
            'pageTitle'  => 'Part Categories',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Categories', null]],
            'categories' => $model->orderBy('name')->findAll(),
        ];
        return view('layouts/main', $data + ['content' => view('categories/index', $data)]);
    }

    public function store()
    {
        $model = new PartCategoryModel();
        $rules = [
            'code' => 'required|max_length[10]|is_unique[part_categories.code]',
            'name' => 'required|max_length[100]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }
        $model->insert([
            'code'       => strtoupper($this->request->getPost('code')),
            'name'       => $this->request->getPost('name'),
            'description'=> $this->request->getPost('description'),
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new AuditLogModel())->log('categories', 'create', $model->insertID(), 'Created category: ' . $this->request->getPost('name'));
        return redirect()->to(base_url('categories'))->with('success', 'Category created.');
    }

    public function update(int $id)
    {
        $model = new PartCategoryModel();
        $rules = ["code" => "required|max_length[10]|is_unique[part_categories.code,id,{$id}]", 'name' => 'required'];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }
        $model->update($id, [
            'code' => strtoupper($this->request->getPost('code')),
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);
        return redirect()->to(base_url('categories'))->with('success', 'Category updated.');
    }

    public function toggle(int $id)
    {
        $model = new PartCategoryModel();
        $cat   = $model->find($id);
        if ($cat) $model->update($id, ['is_active' => $cat['is_active'] ? 0 : 1]);
        return redirect()->to(base_url('categories'))->with('success', 'Category status toggled.');
    }
}
