<?php

namespace App\Controllers;

use App\Models\CashAccountModel;
use App\Models\DailyCashDeclarationModel;
use App\Models\CashTransactionModel;
use App\Models\AccountsReceivableModel;
use App\Models\AccountsPayableModel;
use App\Models\AuditLogModel;

class CashController extends BaseController
{
    protected CashAccountModel           $accountModel;
    protected DailyCashDeclarationModel  $declarationModel;
    protected CashTransactionModel      $transactionModel;
    protected AuditLogModel              $auditModel;

    public function __construct()
    {
        $this->accountModel     = new CashAccountModel();
        $this->declarationModel = new DailyCashDeclarationModel();
        $this->transactionModel = new CashTransactionModel();
        $this->auditModel       = new AuditLogModel();
    }

    public function accounts()
    {
        $data = [
            'pageTitle'  => 'Cash & Bank Accounts',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Cash Tracking', null], ['Accounts', null]],
            'accounts'   => $this->accountModel->orderBy('name', 'ASC')->findAll(),
        ];
        return view('layouts/main', $data + ['content' => view('cash/accounts', $data)]);
    }

    public function storeAccount()
    {
        $rules = [
            'name'           => 'required|min_length[2]|max_length[100]|is_unique[cash_accounts.name]',
            'type'           => 'required|in_list[bank,digital_wallet,cash]',
            'account_number' => 'permit_empty|max_length[100]',
            'balance'        => 'required|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $type = $this->request->getPost('type');
        $accountNum = trim($this->request->getPost('account_number') ?? '');

        if ($type === 'cash') {
            $accountNum = null; // Enforce no account number for Cash
        } elseif ($type !== 'cash' && empty($accountNum)) {
            return redirect()->back()->withInput()->with('error', 'Account number is required for Bank and Digital Wallet account types.');
        }

        $this->accountModel->insert([
            'name'           => trim($this->request->getPost('name')),
            'type'           => $type,
            'account_number' => $accountNum,
            'balance'        => (float)$this->request->getPost('balance'),
            'is_active'      => 1,
        ]);

        $id = $this->accountModel->getInsertID();
        $this->auditModel->log('cash_accounts', 'create', $id, "Created Cash Account: " . $this->request->getPost('name'));

        return redirect()->to(base_url('admin/cash/accounts'))->with('success', 'Cash Account registered successfully.');
    }

    public function updateAccount(int $id)
    {
        $account = $this->accountModel->find($id);
        if (!$account) {
            return redirect()->back()->with('error', 'Account not found.');
        }

        $rules = [
            'name'           => "required|min_length[2]|max_length[100]|is_unique[cash_accounts.name,id,{$id}]",
            'type'           => 'required|in_list[bank,digital_wallet,cash]',
            'account_number' => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $type = $this->request->getPost('type');
        $accountNum = trim($this->request->getPost('account_number') ?? '');

        if ($type === 'cash') {
            $accountNum = null;
        } elseif ($type !== 'cash' && empty($accountNum)) {
            return redirect()->back()->with('error', 'Account number is required for Bank and Digital Wallet account types.');
        }

        $this->accountModel->update($id, [
            'name'           => trim($this->request->getPost('name')),
            'type'           => $type,
            'account_number' => $accountNum,
        ]);

        $this->auditModel->log('cash_accounts', 'update', $id, "Updated Cash Account: " . $this->request->getPost('name'));

        return redirect()->to(base_url('admin/cash/accounts'))->with('success', 'Cash Account updated successfully.');
    }

    public function toggleAccount(int $id)
    {
        $account = $this->accountModel->find($id);
        if (!$account) {
            return redirect()->back()->with('error', 'Account not found.');
        }

        $newStatus = $account['is_active'] ? 0 : 1;
        $this->accountModel->update($id, ['is_active' => $newStatus]);

        $statusStr = $newStatus ? 'Activated' : 'Deactivated';
        $this->auditModel->log('cash_accounts', 'toggle_status', $id, "{$statusStr} Cash Account: {$account['name']}");

        return redirect()->to(base_url('admin/cash/accounts'))->with('success', "Account {$account['name']} has been {$statusStr}.");
    }

    public function declareOpening()
    {
        $today = date('Y-m-d');
        $activeAccounts = $this->accountModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        
        // Check if declaration exists
        $declarations = $this->declarationModel->getDeclarationForDate($today);

        $data = [
            'pageTitle'    => 'Start-of-Day Cash Declaration',
            'breadcrumb'   => [['HW Trucks MNL', base_url('dashboard')], ['Cash Tracking', null], ['Opening Declaration', null]],
            'accounts'     => $activeAccounts,
            'declarations' => $declarations,
            'today'        => $today,
        ];
        return view('layouts/main', $data + ['content' => view('cash/declare_opening', $data)]);
    }

    public function submitOpening()
    {
        $today = date('Y-m-d');
        $activeAccounts = $this->accountModel->where('is_active', 1)->findAll();

        // Check if already declared for today
        $existing = $this->declarationModel->where('declaration_date', $today)->countAllResults();
        if ($existing >= count($activeAccounts) && count($activeAccounts) > 0) {
            return redirect()->to(base_url('dashboard'))->with('info', 'Opening balances for today have already been declared.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($activeAccounts as $account) {
            $fieldName = 'opening_balance_' . $account['id'];
            $openingBal = (float)($this->request->getPost($fieldName) ?? 0);
            $sysBal     = (float)$account['balance'];
            $discrepancy = $openingBal - $sysBal;

            $this->declarationModel->insert([
                'declaration_date' => $today,
                'account_id'       => $account['id'],
                'opening_balance'  => $openingBal,
                'system_balance'   => $sysBal,
                'discrepancy'      => $discrepancy,
                'created_by'       => session()->get('user_id'),
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to save daily cash declaration. Transaction error.');
        }

        $this->auditModel->log('daily_cash_declarations', 'declare', 0, "Declared start-of-day cash balances for " . $today);

        return redirect()->to(base_url('dashboard'))->with('success', 'Daily cash opening balances declared successfully. Dashboard unlocked.');
    }

    public function ledger()
    {
        $filters = [
            'status'     => $this->request->getGet('status') ?? '',
            'type'       => $this->request->getGet('type') ?? '',
            'account_id' => $this->request->getGet('account_id') ?? '',
        ];

        $data = [
            'pageTitle'    => 'Cash Ledger & Movements',
            'breadcrumb'   => [['HW Trucks MNL', base_url('dashboard')], ['Cash Tracking', null], ['Ledger', null]],
            'transactions' => $this->transactionModel->getLedger($filters),
            'accounts'     => $this->accountModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
            'filters'      => $filters,
        ];
        return view('layouts/main', $data + ['content' => view('cash/ledger', $data)]);
    }

    public function storeTransaction()
    {
        $rules = [
            'type'             => 'required|in_list[deposit,withdrawal,transfer,adjustment,advance,initial_adjustment]',
            'from_account_id'  => 'permit_empty|is_natural_no_zero',
            'to_account_id'    => 'permit_empty|is_natural_no_zero',
            'amount'           => 'required|numeric|greater_than[0]',
            'reference_number' => 'permit_empty|max_length[100]',
            'remarks'          => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $type          = $this->request->getPost('type');
        $fromAccountId = $this->request->getPost('from_account_id') ? (int)$this->request->getPost('from_account_id') : null;
        $toAccountId   = $this->request->getPost('to_account_id') ? (int)$this->request->getPost('to_account_id') : null;
        $amount        = (float)$this->request->getPost('amount');
        $remarks       = trim($this->request->getPost('remarks') ?? '');
        $refNum        = trim($this->request->getPost('reference_number') ?? '');

        // Validation based on transaction type
        if ($type === 'transfer' && (empty($fromAccountId) || empty($toAccountId))) {
            return redirect()->back()->withInput()->with('error', 'Source (From) and Destination (To) accounts are required for transfers.');
        }
        if ($type === 'transfer' && $fromAccountId === $toAccountId) {
            return redirect()->back()->withInput()->with('error', 'Source and Destination accounts must be different.');
        }
        if (($type === 'withdrawal' || $type === 'advance') && empty($fromAccountId)) {
            return redirect()->back()->withInput()->with('error', 'Source (From) account is required.');
        }
        if ($type === 'deposit' && empty($toAccountId)) {
            return redirect()->back()->withInput()->with('error', 'Destination (To) account is required.');
        }
        if ($type === 'initial_adjustment' && empty($toAccountId)) {
            return redirect()->back()->withInput()->with('error', 'Target (To) account is required for initial amount adjustments.');
        }
        if ($type === 'adjustment' && empty($fromAccountId) && empty($toAccountId)) {
            return redirect()->back()->withInput()->with('error', 'At least one account (From or To) must be set for adjustments.');
        }

        // Advance requires explanation/remarks
        if ($type === 'advance' && empty($remarks)) {
            return redirect()->back()->withInput()->with('error', 'Remarks explaining the purpose of the Cash Advance are mandatory.');
        }
        // Adjustment requires explanation
        if ($type === 'adjustment' && empty($remarks)) {
            return redirect()->back()->withInput()->with('error', 'Remarks explaining the balance discrepancy for adjustment are mandatory.');
        }
        // Initial balance adjustment requires explanation
        if ($type === 'initial_adjustment' && empty($remarks)) {
            return redirect()->back()->withInput()->with('error', 'Remarks explaining the starting balance target are mandatory.');
        }

        // Upload evidence is mandatory for all manual cash movements
        $file = $this->request->getFile('evidence');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Uploading photo/screenshot evidence is mandatory for all cash movements.');
        }

        // If source account is defined, check sufficient balance
        if ($fromAccountId) {
            $fromAccount = $this->accountModel->find($fromAccountId);
            if (!$fromAccount) {
                return redirect()->back()->withInput()->with('error', 'Source account not found.');
            }
            if ((float)$fromAccount['balance'] < $amount) {
                return redirect()->back()->withInput()->with('error', "Insufficient balance in account '{$fromAccount['name']}'. Current balance is ₱" . number_format($fromAccount['balance'], 2));
            }
        }

        // Save evidence image
        $uploadPath = FCPATH . 'uploads/cash_evidence/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        $evidencePath = 'uploads/cash_evidence/' . $newName;

        // Insert pending transaction
        $txnNum = $this->transactionModel->generateTransactionNumber();
        $this->transactionModel->insert([
            'transaction_number' => $txnNum,
            'reference_number'   => $refNum ?: null,
            'from_account_id'    => $fromAccountId,
            'to_account_id'      => $toAccountId,
            'amount'             => $amount,
            'type'               => $type,
            'reference_source'   => 'manual',
            'reference_id'       => null,
            'evidence_path'      => $evidencePath,
            'remarks'            => $remarks ?: null,
            'status'             => 'pending',
            'created_by'         => session()->get('user_id'),
        ]);

        $id = $this->transactionModel->getInsertID();
        $this->auditModel->log('cash_transactions', 'create_manual', $id, "Submitted manual cash txn: {$txnNum} for ₱" . number_format($amount, 2));

        return redirect()->to(base_url('admin/cash/ledger'))->with('success', "Cash transaction {$txnNum} submitted successfully. Awaiting administrator approval.");
    }

    public function approvals()
    {
        // View approvals lists only pending transactions
        $data = [
            'pageTitle'    => 'Pending Cash Approvals',
            'breadcrumb'   => [['HW Trucks MNL', base_url('dashboard')], ['Cash Tracking', null], ['Approvals', null]],
            'transactions' => $this->transactionModel->getLedger(['status' => 'pending']),
        ];
        return view('layouts/main', $data + ['content' => view('cash/approvals', $data)]);
    }

    public function approveTransaction(int $id)
    {
        $txn = $this->transactionModel->find($id);
        if (!$txn) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        if ($txn['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Only pending transactions can be approved.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Process Account Balance Changes
        $amount = (float)$txn['amount'];

        if ($txn['type'] === 'initial_adjustment') {
            $toAccount = $this->accountModel->find($txn['to_account_id']);
            if (!$toAccount) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Target account not found.');
            }
            // Overwrite balance directly
            $db->query("UPDATE cash_accounts SET balance = ? WHERE id = ?", [$amount, $txn['to_account_id']]);
        } else {
            if ($txn['from_account_id']) {
                $fromAccount = $this->accountModel->find($txn['from_account_id']);
                if (!$fromAccount) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Source account not found.');
                }
                // Double check balance (to prevent race conditions)
                if ((float)$fromAccount['balance'] < $amount) {
                    $db->transRollback();
                    return redirect()->back()->with('error', "Approval failed. Account '{$fromAccount['name']}' has insufficient balance (₱" . number_format($fromAccount['balance'], 2) . ").");
                }
                // Decrement balance
                $db->query("UPDATE cash_accounts SET balance = balance - ? WHERE id = ?", [$amount, $txn['from_account_id']]);
            }

            if ($txn['to_account_id']) {
                $toAccount = $this->accountModel->find($txn['to_account_id']);
                if (!$toAccount) {
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Destination account not found.');
                }
                // Increment balance
                $db->query("UPDATE cash_accounts SET balance = balance + ? WHERE id = ?", [$amount, $txn['to_account_id']]);
            }
        }

        // 2. Integration with Sales / Procurement payments
        if ($txn['reference_source'] === 'sales_payment') {
            $arModel = new AccountsReceivableModel();
            $ar = $arModel->find($txn['reference_id']);
            if ($ar) {
                // Update Accounts Receivable
                $arModel->update($ar['id'], [
                    'status'            => 'paid',
                    'amount_paid'       => $amount,
                    'payment_reference' => $txn['reference_number'] ?: 'Approved cash deposit',
                    'paid_at'           => date('Y-m-d H:i:s'),
                    'paid_by'           => session()->get('user_id'),
                ]);
            }
        } elseif ($txn['reference_source'] === 'procurement_payment') {
            $apModel = new AccountsPayableModel();
            $ap = $apModel->find($txn['reference_id']);
            if ($ap) {
                // Update Accounts Payable
                $apModel->update($ap['id'], [
                    'status'            => 'paid',
                    'amount_paid'       => $amount,
                    'payment_reference' => $txn['reference_number'] ?: 'Approved cash withdrawal',
                    'paid_at'           => date('Y-m-d H:i:s'),
                    'paid_by'           => session()->get('user_id'),
                ]);

                // Send remittance notifications to supplier
                try {
                    $apController = new \App\Controllers\AccountsPayableController();
                    $apController->sendRemittanceNotifications($ap['id']);
                } catch (\Throwable $e) {
                    // Ignore sending exceptions in local dev/simulation
                }
            }
        }

        // 3. Mark transaction as approved
        $this->transactionModel->update($id, [
            'status'      => 'approved',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to approve cash transaction. Database transaction error.');
        }

        $this->auditModel->log('cash_transactions', 'approve', $id, "Approved txn: {$txn['transaction_number']} for ₱" . number_format($amount, 2));

        return redirect()->to(base_url('admin/cash/approvals'))->with('success', "Transaction {$txn['transaction_number']} approved. Account balances updated.");
    }

    public function rejectTransaction(int $id)
    {
        $txn = $this->transactionModel->find($id);
        if (!$txn) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        if ($txn['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Only pending transactions can be rejected.');
        }

        $remarks = trim($this->request->getPost('reject_remarks') ?? '');
        if (empty($remarks)) {
            return redirect()->back()->with('error', 'Remarks are required when rejecting a cash transaction.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Mark transaction as rejected
        $this->transactionModel->update($id, [
            'status'      => 'rejected',
            'remarks'     => $txn['remarks'] . " | REJECTION REASON: " . $remarks,
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        // If linked to AR or AP, reset their invoice status (remain unpaid / pending clearance)
        if ($txn['reference_source'] === 'sales_payment') {
            $arModel = new AccountsReceivableModel();
            $ar = $arModel->find($txn['reference_id']);
            if ($ar) {
                $arModel->update($ar['id'], ['status' => 'unpaid']);
            }
        } elseif ($txn['reference_source'] === 'procurement_payment') {
            $apModel = new AccountsPayableModel();
            $ap = $apModel->find($txn['reference_id']);
            if ($ap) {
                $apModel->update($ap['id'], ['status' => 'unpaid']);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to reject cash transaction. Database error.');
        }

        $this->auditModel->log('cash_transactions', 'reject', $id, "Rejected txn: {$txn['transaction_number']}. Reason: {$remarks}");

        return redirect()->to(base_url('admin/cash/approvals'))->with('success', "Transaction {$txn['transaction_number']} rejected successfully.");
    }
}
