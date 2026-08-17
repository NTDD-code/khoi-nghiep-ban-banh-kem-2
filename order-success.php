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
<link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
<link rel="stylesheet" href="assets/css/checkout.css?v=<?= filemtime(__DIR__ . '/assets/css/checkout.css') ?>" />
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
    <div class="success-icon">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);">
        <circle cx="12" cy="12" r="10" style="stroke: var(--gold); fill: var(--cream);"></circle>
        <polyline points="8 12 11 15 16 9" style="stroke: var(--accent);"></polyline>
      </svg>
    </div>
    <h1 class="success-title">
      Cảm ơn bạn <em><?= htmlspecialchars($order['customer_name']) ?></em>!
    </h1>
    <p class="success-sub">
      Đơn hàng <strong><?= htmlspecialchars($order['order_code']) ?></strong> đã được ghi nhận.
      <?= $isCOD ? 'Bạn sẽ thanh toán khi nhận bánh.' : 'Shop sẽ xác nhận sau khi kiểm tra chuyển khoản.' ?>
    </p>
    <div class="success-status">
      Trạng thái: <span class="status-badge status-new"><span class="status-dot"></span>Đang chờ xác nhận</span>
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
          <div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:3px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>Pickup tại quán
          </div>
          <?php endif; ?>
          <div style="margin-top:6px;font-weight:600;color:#b8956a;display:flex;align-items:center;gap:4px;">
            <?php if ($isCOD): ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
              <span>Thanh toán khi nhận hàng (COD)</span>
            <?php else: ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#b8956a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><rect x="7" y="7" width="3" height="3"></rect><rect x="14" y="7" width="3" height="3"></rect><rect x="7" y="14" width="3" height="3"></rect><rect x="14" y="14" width="3" height="3"></rect></svg>
              <span>Đã chuyển khoản VietQR</span>
            <?php endif; ?>
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
          <td><span style="color:#b45309;font-weight:600;">Chưa tính — thanh toán khi nhận</span></td>
        </tr>
        <?php else: ?>
        <tr class="inv-row">
          <td colspan="4">Phí giao hàng</td>
          <td><span style="color:#059669;font-weight:600;">Miễn phí (Pickup)</span></td>
        </tr>
        <?php endif; ?>
        <?php if ($order['tip'] > 0): ?>
        <tr class="inv-row">
          <td colspan="4">Tip</td>
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
      <span class="inv-ship-note-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>
      </span>
      <div>
        <strong>Lưu ý phí ship:</strong> Hoá đơn trên chưa bao gồm phí giao hàng. Bánh do shop trực tiếp đi giao, quý khách vui lòng thanh toán tiền ship khi nhận bánh nhé.
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
        <div>Cảm ơn bạn đã ủng hộ! Bánh được làm tươi theo đơn đặt hàng.</div>
      </div>
      <div class="inv-footer-contact">
        <div><?= SHOP_FACEBOOK ?></div>
        <div><?= SHOP_PHONE ?></div>
      </div>
    </div>

  </div><!-- /.invoice -->

  <!-- Actions -->
  <div class="success-actions">
    <button class="btn-print" onclick="window.print()" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
      <span>In hoá đơn / Lưu PDF</span>
    </button>
    <a href="<?= SHOP_ZALO ?>" target="_blank" class="btn-contact zalo" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
      <span>Nhắn Zalo</span>
    </a>
    <a href="https://m.me/caryln.fer" target="_blank" class="btn-contact fb" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
      <span>Nhắn Messenger</span>
    </a>
    <a href="checkout.php" class="btn-order-more" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
      <span>Đặt thêm</span>
    </a>
    <a href="index.php" class="btn-home" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      <span>Về trang chủ</span>
    </a>
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
