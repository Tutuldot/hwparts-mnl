<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class AuditLogController extends BaseController
{
    public function index()
    {
        $model = new AuditLogModel();
        $data  = [
            'pageTitle'  => 'Audit Logs',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Audit Logs', null]],
            'logs'       => $model->getRecent(500),
        ];
        return view('layouts/main', $data + ['content' => view('audit_logs', $data)]);
    }
}
