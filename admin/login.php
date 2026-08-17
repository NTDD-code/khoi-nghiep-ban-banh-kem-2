<?php
// ============================================================
// LENA BAKERY — ADMIN LOGIN
// ============================================================
session_start();
require_once __DIR__ . '/../includes/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if ($u === ADMIN_USER && $p === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $u;
        header('Location: index.php');
        exit;
    }
    $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
}
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin Login — <?= SHOP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;1,500&display=swap" rel="stylesheet" />
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{min-height:100vh;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#1a110e 0%,#2d1a15 50%,#1a110e 100%);
    font-family:'DM Sans',sans-serif;color:#f8f3ec}
  .login-box{width:min(420px,calc(100vw - 40px));background:rgba(255,251,245,.06);
    border:1px solid rgba(184,149,106,.2);border-radius:20px;
    padding:48px 40px;backdrop-filter:blur(20px)}
  .login-logo{font-family:'Playfair Display',serif;font-size:28px;text-align:center;
    margin-bottom:8px;letter-spacing:-0.5px}
  .login-logo em{color:#c77557;font-style:italic}
  .login-sub{text-align:center;font-size:12px;color:#9a8f84;letter-spacing:2px;
    text-transform:uppercase;margin-bottom:36px}
  label{display:block;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;
    color:#b0a59a;margin-bottom:8px}
  input[type=text],input[type=password]{width:100%;background:rgba(255,251,245,.08);
    border:1px solid rgba(184,149,106,.25);border-radius:10px;padding:14px 18px;
    font-size:15px;color:#f8f3ec;font-family:inherit;outline:none;
    transition:border .25s}
  input:focus{border-color:rgba(199,117,87,.6)}
  .form-row{margin-bottom:20px}
  .btn-login{width:100%;margin-top:8px;background:linear-gradient(135deg,#8b3a2a,#c77557);
    color:#fff;border:none;border-radius:10px;padding:16px;font-size:14px;
    font-weight:600;letter-spacing:1px;cursor:pointer;
    transition:transform .25s,box-shadow .25s}
  .btn-login:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(139,58,42,.4)}
  .error-msg{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);
    color:#fca5a5;border-radius:8px;padding:12px 16px;font-size:13px;margin-bottom:20px}
  .back-link{display:block;text-align:center;margin-top:24px;font-size:12px;
    color:#9a8f84;text-decoration:none}
  .back-link:hover{color:#c77557}
</style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">Lena<em>Bakery</em></div>
  <div class="login-sub">Admin Dashboard</div>
  <?php if ($error): ?>
  <div class="error-msg"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" novalidate>
    <div class="form-row">
      <label for="username">Tên đăng nhập</label>
      <input type="text" id="username" name="username" autocomplete="username" required autofocus />
    </div>
    <div class="form-row">
      <label for="password">Mật khẩu</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required />
    </div>
    <button type="submit" class="btn-login">ĐĂNG NHẬP →</button>
  </form>
  <a href="../index.php" class="back-link">← Về trang chủ</a>
</div>
</body>
</html>
