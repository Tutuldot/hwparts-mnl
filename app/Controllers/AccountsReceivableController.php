<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AccountsReceivableModel;
use App\Models\ReceivableLogModel;
use App\Models\CustomerModel;
use App\Models\CustomerContactModel;
use App\Models\SalesOrderModel;
use App\Models\AuditLogModel;
use App\Libraries\SmsService;

class AccountsReceivableController extends BaseController
{
    protected AccountsReceivableModel $arModel;
    protected ReceivableLogModel      $logModel;
    protected CustomerModel            $customerModel;
    protected CustomerContactModel     $contactModel;
    protected AuditLogModel            $audit;

    public function __construct()
    {
        $this->arModel       = new AccountsReceivableModel();
        $this->logModel      = new ReceivableLogModel();
        $this->customerModel = new CustomerModel();
        $this->contactModel  = new CustomerContactModel();
        $this->audit         = new AuditLogModel();
    }

    public function index()
    {
        $receivables = $this->arModel->getAllWithDetails();

        $data = [
            'pageTitle'   => 'Accounts Receivable',
            'breadcrumb'  => [['HWParts MNL', base_url('dashboard')], ['Accounts Receivable', null]],
            'receivables' => $receivables,
        ];
        return view('layouts/main', $data + ['content' => view('accounts_receivable/index', $data)]);
    }

    public function show(int $id)
    {
        $receivable = $this->arModel->getWithDetails($id);
        if (!$receivable) {
            return redirect()->to(base_url('accounts-receivable'))->with('error', 'Accounts Receivable record not found.');
        }

        // Get logs history
        $logs = $this->logModel->where('ar_id', $id)->orderBy('id', 'DESC')->findAll();

        $data = [
            'pageTitle'  => 'Settlement Invoice: ' . $receivable['invoice_number'],
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Accounts Receivable', base_url('accounts-receivable')], [$receivable['invoice_number'], null]],
            'ar'         => $receivable,
            'logs'       => $logs,
        ];
        return view('layouts/main', $data + ['content' => view('accounts_receivable/show', $data)]);
    }

    public function pay(int $id)
    {
        $ar = $this->arModel->find($id);
        if (!$ar) {
            return redirect()->to(base_url('accounts-receivable'))->with('error', 'Accounts Receivable record not found.');
        }

        if ($ar['status'] === 'paid') {
            return redirect()->back()->with('error', 'This invoice has already been paid.');
        }

        $rules = [
            'payment_type'      => 'required|in_list[GCASH,BANK TRANSFER,Cheque,Cash via Transmittal]',
            'payment_reference' => 'required|min_length[2]|max_length[100]',
            'amount_paid'       => 'required|numeric|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // Upload proof of payment is mandatory
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
        $paymentRef  = trim($this->request->getPost('payment_reference'));

        if ($paymentType === 'Cheque') {
            $bank = trim($this->request->getPost('cheque_bank') ?? '');
            $chkNum = trim($this->request->getPost('cheque_number') ?? '');
            if (empty($bank) || empty($chkNum)) {
                return redirect()->back()->withInput()->with('error', 'For Cheque payments, both Bank Name and Check Number are required.');
            }
            $paymentRef .= " (Bank: {$bank}, Check#: {$chkNum})";
        }

        $amountPaid = (float)$this->request->getPost('amount_paid');

        $this->arModel->update($id, [
            'status'            => 'paid',
            'payment_type'      => $paymentType,
            'payment_reference' => $paymentRef,
            'proof_of_payment'  => $proofPath,
            'amount_paid'       => $amountPaid,
            'paid_at'           => date('Y-m-d H:i:s'),
            'paid_by'           => session()->get('user_id')
        ]);

        $this->audit->log('accounts_receivable', 'pay', $id, "Recorded payment of ₱" . number_format($amountPaid, 2) . " for AR Invoice {$ar['invoice_number']}");

        return redirect()->to(base_url("accounts-receivable/{$id}"))->with('success', 'Payment recorded successfully.');
    }

    public function notice(int $id)
    {
        $ar = $this->arModel->getWithDetails($id);
        if (!$ar) {
            return redirect()->to(base_url('accounts-receivable'))->with('error', 'Accounts Receivable record not found.');
        }

        $noticeType = $this->request->getPost('notice_type'); // '1st_notice', '2nd_notice', 'final_notice'
        if (!in_array($noticeType, ['1st_notice', '2nd_notice', 'final_notice'])) {
            return redirect()->back()->with('error', 'Invalid notification notice level.');
        }

        // Get customer contacts
        $contacts = $this->contactModel->where('customer_id', $ar['customer_id'])->findAll();
        if (empty($contacts)) {
            return redirect()->back()->with('error', 'This customer has no contact points configured.');
        }

        $emails = [];
        $mobiles = [];
        foreach ($contacts as $c) {
            if ($c['contact_type'] === 'email' && filter_var($c['value'], FILTER_VALIDATE_EMAIL)) {
                $emails[] = trim($c['value']);
            }
            if ($c['contact_type'] === 'mobile' && strlen(trim($c['value'])) >= 7) {
                $mobiles[] = trim($c['value']);
            }
        }

        // Set notice subjects and templates
        $subject = "";
        $noticeHeader = "";
        $noticeColor = "";
        $bodyText = "";
        
        $smsTemplate = "";

        if ($noticeType === '1st_notice') {
            $subject = "Payment Reminder: Invoice {$ar['invoice_number']} - HWParts MNL";
            $noticeHeader = "First Payment Reminder Notice";
            $noticeColor = "#2563eb"; // Blue
            $bodyText = "This is a friendly reminder that your payment for Invoice <strong>{$ar['invoice_number']}</strong> amounting to <strong>₱" . number_format($ar['amount'], 2) . "</strong> is due on <strong>" . date('M d, Y', strtotime($ar['due_date'])) . "</strong>. Please settle this invoice through your customer portal at your earliest convenience.";
            
            $smsTemplate = "HWParts MNL Reminder: Your invoice {$ar['invoice_number']} (₱" . number_format($ar['amount'], 2) . ") is due on " . date('M d, Y', strtotime($ar['due_date'])) . ". Please login to your portal to settle. Thank you!";
        } elseif ($noticeType === '2nd_notice') {
            $subject = "URGENT: Outstanding Invoice {$ar['invoice_number']} - HWParts MNL";
            $noticeHeader = "Second Payment Reminder (Urgent)";
            $noticeColor = "#ea580c"; // Orange
            $bodyText = "This is our second reminder that your payment for Invoice <strong>{$ar['invoice_number']}</strong> of <strong>₱" . number_format($ar['amount'], 2) . "</strong> was due on <strong>" . date('M d, Y', strtotime($ar['due_date'])) . "</strong>. Please submit your payment proof to prevent service disruption.";
            
            $smsTemplate = "HWParts MNL Urgent: Invoice {$ar['invoice_number']} (₱" . number_format($ar['amount'], 2) . ") was due on " . date('M d, Y', strtotime($ar['due_date'])) . ". Please settle immediately. Thank you.";
        } else {
            $subject = "FINAL DEMAND: Overdue Invoice {$ar['invoice_number']} - HWParts MNL";
            $noticeHeader = "Final Demand Payment Notice";
            $noticeColor = "#dc2626"; // Red
            $bodyText = "This is the FINAL demand for payment of Invoice <strong>{$ar['invoice_number']}</strong> of <strong>₱" . number_format($ar['amount'], 2) . "</strong> which is now severely overdue since <strong>" . date('M d, Y', strtotime($ar['due_date'])) . "</strong>. Please settle this immediately to avoid legal actions or account termination.";
            
            $smsTemplate = "HWParts MNL FINAL DEMAND: Invoice {$ar['invoice_number']} (₱" . number_format($ar['amount'], 2) . ") is severely past due since " . date('M d, Y', strtotime($ar['due_date'])) . ". Settle immediately to avoid legal action.";
        }

        // Send Email Notice
        $emailStatus = 'failed';
        if (!empty($emails)) {
            $htmlMessage = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . esc($subject) . '</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:\'Helvetica Neue\',Helvetica,Arial,sans-serif;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f8fafc; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color:#ffffff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="background-color: ' . $noticeColor . '; padding: 24px; text-align: left; color:#ffffff;">
                            <h1 style="margin:0; font-size:22px; font-weight:700;">' . esc($noticeHeader) . '</h1>
                            <p style="margin:4px 0 0 0; font-size:14px; opacity:0.9;">HWParts MNL Billing Department</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px; text-align:left;">
                            <p style="margin:0 0 16px 0; font-size:16px; color:#1e293b;">Dear <strong>' . esc($ar['customer_name']) . '</strong>,</p>
                            <p style="margin:0 0 24px 0; font-size:15px; line-height:1.6; color:#475569;">
                                ' . $bodyText . '
                            </p>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:24px; font-size:14px;">
                                <tr>
                                    <td style="padding:14px 16px; border-bottom:1px solid #e2e8f0; color:#64748b;">BIR Invoice Number</td>
                                    <td style="padding:14px 16px; border-bottom:1px solid #e2e8f0; color:#1e293b; font-family:monospace; font-weight:bold;">' . esc($ar['invoice_number']) . '</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; border-bottom:1px solid #e2e8f0; color:#64748b;">Linked Sales Order</td>
                                    <td style="padding:14px 16px; border-bottom:1px solid #e2e8f0; color:#1e293b; font-family:monospace;">' . esc($ar['so_number']) . '</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; border-bottom:1px solid #e2e8f0; color:#64748b;">Payment Due Date</td>
                                    <td style="padding:14px 16px; border-bottom:1px solid #e2e8f0; color:#dc2626; font-weight:bold;">' . date('M d, Y', strtotime($ar['due_date'])) . '</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; color:#64748b; font-weight:bold;">Amount Due</td>
                                    <td style="padding:14px 16px; color:#2563eb; font-weight:bold; font-size:18px;">₱' . number_format($ar['amount'], 2) . '</td>
                                </tr>
                            </table>
                            <p style="margin:0 0 24px 0; font-size:15px; line-height:1.6; color:#475569;">
                                You can view your invoice details, download billing summaries, and submit payment proof by logging into your portal using the credentials provided to you during enrollment.
                            </p>
                            <div style="text-align: center; margin-bottom: 24px;">
                                <a href="' . base_url('customer/login') . '" style="background-color:#2563eb; color:#ffffff; padding:12px 24px; text-decoration:none; border-radius:4px; font-weight:bold; font-size:15px; display:inline-block;">Log In to Customer Portal</a>
                            </div>
                            <p style="margin:0; font-size:13px; color:#94a3b8; font-style:italic;">Note: If you have already settled this payment, please disregard this notice.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f1f5f9; padding:20px 32px; text-align:center; border-top:1px solid #e2e8f0; font-size:12px; color:#94a3b8;">
                            <p style="margin:0;">HWParts MNL. This is a system-generated billing message.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

            $emailService = \Config\Services::email();
            try {
                $emailService->setMailType('html');
                $emailService->setTo($emails);
                $emailService->setSubject($subject);
                $emailService->setMessage($htmlMessage);
                if ($emailService->send(false)) {
                    $emailStatus = 'sent';
                }
            } catch (\Throwable $e) {
                // Ignore mailing exceptions in local dev/simulation
            }

            // Log email notice
            $this->logModel->insert([
                'ar_id'       => $id,
                'notice_type' => $noticeType,
                'type'        => 'email',
                'recipient'   => implode('; ', $emails),
                'message'     => $htmlMessage,
                'status'      => $emailStatus,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        // Send SMS Notice
        if (!empty($mobiles)) {
            foreach ($mobiles as $mobile) {
                $smsStatus = 'failed';
                try {
                    if (SmsService::send($mobile, $smsTemplate)) {
                        $smsStatus = 'sent';
                    }
                } catch (\Throwable $e) {
                    // Ignore exceptions in simulation
                }

                $this->logModel->insert([
                    'ar_id'       => $id,
                    'notice_type' => $noticeType,
                    'type'        => 'sms',
                    'recipient'   => $mobile,
                    'message'     => $smsTemplate,
                    'status'      => $smsStatus,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->audit->log('accounts_receivable', 'notice', $id, "Sent billing reminder notice ({$noticeType}) for Invoice {$ar['invoice_number']}");

        return redirect()->to(base_url("accounts-receivable/{$id}"))->with('success', 'Reminder notifications successfully transmitted to all registered customer contacts.');
    }
}
