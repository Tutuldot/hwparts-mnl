<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Email\Email;

class SettingsController extends BaseController
{
    public function index()
    {
        $emailConfig = config('Email');
        $data = [
            'pageTitle'   => 'System Settings',
            'breadcrumb'  => [['HW Trucks MNL', base_url('dashboard')], ['Admin', '#'], ['Settings', null]],
            'smtpHost'    => $emailConfig->SMTPHost,
            'smtpPort'    => $emailConfig->SMTPPort,
            'smtpUser'    => $emailConfig->SMTPUser,
            'smtpCrypto'  => $emailConfig->SMTPCrypto,
            'fromEmail'   => $emailConfig->fromEmail,
            'fromName'    => $emailConfig->fromName,
        ];
        return view('layouts/main', $data + ['content' => view('admin/settings', $data)]);
    }

    /**
     * AJAX endpoint: send a test email and return JSON result.
     */
    public function sendTestEmail()
    {
        if (session()->get('user_role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized.'])->setStatusCode(403);
        }

        $toEmail = trim($this->request->getPost('email') ?? '');
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please enter a valid email address.']);
        }

        $email = \Config\Services::email();

        $body = $this->buildTestEmailHtml($toEmail);

        $email->setFrom(config('Email')->fromEmail, config('Email')->fromName);
        $email->setTo($toEmail);
        $email->setSubject('✉️ HW Trucks MNL — Email Configuration Test');
        $email->setMessage($body);
        $email->setMailType('html');

        if ($email->send()) {
            (new \App\Models\AuditLogModel())->log('settings', 'test_email', 0, "Test email sent to: {$toEmail} by user #" . session()->get('user_id'));
            return $this->response->setJSON([
                'success' => true,
                'message' => "Test email successfully delivered to <strong>{$toEmail}</strong>. Please check your inbox (and spam folder).",
            ]);
        }

        $debugMsg = $email->printDebugger(['headers', 'subject']);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to send email. Check your SMTP configuration.',
            'debug'   => strip_tags($debugMsg),
        ]);
    }

    private function buildTestEmailHtml(string $toEmail): string
    {
        $sentAt  = date('F j, Y — g:i A');
        $sentBy  = esc(session()->get('user_name') ?? 'Administrator');
        $appUrl  = base_url();

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { margin:0; padding:0; background:#f0f4f8; font-family:'Segoe UI',Arial,sans-serif; }
  .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%); padding:40px 32px; text-align:center; }
  .header h1 { color:#fff; margin:0 0 6px; font-size:24px; font-weight:700; letter-spacing:-.5px; }
  .header p { color:rgba(255,255,255,.8); margin:0; font-size:14px; }
  .badge { display:inline-block; background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.4); color:#fff; padding:4px 14px; border-radius:20px; font-size:12px; margin-top:14px; letter-spacing:.5px; }
  .body { padding:36px 32px; }
  .check-circle { text-align:center; margin-bottom:24px; }
  .check-circle span { display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; background:#dcfce7; border-radius:50%; font-size:28px; }
  h2 { color:#1e3a5f; font-size:20px; text-align:center; margin:0 0 12px; }
  .desc { color:#64748b; text-align:center; font-size:15px; line-height:1.6; margin:0 0 28px; }
  .info-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:20px 24px; margin-bottom:28px; }
  .info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #e2e8f0; font-size:14px; }
  .info-row:last-child { border-bottom:none; }
  .info-label { color:#94a3b8; font-weight:500; }
  .info-value { color:#1e293b; font-weight:600; }
  .footer { background:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 32px; text-align:center; color:#94a3b8; font-size:12px; }
  .footer a { color:#2563eb; text-decoration:none; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>HW Trucks MNL</h1>
    <p>Hardware Parts Management System</p>
    <div class="badge">EMAIL TEST</div>
  </div>
  <div class="body">
    <div class="check-circle"><span>✅</span></div>
    <h2>Your email is working perfectly!</h2>
    <p class="desc">This is a test message to confirm your SMTP configuration is set up correctly. No action is required.</p>
    <div class="info-box">
      <div class="info-row">
        <span class="info-label">Sent To</span>
        <span class="info-value">{$toEmail}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Sent By</span>
        <span class="info-value">{$sentBy}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Sent At</span>
        <span class="info-value">{$sentAt}</span>
      </div>
      <div class="info-row">
        <span class="info-label">SMTP Host</span>
        <span class="info-value">{$this->getSMTPHostDisplay()}</span>
      </div>
    </div>
    <p style="color:#94a3b8;font-size:13px;text-align:center;">If you received this email, your mail system is configured correctly and ready to send transactional messages including remittance advice, payment receipts, and customer invoices.</p>
  </div>
  <div class="footer">
    &copy; {$this->getYear()} HW Trucks MNL &mdash; <a href="{$appUrl}">{$appUrl}</a>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function getSMTPHostDisplay(): string
    {
        $cfg = config('Email');
        return esc($cfg->SMTPHost . ':' . $cfg->SMTPPort);
    }

    private function getYear(): string
    {
        return date('Y');
    }
}
