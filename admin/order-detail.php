<?php
// ============================================================
// ADMIN — CHI TIẾT ĐƠN HÀNG
// ============================================================
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
session_write_close();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
$db = get_db();
$order = $db->prepare("SELECT * FROM orders WHERE id = :id");
$order->execute([':id' => $id]);
$order = $order->fetch();
if (!$order) { echo '404'; exit; }

$items = $db->prepare("SELECT * FROM order_items WHERE order_id = :id ORDER BY id");
$items->execute([':id' => $id]);
$items = $items->fetchAll();

$statuses = ORDER_STATUSES;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Đơn #<?= htmlspecialchars($order['order_code']) ?> — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
<aside class="admin-sidebar">
  <div class="sidebar-brand">Lena<em>Bakery</em></div>
  <nav class="sidebar-nav">
    <a href="index.php" class="sn-link active">📦 Đơn hàng</a>
    <a href="messages.php" class="sn-link">💬 Tin nhắn</a>
    <a href="../index.php" class="sn-link" target="_blank">🌐 Xem website</a>
    <a href="logout.php" class="sn-link sn-logout">🚪 Đăng xuất</a>
  </nav>
</aside>
<div class="admin-content">
  <div class="admin-header">
    <a href="index.php" class="btn-back-admin">← Quay lại</a>
    <h1 class="admin-title">Đơn #<?= htmlspecialchars($order['order_code']) ?></h1>
  </div>

  <div class="detail-grid">
    <!-- Thông tin khách -->
    <div class="detail-card">
      <div class="dc-title">👤 Thông tin khách hàng</div>
      <div class="dc-row"><span>Tên:</span><strong><?= htmlspecialchars($order['customer_name']) ?></strong></div>
      <div class="dc-row"><span>SĐT:</span>
        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>"><?= htmlspecialchars($order['customer_phone']) ?></a>
      </div>
      <div class="dc-row"><span>Giao hàng:</span>
        <?= $order['shipping_method'] === 'pickup' ? '🏪 Pickup tại quán' : '🛵 Giao tận nơi: ' . htmlspecialchars($order['customer_addr'] ?? '') ?>
      </div>
      <div class="dc-row"><span>Thanh toán:</span>
        <?php if (($order['payment_method'] ?? 'transfer') === 'cod'): ?>
        <span class="pay-pill pay-cod">💵 Thanh toán khi nhận hàng (COD)</span>
        <?php else: ?>
        <span class="pay-pill pay-qr">⚡ Chuyển khoản VietQR</span>
        <?php endif; ?>
      </div>
      <?php if ($order['note']): ?>
      <div class="dc-row dc-note"><span>Lời nhắn:</span> "<?= htmlspecialchars($order['note']) ?>"</div>
      <?php endif; ?>
    </div>

    <!-- Trạng thái -->
    <div class="detail-card">
      <div class="dc-title">📋 Trạng thái đơn hàng</div>
      <div class="dc-row"><span>Hiện tại:</span>
        <span class="status-pill" style="background:<?= $statuses[$order['status']]['color'] ?>22;color:<?= $statuses[$order['status']]['color'] ?>">
          <?= $statuses[$order['status']]['label'] ?>
        </span>
      </div>
      <div class="dc-row"><span>Thay đổi:</span>
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
    <div class="dc-title">🛒 Sản phẩm đặt hàng</div>
    <table class="orders-table">
      <thead><tr><th>Tên</th><th>Vị/Topping</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['product_name']) ?></td>
          <td><?= htmlspecialchars($item['flavor'] . ' • ' . $item['topping']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td><?= number_format($item['unit_price'],0,',','.') ?>đ</td>
          <td><?= number_format($item['subtotal'],0,',','.') ?>đ</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="4">Tạm tính</td><td><?= number_format($order['subtotal'],0,',','.') ?>đ</td></tr>
        <?php if ($order['shipping_fee']): ?>
        <tr><td colspan="4">Phí ship</td><td><?= number_format($order['shipping_fee'],0,',','.') ?>đ</td></tr>
        <?php endif; ?>
        <?php if ($order['tip']): ?>
        <tr><td colspan="4">Tip 💛</td><td><?= number_format($order['tip'],0,',','.') ?>đ</td></tr>
        <?php endif; ?>
        <tr class="inv-total"><td colspan="4"><strong>Tổng</strong></td><td><strong><?= number_format($order['total'],0,',','.') ?>đ</strong></td></tr>
      </tfoot>
    </table>
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
  if (json.ok) { location.reload(); }
  else { alert('Lỗi cập nhật: ' + (json.error||'')); }
}
</script>
</body>
</html>
