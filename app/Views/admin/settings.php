<?php /** @var string $smtpHost @var int $smtpPort @var string $smtpUser @var string $smtpCrypto @var string $fromEmail @var string $fromName */ ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-cog me-2 text-primary"></i>System Settings</h1>
        <p class="page-subtitle">Manage application configuration and test integrations</p>
    </div>
</div>

<div class="row g-4">
    <!-- SMTP Configuration Card -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="fas fa-server text-primary"></i>
                <span class="fw-bold">SMTP Configuration</span>
                <span class="badge bg-success ms-auto">Active</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Current outgoing mail server settings loaded from <code>app/Config/Email.php</code>.
                    To change these, update the <code>.env</code> file and restart the server.
                </p>
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold" style="width:40%">SMTP Host</td>
                            <td class="font-monospace"><?= esc($smtpHost) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">SMTP Port</td>
                            <td class="font-monospace"><?= esc($smtpPort) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Encryption</td>
                            <td>
                                <span class="badge bg-<?= $smtpCrypto === 'ssl' ? 'success' : ($smtpCrypto === 'tls' ? 'info' : 'secondary') ?>">
                                    <?= strtoupper(esc($smtpCrypto)) ?: 'NONE' ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">SMTP Username</td>
                            <td class="font-monospace small"><?= esc($smtpUser) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">From Email</td>
                            <td class="font-monospace small"><?= esc($fromEmail) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">From Name</td>
                            <td><?= esc($fromName) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Test Email Card -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="fas fa-paper-plane text-success"></i>
                <span class="fw-bold">Test Email Sending</span>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted small mb-4">
                    Send a test email to verify your SMTP settings are working. The test message will use the 
                    configured SMTP credentials above. Check both your inbox and spam folder after sending.
                </p>

                <div id="testEmailResult" class="mb-3" style="display:none;"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Recipient Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" id="testEmailInput" class="form-control"
                               placeholder="e.g. yourname@example.com"
                               value="<?= esc($smtpUser) ?>">
                    </div>
                    <div class="form-text">Leave blank to send to the configured SMTP user, or enter any address.</div>
                </div>

                <div class="mt-auto">
                    <button type="button" id="sendTestEmailBtn" class="btn btn-success w-100 py-2">
                        <i class="fas fa-paper-plane me-2"></i>Send Test Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Debug Output (hidden by default, shown on error) -->
    <div class="col-12" id="debugOutputCard" style="display:none;">
        <div class="card shadow-sm border-0 border-start border-danger border-3">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="fas fa-bug text-danger"></i>
                <span class="fw-bold text-danger">SMTP Debug Output</span>
            </div>
            <div class="card-body">
                <pre id="debugOutput" class="bg-dark text-light p-3 rounded small mb-0"
                     style="white-space:pre-wrap;max-height:300px;overflow-y:auto;"></pre>
            </div>
        </div>
    </div>

    <!-- Company & Invoice Print Settings Card -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="fas fa-building text-primary"></i>
                <span class="fw-bold">Company & Official Invoice Print Configuration</span>
                <span class="badge bg-primary ms-auto">Configurable</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    These parameters control the header, proprietor details, VAT TIN, address, contact phone, and Authority to Print (ATP) footer displayed on official Sales Invoices, Accounts Receivable receipts, and Purchase Order printable reports & PDFs.
                </p>
                <form action="<?= base_url('admin/settings/save-company') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold small">Company Name *</label>
                            <input type="text" name="company_name" class="form-control" value="<?= esc($companySettings['company_name'] ?? 'HW TRUCK PARTS TRADING') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold small">Proprietor / Tagline</label>
                            <input type="text" name="company_tagline" class="form-control" value="<?= esc($companySettings['company_tagline'] ?? 'Ana Lourdes C. Bagalihog - Prop.') ?>" placeholder="e.g. Ana Lourdes C. Bagalihog - Prop.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold small">VAT Registered TIN *</label>
                            <input type="text" name="company_tin" class="form-control font-monospace" value="<?= esc($companySettings['company_tin'] ?? '427-851-105-00000') ?>" required placeholder="e.g. 427-851-105-00000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold small">Contact Phone / Mobile</label>
                            <input type="text" name="company_phone" class="form-control" value="<?= esc($companySettings['company_phone'] ?? '+63 917 123 4567') ?>" placeholder="e.g. +63 917 123 4567">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold small">Company Email</label>
                            <input type="email" name="company_email" class="form-control" value="<?= esc($companySettings['company_email'] ?? 'sales@hwtruckparts.ph') ?>" placeholder="e.g. sales@hwtruckparts.ph">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold small">Official Business Address *</label>
                            <textarea name="company_address" class="form-control" rows="3" required><?= esc($companySettings['company_address'] ?? '') ?></textarea>
                            <small class="text-muted">Enter full street, barangay, city, province and postal code.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold small">Authority to Print (ATP) / Footnote Details</label>
                            <textarea name="atp_text" class="form-control font-monospace small" rows="3"><?= esc($companySettings['atp_text'] ?? '') ?></textarea>
                            <small class="text-muted">Printed at the bottom of Official BIR Sales Invoices.</small>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary font-weight-bold">
                            <i class="fas fa-save me-1"></i> Save Company Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('sendTestEmailBtn').addEventListener('click', function () {
    const btn      = this;
    const emailVal = document.getElementById('testEmailInput').value.trim();
    const resultEl = document.getElementById('testEmailResult');
    const debugCard = document.getElementById('debugOutputCard');
    const debugPre  = document.getElementById('debugOutput');

    if (!emailVal) {
        resultEl.innerHTML = '<div class="alert alert-warning py-2 small"><i class="fas fa-exclamation-triangle me-1"></i>Please enter a recipient email address.</div>';
        resultEl.style.display = '';
        return;
    }

    // Loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending…';
    resultEl.style.display = 'none';
    debugCard.style.display = 'none';

    const formData = new FormData();
    formData.append('email', emailVal);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url('admin/settings/send-test-email') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            resultEl.innerHTML = `
                <div class="alert alert-success py-3 d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-check-circle fs-5 flex-shrink-0"></i>
                    <span>${data.message}</span>
                </div>`;
            debugCard.style.display = 'none';
        } else {
            resultEl.innerHTML = `
                <div class="alert alert-danger py-3 d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-times-circle fs-5 flex-shrink-0"></i>
                    <span>${data.message}</span>
                </div>`;
            if (data.debug) {
                debugPre.textContent = data.debug;
                debugCard.style.display = '';
                debugCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        resultEl.style.display = '';
    })
    .catch(err => {
        resultEl.innerHTML = `<div class="alert alert-danger py-2 small"><i class="fas fa-times-circle me-1"></i>Request failed: ${err.message}</div>`;
        resultEl.style.display = '';
        console.error(err);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Test Email';
    });
});

// Allow Enter key in the email input
document.getElementById('testEmailInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') document.getElementById('sendTestEmailBtn').click();
});
</script>
