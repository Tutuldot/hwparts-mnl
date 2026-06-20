<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">POS Sales Cash Register</h1>
        <p class="page-subtitle">Scan barcodes or search parts to assemble a new customer sales invoice</p>
    </div>
    <a href="<?= base_url('sales-orders') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Orders
    </a>
</div>

<div class="row g-3">
    <!-- Cart and Scanning Area -->
    <div class="col-lg-8">
        <!-- Search & Barcode Scan -->
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-2">
                    <!-- Barcode Input (Priority Scan) -->
                    <div class="col-md-5">
                        <label class="form-label font-weight-bold text-primary small"><i class="fas fa-barcode me-1"></i> Barcode Scanner (Press Enter)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-barcode"></i></span>
                            <input type="text" id="barcodeInput" class="form-control border-start-0 border-end-0" placeholder="Scan barcode or type exact SKU..." autocomplete="off">
                            <button class="btn btn-outline-primary" type="button" id="scanBarcodeBtn" title="Scan Barcode/QR with Camera">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Text Search Suggestions -->
                    <div class="col-md-7">
                        <label class="form-label font-weight-bold text-success small"><i class="fas fa-search me-1"></i> Search Parts by Name / SKU</label>
                        <div class="position-relative">
                            <input type="text" id="partSearchInput" class="form-control" placeholder="Type part name or SKU to search..." autocomplete="off">
                            <div class="dropdown-menu w-100 shadow-lg border-0" id="searchSuggestions" style="max-height: 300px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- POS Cart list -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-shopping-cart text-muted me-2"></i>Sales Cart Items</span>
                <button type="button" class="btn btn-xs btn-outline-danger" id="clearCartBtn"><i class="fas fa-trash me-1"></i>Clear Cart</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small" id="cartTable">
                        <thead class="table-light">
                            <tr>
                                <th>Part / Variant Name</th>
                                <th style="width: 140px;">SKU</th>
                                <th style="width: 120px;" class="text-center">Quantity</th>
                                <th style="width: 150px;">Unit Price (₱)</th>
                                <th style="width: 150px;" class="text-end">Total Price</th>
                                <th style="width: 60px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                            <tr id="emptyCartRow">
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-cash-register fa-3x mb-3 text-light"></i>
                                    <p class="mb-0 font-weight-bold">Your cart is empty.</p>
                                    <p class="text-muted small mb-0">Use the barcode scanner or search box above to add parts.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Details & Summary Panel -->
    <div class="col-lg-4">
        <!-- Customer Selector -->
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold">Order Client Info</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label font-weight-medium small">Select Customer *</label>
                    <select class="form-select" id="customerSelect" required>
                        <option value="">-- Select Active Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" data-terms="<?= $c['payment_terms'] ?>">
                                <?= esc($c['name']) ?> <?= $c['type'] === 'corporate' ? '(' . esc($c['company_name']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label font-weight-medium small">Order Remarks / Internal Notes</label>
                    <textarea id="orderRemarks" class="form-control" rows="3" placeholder="Reference note, invoice remark, shipping codes, etc."></textarea>
                </div>
            </div>
        </div>

        <!-- Checkout Summary Panel -->
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body">
                <h5 class="font-weight-bold text-dark mb-3">Order Checkout</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Items</span>
                    <span class="font-weight-bold small text-dark" id="summaryTotalItems">0</span>
                </div>
                <div class="d-flex justify-content-between mb-4 border-top pt-2">
                    <span class="font-weight-bold text-dark">Grand Total</span>
                    <span class="font-weight-black text-primary fs-4" id="summaryGrandTotal">₱0.00</span>
                </div>
                <button type="button" class="btn btn-primary w-100 py-2 font-weight-bold" id="checkoutBtn">
                    <i class="fas fa-save me-2"></i> Save Sales Order Draft
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scanner Modal -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scannerModalLabel">Scan Barcode / QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reader" style="width:100%; min-height:250px; background:#f8f9fa; border:1px dashed #ccc; border-radius:4px; overflow:hidden;"></div>
                <div id="scanFeedback" class="mt-2 text-center text-muted" style="font-size:0.85rem;">Position the barcode/QR inside the frame.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    // State of our POS cart
    let cart = [];

    const barcodeInput = document.getElementById('barcodeInput');
    const partSearchInput = document.getElementById('partSearchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const cartItems = document.getElementById('cartItems');
    const emptyCartRow = document.getElementById('emptyCartRow');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const customerSelect = document.getElementById('customerSelect');
    const orderRemarks = document.getElementById('orderRemarks');
    
    const summaryTotalItems = document.getElementById('summaryTotalItems');
    const summaryGrandTotal = document.getElementById('summaryGrandTotal');

    // Focus on barcode scanner by default
    window.onload = function() {
        barcodeInput.focus();
    };

    // Camera Scanner implementation
    let html5QrCode = null;
    const scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));

    document.getElementById('scanBarcodeBtn').addEventListener('click', () => {
        scannerModal.show();
        setTimeout(() => {
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 15, qrbox: { width: 250, height: 200 } };
            
            html5QrCode.start(
                { facingMode: "environment" }, 
                config,
                (decodedText, decodedResult) => {
                    if (html5QrCode) {
                        html5QrCode.stop().then(() => {
                            html5QrCode = null;
                            scannerModal.hide();
                            lookupBarcodeOrSku(decodedText);
                        }).catch(err => {
                            console.error(err);
                            scannerModal.hide();
                        });
                    }
                },
                (errorMessage) => {
                    // ignore
                }
            ).catch(err => {
                console.error("Camera error", err);
                document.getElementById('reader').innerHTML = `
                    <div class="alert alert-danger m-3 text-center" role="alert">
                        <i class="fas fa-exclamation-circle d-block fs-3 mb-2"></i>
                        <strong>Camera Error</strong><br>
                        <span style="font-size:0.85rem">${err}</span>
                    </div>`;
            });
        }, 450);
    });

    document.getElementById('scannerModal').addEventListener('hidden.bs.modal', () => {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode = null;
            }).catch(err => {
                console.error(err);
                html5QrCode = null;
            });
        }
        document.getElementById('reader').innerHTML = '';
        barcodeInput.focus();
    });

    // Prevent enter submit on barcode field, execute Ajax lookup
    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value.trim();
            if (val) {
                lookupBarcodeOrSku(val);
            }
        }
    });

    // Lookup part by barcode or exact SKU
    function lookupBarcodeOrSku(query) {
        fetch(`<?= base_url('sales-orders/ajax/search-parts') ?>?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    // If exact match (or we take the first match)
                    const match = data.find(i => i.barcode_value === query || i.sku === query) || data[0];
                    addToCart(match);
                    barcodeInput.value = '';
                } else {
                    alert(`Item with SKU or Barcode "${query}" not found.`);
                }
                barcodeInput.focus();
            })
            .catch(err => {
                console.error("Error looking up item:", err);
                barcodeInput.focus();
            });
    }

    // Ajax autocomplete Search Box
    let searchTimeout = null;
    partSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        if (query.length < 2) {
            searchSuggestions.classList.remove('show');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`<?= base_url('sales-orders/ajax/search-parts') ?>?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    renderSuggestions(data);
                });
        }, 300);
    });

    // Close suggestions on outside click
    document.addEventListener('click', function(e) {
        if (!partSearchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.classList.remove('show');
        }
    });

    function renderSuggestions(items) {
        searchSuggestions.innerHTML = '';
        if (items.length === 0) {
            searchSuggestions.innerHTML = '<span class="dropdown-item text-muted">No items found</span>';
            searchSuggestions.classList.add('show');
            return;
        }

        items.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dropdown-item d-flex align-items-center justify-content-between py-2';
            btn.innerHTML = `
                <div>
                    <strong class="text-dark">${escapeHtml(item.part_name)}</strong>
                    ${item.variant_name ? `<span class="badge bg-light text-dark ms-1">${escapeHtml(item.variant_name)}</span>` : ''}
                    <div class="text-muted small font-monospace">${escapeHtml(item.sku)}</div>
                </div>
                <span class="badge bg-secondary font-weight-normal">${item.type === 'non_quantity' ? 'Non-Qty Serialized' : 'Quantity Tracked'}</span>
            `;
            btn.addEventListener('click', () => {
                addToCart(item);
                partSearchInput.value = '';
                searchSuggestions.classList.remove('show');
                barcodeInput.focus();
            });
            searchSuggestions.appendChild(btn);
        });

        searchSuggestions.classList.add('show');
    }

    // Cart Handlers
    function addToCart(item) {
        // Check if already in cart
        const existing = cart.find(i => i.part_id === item.part_id && i.variant_id === item.variant_id);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                part_id: item.part_id,
                variant_id: item.variant_id,
                part_name: item.part_name,
                variant_name: item.variant_name,
                sku: item.sku,
                quantity: 1,
                unit_price: 0.00
            });
        }
        renderCart();
    }

    function removeFromCart(partId, variantId) {
        cart = cart.filter(i => !(i.part_id === partId && i.variant_id === variantId));
        renderCart();
    }

    function updateQty(partId, variantId, qty) {
        const item = cart.find(i => i.part_id === partId && i.variant_id === variantId);
        if (item) {
            item.quantity = Math.max(1, parseInt(qty) || 1);
            renderCart();
        }
    }

    function updatePrice(partId, variantId, price) {
        const item = cart.find(i => i.part_id === partId && i.variant_id === variantId);
        if (item) {
            item.unit_price = Math.max(0.00, parseFloat(price) || 0.00);
            renderCart();
        }
    }

    clearCartBtn.addEventListener('click', () => {
        cart = [];
        renderCart();
    });

    function renderCart() {
        if (cart.length === 0) {
            emptyCartRow.style.display = 'table-row';
            // Clear items except the emptyCartRow
            const rows = cartItems.querySelectorAll('tr:not(#emptyCartRow)');
            rows.forEach(r => r.remove());
            updateSummary();
            return;
        }

        emptyCartRow.style.display = 'none';
        const rows = cartItems.querySelectorAll('tr:not(#emptyCartRow)');
        rows.forEach(r => r.remove());

        cart.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="font-weight-bold text-dark">${escapeHtml(item.part_name)}</div>
                    ${item.variant_name ? `<span class="badge bg-light text-dark">${escapeHtml(item.variant_name)}</span>` : ''}
                </td>
                <td class="font-monospace text-muted">${escapeHtml(item.sku)}</td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center font-weight-bold" min="1" value="${item.quantity}" style="width: 80px; margin: 0 auto;" onchange="updateQty(${item.part_id}, ${item.variant_id}, this.value)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm font-weight-medium" min="0.00" step="0.01" value="${item.unit_price.toFixed(2)}" style="width: 120px;" onchange="updatePrice(${item.part_id}, ${item.variant_id}, this.value)">
                </td>
                <td class="text-end font-weight-bold text-dark">₱${(item.quantity * item.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.part_id}, ${item.variant_id})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            cartItems.appendChild(tr);
        });

        updateSummary();
    }

    function updateSummary() {
        let totalItems = 0;
        let grandTotal = 0;

        cart.forEach(item => {
            totalItems += item.quantity;
            grandTotal += item.quantity * item.unit_price;
        });

        summaryTotalItems.textContent = totalItems;
        summaryGrandTotal.textContent = '₱' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Checkout Form Submission via Fetch POST
    checkoutBtn.addEventListener('click', function() {
        const customerId = customerSelect.value;
        if (!customerId) {
            alert('Please select a customer.');
            customerSelect.focus();
            return;
        }

        if (cart.length === 0) {
            alert('Cart is empty. Please add items before checking out.');
            partSearchInput.focus();
            return;
        }

        const data = new FormData();
        data.append('customer_id', customerId);
        data.append('remarks', orderRemarks.value);
        data.append('lines', JSON.stringify(cart));
        data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

        fetch('<?= base_url('sales-orders/store') ?>', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                window.location.href = res.redirect;
            } else {
                alert(res.message || 'Error occurred while saving sales order.');
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = '<i class="fas fa-save me-2"></i> Save Sales Order Draft';
            }
        })
        .catch(err => {
            console.error(err);
            alert('An unexpected server error occurred.');
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fas fa-save me-2"></i> Save Sales Order Draft';
        });
    });

    // Helper functions
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
