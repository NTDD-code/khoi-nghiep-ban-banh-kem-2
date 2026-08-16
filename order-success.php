<?php
// ============================================================
// LENA BAKERY — TRANG CẢM ƠN + IN HOÁ ĐƠN
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

$orderCode = trim($_GET['code'] ?? '');
$payMethod = $_GET['pay'] ?? 'transfer';

$order = null;
$items = [];

if ($orderCode) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_code = :code LIMIT 1");
    $stmt->execute([':code' => $orderCode]);
    $order = $stmt->fetch();

    if ($order) {
        $iStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :id ORDER BY id");
        $iStmt->execute([':id' => $order['id']]);
        $items = $iStmt->fetchAll();
    }
}

if (!$order) {
    header('Location: index.php');
    exit;
}

$isCOD      = ($order['payment_method'] ?? $payMethod) === 'cod';
$isPickup   = $order['shipping_method'] === 'pickup';
$orderDate  = date('d/m/Y H:i', strtotime($order['created_at']));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Đặt hàng thành công — <?= SHOP_NAME ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><circle cx='32' cy='32' r='30' fill='%23f7f1ea' stroke='%238b3a2a' stroke-width='1.5'/><text x='32' y='38' font-family='Georgia,serif' font-size='20' font-weight='600' fill='%238b3a2a' text-anchor='middle'>Lena</text></svg>" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="assets/css/checkout.css" />
</head>
<body class="success-page">

<!-- Confetti canvas -->
<canvas id="confettiCanvas" style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:999"></canvas>

<header>
  <nav class="co-nav">
    <a href="index.php" class="brand">Lena<em>Bakery</em></a>
    <a href="index.php" class="co-back-link">← Về trang chủ</a>
  </nav>
</header>

<main class="success-main">

  <!-- Hero thank you -->
  <div class="success-hero">
    <div class="success-icon">🎂</div>
    <h1 class="success-title">
      Cảm ơn bạn <em><?= htmlspecialchars($order['customer_name']) ?></em>!
    </h1>
    <p class="success-sub">
      Đơn hàng <strong><?= htmlspecialchars($order['order_code']) ?></strong> đã được ghi nhận.
      <?= $isCOD ? 'Bạn sẽ thanh toán khi nhận bánh.' : 'Shop sẽ xác nhận sau khi kiểm tra chuyển khoản.' ?>
    </p>
    <div class="success-status">
      Trạng thái: <span class="status-badge status-new">🆕 Đang chờ xác nhận</span>
    </div>
  </div>

  <!-- HOÁ ĐƠN — Visa-style -->
  <div class="invoice" id="invoice">

    <!-- Stripe màu ở trên được render qua ::before -->

    <div class="invoice-inner">

      <!-- Header: chip vàng + tên shop | mã đơn -->
      <div class="invoice-header">
        <div class="invoice-brand">
          <div class="inv-chip" title="Lena Bakery"></div>
          <div class="inv-brand-text">
            <div class="inv-shop-name"><?= SHOP_NAME ?></div>
            <div class="inv-shop-tag"><?= SHOP_TAGLINE ?></div>
          </div>
        </div>
        <div class="invoice-meta">
          <div class="inv-label">Hoá đơn</div>
          <div class="inv-code">#<?= htmlspecialchars($order['order_code']) ?></div>
          <div class="inv-date"><?= $orderDate ?></div>
        </div>
      </div>

      <!-- Parties: TỪ / ĐẾN -->
      <div class="invoice-parties">
        <div class="inv-party">
          <div class="inv-party-label">TỪ</div>
          <div class="inv-party-name"><?= SHOP_NAME ?></div>
          <div><?= SHOP_ADDRESS ?></div>
          <div><?= SHOP_PHONE ?></div>
        </div>
        <div class="inv-party">
          <div class="inv-party-label">ĐẾN</div>
          <div class="inv-party-name"><?= htmlspecialchars($order['customer_name']) ?></div>
          <div><?= htmlspecialchars($order['customer_phone']) ?></div>
          <?php if ($order['customer_addr']): ?>
          <div><?= htmlspecialchars($order['customer_addr']) ?></div>
          <?php else: ?>
          <div>🏪 Pickup tại quán</div>
          <?php endif; ?>
          <div style="margin-top:6px;font-weight:600;color:#b8956a;">
            <?= $isCOD ? '💵 Thanh toán khi nhận hàng (COD)' : '⚡ Đã chuyển khoản VietQR' ?>
          </div>
        </div>
      </div>

    </div><!-- /.invoice-inner -->

    <!-- Bảng sản phẩm — full width, sát viền -->
    <table class="invoice-table">
      <thead>
        <tr>
          <th>Sản phẩm</th>
          <th>Vị / Topping</th>
          <th style="text-align:center;">SL</th>
          <th style="text-align:right;">Đơn giá</th>
          <th>Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td><strong><?= htmlspecialchars($item['product_name']) ?></strong></td>
          <td style="color:#666;"><?= htmlspecialchars($item['flavor'] . ' • ' . $item['topping']) ?></td>
          <td style="text-align:center;"><?= $item['quantity'] ?></td>
          <td style="text-align:right;color:#555;"><?= number_format($item['unit_price'], 0, ',', '.') ?>đ</td>
          <td><?= number_format($item['subtotal'], 0, ',', '.') ?>đ</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="inv-row">
          <td colspan="4">Tạm tính</td>
          <td><?= number_format($order['subtotal'], 0, ',', '.') ?>đ</td>
        </tr>
        <?php if (!$isPickup): ?>
        <tr class="inv-row">
          <td colspan="4">Phí giao hàng (Ship)</td>
          <td><span style="color:#b45309;font-weight:600;">Chưa tính — trả shipper</span></td>
        </tr>
        <?php else: ?>
        <tr class="inv-row">
          <td colspan="4">Phí giao hàng</td>
          <td><span style="color:#059669;font-weight:600;">Miễn phí (Pickup)</span></td>
        </tr>
        <?php endif; ?>
        <?php if ($order['tip'] > 0): ?>
        <tr class="inv-row">
          <td colspan="4">Tip 💛</td>
          <td><?= number_format($order['tip'], 0, ',', '.') ?>đ</td>
        </tr>
        <?php endif; ?>
        <tr class="inv-total">
          <td colspan="4"><strong>TỔNG THANH TOÁN</strong></td>
          <td><strong><?= number_format($order['total'], 0, ',', '.') ?>đ</strong></td>
        </tr>
      </tfoot>
    </table>

    <?php if (!$isPickup): ?>
    <div class="inv-ship-note">
      <span class="inv-ship-note-icon">🛵</span>
      <div>
        <strong>Lưu ý phí ship:</strong> Hoá đơn trên chưa bao gồm phí giao hàng. Khi shipper mang bánh đến, quý khách vui lòng thanh toán cước ship theo app trực tiếp cho anh shipper nhé 💛
      </div>
    </div>
    <?php endif; ?>

    <?php if ($order['note']): ?>
    <div class="inv-note">
      <strong>Lời nhắn:</strong> <?= htmlspecialchars($order['note']) ?>
    </div>
    <?php endif; ?>

    <!-- Footer strip đen như thẻ Visa -->
    <div class="invoice-footer">
      <div>
        <div class="inv-footer-brand">Lena Bakery</div>
        <div>Cảm ơn bạn đã ủng hộ! Bánh làm tươi theo đơn 🎂</div>
      </div>
      <div class="inv-footer-contact">
        <div><?= SHOP_FACEBOOK ?></div>
        <div><?= SHOP_PHONE ?></div>
      </div>
    </div>

  </div><!-- /.invoice -->

  <!-- Actions -->
  <div class="success-actions">
    <button class="btn-print" onclick="window.print()">🖨️ In hoá đơn / Lưu PDF</button>
    <a href="<?= SHOP_ZALO ?>" target="_blank" class="btn-contact zalo">💬 Nhắn Zalo</a>
    <a href="https://m.me/caryln.fer" target="_blank" class="btn-contact fb">📱 Nhắn Messenger</a>
    <a href="checkout.php" class="btn-order-more">🛍️ Đặt thêm</a>
    <a href="index.php" class="btn-home">Về trang chủ</a>
  </div>

</main>

<script>
// Confetti animation
(function confetti() {
  const canvas  = document.getElementById('confettiCanvas');
  const ctx     = canvas.getContext('2d');
  canvas.width  = window.innerWidth;
  canvas.height = window.innerHeight;

  const colors = ['#8b3a2a','#b8956a','#d4b48c','#ede3d6','#f8f3ec','#c77557'];
  const pieces = Array.from({length:120}, () => ({
    x:  Math.random() * canvas.width,
    y:  Math.random() * canvas.height - canvas.height,
    w:  6 + Math.random() * 8,
    h:  3 + Math.random() * 5,
    color: colors[Math.floor(Math.random()*colors.length)],
    rot:  Math.random() * Math.PI * 2,
    vx:  (Math.random()-0.5)*2,
    vy:  2 + Math.random() * 3,
    vr:  (Math.random()-0.5)*0.15,
  }));

  let frame = 0;
  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    pieces.forEach(p => {
      ctx.save();
      ctx.translate(p.x, p.y);
      ctx.rotate(p.rot);
      ctx.fillStyle = p.color;
      ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
      ctx.restore();
      p.x += p.vx; p.y += p.vy; p.rot += p.vr;
      if (p.y > canvas.height) { p.y = -10; p.x = Math.random()*canvas.width; }
    });
    if (frame++ < 180) requestAnimationFrame(draw);
    else ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
  draw();
})();
</script>

</body>
</html>
