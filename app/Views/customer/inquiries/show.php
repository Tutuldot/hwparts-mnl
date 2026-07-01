<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry #<?= $inquiry['id'] ?> — HW Trucks MNL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        .navbar {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 600;
        }
        .badge-open { background-color: #d1fae5; color: #065f46; }
        .badge-closed { background-color: #e5e7eb; color: #374151; }

        /* Chat System CSS */
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
        .message-customer {
            align-self: flex-end;
            background-color: #0284c7;
            color: #ffffff;
            border-bottom-right-radius: 2px;
        }
        .message-admin {
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
        .message-admin .message-meta {
            color: #64748b;
        }
        .message-customer .message-meta {
            color: #e0f2fe;
        }
        .message-sender {
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
            display: block;
        }
        .message-admin .message-sender {
            color: #0f172a;
        }
        .message-customer .message-sender {
            color: #bae6fd;
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
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-cubes me-2"></i>HW Trucks MNL Customer Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPortal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPortal">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('customer/orders') ?>"><i class="fas fa-list-check me-1"></i>My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?= base_url('customer/inquiries') ?>"><i class="fas fa-question-circle me-1"></i>Inquiries</a>
                </li>
            </ul>
            <div class="navbar-nav">
                <span class="nav-item nav-link me-3"><i class="fas fa-user-circle me-1"></i>Hello, <?= esc(session()->get('customer_name')) ?></span>
                <a class="nav-link text-warning" href="<?= base_url('customer/logout') ?>"><i class="fas fa-sign-out-alt me-1"></i>Sign Out</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0">Support Inquiry Thread</h2>
            <p class="text-muted mb-0">Inquiry ID: <span class="fw-bold">#<?= $inquiry['id'] ?></span> — Created on <?= date('M d, Y', strtotime($inquiry['created_at'])) ?></p>
        </div>
        <a href="<?= base_url('customer/inquiries') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- Chat Area -->
        <div class="col-lg-8">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold"><i class="fas fa-comments text-primary me-2"></i>Conversation Thread</span>
                    <span class="badge badge-<?= $inquiry['status'] ?>"><?= ucfirst($inquiry['status']) ?></span>
                </div>
                <div class="card-body">
                    <!-- Chat Container -->
                    <div class="chat-container" id="chatContainer">
                        <?php foreach ($messages as $msg): ?>
                            <?php 
                                $isAdminMsg = ($msg['sender_type'] === 'user');
                                $bubbleClass = $isAdminMsg ? 'message-admin' : 'message-customer';
                            ?>
                            <div class="chat-message <?= $bubbleClass ?>" id="msg-<?= $msg['id'] ?>">
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
                                    <span><?= date('M d h:i A', strtotime($msg['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Chat Box Reply Form -->
                    <div class="mt-4">
                        <?php if ($inquiry['status'] === 'open'): ?>
                            <form id="replyForm" action="<?= base_url('customer/inquiries/' . $inquiry['id'] . '/message-ajax') ?>" method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <textarea class="form-control" id="messageTextarea" name="message" rows="3" placeholder="Type your response here... (Press Enter to send)"></textarea>
                                </div>
                                <div id="spamWarning" class="alert alert-danger p-2 small mb-2 d-none">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Please wait for a response from our staff before sending more messages.
                                </div>
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="flex-grow-1" style="max-width: 400px;">
                                        <div class="input-group input-group-sm">
                                            <label class="input-group-text bg-light" for="photoInput"><i class="fas fa-image me-1"></i>Attach Photo</label>
                                            <input type="file" class="form-control" id="photoInput" name="photo" accept="image/*">
                                        </div>
                                    </div>
                                    <button type="submit" id="submitBtn" class="btn btn-primary px-4">
                                        <i class="fas fa-paper-plane me-1"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-4 bg-light rounded" style="border: 1px dashed #cbd5e1;">
                                <i class="fas fa-lock fa-2x text-muted mb-2"></i>
                                <h6 class="fw-bold mb-1 text-secondary">This inquiry is closed</h6>
                                <p class="text-muted small mb-0">You cannot send replies to closed threads. If you have another issue, please go back and create a new inquiry.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Panel / Order Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold py-3"><i class="fas fa-info-circle text-primary me-2"></i>Inquiry Info</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-0 small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Inquiry Status:</span>
                            <span class="fw-bold text-capitalize"><?= esc($inquiry['status']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Created Date:</span>
                            <span class="fw-bold"><?= date('M d, Y h:i A', strtotime($inquiry['created_at'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Last Updated:</span>
                            <span class="fw-bold"><?= date('M d, Y h:i A', strtotime($inquiry['updated_at'])) ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Linked Sales Order -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold py-3"><i class="fas fa-file-invoice-dollar text-success me-2"></i>Linked Sales Order</div>
                <div class="card-body text-center py-4">
                    <?php if ($so): ?>
                        <i class="fas fa-receipt fa-3x text-success mb-3"></i>
                        <h5 class="fw-bold mb-1"><?= esc($so['so_number']) ?></h5>
                        <p class="text-muted small mb-3">Invoice Amount: <strong class="text-dark">₱<?= number_format($so['amount'], 2) ?></strong></p>
                        
                        <div class="mb-3">
                            <span class="badge bg-secondary"><?= ucfirst($so['status']) ?></span>
                        </div>
                        
                        <a href="<?= base_url('customer/orders/' . $so['id']) ?>" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i>View Sales Invoice
                        </a>
                    <?php else: ?>
                        <i class="fas fa-receipt fa-3x text-light mb-3"></i>
                        <p class="text-muted small mb-0">No Sales Order is currently assigned to this inquiry.</p>
                    <?php endif; ?>
                </div>
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

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Scroll chat to bottom
    // Scroll chat to bottom
    const chatContainer = document.getElementById('chatContainer');
    const replyForm = document.getElementById('replyForm');
    const messageTextarea = document.getElementById('messageTextarea');
    const photoInput = document.getElementById('photoInput');
    const submitBtn = document.getElementById('submitBtn');
    const spamWarning = document.getElementById('spamWarning');

    let lastMessageId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
    let inquiryStatus = "<?= esc($inquiry['status']) ?>";

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
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Message';
                messageTextarea.focus();
            });
        });
    }

    // Fetch updates polling
    function fetchUpdates() {
        if (document.hidden || inquiryStatus === 'closed') {
            return;
        }

        fetch('<?= base_url('customer/inquiries/' . $inquiry['id'] . '/updates') ?>?last_id=' + lastMessageId)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    inquiryStatus = res.inquiry_status;
                    
                    // Update inquiry status badges in UI
                    if (inquiryStatus === 'closed') {
                        location.reload(); // Quickest way to lock view state for closed thread
                        return;
                    }

                    // Check spam warning UI
                    if (res.spam_blocked) {
                        messageTextarea.disabled = true;
                        photoInput.disabled = true;
                        submitBtn.disabled = true;
                        messageTextarea.placeholder = "Please wait for a staff response before sending more...";
                        spamWarning.classList.remove('d-none');
                    } else {
                        messageTextarea.disabled = false;
                        photoInput.disabled = false;
                        submitBtn.disabled = false;
                        messageTextarea.placeholder = "Type your response here... (Press Enter to send)";
                        spamWarning.classList.add('d-none');
                    }

                    // Append messages
                    if (res.messages && res.messages.length > 0) {
                        res.messages.forEach(msg => {
                            if (document.getElementById(`msg-${msg.id}`)) {
                                return; // Prevent double rendering
                            }
                            const isAdmin = (msg.sender_type === 'user');
                            const bubbleClass = isAdmin ? 'message-admin' : 'message-customer';
                            
                            const msgDiv = document.createElement('div');
                            msgDiv.className = `chat-message ${bubbleClass}`;
                            msgDiv.id = `msg-${msg.id}`;
                            
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

                    // Update sales order side panel if updated
                    if (res.so) {
                        const soPanelBody = document.querySelector('.card-body.text-center');
                        if (soPanelBody) {
                            const soUrl = '<?= base_url('customer/orders/') ?>' + res.so.id;
                            soPanelBody.innerHTML = `
                                <i class="fas fa-receipt fa-3x text-success mb-3"></i>
                                <h5 class="fw-bold mb-1">${res.so.so_number}</h5>
                                <p class="text-muted small mb-3">Invoice Amount: <strong class="text-dark">₱${res.so.amount}</strong></p>
                                <div class="mb-3">
                                    <span class="badge bg-secondary">${res.so.status.charAt(0).toUpperCase() + res.so.status.slice(1)}</span>
                                </div>
                                <a href="${soUrl}" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-eye me-1"></i>View Sales Invoice
                                </a>
                            `;
                        }
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
</body>
</html>
