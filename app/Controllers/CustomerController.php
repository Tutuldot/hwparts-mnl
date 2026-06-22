<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\CustomerContactModel;
use App\Models\SalesOrderModel;
use App\Models\SalesOrderLineModel;
use App\Models\AuditLogModel;

class CustomerController extends BaseController
{
    protected CustomerModel        $customerModel;
    protected CustomerContactModel $contactModel;
    protected AuditLogModel        $audit;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->contactModel  = new CustomerContactModel();
        $this->audit         = new AuditLogModel();
    }

    // ==========================================
    // PUBLIC CUSTOMER SELF-ENROLLMENT & PORTAL
    // ==========================================

    public function enroll()
    {
        if (session()->get('customer_id')) {
            return redirect()->to('/customer/orders');
        }
        return view('customer/enroll');
    }

    public function enrollPost()
    {
        $rules = [
            'type'             => 'required|in_list[individual,corporate]',
            'name'             => 'required|min_length[2]|max_length[200]',
            'billing_address'  => 'required|min_length[5]',
            'shipping_address' => 'required|min_length[5]',
            'username'         => 'required|alpha_dash|min_length[3]|max_length[50]|is_unique[customers.username]',
            'password'         => 'required|min_length[6]',
        ];

        $type = $this->request->getPost('type');
        if ($type === 'corporate') {
            $rules['company_name'] = 'required|min_length[2]|max_length[200]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // Validate contacts (need at least 1 contact)
        $contactsData = $this->request->getPost('contacts');
        if (!is_array($contactsData) || empty($contactsData)) {
            return redirect()->back()->withInput()->with('error', 'At least one contact method (email or mobile) is mandatory.');
        }

        $validContacts = [];
        foreach ($contactsData as $c) {
            $val = trim($c['value'] ?? '');
            $cType = $c['contact_type'] ?? '';
            if (empty($val)) continue;
            
            if ($cType === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->withInput()->with('error', 'Please enter a valid email address for all email contacts.');
            }
            if ($cType === 'mobile' && strlen($val) < 7) {
                return redirect()->back()->withInput()->with('error', 'Please enter a valid mobile number.');
            }
            $validContacts[] = [
                'contact_type' => $cType,
                'value'        => $val,
                'remarks'      => trim($c['remarks'] ?? '')
            ];
        }

        if (empty($validContacts)) {
            return redirect()->back()->withInput()->with('error', 'At least one valid contact method is required.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $customerId = $this->customerModel->insert([
            'type'             => $type,
            'name'             => $this->request->getPost('name'),
            'company_name'     => $type === 'corporate' ? $this->request->getPost('company_name') : null,
            'billing_address'  => $this->request->getPost('billing_address'),
            'shipping_address' => $this->request->getPost('shipping_address'),
            'tin'              => $this->request->getPost('tin') ?: null,
            'payment_terms'    => 0, // Default enrollment has cash terms (0 days)
            'username'         => $this->request->getPost('username'),
            'password'         => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'is_active'        => 1
        ]);

        foreach ($validContacts as $vc) {
            $this->contactModel->insert([
                'customer_id'  => $customerId,
                'contact_type' => $vc['contact_type'],
                'value'        => $vc['value'],
                'remarks'      => $vc['remarks']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'An error occurred during registration. Please try again.');
        }

        // Direct login
        session()->set([
            'customer_id'         => $customerId,
            'customer_name'       => $this->request->getPost('name'),
            'customer_logged_in'  => true
        ]);

        return redirect()->to('/customer/orders')->with('success', 'Registration completed successfully! Welcome to HW Trucks MNL!');
    }

    public function login()
    {
        if (session()->get('customer_id')) {
            return redirect()->to('/customer/orders');
        }
        return view('customer/login');
    }

    public function loginPost()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $customer = $this->customerModel->where('username', $this->request->getPost('username'))
                                         ->where('is_active', 1)
                                         ->first();

        if (!$customer || !password_verify($this->request->getPost('password'), $customer['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid username or password.');
        }

        session()->set([
            'customer_id'        => $customer['id'],
            'customer_name'      => $customer['name'],
            'customer_logged_in' => true
        ]);

        return redirect()->to('/customer/orders')->with('success', 'Logged in successfully.');
    }

    public function logout()
    {
        session()->remove(['customer_id', 'customer_name', 'customer_logged_in']);
        return redirect()->to('/customer/login')->with('success', 'Logged out.');
    }

    // ==========================================
    // CUSTOMER PORTAL VIEWS
    // ==========================================

    public function orders()
    {
        $customerId = session()->get('customer_id');
        $soModel = new SalesOrderModel();
        $orders = $soModel->where('customer_id', $customerId)->orderBy('id', 'DESC')->findAll();

        $data = [
            'pageTitle' => 'My Sales Orders',
            'orders'    => $orders
        ];
        return view('customer/orders', $data);
    }

    public function viewOrder(int $id)
    {
        $customerId = session()->get('customer_id');
        $soModel = new SalesOrderModel();
        $order = $soModel->getWithDetails($id);

        if (!$order || (int)$order['customer_id'] !== (int)$customerId) {
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        $lineModel = new SalesOrderLineModel();
        $lines = $lineModel->getBySo($id);

        $data = [
            'pageTitle' => 'Order ' . $order['so_number'],
            'order'     => $order,
            'lines'     => $lines
        ];
        return view('customer/show_order', $data);
    }

    // ==========================================
    // ADMIN END: CUSTOMER PROFILES CRUD
    // ==========================================

    public function index()
    {
        $customers = $this->customerModel->findAll();

        $data = [
            'pageTitle'  => 'Customers',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Customers', null]],
            'customers'  => $customers,
        ];
        return view('layouts/main', $data + ['content' => view('customers/index', $data)]);
    }

    public function create()
    {
        $data = [
            'pageTitle'  => 'Add Customer',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Customers', base_url('customers')], ['New', null]],
        ];
        return view('layouts/main', $data + ['content' => view('customers/create', $data)]);
    }

    public function store()
    {
        $rules = [
            'type'             => 'required|in_list[individual,corporate]',
            'name'             => 'required|min_length[2]|max_length[200]',
            'billing_address'  => 'required|min_length[5]',
            'shipping_address' => 'required|min_length[5]',
            'payment_terms'    => 'required|integer|greater_than_equal_to[0]',
            'username'         => 'required|alpha_dash|min_length[3]|max_length[50]|is_unique[customers.username]',
            'password'         => 'required|min_length[6]',
        ];

        $type = $this->request->getPost('type');
        if ($type === 'corporate') {
            $rules['company_name'] = 'required|min_length[2]|max_length[200]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // Validate contacts
        $contactsData = $this->request->getPost('contacts');
        if (!is_array($contactsData) || empty($contactsData)) {
            return redirect()->back()->withInput()->with('error', 'At least one contact method (email or mobile) is mandatory.');
        }

        $validContacts = [];
        foreach ($contactsData as $c) {
            $val = trim($c['value'] ?? '');
            $cType = $c['contact_type'] ?? '';
            if (empty($val)) continue;
            
            if ($cType === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->withInput()->with('error', 'Please enter a valid email address for all email contacts.');
            }
            if ($cType === 'mobile' && strlen($val) < 7) {
                return redirect()->back()->withInput()->with('error', 'Please enter a valid mobile number.');
            }
            $validContacts[] = [
                'contact_type' => $cType,
                'value'        => $val,
                'remarks'      => trim($c['remarks'] ?? '')
            ];
        }

        if (empty($validContacts)) {
            return redirect()->back()->withInput()->with('error', 'At least one valid contact method is required.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $customerId = $this->customerModel->insert([
            'type'             => $type,
            'name'             => $this->request->getPost('name'),
            'company_name'     => $type === 'corporate' ? $this->request->getPost('company_name') : null,
            'billing_address'  => $this->request->getPost('billing_address'),
            'shipping_address' => $this->request->getPost('shipping_address'),
            'tin'              => $this->request->getPost('tin') ?: null,
            'payment_terms'    => (int)$this->request->getPost('payment_terms'),
            'username'         => $this->request->getPost('username'),
            'password'         => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'is_active'        => 1
        ]);

        foreach ($validContacts as $vc) {
            $this->contactModel->insert([
                'customer_id'  => $customerId,
                'contact_type' => $vc['contact_type'],
                'value'        => $vc['value'],
                'remarks'      => $vc['remarks']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'An error occurred. Please try again.');
        }

        $this->audit->log('customers', 'create', $customerId, "Created customer profile: {$this->request->getPost('name')}");

        return redirect()->to(base_url('customers'))->with('success', 'Customer profile created successfully.');
    }

    public function show(int $id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return redirect()->to(base_url('customers'))->with('error', 'Customer profile not found.');
        }

        $contacts = $this->contactModel->where('customer_id', $id)->findAll();
        $soModel  = new SalesOrderModel();
        $orders   = $soModel->where('customer_id', $id)->orderBy('id', 'DESC')->findAll();

        $data = [
            'pageTitle'  => 'Customer Profile: ' . $customer['name'],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Customers', base_url('customers')], [$customer['name'], null]],
            'customer'   => $customer,
            'contacts'   => $contacts,
            'orders'     => $orders,
        ];
        return view('layouts/main', $data + ['content' => view('customers/show', $data)]);
    }

    public function edit(int $id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return redirect()->to(base_url('customers'))->with('error', 'Customer profile not found.');
        }

        $contacts = $this->contactModel->where('customer_id', $id)->findAll();

        $data = [
            'pageTitle'  => 'Edit ' . $customer['name'],
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Customers', base_url('customers')], [$customer['name'], base_url("customers/{$id}")], ['Edit', null]],
            'customer'   => $customer,
            'contacts'   => $contacts,
        ];
        return view('layouts/main', $data + ['content' => view('customers/edit', $data)]);
    }

    public function update(int $id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return redirect()->to(base_url('customers'))->with('error', 'Customer profile not found.');
        }

        $rules = [
            'type'             => 'required|in_list[individual,corporate]',
            'name'             => 'required|min_length[2]|max_length[200]',
            'billing_address'  => 'required|min_length[5]',
            'shipping_address' => 'required|min_length[5]',
            'payment_terms'    => 'required|integer|greater_than_equal_to[0]',
            'username'         => "required|alpha_dash|min_length[3]|max_length[50]|is_unique[customers.username,id,{$id}]",
        ];

        $type = $this->request->getPost('type');
        if ($type === 'corporate') {
            $rules['company_name'] = 'required|min_length[2]|max_length[200]';
        }

        $pwd = $this->request->getPost('password');
        if (!empty($pwd)) {
            $rules['password'] = 'min_length[6]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // Validate contacts
        $contactsData = $this->request->getPost('contacts');
        if (!is_array($contactsData) || empty($contactsData)) {
            return redirect()->back()->withInput()->with('error', 'At least one contact method is required.');
        }

        $validContacts = [];
        foreach ($contactsData as $c) {
            $val = trim($c['value'] ?? '');
            $cType = $c['contact_type'] ?? '';
            if (empty($val)) continue;
            
            if ($cType === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->withInput()->with('error', 'Please enter a valid email address.');
            }
            if ($cType === 'mobile' && strlen($val) < 7) {
                return redirect()->back()->withInput()->with('error', 'Please enter a valid mobile number.');
            }
            $validContacts[] = [
                'contact_type' => $cType,
                'value'        => $val,
                'remarks'      => trim($c['remarks'] ?? '')
            ];
        }

        if (empty($validContacts)) {
            return redirect()->back()->withInput()->with('error', 'At least one valid contact method is required.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $updateData = [
            'type'             => $type,
            'name'             => $this->request->getPost('name'),
            'company_name'     => $type === 'corporate' ? $this->request->getPost('company_name') : null,
            'billing_address'  => $this->request->getPost('billing_address'),
            'shipping_address' => $this->request->getPost('shipping_address'),
            'tin'              => $this->request->getPost('tin') ?: null,
            'payment_terms'    => (int)$this->request->getPost('payment_terms'),
            'username'         => $this->request->getPost('username')
        ];

        if (!empty($pwd)) {
            $updateData['password'] = password_hash($pwd, PASSWORD_BCRYPT);
        }

        $this->customerModel->update($id, $updateData);

        // Replace contacts
        $this->contactModel->where('customer_id', $id)->delete();
        foreach ($validContacts as $vc) {
            $this->contactModel->insert([
                'customer_id'  => $id,
                'contact_type' => $vc['contact_type'],
                'value'        => $vc['value'],
                'remarks'      => $vc['remarks']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'An error occurred during update. Please try again.');
        }

        $this->audit->log('customers', 'update', $id, "Updated customer profile: {$this->request->getPost('name')}");

        return redirect()->to(base_url("customers/{$id}"))->with('success', 'Customer profile updated successfully.');
    }

    public function toggle(int $id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return redirect()->back()->with('error', 'Customer not found.');
        }

        $newActive = $customer['is_active'] ? 0 : 1;
        $this->customerModel->update($id, ['is_active' => $newActive]);

        $statusStr = $newActive ? 'activated' : 'deactivated';
        $this->audit->log('customers', 'toggle', $id, "Toggled active status to {$newActive} for customer {$customer['name']}");

        return redirect()->back()->with('success', "Customer account profile successfully {$statusStr}.");
    }
}
