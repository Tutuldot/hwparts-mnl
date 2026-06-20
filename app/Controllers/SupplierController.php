<?php

namespace App\Controllers;

use App\Models\SupplierModel;
use App\Models\SupplierContactModel;
use App\Models\AuditLogModel;

class SupplierController extends BaseController
{
    protected SupplierModel        $sm;
    protected SupplierContactModel $scm;
    protected AuditLogModel        $audit;

    public function __construct()
    {
        $this->sm    = new SupplierModel();
        $this->scm   = new SupplierContactModel();
        $this->audit = new AuditLogModel();
    }

    public function index()
    {
        $suppliers = $this->sm->orderBy('name', 'ASC')->findAll();

        $data = [
            'pageTitle'  => 'Suppliers',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Suppliers', null]],
            'suppliers'  => $suppliers,
        ];
        return view('layouts/main', $data + ['content' => view('supplier/index', $data)]);
    }

    public function create()
    {
        $data = [
            'pageTitle'  => 'Add Supplier',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Suppliers', base_url('suppliers')], ['Add', null]],
        ];
        return view('layouts/main', $data + ['content' => view('supplier/create', $data)]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[200]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // Clean emails separated by ;
        $rawEmails = $this->request->getPost('emails_for_notice');
        $emails = [];
        if ($rawEmails) {
            $parts = explode(';', $rawEmails);
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $trimmed;
                }
            }
        }
        $emailsStr = implode('; ', $emails);

        $supplierId = $this->sm->insert([
            'name'              => $this->request->getPost('name'),
            'emails_for_notice' => $emailsStr ?: null,
            'address'           => $this->request->getPost('address') ?: null,
            'tags'              => $this->request->getPost('tags') ?: null,
            'is_active'         => 1,
            'created_by'        => session()->get('user_id'),
        ]);

        // Sync contacts
        $contacts = json_decode($this->request->getPost('contacts') ?? '[]', true) ?? [];
        $this->scm->syncContacts($supplierId, $contacts);

        $this->audit->log('suppliers', 'create', $supplierId, "Created supplier: " . $this->request->getPost('name'));
        return redirect()->to(base_url('suppliers'))->with('success', 'Supplier created successfully.');
    }

    public function edit(int $id)
    {
        $supplier = $this->sm->find($id);
        if (! $supplier) return redirect()->to(base_url('suppliers'))->with('error', 'Supplier not found.');

        $data = [
            'pageTitle'  => 'Edit Supplier',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Suppliers', base_url('suppliers')], ['Edit', null]],
            'supplier'   => $supplier,
            'contacts'   => $this->scm->getBySupplier($id),
        ];
        return view('layouts/main', $data + ['content' => view('supplier/edit', $data)]);
    }

    public function update(int $id)
    {
        $supplier = $this->sm->find($id);
        if (! $supplier) return redirect()->to(base_url('suppliers'))->with('error', 'Supplier not found.');

        $rules = [
            'name' => 'required|min_length[2]|max_length[200]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $rawEmails = $this->request->getPost('emails_for_notice');
        $emails = [];
        if ($rawEmails) {
            $parts = explode(';', $rawEmails);
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $trimmed;
                }
            }
        }
        $emailsStr = implode('; ', $emails);

        $this->sm->update($id, [
            'name'              => $this->request->getPost('name'),
            'emails_for_notice' => $emailsStr ?: null,
            'address'           => $this->request->getPost('address') ?: null,
            'tags'              => $this->request->getPost('tags') ?: null,
        ]);

        // Sync contacts
        $contacts = json_decode($this->request->getPost('contacts') ?? '[]', true) ?? [];
        $this->scm->syncContacts($id, $contacts);

        $this->audit->log('suppliers', 'update', $id, "Updated supplier: " . $this->request->getPost('name'));
        return redirect()->to(base_url('suppliers'))->with('success', 'Supplier updated successfully.');
    }

    public function toggle(int $id)
    {
        $supplier = $this->sm->find($id);
        if ($supplier) {
            $newVal = $supplier['is_active'] ? 0 : 1;
            $this->sm->update($id, ['is_active' => $newVal]);
            $this->audit->log('suppliers', 'toggle', $id, "Toggled supplier active status: " . $supplier['name']);
        }
        return redirect()->to(base_url('suppliers'))->with('success', 'Supplier status updated.');
    }
}
