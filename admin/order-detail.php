<?php
// ============================================================
// ADMIN — CHI TIẾT ĐƠN HÀNG (Modern & Professional)
// ============================================================
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
session_write_close();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
$db = get_db();
$order = $db->prepare("SELECT * FROM orders WHERE id = :id");
$order->execute([':id' => $id]);
$order = $order->fetch();
if (!$order) {
    echo '404 - Không tìm thấy đơn hàng';
    exit;
}

$items = $db->prepare("SELECT * FROM order_items WHERE order_id = :id ORDER BY id");
$items->execute([':id' => $id]);
$items = $items->fetchAll();

$statuses = ORDER_STATUSES;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Đơn #<?= htmlspecialchars($order['order_code']) ?> — <?= SHOP_NAME ?> Admin</title>
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
    <a href="index.php" class="sn-link active">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
      <span>Đơn hàng</span>
    </a>
    <a href="products.php" class="sn-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
      <span>Sản phẩm</span>
    </a>
    <a href="messages.php" class="sn-link">
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
    <a href="index.php" class="btn-back-admin">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
      <span>Quay lại danh sách</span>
    </a>
    <h1 class="admin-title">Đơn hàng #<?= htmlspecialchars($order['order_code']) ?></h1>
  </div>

  <div class="detail-grid">
    <!-- Thông tin khách -->
    <div class="detail-card">
      <div class="dc-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        <span>Thông tin khách hàng</span>
      </div>
      <div class="dc-row"><span>Tên:</span><strong><?= htmlspecialchars($order['customer_name']) ?></strong></div>
      <div class="dc-row"><span>SĐT:</span>
        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>"><strong><?= htmlspecialchars($order['customer_phone']) ?></strong></a>
      </div>
      <div class="dc-row"><span>Giao hàng:</span>
        <?php if ($order['shipping_method'] === 'pickup'): ?>
        <span class="ship-pill">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          Nhận tại quán (Pickup)
        </span>
        <?php else: ?>
        <div>
          <span class="ship-pill" style="margin-bottom:4px;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            Giao tận nơi
          </span>
          <div style="color:var(--text);"><?= htmlspecialchars($order['customer_addr'] ?? '') ?></div>
        </div>
        <?php endif; ?>
      </div>
      <div class="dc-row"><span>Thanh toán:</span>
        <?php if (($order['payment_method'] ?? 'transfer') === 'cod'): ?>
        <span class="pay-pill pay-cod">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
          Thanh toán khi nhận hàng (COD)
        </span>
        <?php else: ?>
        <span class="pay-pill pay-qr">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><rect x="7" y="7" width="3" height="3"></rect><rect x="14" y="7" width="3" height="3"></rect><rect x="7" y="14" width="3" height="3"></rect><rect x="14" y="14" width="3" height="3"></rect></svg>
          Chuyển khoản VietQR
        </span>
        <?php endif; ?>
      </div>
      <?php if ($order['note']): ?>
      <div class="dc-row dc-note"><span>Lời nhắn:</span> "<?= htmlspecialchars($order['note']) ?>"</div>
      <?php endif; ?>
    </div>

    <!-- Trạng thái -->
    <div class="detail-card">
      <div class="dc-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Trạng thái đơn hàng</span>
      </div>
      <div class="dc-row"><span>Hiện tại:</span>
        <span class="status-pill" style="background:<?= $statuses[$order['status']]['color'] ?>1c;color:<?= $statuses[$order['status']]['color'] ?>">
          <span class="status-dot"></span>
          <?= $statuses[$order['status']]['label'] ?>
        </span>
      </div>
      <div class="dc-row"><span>Cập nhật:</span>
        <select class="status-select" data-id="<?= $order['id'] ?>" onchange="updateStatus(this)">
          <?php foreach ($statuses as $k => $s): ?>
          <option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= $s['label'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="dc-row"><span>Thời gian:</span><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></div>
    </div>
  </div>

  <!-- Sản phẩm -->
  <div class="detail-card mt-20">
    <div class="dc-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
      <span>Danh sách sản phẩm</span>
    </div>
    <div class="table-responsive">
      <table class="orders-table">
        <thead>
          <tr>
            <th>Tên sản phẩm</th>
            <th>Vị & Topping</th>
            <th style="text-align:center;">Số lượng</th>
            <th style="text-align:right;">Đơn giá</th>
            <th style="text-align:right;">Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td><strong><?= htmlspecialchars($item['product_name']) ?></strong></td>
            <td><?= htmlspecialchars($item['flavor'] . ' • ' . $item['topping']) ?></td>
            <td style="text-align:center;"><span class="item-qty"><?= $item['quantity'] ?></span></td>
            <td style="text-align:right;color:var(--text-muted);"><?= number_format($item['unit_price'], 0, ',', '.') ?>đ</td>
            <td style="text-align:right;"><strong><?= number_format($item['subtotal'], 0, ',', '.') ?>đ</strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" style="text-align:right;color:var(--text-muted);">Tạm tính:</td>
            <td style="text-align:right;"><?= number_format($order['subtotal'], 0, ',', '.') ?>đ</td>
          </tr>
          <?php if ($order['shipping_fee']): ?>
          <tr>
            <td colspan="4" style="text-align:right;color:var(--text-muted);">Phí ship:</td>
            <td style="text-align:right;"><?= number_format($order['shipping_fee'], 0, ',', '.') ?>đ</td>
          </tr>
          <?php endif; ?>
          <?php if ($order['tip']): ?>
          <tr>
            <td colspan="4" style="text-align:right;color:var(--text-muted);">Tiền Tip:</td>
            <td style="text-align:right;color:var(--gold);"><?= number_format($order['tip'], 0, ',', '.') ?>đ</td>
          </tr>
          <?php endif; ?>
          <tr>
            <td colspan="4" style="text-align:right;font-weight:700;color:var(--gold);font-size:15px;">TỔNG CỘNG:</td>
            <td style="text-align:right;font-weight:700;color:var(--gold);font-size:17px;"><?= number_format($order['total'], 0, ',', '.') ?>đ</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<script>
async function updateStatus(sel) {
  const res = await fetch('api/update-status.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({id: +sel.dataset.id, status: sel.value})
  });
  const json = await res.json();
  if (json.ok) {
    location.reload();
  } else {
    alert('Lỗi cập nhật: ' + (json.error || ''));
  }
}
</script>
</body>
</html>
