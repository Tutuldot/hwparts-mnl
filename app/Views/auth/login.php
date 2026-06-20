<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HWParts MNL — Login</title>
    <meta name="description" content="HWParts MNL Supply Chain Management System — Sign in to your account">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary:   #0A1628;
            --secondary: #1B3A6B;
            --accent:    #2563EB;
            --accent-h:  #1D4ED8;
            --surface:   #FFFFFF;
            --text:      #0F172A;
            --muted:     #64748B;
            --danger:    #EF4444;
            --success:   #10B981;
            --border:    #E2E8F0;
        }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0A1628 0%, #1B3A6B 50%, #0A1628 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-wrap {
            width: 100%;
            max-width: 440px;
        }
        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-brand .logo-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--accent), #60A5FA);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #fff;
            margin-bottom: 1rem;
            box-shadow: 0 8px 32px rgba(37,99,235,.4);
        }
        .login-brand h1 { font-size: 1.75rem; font-weight: 700; color: #fff; letter-spacing: -.02em; }
        .login-brand p  { color: rgba(255,255,255,.6); font-size: .875rem; margin-top: .25rem; }
        .card {
            background: rgba(255,255,255,.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 20px;
            padding: 2.5rem;
        }
        .alert {
            padding: .875rem 1rem;
            border-radius: 10px;
            font-size: .875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: .625rem;
        }
        .alert-danger  { background: rgba(239,68,68,.15);  color: #FCA5A5; border: 1px solid rgba(239,68,68,.3); }
        .alert-success { background: rgba(16,185,129,.15); color: #6EE7B7; border: 1px solid rgba(16,185,129,.3); }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: .8125rem; font-weight: 500; color: rgba(255,255,255,.8); margin-bottom: .5rem; letter-spacing: .02em; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: .875rem; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,.4); font-size: .875rem; pointer-events: none;
        }
        .form-control {
            width: 100%; padding: .75rem .875rem .75rem 2.5rem;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 10px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: .9375rem;
            transition: border-color .2s, background .2s;
            outline: none;
        }
        .form-control::placeholder { color: rgba(255,255,255,.3); }
        .form-control:focus {
            border-color: var(--accent);
            background: rgba(255,255,255,.12);
            box-shadow: 0 0 0 3px rgba(37,99,235,.25);
        }
        .btn-primary {
            width: 100%; padding: .875rem;
            background: linear-gradient(135deg, var(--accent), #60A5FA);
            border: none; border-radius: 10px;
            color: #fff; font-family: 'Inter', sans-serif;
            font-size: 1rem; font-weight: 600;
            cursor: pointer; letter-spacing: .01em;
            transition: transform .15s, box-shadow .15s, opacity .15s;
            margin-top: .5rem;
            box-shadow: 0 4px 20px rgba(37,99,235,.4);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 28px rgba(37,99,235,.5); }
        .btn-primary:active { transform: translateY(0); }
        .login-footer { text-align: center; margin-top: 1.5rem; color: rgba(255,255,255,.4); font-size: .8125rem; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-brand">
            <div class="logo-icon"><i class="fas fa-warehouse"></i></div>
            <h1>HWParts MNL</h1>
            <p>Supply Chain Management System</p>
        </div>

        <div class="card">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-circle-exclamation"></i>
                    <div><?= implode('<br>', array_map('esc', $errors)) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('auth/login') ?>" method="POST" id="loginForm">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="admin@hwparts.com"
                               value="<?= esc(old('email')) ?>" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary" id="loginBtn">
                    <i class="fas fa-right-to-bracket"></i> Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">© <?= date('Y') ?> HWParts MNL · All rights reserved</div>
    </div>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
