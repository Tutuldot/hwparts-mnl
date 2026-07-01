<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AccountsPayableModel;
use App\Models\PurchaseOrderModel;
use App\Models\SupplierModel;
use App\Models\SupplierContactModel;
use App\Models\AuditLogModel;
use App\Models\RemittanceLogModel;
use App\Libraries\SmsService;

class AccountsPayableController extends BaseController
{
    protected AccountsPayableModel $apModel;
    protected AuditLogModel        $audit;

    public function __construct()
    {
        $this->apModel = new AccountsPayableModel();
        $this->audit   = new AuditLogModel();
    }

    public function index()
    {
        $payables = $this->apModel->getAllWithDetails();

        $data = [
            'pageTitle'  => 'Accounts Payable',
            'breadcrumb' => [['HW Trucks MNL', base_url('dashboard')], ['Accounts Payable', null]],
            'payables'   => $payables,
        ];
        return view('layouts/main', $data + ['content' => view('accounts_payable/index', $data)]);
    }

    public function show(int $id)
    {
        $payable = $this->apModel->getWithDetails($id);
        if (! $payable) {
            return redirect()->to(base_url('accounts-payable'))->with('error', 'Accounts Payable record not found.');
        }

        $logModel = new RemittanceLogModel();
        $remittanceLogs = $logModel->where('ap_id', $id)->orderBy('created_at', 'DESC')->findAll();

        $accountModel = new \App\Models\CashAccountModel();
        $db = \Config\Database::connect();
        $pendingTxn = $db->table('cash_transactions')
                         ->where('reference_source', 'procurement_payment')
                         ->where('reference_id', $id)
                         ->where('status', 'pending')
                         ->get()
                         ->getRowArray();

        $data = [
            'pageTitle'      => 'Payable: ' . $payable['po_number'],
            'breadcrumb'     => [['HW Trucks MNL', base_url('dashboard')], ['Accounts Payable', base_url('accounts-payable')], [$payable['po_number'], null]],
            'payable'        => $payable,
            'logs'           => $remittanceLogs,
            'cashAccounts'   => $accountModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
            'pendingTxn'     => $pendingTxn,
        ];
        return view('layouts/main', $data + ['content' => view('accounts_payable/show', $data)]);
    }

    public function pay(int $id)
    {
        $payable = $this->apModel->getWithDetails($id);
        if (! $payable) {
            return redirect()->to(base_url('accounts-payable'))->with('error', 'Accounts Payable record not found.');
        }

        if ($payable['status'] === 'paid') {
            return redirect()->back()->with('error', 'This accounts payable has already been paid.');
        }

        // Check if there is already a pending transaction for this AP record
        $db = \Config\Database::connect();
        $pending = $db->table('cash_transactions')
                      ->where('reference_source', 'procurement_payment')
                      ->where('reference_id', $id)
                      ->where('status', 'pending')
                      ->countAllResults();

        if ($pending > 0) {
            return redirect()->back()->with('error', 'A settlement payment is already pending clearance for this accounts payable.');
        }

        $rules = [
            'cash_account_id'   => 'required|is_natural_no_zero',
            'payment_type'      => 'required|in_list[GCASH,BANK TRANSFER,Cheque,Cash via Transmittal]',
            'payment_reference' => 'required|min_length[2]|max_length[100]',
            'invoice_number'    => 'required|max_length[100]',
            'amount_paid'       => 'required|numeric|greater_than[0]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $amountPaid = (float)$this->request->getPost('amount_paid');
        $cashAccountId = (int)$this->request->getPost('cash_account_id');

        // Verify source account has sufficient balance for general approval
        $accountModel = new \App\Models\CashAccountModel();
        $cashAccount = $accountModel->find($cashAccountId);
        if (!$cashAccount) {
            return redirect()->back()->withInput()->with('error', 'Source account not found.');
        }
        if ((float)$cashAccount['balance'] < $amountPaid) {
            return redirect()->back()->withInput()->with('error', "Insufficient balance in account '{$cashAccount['name']}'. Current balance is ₱" . number_format($cashAccount['balance'], 2));
        }

        // Upload proof of payment is MANDATORY
        $file = $this->request->getFile('proof_of_payment');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Uploading payment proof is mandatory.');
        }

        $uploadPath = FCPATH . 'uploads/payments/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        $proofPath = 'uploads/payments/' . $newName;

        $paymentType = $this->request->getPost('payment_type');
        $chequeDetails = null;
        $paymentRef = $this->request->getPost('payment_reference');
        if ($paymentType === 'Cheque') {
            $bank = trim($this->request->getPost('cheque_bank') ?? '');
            $chkNum = trim($this->request->getPost('cheque_number') ?? '');
            if (empty($bank) || empty($chkNum)) {
                return redirect()->back()->withInput()->with('error', 'For Cheque payments, both Bank Name and Check Number are required.');
            }
            $chequeDetails = $bank . ' - Chk# ' . $chkNum;
            $paymentRef .= " (Bank: {$bank}, Check#: {$chkNum})";
        }

        // Create pending Cash Transaction instead of directly updating status to paid
        $txnModel = new \App\Models\CashTransactionModel();
        $txnNum = $txnModel->generateTransactionNumber();
        $txnModel->insert([
            'transaction_number' => $txnNum,
            'reference_number'   => $paymentRef,
            'from_account_id'    => $cashAccountId,
            'to_account_id'      => null,
            'amount'             => $amountPaid,
            'type'               => 'expense',
            'reference_source'   => 'procurement_payment',
            'reference_id'       => $id,
            'evidence_path'      => $proofPath,
            'remarks'            => "Payment for Accounts Payable: PO {$payable['po_number']}",
            'status'             => 'pending',
            'created_by'         => session()->get('user_id'),
        ]);

        // Keep AP invoice status as 'unpaid' but save details for preview
        $this->apModel->update($id, [
            'payment_reference' => $this->request->getPost('payment_reference'),
            'payment_type'      => $paymentType,
            'cheque_details'    => $chequeDetails,
            'proof_of_payment'  => $proofPath,
            'invoice_number'    => $this->request->getPost('invoice_number'),
        ]);

        // Audit log
        $this->audit->log('accounts_payable', 'pay_submit', $id, "Submitted AP PO {$payable['po_number']} payment of ₱" . number_format($amountPaid, 2) . " (Awaiting Cash Clearance Txn: {$txnNum})");

        return redirect()->to(base_url("accounts-payable/{$id}"))->with('success', "Payment submitted successfully. Cash transaction {$txnNum} is pending administrator clearance.");
    }

    public function sendRemittanceNotifications(int $id)
    {
        $payable = $this->apModel->getWithDetails($id);
        if (!$payable) {
            return;
        }
        $paymentType = $payable['payment_type'] ?? 'GCASH';
        $chequeDetails = $payable['cheque_details'] ?? null;

        // 1. Send Remittance Advice via Email
        $this->sendEmailRemittance($payable, $paymentType, $chequeDetails);

        // 2. Send SMS Remittance Advice to Supplier Contacts
        $this->sendSmsRemittance($payable, $paymentType, $chequeDetails);
    }

    public function resendRemittance(int $id)
    {
        $payable = $this->apModel->getWithDetails($id);
        if (! $payable) {
            return redirect()->to(base_url('accounts-payable'))->with('error', 'Accounts Payable record not found.');
        }

        if ($payable['status'] !== 'paid') {
            return redirect()->back()->with('error', 'Remittance advice can only be sent for paid accounts payable.');
        }

        $paymentType = $payable['payment_type'];
        $chequeDetails = $payable['cheque_details'];

        // Re-send Email
        $this->sendEmailRemittance($payable, $paymentType, $chequeDetails);

        // Re-send SMS
        $this->sendSmsRemittance($payable, $paymentType, $chequeDetails);

        // Log audit log
        $this->audit->log('accounts_payable', 'resend_remittance', $id, "Manually resent remittance advice for PO {$payable['po_number']}");

        return redirect()->to(base_url("accounts-payable/{$id}"))->with('success', 'Remittance advice notifications have been re-sent and logged.');
    }

    private function sendEmailRemittance(array $payable, string $paymentType, ?string $chequeDetails)
    {
        $logModel = new RemittanceLogModel();
        $recipientEmails = [];

        // 1. Gather from supplier's notice emails field
        if (!empty($payable['emails_for_notice'])) {
            $emails = explode(';', $payable['emails_for_notice']);
            foreach ($emails as $email) {
                $trimmed = trim($email);
                if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                    $recipientEmails[] = $trimmed;
                }
            }
        }

        // 2. Gather from visible supplier contacts
        $scmModel = new SupplierContactModel();
        $contacts = $scmModel->where('supplier_id', $payable['supplier_id'])
                             ->where('is_visible', 1)
                             ->where('email IS NOT NULL AND email != ""')
                             ->findAll();
        foreach ($contacts as $contact) {
            $trimmed = trim($contact['email']);
            if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                $recipientEmails[] = $trimmed;
            }
        }

        $recipientEmails = array_values(array_unique($recipientEmails));

        if (empty($recipientEmails)) {
            $logModel->insert([
                'ap_id'      => $payable['id'],
                'type'       => 'email',
                'recipient'  => $payable['emails_for_notice'] ?: 'None',
                'message'    => 'Skipped: No valid notice emails found in supplier notice emails or supplier contacts.',
                'status'     => 'failed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            return;
        }

        $subject = "Remittance Advice - Settle Payment for PO " . $payable['po_number'];
        $detailsStr = $paymentType === 'Cheque' ? "Cheque ($chequeDetails)" : $paymentType;
        $amountToDisplay = $payable['amount_paid'] ?? $payable['amount'];
        $formattedAmount = number_format($amountToDisplay, 2);
        $settledDate = date('M d, Y H:i A');

        $invoiceRow = "";
        if (!empty($payable['invoice_number'])) {
            $invoiceRow = '<tr>
                <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b;">Supplier Invoice #</td>
                <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; font-family: monospace;">' . esc($payable['invoice_number']) . '</td>
            </tr>';
        }

        // HTML message construction
        $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Remittance Advice - HW Trucks MNL</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100% !important;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f6f9; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border: 1px solid #e1e8ed; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="background-color: #1e3a8a; padding: 24px 32px; text-align: left;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">HW Trucks MNL</h1>
                            <p style="margin: 4px 0 0 0; color: #93c5fd; font-size: 14px;">Remittance Advice & Payment Confirmation</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.5; color: #334155;">
                                Dear <strong>' . esc($payable['supplier_name']) . '</strong>,
                            </p>
                            <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 1.5; color: #475569;">
                                Please be advised that we have settled the payment for Purchase Order <strong>' . esc($payable['po_number']) . '</strong>. Below are the transaction details for your verification:
                            </p>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b; width: 40%;">Purchase Order</td>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; font-family: monospace; font-weight: bold;">' . esc($payable['po_number']) . '</td>
                                </tr>
                                ' . $invoiceRow . '
                                <tr>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b;">Payment Form</td>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b;">' . esc($detailsStr) . '</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b;">Reference No.</td>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; font-family: monospace;">' . esc($payable['payment_reference']) . '</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #64748b;">Settled Date</td>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b;">' . $settledDate . '</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px; font-size: 14px; color: #64748b; font-weight: bold;">Amount Paid</td>
                                    <td style="padding: 16px 20px; font-size: 18px; color: #2563eb; font-weight: bold;">₱' . $formattedAmount . '</td>
                                </tr>
                            </table>
                            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.5; color: #64748b; font-style: italic;">
                                Note: We have attached the proof of payment transaction file (image/PDF) to this email for your reference.
                            </p>
                            <p style="margin: 0 0 4px 0; font-size: 15px; color: #475569;">Sincerely,</p>
                            <p style="margin: 0; font-size: 15px; font-weight: bold; color: #1e293b;">Finance Department</p>
                            <p style="margin: 0; font-size: 13px; color: #64748b;">HW Trucks MNL Supply Chain</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 20px 32px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">This is an automated transaction confirmation. Please do not reply directly to this email.</p>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">&copy; 2026 HW Trucks MNL. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

        $emailService = \Config\Services::email();
        $status = 'failed';
        try {
            $emailService->setMailType('html');
            $emailService->setTo($recipientEmails);
            $emailService->setSubject($subject);
            $emailService->setMessage($message);

            // Attach proof of payment if exists
            if (!empty($payable['proof_of_payment'])) {
                $fullProofPath = FCPATH . $payable['proof_of_payment'];
                if (is_file($fullProofPath)) {
                    $emailService->attach($fullProofPath);
                }
            }

            if ($emailService->send(false)) {
                $status = 'sent';
            }
        } catch (\Throwable $e) {
            // Ignore sending exceptions in local dev/simulation
        }

        // Log to remittance_logs
        $logModel->insert([
            'ap_id'      => $payable['id'],
            'type'       => 'email',
            'recipient'  => implode('; ', $recipientEmails),
            'message'    => $message,
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function sendSmsRemittance(array $payable, string $paymentType, ?string $chequeDetails)
    {
        $scmModel = new SupplierContactModel();
        // Retrieve visible supplier contacts who have a mobile number
        $contacts = $scmModel->where('supplier_id', $payable['supplier_id'])
                             ->where('is_visible', 1)
                             ->where('mobile IS NOT NULL AND mobile != ""')
                             ->findAll();

        $logModel = new RemittanceLogModel();

        if (empty($contacts)) {
            $logModel->insert([
                'ap_id'      => $payable['id'],
                'type'       => 'sms',
                'recipient'  => 'None',
                'message'    => 'Skipped: No contact persons with mobile numbers found for supplier.',
                'status'     => 'failed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            return;
        }

        $ref = esc($payable['payment_reference']);
        $detailsStr = $paymentType === 'Cheque' ? " ($chequeDetails)" : "";
        $amountToDisplay = $payable['amount_paid'] ?? $payable['amount'];
        $invStr = !empty($payable['invoice_number']) ? " Inv: {$payable['invoice_number']}" : "";
        
        $smsMessage = "HW Trucks MNL Remittance: Settled ₱" . number_format($amountToDisplay, 2) . " for PO " . $payable['po_number'] . " via " . $paymentType . $detailsStr . " (Ref: " . $ref . $invStr . "). Thank you!";

        foreach ($contacts as $contact) {
            $success = SmsService::send($contact['mobile'], $smsMessage);
            
            $logModel->insert([
                'ap_id'      => $payable['id'],
                'type'       => 'sms',
                'recipient'  => $contact['name'] . ' (' . $contact['mobile'] . ')',
                'message'    => $smsMessage,
                'status'     => $success ? 'sent' : 'failed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
