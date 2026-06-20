<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\AuditLogModel;

class UserController extends BaseController
{
    public function index()
    {
        $model = new UserModel();
        $data  = [
            'pageTitle'  => 'User Management',
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Admin', '#'], ['Users', null]],
            'users'      => $model->orderBy('name')->findAll(),
        ];
        return view('layouts/main', $data + ['content' => view('admin/users', $data)]);
    }

    public function store()
    {
        $model = new UserModel();
        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[admin,warehouse,purchasing,approver]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $id = $model->insert([
            'name'      => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'password'  => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'      => $this->request->getPost('role'),
            'is_active' => 1,
        ]);
        (new AuditLogModel())->log('users', 'create', $id, 'Created user: ' . $this->request->getPost('email'));
        return redirect()->to(base_url('admin/users'))->with('success', 'User created.');
    }

    public function update(int $id)
    {
        $model = new UserModel();
        $rules = [
            'name'  => 'required|min_length[2]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role'  => 'required|in_list[admin,warehouse,purchasing,approver]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }
        $model->update($id, [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role'  => $this->request->getPost('role'),
        ]);
        (new AuditLogModel())->log('users', 'update', $id, 'Updated user #' . $id);
        return redirect()->to(base_url('admin/users'))->with('success', 'User updated.');
    }

    public function toggle(int $id)
    {
        $model = new UserModel();
        $user  = $model->find($id);
        if ($user && $user['id'] === session()->get('user_id')) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }
        if ($user) $model->update($id, ['is_active' => $user['is_active'] ? 0 : 1]);
        return redirect()->to(base_url('admin/users'))->with('success', 'User status updated.');
    }

    public function resetPassword(int $id)
    {
        $model    = new UserModel();
        $password = $this->request->getPost('new_password');
        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters.');
        }
        $model->update($id, ['password' => password_hash($password, PASSWORD_BCRYPT)]);
        (new AuditLogModel())->log('users', 'reset_password', $id, 'Reset password for user #' . $id);
        return redirect()->to(base_url('admin/users'))->with('success', 'Password reset successfully.');
    }
}
