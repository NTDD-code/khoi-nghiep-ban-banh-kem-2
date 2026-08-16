<?php
// ============================================================
// LENA BAKERY — ADMIN DASHBOARD
// ============================================================
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}
$adminUser = htmlspecialchars($_SESSION['admin_user'] ?? 'Admin');
session_write_close(); // Tránh lock session cho các request tiếp theo
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$db = get_db();

// Thống kê hôm nay
$today = $db->query("
    SELECT COUNT(*) as cnt, COALESCE(SUM(total),0) as revenue
    FROM orders WHERE DATE(created_at) = CURDATE()
")->fetch();

$pending = $db->query("SELECT COUNT(*) FROM orders WHERE status='new'")->fetchColumn();
$unread  = $db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn();

// Đơn hàng gần đây
$orders = $db->query("
    SELECT * FROM orders ORDER BY id DESC LIMIT 50
")->fetchAll();

$statuses = ORDER_STATUSES;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Dashboard — <?= SHOP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">

<!-- Toast container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Sidebar -->
<aside class="admin-sidebar">
  <div class="sidebar-brand">Lena<em>Bakery</em></div>
  <nav class="sidebar-nav">
    <a href="index.php" class="sn-link active">📦 Đơn hàng <span class="sn-badge" id="pendingBadge"><?= $pending ?></span></a>
    <a href="messages.php" class="sn-link">💬 Tin nhắn <span class="sn-badge" id="msgBadge"><?= $unread ?></span></a>
    <a href="../index.php" class="sn-link" target="_blank">🌐 Xem website</a>
    <a href="logout.php" class="sn-link sn-logout">🚪 Đăng xuất</a>
  </nav>
  <div class="sidebar-info">
    <div>Xin chào, <strong><?= $adminUser ?></strong></div>
    <div class="realtime-status" id="realtimeStatus">⚡ Đang kết nối...</div>
  </div>
</aside>

<!-- Main content -->
<div class="admin-content">

  <!-- Header -->
  <div class="admin-header">
    <h1 class="admin-title">Dashboard</h1>
    <div class="admin-date"><?= date('l, d/m/Y') ?></div>
  </div>

  <!-- Stats cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">📦</div>
      <div class="stat-val" id="statOrders"><?= $today['cnt'] ?></div>
      <div class="stat-label">Đơn hôm nay</div>
    </div>
    <div class="stat-card accent">
      <div class="stat-icon">💰</div>
      <div class="stat-val"><?= number_format($today['revenue'],0,',','.') ?>đ</div>
      <div class="stat-label">Doanh thu hôm nay</div>
    </div>
    <div class="stat-card warn" id="pendingCard">
      <div class="stat-icon">🆕</div>
      <div class="stat-val" id="statPending"><?= $pending ?></div>
      <div class="stat-label">Chờ xác nhận</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💬</div>
      <div class="stat-val" id="statMsg"><?= $unread ?></div>
      <div class="stat-label">Tin nhắn chưa đọc</div>
    </div>
  </div>

  <!-- Filter bar -->
  <div class="filter-bar">
    <div class="filter-tabs" id="filterTabs">
      <button class="ftab active" data-status="">Tất cả</button>
      <?php foreach ($statuses as $k => $s): ?>
      <button class="ftab" data-status="<?= $k ?>"><?= $s['label'] ?></button>
      <?php endforeach; ?>
    </div>
    <div class="filter-search">
      <input type="text" id="searchInput" placeholder="🔍 Tìm tên, SĐT, mã đơn..." />
    </div>
  </div>

  <!-- Orders table -->
  <div class="orders-wrap">
    <table class="orders-table" id="ordersTable">
      <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Thời gian</th>
          <th>Khách hàng</th>
          <th>Giao hàng</th>
          <th>Thanh toán</th>
          <th>Tổng tiền</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody id="ordersBody">
        <?php foreach ($orders as $order): ?>
        <?php $isCod = ($order['payment_method'] ?? 'transfer') === 'cod'; ?>
        <tr class="order-row" data-status="<?= $order['status'] ?>" data-id="<?= $order['id'] ?>">
          <td class="td-code"><a href="order-detail.php?id=<?= $order['id'] ?>"><?= htmlspecialchars($order['order_code']) ?></a></td>
          <td class="td-date"><?= date('d/m H:i', strtotime($order['created_at'])) ?></td>
          <td class="td-customer">
            <div class="cust-name"><?= htmlspecialchars($order['customer_name']) ?></div>
            <div class="cust-phone"><?= htmlspecialchars($order['customer_phone']) ?></div>
          </td>
          <td><?= $order['shipping_method'] === 'pickup' ? '🏪 Pickup' : '🛵 Giao' ?></td>
          <td>
            <?php if ($isCod): ?>
            <span class="pay-pill pay-cod">💵 COD</span>
            <?php else: ?>
            <span class="pay-pill pay-qr">⚡ QR Chuyển khoản</span>
            <?php endif; ?>
          </td>
          <td class="td-total"><strong><?= number_format($order['total'],0,',','.') ?>đ</strong></td>
          <td>
            <span class="status-pill" style="background:<?= $statuses[$order['status']]['color'] ?>22;color:<?= $statuses[$order['status']]['color'] ?>">
              <?= $statuses[$order['status']]['label'] ?>
            </span>
          </td>
          <td>
            <select class="status-select" data-id="<?= $order['id'] ?>" onchange="updateStatus(this)">
              <?php foreach ($statuses as $k => $s): ?>
              <option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= $s['label'] ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($orders)): ?>
    <div class="empty-state">Chưa có đơn hàng nào. Chia sẻ link website để nhận đơn! 🎂</div>
    <?php endif; ?>
  </div>

</div>

<script>
const SINCE_TS = <?= time() ?>;
const STATUSES = <?= json_encode(ORDER_STATUSES, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="../assets/js/admin.js"></script>

</body>
</html>
