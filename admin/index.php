<?php
// ============================================================
// LENA BAKERY — ADMIN DASHBOARD (Professional / Modern / Clean)
// ============================================================
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
$adminUser = htmlspecialchars($_SESSION['admin_user'] ?? 'Admin');
session_write_close();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$db = get_db();

// 1. Xử lý bộ lọc ngày
$dateFilter = trim($_GET['date'] ?? 'today');
$whereDateSql = "";
$dateParams = [];
$dateDisplayLabel = "";

if ($dateFilter === 'today') {
    $whereDateSql = "DATE(created_at) = CURDATE()";
    $dateDisplayLabel = "Hôm nay (" . date('d/m/Y') . ")";
} elseif ($dateFilter === 'yesterday') {
    $whereDateSql = "DATE(created_at) = SUBDATE(CURDATE(), 1)";
    $dateDisplayLabel = "Hôm qua (" . date('d/m/Y', strtotime('-1 day')) . ")";
} elseif ($dateFilter === '7days') {
    $whereDateSql = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $dateDisplayLabel = "7 ngày gần nhất";
} elseif ($dateFilter === 'all') {
    $whereDateSql = "1=1";
    $dateDisplayLabel = "Toàn bộ thời gian";
} elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
    $whereDateSql = "DATE(created_at) = :custom_date";
    $dateParams[':custom_date'] = $dateFilter;
    $dateDisplayLabel = "Ngày " . date('d/m/Y', strtotime($dateFilter));
} else {
    $whereDateSql = "DATE(created_at) = CURDATE()";
    $dateFilter = 'today';
    $dateDisplayLabel = "Hôm nay (" . date('d/m/Y') . ")";
}

// 2. Thống kê theo mốc ngày đã chọn (Tách biệt đơn huỷ và đơn thành công)
$statQuery = $db->prepare("
    SELECT 
        COUNT(*) as cnt,
        COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total ELSE 0 END), 0) as revenue_valid,
        COALESCE(SUM(CASE WHEN status = 'done' THEN total ELSE 0 END), 0) as revenue_done,
        COALESCE(SUM(total), 0) as revenue_all
    FROM orders
    WHERE $whereDateSql
");
$statQuery->execute($dateParams);
$statData = $statQuery->fetch();

$pending = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='new'")->fetchColumn();
$unread  = (int)$db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn();

// 3. Danh sách đơn hàng kèm chi tiết món đặt
$orderSql = "
    SELECT o.*,
    (
        SELECT GROUP_CONCAT(CONCAT(oi.quantity, '::', oi.product_name, '::', IFNULL(oi.flavor,''), '::', IFNULL(oi.topping,'')) SEPARATOR '||')
        FROM order_items oi WHERE oi.order_id = o.id
    ) as items_data
    FROM orders o
    WHERE $whereDateSql
    ORDER BY o.id DESC
    LIMIT 200
";
$orderStmt = $db->prepare($orderSql);
$orderStmt->execute($dateParams);
$orders = $orderStmt->fetchAll();

$statuses = ORDER_STATUSES;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Quản lý Đơn hàng — <?= SHOP_NAME ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>" />
</head>
<body class="admin-body">

<!-- Toast container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Sidebar -->
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8"></path><path d="M8 12h8"></path></svg>
    <span>Lena<em>Bakery</em></span>
  </div>
  
  <nav class="sidebar-nav">
    <a href="index.php" class="sn-link active">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
      <span>Đơn hàng</span>
      <span class="sn-badge" id="pendingBadge"><?= $pending ?></span>
    </a>
    <a href="products.php" class="sn-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
      <span>Sản phẩm</span>
    </a>
    <a href="messages.php" class="sn-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
      <span>Tin nhắn</span>
      <span class="sn-badge" id="msgBadge"><?= $unread ?></span>
    </a>
    <a href="javascript:void(0)" class="sn-link" onclick="openSettingsModal()">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
      <span>Cài đặt</span>
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
  
  <div class="sidebar-info">
    <div>Tài khoản: <strong><?= $adminUser ?></strong></div>
    <div class="realtime-status" id="realtimeStatus">
      <span class="realtime-dot"></span> Realtime — Đang kết nối
    </div>
  </div>
</aside>

<!-- Main content -->
<div class="admin-content">

  <!-- Header -->
  <div class="admin-header">
    <div class="admin-title-wrap">
      <h1 class="admin-title">Quản lý Đơn hàng</h1>
      <div class="admin-date-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        <span><?= $dateDisplayLabel ?></span>
      </div>
    </div>
  </div>

  <!-- Stats cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-header">
        <span class="stat-label">Số đơn trong kỳ</span>
        <div class="stat-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
        </div>
      </div>
      <div class="stat-val" id="statOrders"><?= number_format($statData['cnt']) ?></div>
      <div class="stat-label" style="font-size:11.5px;color:var(--text-dim);">Đơn hàng ghi nhận</div>
    </div>

    <div class="stat-card accent">
      <div class="stat-header">
        <span class="stat-label">Tổng doanh thu</span>
        <div style="display:flex;align-items:center;gap:6px;">
          <button type="button" class="btn-toggle-mask" onclick="toggleRevenueMask()" title="Ẩn/Hiện doanh thu (Bảo mật)">
            <svg class="icon-eye-open" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            <svg class="icon-eye-closed" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
          </button>
          <div class="stat-icon-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
          </div>
        </div>
      </div>
      <div class="stat-val revenue-display" id="statRevenue" 
           data-revenue-valid="<?= (int)$statData['revenue_valid'] ?>"
           data-revenue-done="<?= (int)$statData['revenue_done'] ?>"
           data-revenue-all="<?= (int)$statData['revenue_all'] ?>">
        <?= number_format($statData['revenue_valid'], 0, ',', '.') ?>đ
      </div>
      <div class="stat-label" style="font-size:11.5px;color:var(--text-dim);" id="revenueRuleDesc">Đơn hợp lệ (trừ đơn huỷ)</div>
    </div>

    <div class="stat-card warn" id="pendingCard">
      <div class="stat-header">
        <span class="stat-label">Chờ xác nhận</span>
        <div class="stat-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
      </div>
      <div class="stat-val" id="statPending"><?= $pending ?></div>
      <div class="stat-label" style="font-size:11.5px;color:var(--text-dim);">Đơn mới chưa xử lý</div>
    </div>

    <div class="stat-card">
      <div class="stat-header">
        <span class="stat-label">Tin nhắn mới</span>
        <div class="stat-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </div>
      </div>
      <div class="stat-val" id="statMsg"><?= $unread ?></div>
      <div class="stat-label" style="font-size:11.5px;color:var(--text-dim);">Tin chưa đọc từ khách</div>
    </div>
  </div>

  <!-- Dashboard toolbar -->
  <div class="dashboard-toolbar">
    
    <!-- Top toolbar: Date Filters & Export Excel -->
    <div class="toolbar-top">
      <div class="date-presets">
        <a href="index.php?date=today" class="dpreset-btn <?= $dateFilter === 'today' ? 'active' : '' ?>">Hôm nay</a>
        <a href="index.php?date=yesterday" class="dpreset-btn <?= $dateFilter === 'yesterday' ? 'active' : '' ?>">Hôm qua</a>
        <a href="index.php?date=7days" class="dpreset-btn <?= $dateFilter === '7days' ? 'active' : '' ?>">7 ngày qua</a>
        <a href="index.php?date=all" class="dpreset-btn <?= $dateFilter === 'all' ? 'active' : '' ?>">Tất cả</a>
        
        <!-- Date picker to view ANY old date -->
        <div class="date-picker-wrap">
          <input type="date" class="date-picker-input" id="customDatePicker" 
                 value="<?= preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter) ? $dateFilter : date('Y-m-d') ?>" 
                 onchange="window.location.href='index.php?date=' + this.value" 
                 title="Chọn ngày cụ thể để xem lại đơn cũ" />
        </div>
      </div>

      <button type="button" class="btn-export" onclick="exportOrdersExcel()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        <span>Xuất Excel (.csv)</span>
      </button>
    </div>

    <!-- Bottom toolbar: Status Tabs (Touch-Swipeable on Mobile) & Search -->
    <div class="toolbar-bottom">
      <div class="filter-tabs" id="filterTabs">
        <button class="ftab active" data-status="">
          <span>Tất cả</span>
        </button>
        <?php foreach ($statuses as $k => $s): ?>
        <button class="ftab" data-status="<?= $k ?>">
          <span class="status-dot" style="background:<?= $s['color'] ?>"></span>
          <span><?= $s['label'] ?></span>
        </button>
        <?php endforeach; ?>
      </div>

      <div class="filter-search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" id="searchInput" placeholder="Tìm tên, SĐT, mã đơn, món bánh..." />
      </div>
    </div>

  </div>

  <!-- Orders table -->
  <div class="orders-wrap">
    <div class="table-responsive">
      <table class="orders-table" id="ordersTable">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Thời gian</th>
            <th>Khách hàng</th>
            <th>Món đã đặt</th>
            <th>Giao hàng</th>
            <th>Thanh toán</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody id="ordersBody">
          <?php foreach ($orders as $order): ?>
          <?php 
            $isCod = ($order['payment_method'] ?? 'transfer') === 'cod';
            $itemsRaw = explode('||', $order['items_data'] ?? '');
          ?>
          <tr class="order-row" data-status="<?= $order['status'] ?>" data-id="<?= $order['id'] ?>" data-total="<?= $order['total'] ?>">
            <!-- Mã đơn -->
            <td class="td-code">
              <a href="order-detail.php?id=<?= $order['id'] ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                <span><?= htmlspecialchars($order['order_code']) ?></span>
              </a>
            </td>

            <!-- Thời gian -->
            <td class="td-date">
              <div><?= date('d/m/Y', strtotime($order['created_at'])) ?></div>
              <div style="color:var(--text-dim);font-size:11.5px;"><?= date('H:i:s', strtotime($order['created_at'])) ?></div>
            </td>

            <!-- Khách hàng -->
            <td class="td-customer">
              <div class="cust-name"><?= htmlspecialchars($order['customer_name']) ?></div>
              <div class="cust-phone">
                <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" style="color:inherit;text-decoration:none;">
                  <?= htmlspecialchars($order['customer_phone']) ?>
                </a>
              </div>
              <?php if ($order['customer_addr']): ?>
              <div class="cust-addr" title="<?= htmlspecialchars($order['customer_addr']) ?>">
                <?= htmlspecialchars($order['customer_addr']) ?>
              </div>
              <?php endif; ?>
            </td>

            <!-- Món đã đặt (Ngăn nắp, chi tiết) -->
            <td class="td-items">
              <div class="order-items-list">
                <?php if (!empty($itemsRaw) && !empty($itemsRaw[0])): ?>
                  <?php foreach ($itemsRaw as $iStr): ?>
                    <?php 
                      $iParts = explode('::', $iStr);
                      $q = $iParts[0] ?? 1;
                      $name = $iParts[1] ?? '';
                      $flavor = $iParts[2] ?? '';
                      $topping = $iParts[3] ?? '';
                      $optStr = trim(($flavor ? $flavor : '') . ($topping && $topping !== 'none' ? ' • ' . $topping : ''));
                    ?>
                    <div class="item-pill">
                      <span class="item-qty"><?= $q ?>x</span>
                      <span class="item-name"><?= htmlspecialchars($name) ?></span>
                      <?php if ($optStr): ?>
                      <span class="item-opt">(<?= htmlspecialchars($optStr) ?>)</span>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span style="color:var(--text-dim);font-size:12px;">Chưa có dữ liệu món</span>
                <?php endif; ?>

                <?php if (!empty($order['note'])): ?>
                <div class="order-note-box" title="Ghi chú từ khách hàng">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                  <span>"<?= htmlspecialchars($order['note']) ?>"</span>
                </div>
                <?php endif; ?>
              </div>
            </td>

            <!-- Giao hàng -->
            <td>
              <?php if ($order['shipping_method'] === 'pickup'): ?>
              <span class="ship-pill">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Pickup</span>
              </span>
              <?php else: ?>
              <span class="ship-pill">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                <span>Giao hàng</span>
              </span>
              <?php endif; ?>
            </td>

            <!-- Thanh toán -->
            <td>
              <?php if ($isCod): ?>
              <span class="pay-pill pay-cod">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
                <span>COD</span>
              </span>
              <?php else: ?>
              <span class="pay-pill pay-qr">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><rect x="7" y="7" width="3" height="3"></rect><rect x="14" y="7" width="3" height="3"></rect><rect x="7" y="14" width="3" height="3"></rect><rect x="14" y="14" width="3" height="3"></rect></svg>
                <span>VietQR</span>
              </span>
              <?php endif; ?>
            </td>

            <!-- Tổng tiền -->
            <td class="td-total">
              <strong><?= number_format($order['total'], 0, ',', '.') ?>đ</strong>
            </td>

            <!-- Trạng thái -->
            <td>
              <span class="status-pill" style="background:<?= $statuses[$order['status']]['color'] ?>1c;color:<?= $statuses[$order['status']]['color'] ?>">
                <span class="status-dot"></span>
                <span><?= $statuses[$order['status']]['label'] ?></span>
              </span>
            </td>

            <!-- Thao tác thay đổi trạng thái nhanh -->
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

        <!-- Footer table tổng doanh thu đang hiển thị -->
        <tfoot>
          <tr>
            <td colspan="3" class="td-summary-label">
              Hiển thị: <span id="visibleCountDisplay" style="color:var(--text);"><?= count($orders) ?></span> đơn
            </td>
            <td colspan="3" class="td-summary-label" style="text-align:right;">
              Tổng doanh thu danh sách đang lọc:
            </td>
            <td colspan="3" class="td-summary-val revenue-display" id="visibleTotalDisplay">
              <?= number_format($statData['revenue_valid'], 0, ',', '.') ?>đ
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <?php if (empty($orders)): ?>
    <div class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path></svg>
      <div>Không có đơn hàng nào trong khoảng thời gian đã chọn.</div>
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- SETTINGS MODAL -->
<div class="modal-backdrop" id="settingsModal" style="display:none;" onclick="if(event.target===this)closeSettingsModal()">
  <div class="admin-modal-card">
    <div class="modal-header">
      <div class="modal-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
        <span>Cài đặt hệ thống Admin</span>
      </div>
      <button type="button" class="modal-close-btn" onclick="closeSettingsModal()">&times;</button>
    </div>
    
    <div class="modal-body">
      <!-- 1. Quy tắc tính doanh thu -->
      <div class="setting-group">
        <label class="setting-label">Quy tắc tính doanh thu</label>
        <div class="setting-desc">Lựa chọn trạng thái đơn hàng được phép cộng vào doanh thu hiển thị và báo cáo.</div>
        <div class="setting-radio-list">
          <label class="setting-radio-item">
            <input type="radio" name="revenueRule" value="non_cancelled" id="ruleNonCancelled" onchange="saveRevenueRule(this.value)">
            <div>
              <strong>Trừ đơn "Đã huỷ" (Mặc định khuyên dùng)</strong>
              <div class="radio-hint">Chỉ bỏ qua các đơn có trạng thái "Đã huỷ", các đơn còn lại đều được tính tiền.</div>
            </div>
          </label>

          <label class="setting-radio-item">
            <input type="radio" name="revenueRule" value="done_only" id="ruleDoneOnly" onchange="saveRevenueRule(this.value)">
            <div>
              <strong>Chỉ tính đơn "Hoàn thành"</strong>
              <div class="radio-hint">Chỉ cộng tiền khi đơn hàng đã giao thành công và chuyển sang trạng thái "Hoàn thành".</div>
            </div>
          </label>

          <label class="setting-radio-item">
            <input type="radio" name="revenueRule" value="all" id="ruleAll" onchange="saveRevenueRule(this.value)">
            <div>
              <strong>Tính toàn bộ tất cả đơn</strong>
              <div class="radio-hint">Cộng tổng tiền tất cả đơn hàng mà không phân biệt trạng thái.</div>
            </div>
          </label>
        </div>
      </div>

      <!-- 2. Chế độ riêng tư (Ẩn doanh thu dấu sao) -->
      <div class="setting-group">
        <div class="setting-switch-row">
          <div>
            <label class="setting-label" style="margin-bottom:2px;">Chế độ bảo mật (Ẩn số tiền doanh thu)</label>
            <div class="setting-desc">Che toàn bộ các con số doanh thu thành dấu sao (******) để bảo mật khi ở nơi đông người.</div>
          </div>
          <label class="switch-toggle">
            <input type="checkbox" id="settingMaskToggle" onchange="setRevenueMask(this.checked)">
            <span class="switch-slider"></span>
          </label>
        </div>
      </div>

      <!-- 3. Âm thanh chuông báo -->
      <div class="setting-group">
        <div class="setting-switch-row">
          <div>
            <label class="setting-label" style="margin-bottom:2px;">Âm thanh thông báo Realtime</label>
            <div class="setting-desc">Phát chuông thông báo nhẹ khi có đơn hàng hoặc tin nhắn mới từ khách.</div>
          </div>
          <label class="switch-toggle">
            <input type="checkbox" id="settingSoundToggle" onchange="setSoundSetting(this.checked)">
            <span class="switch-slider"></span>
          </label>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn-modal-save" onclick="closeSettingsModal()">Đã lưu cài đặt</button>
    </div>
  </div>
</div>

<script>
const SINCE_TS = <?= time() ?>;
const STATUSES = <?= json_encode(ORDER_STATUSES, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="../assets/js/admin.js?v=<?= filemtime(__DIR__ . '/../assets/js/admin.js') ?>"></script>

</body>
</html>
