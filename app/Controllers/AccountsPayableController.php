<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AccountsPayableModel;
use App\Models\PurchaseOrderModel;
use App\Models\SupplierModel;
use App\Models\SupplierContactModel;
use App\Models\AuditLogModel;
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
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Accounts Payable', null]],
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

        $data = [
            'pageTitle'  => 'Payable: ' . $payable['po_number'],
            'breadcrumb' => [['HWParts MNL', base_url('dashboard')], ['Accounts Payable', base_url('accounts-payable')], [$payable['po_number'], null]],
            'payable'    => $payable,
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

        $rules = [
            'payment_type'      => 'required|in_list[GCASH,BANK TRANSFER,Cheque,Cash via Transmittal]',
            'payment_reference' => 'required|min_length[2]|max_length[100]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
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
        if ($paymentType === 'Cheque') {
            $bank = trim($this->request->getPost('cheque_bank') ?? '');
            $chkNum = trim($this->request->getPost('cheque_number') ?? '');
            if (empty($bank) || empty($chkNum)) {
                return redirect()->back()->withInput()->with('error', 'For Cheque payments, both Bank Name and Check Number are required.');
            }
            $chequeDetails = $bank . ' - Chk# ' . $chkNum;
        }

        $this->apModel->update($id, [
            'status'            => 'paid',
            'payment_reference' => $this->request->getPost('payment_reference'),
            'payment_type'      => $paymentType,
            'cheque_details'    => $chequeDetails,
            'proof_of_payment'  => $proofPath,
            'paid_at'           => date('Y-m-d H:i:s'),
            'paid_by'           => session()->get('user_id'),
        ]);

        // Audit log
        $this->audit->log('accounts_payable', 'pay', $id, "Settled AP linked to PO {$payable['po_number']} via {$paymentType}");

        // 1. Send Remittance Advice via Email
        $this->sendEmailRemittance($payable, $paymentType, $chequeDetails);

        // 2. Send SMS Remittance Advice to Supplier Contacts
        $this->sendSmsRemittance($payable, $paymentType, $chequeDetails);

        return redirect()->to(base_url("accounts-payable/{$id}"))->with('success', 'Payment recorded successfully. Remittance notifications sent.');
    }

    private function sendEmailRemittance(array $payable, string $paymentType, ?string $chequeDetails)
    {
        if (empty($payable['emails_for_notice'])) {
            return;
        }

        $emails = explode(';', $payable['emails_for_notice']);
        $recipientEmails = [];
        foreach ($emails as $email) {
            $trimmed = trim($email);
            if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                $recipientEmails[] = $trimmed;
            }
        }

        if (empty($recipientEmails)) {
            return;
        }

        $subject = "Remittance Advice - Settle Payment for PO " . $payable['po_number'];
        $detailsStr = $paymentType === 'Cheque' ? " ($chequeDetails)" : "";
        $message = "Dear " . esc($payable['supplier_name']) . ",\n\n"
                 . "Please be advised that we have settled the payment for Purchase Order " . $payable['po_number'] . ".\n\n"
                 . "Payment Details:\n"
                 . "- Amount Paid: ₱" . number_format($payable['amount'], 2) . "\n"
                 . "- Payment Form: " . $paymentType . $detailsStr . "\n"
                 . "- Reference/Transaction ID: " . esc($this->request->getPost('payment_reference')) . "\n"
                 . "- Settled Date: " . date('M d, Y H:i A') . "\n\n"
                 . "You may verify this payment directly on your records. Thank you.\n\n"
                 . "Sincerely,\n"
                 . "Finance Department\n"
                 . "HWParts MNL Supply Chain";

        $emailService = \Config\Services::email();
        try {
            $emailService->setTo($recipientEmails);
            $emailService->setSubject($subject);
            $emailService->setMessage($message);
            $emailService->send(false); // Send without throwing exception
        } catch (\Throwable $e) {
            // Ignore sending exceptions in local dev/simulation
        }
    }

    private function sendSmsRemittance(array $payable, string $paymentType, ?string $chequeDetails)
    {
        $scmModel = new SupplierContactModel();
        // Retrieve visible supplier contacts who have a mobile number
        $contacts = $scmModel->where('supplier_id', $payable['supplier_id'])
                             ->where('is_visible', 1)
                             ->where('mobile IS NOT NULL AND mobile != ""')
                             ->findAll();

        if (empty($contacts)) {
            return;
        }

        $ref = esc($this->request->getPost('payment_reference'));
        $detailsStr = $paymentType === 'Cheque' ? " ($chequeDetails)" : "";
        
        $smsMessage = "HWParts MNL Remittance: Settled ₱" . number_format($payable['amount'], 2) . " for PO " . $payable['po_number'] . " via " . $paymentType . $detailsStr . " (Ref: " . $ref . "). Thank you!";

        foreach ($contacts as $contact) {
            SmsService::send($contact['mobile'], $smsMessage);
        }
    }
}
