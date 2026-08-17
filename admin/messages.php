<?php
// ============================================================
// ADMIN — TIN NHẮN (Modern & Professional)
// ============================================================
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Tin nhắn khách hàng — <?= SHOP_NAME ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>" />
</head>
<body class="admin-body">

<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8"></path><path d="M8 12h8"></path></svg>
    <span>Lena<em>Bakery</em></span>
  </div>
  <nav class="sidebar-nav">
    <a href="index.php" class="sn-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
      <span>Đơn hàng</span>
    </a>
    <a href="products.php" class="sn-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
      <span>Sản phẩm</span>
    </a>
    <a href="messages.php" class="sn-link active">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
      <span>Tin nhắn</span>
    </a>
    <a href="../index.php" class="sn-link" target="_blank">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
      <span>Xem website</span>
    </a>
    <a href="logout.php" class="sn-link sn-logout">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      <span>Đăng xuất</span>
    </a>
  </nav>
</aside>

<div class="admin-content">
  <div class="admin-header">
    <div class="admin-title-wrap">
      <h1 class="admin-title">Tin nhắn từ khách</h1>
      <span class="admin-date-badge"><?= count($messages) ?> tin nhắn</span>
    </div>
  </div>

  <?php if (empty($messages)): ?>
  <div class="empty-state">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
    <div>Chưa có tin nhắn nào từ khách hàng.</div>
  </div>
  <?php endif; ?>

  <div class="messages-list">
    <?php foreach ($messages as $msg): ?>
    <div class="msg-card">
      <div class="msg-card-header">
        <strong><?= htmlspecialchars($msg['sender_name']) ?></strong>
        <?php if ($msg['sender_phone']): ?>
        <a href="tel:<?= htmlspecialchars($msg['sender_phone']) ?>" class="msg-phone">
          <?= htmlspecialchars($msg['sender_phone']) ?>
        </a>
        <?php endif; ?>
        <span class="msg-time"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
      </div>
      <div class="msg-content"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>
