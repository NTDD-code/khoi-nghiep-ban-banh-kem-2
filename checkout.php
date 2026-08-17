<?php
// ============================================================
// LENA BAKERY — TRANG CHECKOUT (3 BƯỚC)
// ============================================================

require_once __DIR__ . '/includes/config.php';

$products = PRODUCTS;
$toppings = TOPPINGS;
$productJson  = json_encode($products,  JSON_UNESCAPED_UNICODE);
$toppingJson  = json_encode($toppings,  JSON_UNESCAPED_UNICODE);
$shipFeeJson  = SHIP_FEE;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Đặt Bánh — <?= SHOP_NAME ?></title>
<meta name="description" content="Đặt bánh Tiramisu tươi tại Lena Bakery — chọn sản phẩm, điền thông tin và thanh toán chuyển khoản dễ dàng." />
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><circle cx='32' cy='32' r='30' fill='%23f7f1ea' stroke='%238b3a2a' stroke-width='1.5'/><text x='32' y='38' font-family='Georgia,serif' font-size='20' font-weight='600' fill='%238b3a2a' text-anchor='middle'>Lena</text></svg>" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
<link rel="stylesheet" href="assets/css/checkout.css?v=<?= filemtime(__DIR__ . '/assets/css/checkout.css') ?>" />
<style>
/* === MOBILE LAYOUT FIX ===
   Đảm bảo toàn bộ checkout hiển thị đúng trên mọi điện thoại.
   Dùng !important để ép browser bỏ layout desktop.
*/
@media screen and (max-width: 900px) {

  /* Body + html không được tràn ngang */
  html, body {
    width: 100% !important;
    max-width: 100vw !important;
    overflow-x: hidden !important;
  }

  /* Main container chiếm full width */
  .co-main {
    width: 100% !important;
    max-width: 100% !important;
    padding-left: 14px !important;
    padding-right: 14px !important;
    padding-bottom: 130px !important;
    box-sizing: border-box !important;
    margin: 0 !important;
  }

  /* Bỏ grid 2 cột, chuyển sang block đơn giản */
  .co-panel,
  .co-panel.active,
  #step1, #step1.active,
  #step2, #step2.active,
  #step3, #step3.active {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    grid-template-columns: unset !important;
    padding-top: 14px !important;
  }

  /* Cột trái chiếm toàn bộ */
  .co-left {
    width: 100% !important;
    max-width: 100% !important;
    float: none !important;
    display: block !important;
    overflow: visible !important;
  }

  /* Grid sản phẩm: 1 cột, 100% width — FIX CHÍNH */
  #productGrid,
  .product-grid {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    width: 100% !important;
    max-width: 100% !important;
    gap: 14px !important;
    margin: 0 0 20px 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    grid-template-columns: unset !important;
  }

  /* Thẻ sản phẩm full width */
  .product-card {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    flex-shrink: 0 !important;
    display: block !important;
  }

  /* Ảnh sản phẩm */
  .product-img-wrap {
    width: 100% !important;
    height: 200px !important;
    overflow: hidden !important;
  }
  .product-img-wrap img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
  }

  /* Thông tin sản phẩm */
  .product-info {
    width: 100% !important;
    box-sizing: border-box !important;
    padding: 14px 16px !important;
  }

  /* Tiêu đề trang */
  .co-title {
    font-size: 24px !important;
    letter-spacing: -0.5px !important;
    margin-bottom: 16px !important;
    line-height: 1.2 !important;
  }

  /* Sidebar giỏ hàng: ẩn ở bước 1, dùng floating bar */
  #step1 .co-cart,
  #step1 aside.co-cart {
    display: none !important;
  }

  /* Sidebar ở bước 2 hiển thị bình thường */
  #step2 aside.co-cart {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    position: static !important;
    margin-top: 20px !important;
  }

  /* Sticky floating bar */
  .co-mobile-bar {
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 1000 !important;
    background: rgba(26,17,14,0.96) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 12px 18px calc(12px + env(safe-area-inset-bottom,0px)) !important;
    transform: translateY(120%) !important;
    transition: transform 0.35s cubic-bezier(0.2,0.9,0.3,1) !important;
    box-shadow: 0 -6px 24px rgba(0,0,0,0.25) !important;
  }
  .co-mobile-bar.visible {
    transform: translateY(0) !important;
  }
  .co-m-count {
    font-size: 11px !important;
    color: rgba(255,255,255,0.65) !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    display: block !important;
  }
  .co-m-total {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #f0d080 !important;
    display: block !important;
    line-height: 1.1 !important;
  }
  .co-mobile-bar-btn {
    background: linear-gradient(135deg,#8b3a2a,#b55a47) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 999px !important;
    padding: 11px 22px !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    white-space: nowrap !important;
  }

  /* Form nhập thông tin: 1 cột */
  .ship-method-cards,
  .pay-method-tabs {
    grid-template-columns: 1fr !important;
    gap: 10px !important;
  }
  .form-row input[type=text],
  .form-row input[type=tel],
  .form-row textarea {
    font-size: 16px !important; /* ngăn iOS tự zoom */
    width: 100% !important;
    box-sizing: border-box !important;
  }

  /* Layout thanh toán 1 cột */
  .payment-layout {
    display: block !important;
    width: 100% !important;
  }
  .qr-box, .cod-box {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    margin-bottom: 20px !important;
  }
  .qr-img-wrap {
    width: 220px !important;
    height: 220px !important;
  }
}
</style>
</head>
<body class="checkout-page">

<header>
  <nav class="co-nav">
    <a href="index.php" class="brand">Lena<em>Bakery</em></a>
    <div class="co-nav-steps">
      <span class="co-step active" data-step="1"><span class="step-num">1</span> Chọn bánh</span>
      <span class="co-step-sep">›</span>
      <span class="co-step" data-step="2"><span class="step-num">2</span> Thông tin</span>
      <span class="co-step-sep">›</span>
      <span class="co-step" data-step="3"><span class="step-num">3</span> Thanh toán</span>
    </div>
    <a href="index.php" class="co-back-link">← Về trang chủ</a>
  </nav>
</header>

<main class="co-main">

  <!-- ===== BƯỚC 1: CHỌN SẢN PHẨM ===== -->
  <div class="co-panel active" id="step1">
    <div class="co-left">
      <div class="co-section-label">01 — Chọn bánh</div>
      <h1 class="co-title">Bạn muốn đặt <em>loại nào?</em></h1>

      <div class="product-grid" id="productGrid">
        <?php foreach ($products as $pid => $p): ?>
        <?php $isOut = !empty($p['is_out_of_stock']); ?>
        <div class="product-card <?= $isOut ? 'is-out-of-stock' : '' ?>" data-pid="<?= $pid ?>" data-price="<?= $p['price'] ?>" data-outofstock="<?= $isOut ? '1' : '0' ?>" style="<?= $isOut ? 'opacity:0.75;background:#fcf8f8;' : '' ?>">
          <?php if ($isOut): ?>
          <span class="product-badge" style="background:#ef4444;color:#fff;">Tạm hết</span>
          <?php elseif ($p['badge']): ?>
          <span class="product-badge"><?= $p['badge'] ?></span>
          <?php endif; ?>

          <!-- Ảnh vuông bên trái -->
          <div class="product-img-wrap" style="<?= $isOut ? 'filter: grayscale(0.5);' : '' ?>">
            <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" />
          </div>

          <!-- Nội dung bên phải -->
          <div class="product-info">
            <div class="product-meta">
              <h3><?= htmlspecialchars($p['name']) ?></h3>
              <span class="product-price"><?= number_format($p['price']/1000,0) ?>k</span>
            </div>
            <p class="product-desc"><?= htmlspecialchars($p['desc']) ?></p>

            <!-- Chọn vị -->
            <div class="product-options" style="<?= $isOut ? 'opacity:0.5;pointer-events:none;' : '' ?>">
              <label class="opt-label">Vị:</label>
              <div class="opt-chips">
                <?php foreach (($p['flavors'] ?? ['cacao', 'matcha']) as $idx => $f): ?>
                <label class="chip"><input type="radio" name="flavor_<?= $pid ?>" value="<?= htmlspecialchars($f) ?>" <?= $idx === 0 ? 'checked' : '' ?> /> <?= ucfirst(htmlspecialchars($f)) ?></label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Topping -->
            <div class="product-options" style="<?= $isOut ? 'opacity:0.5;pointer-events:none;' : '' ?>">
              <label class="opt-label">Topping:</label>
              <select class="topping-select" name="topping_<?= $pid ?>">
                <?php foreach ($toppings as $tid => $t): ?>
                <option value="<?= $tid ?>"><?= $t['name'] ?><?= $t['price'] ? ' +' . number_format($t['price']/1000,0) . 'k' : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Qty controls -->
            <div class="qty-row">
              <?php if ($isOut): ?>
              <div style="display:flex;align-items:center;gap:6px;">
                <span class="badge" style="background:#fee2e2;color:#dc2626;font-size:12px;padding:4px 8px;border-radius:4px;font-weight:600;">Tạm hết hàng</span>
              </div>
              <?php else: ?>
              <div class="qty-control">
                <button class="qty-btn" data-action="minus" data-pid="<?= $pid ?>" aria-label="Giảm">−</button>
                <span class="qty-val" id="qty_<?= $pid ?>">0</span>
                <button class="qty-btn" data-action="plus" data-pid="<?= $pid ?>" aria-label="Tăng">+</button>
              </div>
              <span class="qty-subtotal" id="sub_<?= $pid ?>"></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Trái cây tươi ghi chú -->
      <div class="fruit-note">
        🍓 <strong>Trái cây tươi ăn kèm</strong> đã bao gồm trong phần chọn topping phía trên.
      </div>
    </div>

    <!-- Sidebar giỏ hàng (Desktop) -->
    <aside class="co-cart" id="cartSidebar">
      <div class="cart-header">
        <span class="cart-title">🛒 Giỏ hàng</span>
        <span class="cart-count" id="cartCount">0 món</span>
      </div>
      <div class="cart-empty" id="cartEmpty">Chưa có sản phẩm nào</div>
      <ul class="cart-list" id="cartList"></ul>
      <div class="cart-total-row" id="cartTotalRow" style="display:none">
        <span>Tạm tính</span>
        <strong id="cartSubtotal">0đ</strong>
      </div>
      <button class="cart-next-btn" id="cartNextBtn" disabled onclick="goStep(2)">
        Tiếp tục → Thông tin giao hàng
      </button>
    </aside>

    <!-- Mobile Sticky Cart Bar (chỉ hiện trên điện thoại khi có món) -->
    <div class="co-mobile-bar" id="mobileCartBar">
      <div class="co-mobile-bar-info" onclick="openCartSheet()" style="cursor:pointer;flex:1">
        <span class="co-m-count" id="mCartCount">0 món đã chọn</span>
        <span class="co-m-total" id="mCartTotal">0đ</span>
      </div>
      <button class="co-mobile-bar-btn" id="mCartNextBtn" disabled onclick="openCartSheet()">
        Xem giỏ 🛒
      </button>
    </div>

    <!-- Cart Bottom Sheet (overlay + sheet) -->
    <div class="cart-sheet-overlay" id="cartSheetOverlay" onclick="closeCartSheet()"></div>
    <div class="cart-sheet" id="cartSheet">
      <div class="cart-sheet-handle"></div>
      <div class="cart-sheet-header">
        <span class="cart-sheet-title">🛒 Giỏ hàng của bạn</span>
        <button class="cart-sheet-close" onclick="closeCartSheet()" aria-label="Đóng">✕</button>
      </div>
      <ul class="cart-sheet-list" id="cartSheetList"></ul>
      <div class="cart-sheet-footer">
        <div class="cart-sheet-total-row">
          <span>Tạm tính</span>
          <strong id="cartSheetSubtotal">0đ</strong>
        </div>
        <button class="cart-sheet-next-btn" id="cartSheetNextBtn" onclick="closeCartSheet(); goStep(2);">
          Tiếp tục đặt hàng →
        </button>
      </div>
    </div>
  </div>

  <!-- ===== BƯỚC 2: THÔNG TIN GIAO HÀNG ===== -->
  <div class="co-panel" id="step2">
    <div class="co-left">
      <div class="co-section-label">02 — Thông tin</div>
      <h2 class="co-title">Thông tin <em>nhận bánh</em></h2>

      <form id="infoForm" class="info-form" novalidate>

        <div class="form-row">
          <label for="customerName">Tên của bạn *</label>
          <input type="text" id="customerName" name="customer_name" placeholder="VD: Nguyễn Văn A" required autocomplete="name" />
        </div>

        <div class="form-row">
          <label for="customerPhone">Số điện thoại *</label>
          <input type="tel" id="customerPhone" name="customer_phone" placeholder="0906 819 341" required autocomplete="tel" />
        </div>

        <!-- Hình thức giao -->
        <div class="form-row">
          <label>Hình thức nhận bánh *</label>
          <div class="ship-method-cards">
            <label class="ship-card active" id="pickupCard">
              <input type="radio" name="shipping_method" value="pickup" checked />
              <div class="ship-card-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
              </div>
              <div>
                <strong>Tự đến lấy (Pickup)</strong>
                <small>Tại <?= SHOP_ADDRESS ?><br><span class="badge-free">Miễn phí ship</span></small>
              </div>
            </label>
            <label class="ship-card" id="deliveryCard">
              <input type="radio" name="shipping_method" value="delivery" />
              <div class="ship-card-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><circle cx="15" cy="5" r="1"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>
              </div>
              <div>
                <strong>Giao tận nơi (Shop tự ship)</strong>
                <small>Shop trực tiếp giao hàng<br><span class="ship-warn-badge">⚠️ Chưa tính tiền ship vào đơn</span></small>
              </div>
            </label>
          </div>
        </div>

        <!-- Hộp thông báo khi chọn Giao tận nơi -->
        <div class="ship-notice-box" id="deliveryNoticeBox" style="display:none">
          <div class="snb-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>
          </div>
          <div class="snb-content">
            <strong>Shop tự ship tận nơi cho bạn nè:</strong>
            <p>Bánh ngon sẽ do <strong>shop tự mang tới tận nơi</strong> cho bạn nha! Tổng tiền trên web <strong>chưa bao gồm phí ship</strong>, tiền ship sẽ được thanh toán trực tiếp cho người giao bánh khi nhận nhé 💛.</p>
          </div>
        </div>

        <!-- Địa chỉ (chỉ hiện khi chọn delivery) -->
        <div class="form-row" id="addrRow" style="display:none">
          <label for="customerAddr">Địa chỉ giao hàng *</label>
          <input type="text" id="customerAddr" name="customer_addr" placeholder="Số nhà, đường, phường/quận, TP.HCM" autocomplete="street-address" />
        </div>

        <!-- Lời nhắn -->
        <div class="form-row">
          <label for="customerNote">Lời nhắn cho shop <span class="optional">(tuỳ chọn)</span></label>
          <textarea id="customerNote" name="note" rows="3" placeholder="VD: Giao buổi sáng trước 10h, bánh không cần đá..."></textarea>
        </div>

        <!-- Tip slider -->
        <div class="form-row tip-row">
          <label>
            Thêm tip cho shop 💛
            <span class="optional">(tuỳ chọn — đây là cách cảm ơn người làm bánh)</span>
          </label>
          <div class="tip-options">
            <button type="button" class="tip-chip active" data-tip="0">0k (Không)</button>
            <button type="button" class="tip-chip" data-tip="5000">5k</button>
            <button type="button" class="tip-chip" data-tip="10000">10k</button>
            <button type="button" class="tip-chip" data-tip="20000">20k</button>
            <button type="button" class="tip-chip" data-tip="50000">50k</button>
          </div>
          <div class="tip-custom-row">
            <span>Hoặc nhập số tiền khác:</span>
            <input type="number" id="tipCustom" min="0" step="1000" placeholder="VD: 15000" />
            <span>đ</span>
          </div>
          <div class="tip-display">Tip đang chọn: <strong id="tipAmount">0đ</strong></div>
        </div>

      </form>

      <div class="step-nav">
        <button class="btn-back" onclick="goStep(1)">← Quay lại</button>
        <button class="btn-next" onclick="validateStep2()">Tiếp tục → Thanh toán</button>
      </div>
    </div>

    <!-- Sidebar tóm tắt -->
    <aside class="co-cart summary-sidebar" id="summaryStep2">
      <div class="summary-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        Tóm tắt đơn hàng
      </div>
      <ul class="summary-list" id="summaryList2"></ul>
      <div class="summary-rows" id="summaryRows2"></div>
    </aside>
  </div>

  <!-- ===== BƯỚC 3: THANH TOÁN ===== -->
  <div class="co-panel" id="step3">
    <div class="co-left">
      <div class="co-section-label">03 — Thanh toán</div>
      <h2 class="co-title">Chọn hình thức <em>thanh toán</em></h2>

      <!-- Chọn phương thức thanh toán -->
      <div class="pay-method-tabs">
        <label class="pay-tab active" id="tabTransfer">
          <input type="radio" name="payment_choice" value="transfer" checked />
          <span class="pay-tab-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#8b3a2a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><rect x="7" y="7" width="3" height="3"></rect><rect x="14" y="7" width="3" height="3"></rect><rect x="7" y="14" width="3" height="3"></rect><rect x="14" y="14" width="3" height="3"></rect></svg>
          </span>
          <div>
            <strong>Chuyển khoản VietQR</strong>
            <small>Quét mã QR tự động từ mọi app ngân hàng</small>
          </div>
        </label>
        <label class="pay-tab" id="tabCOD">
          <input type="radio" name="payment_choice" value="cod" />
          <span class="pay-tab-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
          </span>
          <div>
            <strong>Thanh toán khi nhận bánh (COD)</strong>
            <small>Tiền mặt hoặc chuyển khoản lúc nhận bánh</small>
          </div>
        </label>
      </div>

      <div class="payment-layout">
        <!-- Khung 1: VietQR Layout (hiện khi chọn transfer) -->
        <div class="qr-box" id="qrBox">
          <div class="qr-bank-info">
            <span class="qr-bank-name">Sacombank</span>
            <span class="qr-acc"><?= BANK_ACCOUNT_NO ?></span>
            <span class="qr-acc-name"><?= BANK_ACCOUNT_NAME ?></span>
          </div>
          <div class="qr-img-wrap">
            <img id="qrImage" src="" alt="QR VietQR" />
            <div class="qr-loading" id="qrLoading">Đang tạo QR...</div>
          </div>
          <div class="qr-amount-display">
            Số tiền: <strong id="qrAmountDisplay"></strong>
          </div>
          <div class="qr-content-display">
            Nội dung CK: <code id="qrContentDisplay"></code>
            <button type="button" class="btn-copy-code" onclick="copyQRContent()" title="Sao chép nội dung">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>Sao chép
            </button>
          </div>
          <div class="qr-note">
            📌 Vui lòng chuyển <strong>đúng số tiền và nội dung</strong> để shop xử lý nhanh nhất
          </div>
        </div>

        <!-- Khung 2: COD Info Box (hiện khi chọn COD) -->
        <div class="cod-box" id="codBox" style="display:none">
          <div class="cod-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
          </div>
          <h3>Thanh toán khi nhận hàng (COD)</h3>
          <p>Bạn sẽ thanh toán tiền bánh trực tiếp cho <strong>người giao hàng của shop</strong> khi nhận bánh tận nơi (hoặc thanh toán tại quán nếu bạn tự đến lấy).</p>
          <div class="cod-amount-card">
            <span>Tổng tiền bánh cần thanh toán:</span>
            <strong id="codAmountDisplay">0đ</strong>
          </div>
          <div class="cod-reminder">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <em>Lưu ý: Nếu chọn giao tận nơi, phí ship sẽ được thanh toán trực tiếp khi nhận bánh nhé!</em>
          </div>
        </div>

        <!-- Hành động -->
        <div class="payment-actions">
          <div class="payment-final-summary" id="finalSummary"></div>

          <div class="payment-btns">
            <!-- Nút khi chọn Transfer -->
            <button class="btn-confirm-paid" id="btnConfirmPaid">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><polyline points="20 6 9 17 4 12"></polyline></svg>Tôi đã chuyển khoản xong — Đặt bánh!
            </button>
            <!-- Nút khi chọn COD -->
            <button class="btn-confirm-paid btn-cod-main" id="btnSubmitCOD" style="display:none">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>Xác nhận đặt bánh (Thanh toán khi nhận hàng) →
            </button>

            <div class="payment-spinner" id="paySpinner" style="display:none">
              <span class="spinner"></span> Đang xử lý đơn hàng...
            </div>
          </div>

          <div class="payment-back-row">
            <button type="button" class="btn-step-back" onclick="goStep(2)">
              ← Quay lại Bước 2 (Sửa thông tin / Đổi bánh)
            </button>
          </div>

          <div class="payment-contacts">
            <p>Cần hỗ trợ? Nhắn ngay:</p>
            <a href="<?= SHOP_ZALO ?>" target="_blank" class="contact-pill zalo-pill">💬 Zalo <?= SHOP_PHONE ?></a>
            <a href="https://m.me/caryln.fer" target="_blank" class="contact-pill fb-pill">📱 Messenger</a>
          </div>
        </div>
      </div>
    </div>
  </div>

</main>

<script>
// ============================================================
// DATA từ PHP
// ============================================================
const PRODUCTS_DATA  = <?= $productJson ?>;
const TOPPINGS_DATA  = <?= $toppingJson ?>;
const SHIP_FEE       = <?= $shipFeeJson ?>;
const BANK_ID        = '<?= BANK_ID ?>';
const BANK_ACC       = '<?= BANK_ACCOUNT_NO ?>';
const BANK_NAME      = '<?= BANK_ACCOUNT_NAME ?>';
</script>
<script src="assets/js/checkout.js?v=<?= filemtime(__DIR__ . '/assets/js/checkout.js') ?>"></script>
</body>
</html>
