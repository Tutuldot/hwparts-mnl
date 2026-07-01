<style>
    .chat-container {
        max-height: 500px;
        overflow-y: auto;
        padding: 1.5rem;
        background-color: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .chat-message {
        max-width: 75%;
        padding: 0.75rem 1rem;
        border-radius: 14px;
        position: relative;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        line-height: 1.5;
    }
    .message-admin {
        align-self: flex-end;
        background-color: #2563eb;
        color: #ffffff;
        border-bottom-right-radius: 2px;
    }
    .message-customer {
        align-self: flex-start;
        background-color: #ffffff;
        color: #1f2937;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 2px;
    }
    .message-meta {
        font-size: 0.7rem;
        margin-top: 0.25rem;
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        opacity: 0.8;
    }
    .message-customer .message-meta {
        color: #64748b;
    }
    .message-admin .message-meta {
        color: #dbeafe;
    }
    .message-sender {
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 0.125rem;
        display: block;
    }
    .message-customer .message-sender {
        color: #0f172a;
    }
    .message-admin .message-sender {
        color: #bfdbfe;
    }
    .chat-img-thumb {
        max-width: 100%;
        border-radius: 8px;
        margin-top: 0.5rem;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .chat-img-thumb:hover {
        opacity: 0.9;
    }
</style>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Inquiry Thread #<?= $inquiry['id'] ?></h1>
        <p class="page-subtitle">Client: <span class="fw-bold text-dark"><?= esc($customer['name']) ?></span></p>
    </div>
    <a href="<?= base_url('admin/inquiries') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Inquiries
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <script>document.addEventListener('DOMContentLoaded',()=>toastr.success(<?= json_encode(session()->getFlashdata('success')) ?>));</script>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <script>document.addEventListener('DOMContentLoaded',()=>toastr.error(<?= json_encode(session()->getFlashdata('error')) ?>));</script>
<?php endif; ?>

<div class="row g-3 mb-5">
    <!-- Chat Card -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold text-primary"><i class="fas fa-comments text-muted me-2"></i>Inquiry Communication</span>
                <div>
                    <?php if ($inquiry['status'] === 'open'): ?>
                        <span class="badge badge-submitted me-2"><i class="fas fa-envelope-open me-1"></i>Open</span>
                        <form action="<?= base_url('admin/inquiries/' . $inquiry['id'] . '/close') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to close this inquiry? The customer will no longer be able to reply.')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-lock me-1"></i>Close Inquiry</button>
                        </form>
                    <?php else: ?>
                        <span class="badge badge-draft"><i class="fas fa-check-circle me-1"></i>Closed</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Chat Container -->
                <div class="chat-container" id="chatContainer">
                    <?php foreach ($messages as $msg): ?>
                        <?php 
                            $isAdminMsg = ($msg['sender_type'] === 'user');
                            $bubbleClass = $isAdminMsg ? 'message-admin' : 'message-customer';
                        ?>
                        <div class="chat-message <?= $bubbleClass ?>">
                            <span class="message-sender"><?= esc($msg['sender_name']) ?></span>
                            <div>
                                <?php if (!empty($msg['message'])): ?>
                                    <p class="mb-0 text-break"><?= nl2br(esc($msg['message'])) ?></p>
                                <?php endif; ?>
                                <?php if ($msg['photo_path']): ?>
                                    <img src="<?= base_url($msg['photo_path']) ?>" class="chat-img-thumb img-fluid" alt="Attached photo" onclick="showLightbox('<?= base_url($msg['photo_path']) ?>')">
                                <?php endif; ?>
                            </div>
                            <div class="message-meta">
                                <span><?= date('Y-m-d h:i A', strtotime($msg['created_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Chat Box Reply Form -->
                <div class="mt-4">
                    <?php if ($inquiry['status'] === 'open'): ?>
                        <form id="replyForm" action="<?= base_url('admin/inquiries/' . $inquiry['id'] . '/message-ajax') ?>" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <textarea class="form-control" id="messageTextarea" name="message" rows="3" placeholder="Type your response to the customer... (Press Enter to send)"></textarea>
                            </div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="flex-grow-1" style="max-width: 400px;">
                                    <div class="input-group input-group-sm">
                                        <label class="input-group-text bg-light" for="photoInput"><i class="fas fa-image me-1"></i>Attach Photo</label>
                                        <input type="file" class="form-control" id="photoInput" name="photo" accept="image/*">
                                    </div>
                                </div>
                                <button type="submit" id="submitBtn" class="btn btn-primary px-4">
                                    <i class="fas fa-paper-plane me-1"></i>Send Response
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-4 bg-light rounded" style="border: 1px dashed #cbd5e1;">
                            <i class="fas fa-lock fa-2x text-muted mb-2"></i>
                            <h6 class="fw-bold mb-1 text-secondary">This inquiry is closed</h6>
                            <p class="text-muted small mb-0">Replies are disabled for closed threads. To reply, you would need to reopen this inquiry or ask the customer to start a new thread.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar Panels -->
    <div class="col-lg-4">
        <!-- Customer details card -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white fw-bold py-3"><i class="fas fa-user text-muted me-2"></i>Client Profile</div>
            <div class="card-body">
                <h6 class="fw-bold mb-1"><?= esc($customer['name']) ?></h6>
                <div class="text-muted small mb-3"><?= esc($customer['type']) === 'corporate' ? 'Corporate Client (' . esc($customer['company_name']) . ')' : 'Individual Client' ?></div>

                <div class="mb-3">
                    <div class="text-muted small fw-bold uppercase">Billing Address</div>
                    <div class="small"><?= nl2br(esc($customer['billing_address'])) ?></div>
                </div>

                <div class="mb-0">
                    <div class="text-muted small fw-bold uppercase">Shipping Address</div>
                    <div class="small"><?= nl2br(esc($customer['shipping_address'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Sales Order details / assignment -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold py-3"><i class="fas fa-file-invoice-dollar text-muted me-2"></i>Linked Sales Order</div>
            <div class="card-body">
                <?php if ($so): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-receipt fa-3x text-success mb-3"></i>
                        <h5 class="fw-bold mb-1">
                            <a href="<?= base_url('sales-orders/' . $so['id']) ?>" class="text-decoration-none">
                                <?= esc($so['so_number']) ?>
                            </a>
                        </h5>
                        <p class="text-muted small mb-3">Amount: <strong class="text-dark">₱<?= number_format($so['amount'], 2) ?></strong></p>
                        <div class="mb-3">
                            <span class="badge badge-<?= $so['status'] ?>"><?= ucfirst($so['status']) ?></span>
                        </div>
                        <a href="<?= base_url('sales-orders/' . $so['id']) ?>" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i>View Sales Order details
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3 border-bottom mb-3">
                        <i class="fas fa-receipt fa-2x text-light mb-2"></i>
                        <p class="small mb-0">No Sales Order linked to this inquiry.</p>
                    </div>

                    <?php if ($inquiry['status'] === 'open'): ?>
                        <!-- Option 1: Create New Sales Order -->
                        <div class="mb-3">
                            <a href="<?= base_url('sales-orders/create?inquiry_id=' . $inquiry['id']) ?>" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-cart-plus me-1"></i>Create Sales Order
                            </a>
                            <div class="form-text text-muted text-center small mt-1">This will preset the client and auto-assign the order back to this inquiry.</div>
                        </div>

                        <div class="text-center text-muted small my-3">— OR —</div>

                        <!-- Option 2: Assign Existing Sales Order -->
                        <form action="<?= base_url('admin/inquiries/' . $inquiry['id'] . '/assign-so') ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Assign Existing Sales Order</label>
                                <select class="form-select form-select-sm" name="sales_order_id" required>
                                    <option value="">-- Select client Sales Order --</option>
                                    <?php foreach ($unassignedSos as $us): ?>
                                        <option value="<?= $us['id'] ?>">
                                            <?= esc($us['so_number']) ?> (₱<?= number_format($us['amount'], 2) ?> - <?= ucfirst($us['status']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($unassignedSos)): ?>
                                    <div class="form-text text-danger small mt-1">No unassigned Sales Orders found for this client.</div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100" <?= empty($unassignedSos) ? 'disabled' : '' ?>>
                                <i class="fas fa-link me-1"></i>Link Selected Order
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-light text-center small mb-0 border">
                            Sales order mapping is disabled because this inquiry thread is closed.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Image Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-header border-0 p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="lightboxImg" src="" class="img-fluid rounded" alt="Enlarged photo" style="max-height: 90vh;">
            </div>
        </div>
    </div>
</div>

<?= $this->section('extraJs') ?>
<script>
    // Scroll chat to bottom
    const chatContainer = document.getElementById('chatContainer');
    const replyForm = document.getElementById('replyForm');
    const messageTextarea = document.getElementById('messageTextarea');
    const photoInput = document.getElementById('photoInput');
    const submitBtn = document.getElementById('submitBtn');

    let lastMessageId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
    let inquiryStatus = "<?= esc($inquiry['status']) ?>";
    let hasSalesOrder = <?= $so ? 'true' : 'false' ?>;

    function scrollChatToBottom() {
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }
    scrollChatToBottom();

    // Lightbox modal helper
    function showLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        const lightbox = new bootstrap.Modal(document.getElementById('lightboxModal'));
        lightbox.show();
    }

    // Enter to Send keydown listener
    if (messageTextarea && replyForm) {
        messageTextarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                replyForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Handle AJAX reply form submission
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const message = messageTextarea.value.trim();
            const photo = photoInput.files[0];

            if (!message && !photo) {
                alert('Please type a message or choose a photo to attach.');
                return;
            }

            const formData = new FormData(replyForm);
            
            // Disable inputs while sending
            submitBtn.disabled = true;
            messageTextarea.disabled = true;
            photoInput.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

            fetch(replyForm.action, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    // Reset form fields
                    messageTextarea.value = '';
                    photoInput.value = '';
                    // Immediately check for updates to show sent message
                    fetchUpdates();
                } else {
                    alert(res.message || 'An error occurred while sending your message.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection error. Failed to send message.');
            })
            .finally(() => {
                // Re-enable inputs
                submitBtn.disabled = false;
                messageTextarea.disabled = false;
                photoInput.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Response';
                messageTextarea.focus();
            });
        });
    }

    // Fetch updates polling
    function fetchUpdates() {
        if (document.hidden || inquiryStatus === 'closed') {
            return;
        }

        fetch('<?= base_url('admin/inquiries/' . $inquiry['id'] . '/updates') ?>?last_id=' + lastMessageId)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    inquiryStatus = res.inquiry_status;
                    
                    // Update inquiry status badges in UI
                    if (inquiryStatus === 'closed') {
                        location.reload(); // Reload to update lock view states
                        return;
                    }

                    // Reload page if a new sales order is linked to initialize dropdowns/actions
                    if (res.so && !hasSalesOrder) {
                        location.reload();
                        return;
                    }

                    // Append messages
                    if (res.messages && res.messages.length > 0) {
                        res.messages.forEach(msg => {
                            const isAdmin = (msg.sender_type === 'user');
                            const bubbleClass = isAdmin ? 'message-admin' : 'message-customer';
                            
                            const msgDiv = document.createElement('div');
                            msgDiv.className = `chat-message ${bubbleClass}`;
                            
                            let contentHtml = `<span class="message-sender">${msg.sender_name}</span><div>`;
                            if (msg.message) {
                                contentHtml += `<p class="mb-0 text-break">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</p>`;
                            }
                            if (msg.photo_path) {
                                const photoUrl = '<?= base_url() ?>' + msg.photo_path;
                                contentHtml += `<img src="${photoUrl}" class="chat-img-thumb img-fluid" alt="Attached photo" onclick="showLightbox('${photoUrl}')">`;
                            }
                            contentHtml += `</div><div class="message-meta"><span>${formatDate(msg.created_at)}</span></div>`;
                            msgDiv.innerHTML = contentHtml;
                            
                            chatContainer.appendChild(msgDiv);
                            lastMessageId = Math.max(lastMessageId, parseInt(msg.id));
                        });
                        
                        scrollChatToBottom();
                    }
                }
            })
            .catch(err => console.error('Error fetching chat updates:', err));
    }

    // Helper functions
    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' ' + 
               date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    // Start polling every 4 seconds
    setInterval(fetchUpdates, 4000);

    // Fetch updates on tab focus
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            fetchUpdates();
        }
    });
</script>
<?= $this->endSection() ?>
