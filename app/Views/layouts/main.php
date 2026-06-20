<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'HWParts MNL' ?> — HWParts MNL</title>
    <meta name="description" content="<?= $metaDesc ?? 'HWParts MNL Supply Chain Management System' ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <?= $extraCss ?? '' ?>
    <style>
        :root {
            --primary:      #0A1628;
            --primary-light:#0F1E38;
            --secondary:    #1B3A6B;
            --accent:       #2563EB;
            --accent-h:     #1D4ED8;
            --surface:      #FFFFFF;
            --surface-alt:  #F1F5F9;
            --border:       #E2E8F0;
            --text:         #0F172A;
            --muted:        #64748B;
            --success:      #10B981;
            --warning:      #F59E0B;
            --danger:       #EF4444;
            --info:         #0EA5E9;
            --sidebar-w:    260px;
            --topbar-h:     64px;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--surface-alt); color: var(--text); margin: 0; }

        /* ─── Sidebar ─────────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w); background: var(--primary);
            display: flex; flex-direction: column;
            z-index: 1000; transition: transform .3s ease;
            box-shadow: 4px 0 24px rgba(0,0,0,.25);
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: .75rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-decoration: none;
        }
        .sidebar-brand .brand-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), #60A5FA);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem; flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(37,99,235,.4);
        }
        .sidebar-brand .brand-text { color: #fff; font-weight: 700; font-size: 1.0625rem; line-height: 1.1; }
        .sidebar-brand .brand-sub  { color: rgba(255,255,255,.45); font-size: .6875rem; font-weight: 400; }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: .75rem 0; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }
        .nav-section { padding: .5rem 1.5rem .25rem; font-size: .6875rem; font-weight: 600;
                        text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.3); margin-top: .5rem; }
        .nav-item { display: flex; align-items: center; gap: .75rem;
                    padding: .625rem 1.5rem; color: rgba(255,255,255,.65);
                    text-decoration: none; font-size: .875rem; font-weight: 500;
                    transition: all .2s; position: relative; border-radius: 0; }
        .nav-item i { width: 18px; text-align: center; font-size: .9375rem; }
        .nav-item:hover { color: #fff; background: rgba(255,255,255,.06); }
        .nav-item.active { color: #fff; background: linear-gradient(90deg, rgba(37,99,235,.3), rgba(37,99,235,.05)); }
        .nav-item.active::before {
            content: ''; position: absolute; left: 0; top: 20%; bottom: 20%;
            width: 3px; background: var(--accent); border-radius: 0 3px 3px 0;
        }
        .nav-badge { margin-left: auto; background: var(--danger); color: #fff;
                     font-size: .6875rem; font-weight: 700; padding: .125rem .4375rem;
                     border-radius: 20px; min-width: 20px; text-align: center; }
        .nav-divider { height: 1px; background: rgba(255,255,255,.07); margin: .5rem 1.5rem; }
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-user { display: flex; align-items: center; gap: .75rem; }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: .875rem; flex-shrink: 0;
        }
        .user-name { color: #fff; font-size: .875rem; font-weight: 600; }
        .user-role { color: rgba(255,255,255,.45); font-size: .75rem; text-transform: capitalize; }
        .btn-logout {
            display: flex; align-items: center; gap: .5rem;
            color: rgba(255,255,255,.45); font-size: .75rem;
            text-decoration: none; margin-top: .375rem;
            transition: color .2s;
        }
        .btn-logout:hover { color: var(--danger); }

        /* ─── Topbar ──────────────────────────────────── */
        .topbar {
            position: fixed; top: 0; right: 0;
            left: var(--sidebar-w); height: var(--topbar-h);
            background: var(--surface); border-bottom: 1px solid var(--border);
            display: flex; align-items: center; padding: 0 1.5rem;
            z-index: 900; transition: left .3s;
            box-shadow: 0 1px 8px rgba(0,0,0,.06);
        }
        .topbar-toggle { display: none; background: none; border: none;
                          font-size: 1.25rem; color: var(--muted); cursor: pointer; padding: .5rem; }
        .topbar-breadcrumb { flex: 1; }
        .breadcrumb { margin: 0; padding: 0; list-style: none; display: flex; align-items: center; gap: .375rem; }
        .breadcrumb-item { font-size: .875rem; color: var(--muted); }
        .breadcrumb-item a { color: var(--accent); text-decoration: none; }
        .breadcrumb-item.active { color: var(--text); font-weight: 500; }
        .breadcrumb-item + .breadcrumb-item::before { content: '/'; color: var(--border); }
        .topbar-actions { display: flex; align-items: center; gap: .75rem; }
        .topbar-clock { font-size: .8125rem; color: var(--muted); font-feature-settings: 'tnum'; }

        /* ─── Main Content ────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }
        .page-body { padding: 1.75rem; }
        .page-header { margin-bottom: 1.5rem; }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--text); margin: 0; }
        .page-subtitle { color: var(--muted); font-size: .875rem; margin-top: .25rem; }

        /* ─── Cards ───────────────────────────────────── */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
                 box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { padding: 1.125rem 1.5rem; border-bottom: 1px solid var(--border);
                        display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: 1rem; font-weight: 600; margin: 0; }
        .card-body { padding: 1.5rem; }

        /* ─── Stats Cards ─────────────────────────────── */
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.25rem 1.5rem;
            display: flex; align-items: center; gap: 1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            transition: transform .2s, box-shadow .2s;
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.08); }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px;
                      display: flex; align-items: center; justify-content: center;
                      font-size: 1.25rem; flex-shrink: 0; }
        .stat-icon.blue   { background: rgba(37,99,235,.12);  color: var(--accent); }
        .stat-icon.green  { background: rgba(16,185,129,.12); color: var(--success); }
        .stat-icon.amber  { background: rgba(245,158,11,.12); color: var(--warning); }
        .stat-icon.red    { background: rgba(239,68,68,.12);  color: var(--danger); }
        .stat-icon.indigo { background: rgba(99,102,241,.12); color: #6366F1; }
        .stat-value { font-size: 1.75rem; font-weight: 700; line-height: 1; }
        .stat-label { font-size: .8125rem; color: var(--muted); margin-top: .25rem; }

        /* ─── Badges / Status ─────────────────────────── */
        .badge { display: inline-flex; align-items: center; gap: .3125rem;
                  padding: .25rem .625rem; border-radius: 20px;
                  font-size: .75rem; font-weight: 600; }
        .badge-draft           { background: #F1F5F9; color: #64748B; }
        .badge-submitted       { background: rgba(14,165,233,.12);  color: #0284C7; }
        .badge-approved        { background: rgba(16,185,129,.12);  color: #059669; }
        .badge-rejected        { background: rgba(239,68,68,.12);   color: #DC2626; }
        .badge-in_transit      { background: rgba(245,158,11,.12);  color: #D97706; }
        .badge-partially_transferred { background: rgba(99,102,241,.12); color: #4F46E5; }
        .badge-completed       { background: rgba(16,185,129,.12);  color: #059669; }
        .badge-cancelled       { background: rgba(239,68,68,.12);   color: #DC2626; }
        .badge-available       { background: rgba(16,185,129,.12);  color: #059669; }
        .badge-consumed        { background: rgba(100,116,139,.12); color: #475569; }
        .badge-damaged         { background: rgba(239,68,68,.12);   color: #DC2626; }
        .badge-quantity        { background: rgba(37,99,235,.12);   color: var(--accent); }
        .badge-non_quantity    { background: rgba(139,92,246,.12);  color: #7C3AED; }
        .badge-active          { background: rgba(16,185,129,.12);  color: #059669; }
        .badge-inactive        { background: rgba(239,68,68,.12);   color: #DC2626; }

        /* ─── Buttons ─────────────────────────────────── */
        .btn { border-radius: 8px; font-family: 'Inter', sans-serif;
                font-weight: 500; transition: all .15s; display: inline-flex;
                align-items: center; gap: .375rem; }
        .btn-primary   { background: var(--accent); border-color: var(--accent); }
        .btn-primary:hover { background: var(--accent-h); border-color: var(--accent-h); }
        .btn-sm { font-size: .8125rem; }
        .btn-icon { width: 32px; height: 32px; padding: 0; justify-content: center; }

        /* ─── Mono text ───────────────────────────────── */
        .mono { font-family: 'JetBrains Mono', monospace; font-size: .8125rem; }

        /* ─── Font-weight helpers (Bootstrap 5 gap) ──── */
        .fw-500 { font-weight: 500 !important; }
        .fw-600 { font-weight: 600 !important; }

        /* ─── DataTables Custom Spacing ─────────────── */
        .dataTables_wrapper > .row:first-child {
            padding: 1rem 1.5rem 0.75rem 1.5rem;
            margin: 0;
            border-bottom: 1px solid var(--border);
        }
        .dataTables_wrapper > .row:last-child {
            padding: 0.75rem 1.5rem 1rem 1.5rem;
            margin: 0;
            border-top: 1px solid var(--border);
        }
        /* Add gap/padding adjustment to search and page-size select inputs */
        .dataTables_filter input, .dataTables_length select {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.25rem 0.5rem;
            outline: none;
        }
        .dataTables_filter input:focus, .dataTables_length select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }

        /* ─── Overlay for mobile ──────────────────────── */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 999; }

        /* ─── Responsive ──────────────────────────────── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .topbar { left: 0; }
            .main-content { margin-left: 0; }
            .topbar-toggle { display: block; }
            .page-body { padding: 1rem; }
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <a href="<?= base_url('dashboard') ?>" class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-warehouse"></i></div>
        <div>
            <div class="brand-text">HWParts MNL</div>
            <div class="brand-sub">Supply Chain</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <?php
        $uri      = current_url();
        $role     = session()->get('user_role');
        $alerts   = session()->get('low_stock_count') ?? 0;

        function navItem(string $url, string $icon, string $label, string $currentUri, ?int $badge = null): string {
            $active = str_contains($currentUri, $url) ? ' active' : '';
            $b = $badge ? "<span class=\"nav-badge\">{$badge}</span>" : '';
            return "<a href=\"{$url}\" class=\"nav-item{$active}\"><i class=\"{$icon}\"></i> {$label}{$b}</a>";
        }
        ?>

        <?= navItem(base_url('dashboard'), 'fas fa-gauge-high', 'Dashboard', $uri, $alerts > 0 ? $alerts : null) ?>

        <div class="nav-section">Catalogue</div>
        <?= navItem(base_url('parts'), 'fas fa-gears', 'Parts', $uri) ?>
        <?= navItem(base_url('categories'), 'fas fa-tags', 'Categories', $uri) ?>

        <div class="nav-section">Inventory</div>
        <?= navItem(base_url('inventory'), 'fas fa-boxes', 'Inventory', $uri) ?>
        <?= navItem(base_url('parts-details'), 'fas fa-barcode', 'Tracked Units', $uri) ?>
        <?= navItem(base_url('warehouses'), 'fas fa-building', 'Warehouses', $uri) ?>
        <?= navItem(base_url('transfers'), 'fas fa-right-left', 'Transfers', $uri) ?>

        <div class="nav-section">Procurement</div>
        <?= navItem(base_url('purchase-orders'), 'fas fa-file-invoice', 'Purchase Orders', $uri) ?>
        <?= navItem(base_url('suppliers'), 'fas fa-truck-field', 'Suppliers', $uri) ?>

        <?php if ($role === 'admin'): ?>
        <div class="nav-section">Admin</div>
        <?= navItem(base_url('thresholds'), 'fas fa-bell', 'Stock Thresholds', $uri) ?>
        <?= navItem(base_url('admin/users'), 'fas fa-users', 'Users', $uri) ?>
        <?php endif; ?>

        <div class="nav-divider"></div>
        <?= navItem(base_url('audit-logs'), 'fas fa-clock-rotate-left', 'Audit Logs', $uri) ?>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= esc(session()->get('user_name')) ?></div>
                <div class="user-role"><?= esc(session()->get('user_role')) ?></div>
            </div>
        </div>
        <a href="<?= base_url('auth/logout') ?>" class="btn-logout">
            <i class="fas fa-right-from-bracket"></i> Sign Out
        </a>
    </div>
</aside>

<!-- Topbar -->
<header class="topbar">
    <button class="topbar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <div class="topbar-breadcrumb">
        <ol class="breadcrumb">
            <?php foreach ($breadcrumb ?? [['HWParts MNL', base_url('dashboard')], ['Dashboard', null]] as $i => $crumb): ?>
                <?php if ($i === count($breadcrumb ?? [['HWParts MNL', base_url('dashboard')], ['Dashboard', null]]) - 1 || $crumb[1] === null): ?>
                    <li class="breadcrumb-item active"><?= esc($crumb[0]) ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb[1] ?>"><?= esc($crumb[0]) ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
    <div class="topbar-actions">
        <span class="topbar-clock" id="liveClock"></span>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <div class="page-body">
        <?php if (session()->getFlashdata('success')): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>toastr.success(<?= json_encode(session()->getFlashdata('success')) ?>));</script>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>toastr.error(<?= json_encode(session()->getFlashdata('error')) ?>));</script>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>
</main>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<script>
    // Toastr config
    toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 4000 };

    // Live clock
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
    }
    updateClock(); setInterval(updateClock, 1000);

    // Mobile sidebar
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const toggle   = document.getElementById('sidebarToggle');
    toggle.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
    overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('show'); });

    // DataTables default init helper
    function initDataTable(selector, opts = {}) {
        return $(selector).DataTable(Object.assign({
            pageLength: 25, responsive: true,
            language: { search: '', searchPlaceholder: 'Search...' }
        }, opts));
    }
</script>
<?= $extraJs ?? '' ?>
</body>
</html>
