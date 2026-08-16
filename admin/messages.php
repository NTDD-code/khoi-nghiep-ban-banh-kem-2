<?php
// ============================================================
// ADMIN — TIN NHẮN
// ============================================================
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
session_write_close();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$db = get_db();

// Mark all as read
$db->exec("UPDATE messages SET is_read = 1 WHERE is_read = 0");

$messages = $db->query("SELECT * FROM messages ORDER BY id DESC LIMIT 200")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Tin nhắn — <?= SHOP_NAME ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
<aside class="admin-sidebar">
  <div class="sidebar-brand">Lena<em>Bakery</em></div>
  <nav class="sidebar-nav">
    <a href="index.php" class="sn-link">📦 Đơn hàng</a>
    <a href="messages.php" class="sn-link active">💬 Tin nhắn</a>
    <a href="../index.php" class="sn-link" target="_blank">🌐 Website</a>
    <a href="logout.php" class="sn-link sn-logout">🚪 Đăng xuất</a>
  </nav>
</aside>
<div class="admin-content">
  <div class="admin-header">
    <h1 class="admin-title">💬 Tin nhắn từ khách</h1>
    <span style="font-size:13px;color:#9a8f84"><?= count($messages) ?> tin nhắn</span>
  </div>
  <?php if (empty($messages)): ?>
  <div class="empty-state">Chưa có tin nhắn nào.</div>
  <?php endif; ?>
  <div class="messages-list">
    <?php foreach ($messages as $msg): ?>
    <div class="msg-card">
      <div class="msg-card-header">
        <strong><?= htmlspecialchars($msg['sender_name']) ?></strong>
        <?php if ($msg['sender_phone']): ?>
        <a href="tel:<?= htmlspecialchars($msg['sender_phone']) ?>" class="msg-phone"><?= htmlspecialchars($msg['sender_phone']) ?></a>
        <?php endif; ?>
        <span class="msg-time"><?= date('d/m H:i', strtotime($msg['created_at'])) ?></span>
      </div>
      <div class="msg-content"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
