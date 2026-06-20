<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'email', 'password', 'role', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps  = true;

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[100]',
        'email' => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
        'role'  => 'required|in_list[admin,warehouse,purchasing,approver]',
    ];
}
