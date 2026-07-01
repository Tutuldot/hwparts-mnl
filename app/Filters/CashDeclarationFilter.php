<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CashDeclarationFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Enforce only for logged-in internal users (admins / staff)
        if (!session()->get('user_id')) {
            return;
        }

        $uri = $request->getUri()->getPath();

        // Exclude AJAX poll endpoints, login/logout routes, and declaration paths to prevent infinite redirects
        $excludes = [
            'admin/cash/declare-opening',
            'admin/cash/submit-opening',
            'admin/inquiries/poll-list',
            'updates',
            'auth/logout',
            'auth/login',
        ];

        foreach ($excludes as $ex) {
            if (strpos($uri, $ex) !== false) {
                return;
            }
        }

        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        // Check if there are active cash accounts
        $activeAccountsCount = $db->table('cash_accounts')
            ->where('is_active', 1)
            ->countAllResults();

        if ($activeAccountsCount > 0) {
            // Count declarations for today
            $declarationsCount = $db->table('daily_cash_declarations')
                ->where('declaration_date', $today)
                ->countAllResults();

            // If not all active accounts have opening balances declared for today, redirect
            if ($declarationsCount < $activeAccountsCount) {
                return redirect()->to(base_url('admin/cash/declare-opening'))
                    ->with('info', 'Start-of-day cash declaration is mandatory. Please state the opening balances to proceed.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
