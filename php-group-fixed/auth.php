<?php
session_start();
require_once 'includes/db.php';

// ถ้าล็อกอินอยู่แล้ว ให้ redirect ไปหน้าที่เหมาะสมเลย
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin_index.php' : 'index.php'));
    exit();
}

$login_error   = '';
$reg_error     = '';
$reg_success   = '';
$active_tab    = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';

// --- Handle LOGIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $active_tab = 'login';
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$user, $pass]);
    $userData = $stmt->fetch();

    if ($userData) {
        $_SESSION['user_id']  = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role']     = $userData['role'] ?? 'user';
        header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin_index.php' : 'index.php'));
        exit();
    } else {
        $login_error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}

// --- Handle REGISTER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $active_tab = 'register';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    if (empty($username) || empty($password)) {
        $reg_error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } elseif ($password !== $confirm) {
        $reg_error = 'รหัสผ่านไม่ตรงกัน';
    } else {
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->fetch()) {
            $reg_error = 'ชื่อผู้ใช้นี้มีคนใช้แล้ว';
        } else {
            $stmt_insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            if ($stmt_insert->execute([$username, $password])) {
                $reg_success = '✅ สมัครสมาชิกสำเร็จ! เข้าสู่ระบบได้เลย';
                $active_tab  = 'login';
            } else {
                $reg_error = 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 Gameproduct Store — เข้าสู่ระบบ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --bg:        #0a0c12;
            --surface:   #111420;
            --border:    #1e2436;
            --accent:    #4f6ef7;
            --accent2:   #7c3aed;
            --glow:      rgba(79,110,247,0.35);
            --text:      #e2e8f0;
            --muted:     #64748b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Noto Sans Thai', sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background grid */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(79,110,247,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,110,247,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            animation: gridDrift 30s linear infinite;
            z-index: 0;
        }
        @keyframes gridDrift {
            0%   { transform: translateY(0); }
            100% { transform: translateY(48px); }
        }

        /* Glow orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
        }
        .orb-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(79,110,247,0.18) 0%, transparent 70%);
            top: -150px; left: -100px;
            animation: orbFloat 8s ease-in-out infinite;
        }
        .orb-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(124,58,237,0.15) 0%, transparent 70%);
            bottom: -100px; right: -80px;
            animation: orbFloat 10s ease-in-out infinite reverse;
        }
        @keyframes orbFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(30px) scale(1.05); }
        }

        /* Back Button */
        .btn-back {
            position: absolute;
            top: 30px;
            left: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            z-index: 20;
            transition: color 0.2s ease, transform 0.2s ease;
            padding: 10px 16px;
            border-radius: 12px;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
        }
        .btn-back:hover {
            color: var(--text);
            background: rgba(255,255,255,0.05);
            transform: translateX(-4px);
        }

        /* Card */
        .card {
            position: relative;
            z-index: 10;
            width: 420px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 0 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.03) inset;
            animation: cardIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Logo */
        .logo {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            background: linear-gradient(135deg, #4f6ef7 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Tabs */
        .tab-bar {
            display: flex;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 28px;
        }
        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 9px;
            font-family: 'Noto Sans Thai', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            background: transparent;
            color: var(--muted);
        }
        .tab-btn.active {
            background: linear-gradient(135deg, #4f6ef7, #7c3aed);
            color: #fff;
            box-shadow: 0 4px 16px rgba(79,110,247,0.4);
        }

        /* Panels */
        .panel { display: none; animation: fadeUp 0.3s ease forwards; }
        .panel.active { display: block; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Input */
        .field-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .field-input {
            width: 100%;
            padding: 11px 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: 'Noto Sans Thai', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--glow);
        }
        .field-input::placeholder { color: #3a4255; }

        /* Button */
        .btn-primary {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f6ef7 0%, #7c3aed 100%);
            color: #fff;
            font-family: 'Noto Sans Thai', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.02em;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 20px rgba(79,110,247,0.35);
            margin-top: 4px;
        }
        .btn-primary:hover  { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(79,110,247,0.5); }
        .btn-primary:active { transform: scale(0.98); }

        /* Alert */
        .alert {
            padding: 10px 14px;
            border-radius: 9px;
            font-size: 0.84rem;
            margin-bottom: 16px;
        }
        .alert-error   { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .alert-success { background: rgba(34,197,94,0.12);  border: 1px solid rgba(34,197,94,0.3);  color: #86efac; }

        /* Hint text */
        .hint {
            text-align: center;
            font-size: 0.8rem;
            color: var(--muted);
            margin-top: 20px;
        }
        .hint a {
            color: #818cf8;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .hint a:hover { color: #a5b4fc; text-decoration: underline; }

        /* Space */
        .space { margin-bottom: 16px; }

        /* Responsive Back Button for Mobile */
        @media (max-width: 600px) {
            .btn-back {
                top: 15px;
                left: 15px;
                font-size: 0.85rem;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <a href="index.php" class="btn-back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        กลับหน้าร้านค้า
    </a>

    <div class="card">
        <div style="text-align:center; margin-bottom:28px;">
            <div style="font-size:2.2rem; margin-bottom:6px;">🛒</div>
            <div class="logo">GAMEPRODUCT STORE</div>
            <div style="font-size:0.78rem; color:var(--muted); margin-top:4px; letter-spacing:0.04em;">ยินดีต้อนรับสู่ร้านค้าเกม</div>
        </div>

        <?php if ($reg_success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($reg_success) ?></div>
        <?php endif; ?>

        <div class="tab-bar">
            <button class="tab-btn <?= $active_tab === 'login'    ? 'active' : '' ?>" onclick="switchTab('login')">เข้าสู่ระบบ</button>
            <button class="tab-btn <?= $active_tab === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">สมัครสมาชิก</button>
        </div>

        <div id="panel-login" class="panel <?= $active_tab === 'login' ? 'active' : '' ?>">
            <?php if ($login_error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($login_error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="space">
                    <label class="field-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username" class="field-input" placeholder="กรอกชื่อผู้ใช้" required
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="space">
                    <label class="field-label">รหัสผ่าน</label>
                    <input type="password" name="password" class="field-input" placeholder="กรอกรหัสผ่าน" required>
                </div>
                <button type="submit" class="btn-primary">เข้าสู่ระบบ →</button>
            </form>
            <div class="hint">ยังไม่มีบัญชี? <a onclick="switchTab('register')">สมัครสมาชิกฟรี</a></div>
        </div>

        <div id="panel-register" class="panel <?= $active_tab === 'register' ? 'active' : '' ?>">
            <?php if ($reg_error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($reg_error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="space">
                    <label class="field-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username" class="field-input" placeholder="ตั้งชื่อผู้ใช้" required
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="space">
                    <label class="field-label">รหัสผ่าน</label>
                    <input type="password" name="password" class="field-input" placeholder="ตั้งรหัสผ่าน" required>
                </div>
                <div class="space">
                    <label class="field-label">ยืนยันรหัสผ่าน</label>
                    <input type="password" name="confirm_password" class="field-input" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
                </div>
                <button type="submit" class="btn-primary">สมัครสมาชิก ✓</button>
            </form>
            <div class="hint">มีบัญชีอยู่แล้ว? <a onclick="switchTab('login')">เข้าสู่ระบบ</a></div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach((btn, i) => {
                const tabs = ['login', 'register'];
                btn.classList.toggle('active', tabs[i] === tab);
            });
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
            const target = document.getElementById('panel-' + tab);
            target.classList.add('active');
            // reset animation
            target.style.animation = 'none';
            target.offsetHeight;
            target.style.animation = '';
        }
    </script>
</body>
</html>