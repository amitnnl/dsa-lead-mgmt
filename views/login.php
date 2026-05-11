<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DSA LeadFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #6366f1; --primary-glow: rgba(99,102,241,0.3);
            --bg: #0a0a1a; --surface: #12122a; --surface-2: #1a1a3e;
            --text: #e2e8f0; --text-dim: #94a3b8; --border: rgba(255,255,255,0.08);
            --danger: #ef4444; --success: #10b981;
        }
        body {
            font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        /* Animated background */
        .bg-animation {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; overflow: hidden;
        }
        .bg-animation .orb {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
            animation: float 20s infinite ease-in-out;
        }
        .bg-animation .orb:nth-child(1) { width: 400px; height: 400px; background: var(--primary); top: -100px; left: -100px; }
        .bg-animation .orb:nth-child(2) { width: 350px; height: 350px; background: #8b5cf6; bottom: -80px; right: -80px; animation-delay: -7s; }
        .bg-animation .orb:nth-child(3) { width: 250px; height: 250px; background: #06b6d4; top: 50%; left: 50%; animation-delay: -14s; }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        .login-container {
            position: relative; z-index: 1; width: 100%; max-width: 420px; padding: 20px;
        }
        .login-card {
            background: rgba(18,18,42,0.85); backdrop-filter: blur(40px); border: 1px solid var(--border);
            border-radius: 24px; padding: 48px 40px; box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .login-brand {
            text-align: center; margin-bottom: 40px;
        }
        .login-brand .icon {
            width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 18px; display: inline-flex; align-items: center; justify-content: center;
            font-size: 28px; color: white; margin-bottom: 16px;
            box-shadow: 0 8px 32px var(--primary-glow);
        }
        .login-brand h1 {
            font-size: 24px; font-weight: 700; letter-spacing: -0.5px;
        }
        .login-brand p {
            color: var(--text-dim); font-size: 14px; margin-top: 6px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block; font-size: 13px; font-weight: 500; color: var(--text-dim);
            margin-bottom: 8px; letter-spacing: 0.3px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: var(--text-dim); font-size: 14px; transition: color 0.3s;
        }
        .input-wrap input {
            width: 100%; padding: 14px 16px 14px 44px; background: var(--surface-2);
            border: 1px solid var(--border); border-radius: 12px; color: var(--text);
            font-size: 15px; font-family: inherit; transition: all 0.3s;
            outline: none;
        }
        .input-wrap input:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .input-wrap input:focus + i, .input-wrap input:focus ~ i { color: var(--primary); }
        .input-wrap input::placeholder { color: #4a4a6a; }
        .btn-login {
            width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border: none; border-radius: 12px; color: white; font-size: 15px; font-weight: 600;
            font-family: inherit; cursor: pointer; transition: all 0.3s; margin-top: 8px;
            box-shadow: 0 4px 15px var(--primary-glow);
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 25px var(--primary-glow); }
        .btn-login:active { transform: translateY(0); }
        .error-msg {
            background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5; padding: 12px 16px; border-radius: 10px; font-size: 13px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }
        .demo-info {
            margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--border);
            text-align: center;
        }
        .demo-info p { color: var(--text-dim); font-size: 12px; margin-bottom: 10px; }
        .demo-creds {
            display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;
        }
        .demo-creds button {
            padding: 6px 12px; background: var(--surface-2); border: 1px solid var(--border);
            border-radius: 8px; color: var(--text-dim); font-size: 11px; font-family: inherit;
            cursor: pointer; transition: all 0.2s;
        }
        .demo-creds button:hover { border-color: var(--primary); color: var(--primary); }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-brand">
                <div class="icon"><i class="fas fa-bolt"></i></div>
                <h1>DSA LeadFlow</h1>
                <p>Lead Management System</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login&action=login">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <input type="email" name="email" id="loginEmail" placeholder="Enter your email" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required>
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-arrow-right"></i> Sign In
                </button>
            </form>

            <div class="demo-info">
                <p>Demo Credentials (password: admin123)</p>
                <div class="demo-creds">
                    <button onclick="fillDemo('admin@dsa.com')">Admin</button>
                    <button onclick="fillDemo('manager@dsa.com')">Manager</button>
                    <button onclick="fillDemo('agent@dsa.com')">Agent</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function fillDemo(email) {
        document.getElementById('loginEmail').value = email;
        document.getElementById('loginPassword').value = 'admin123';
    }
    </script>
</body>
</html>
