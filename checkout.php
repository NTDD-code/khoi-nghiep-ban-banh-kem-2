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
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="assets/css/checkout.css" />
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
        <div class="product-card" data-pid="<?= $pid ?>" data-price="<?= $p['price'] ?>">
          <?php if ($p['badge']): ?>
          <span class="product-badge"><?= $p['badge'] ?></span>
          <?php endif; ?>
          <div class="product-img-wrap">
            <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" />
          </div>
          <div class="product-info">
            <div class="product-meta">
              <h3><?= htmlspecialchars($p['name']) ?></h3>
              <span class="product-price"><?= number_format($p['price']/1000,0) ?>k</span>
            </div>
            <p class="product-desc"><?= htmlspecialchars($p['desc']) ?></p>

            <!-- Chọn vị -->
            <div class="product-options">
              <label class="opt-label">Vị:</label>
              <div class="opt-chips">
                <label class="chip"><input type="radio" name="flavor_<?= $pid ?>" value="cacao" checked /> Cacao</label>
                <label class="chip"><input type="radio" name="flavor_<?= $pid ?>" value="matcha" /> Matcha</label>
              </div>
            </div>

            <!-- Topping -->
            <div class="product-options">
              <label class="opt-label">Topping:</label>
              <select class="topping-select" name="topping_<?= $pid ?>">
                <?php foreach ($toppings as $tid => $t): ?>
                <option value="<?= $tid ?>"><?= $t['name'] ?><?= $t['price'] ? ' +' . number_format($t['price']/1000,0) . 'k' : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Qty -->
            <div class="qty-row">
              <div class="qty-control">
                <button class="qty-btn" data-action="minus" data-pid="<?= $pid ?>">−</button>
                <span class="qty-val" id="qty_<?= $pid ?>">0</span>
                <button class="qty-btn" data-action="plus" data-pid="<?= $pid ?>">+</button>
              </div>
              <span class="qty-subtotal" id="sub_<?= $pid ?>"></span>
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

    <!-- Sidebar giỏ hàng -->
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
              <div class="ship-card-icon">🏪</div>
              <div>
                <strong>Tự đến lấy (Pickup)</strong>
                <small>Tại <?= SHOP_ADDRESS ?><br><span class="badge-free">Miễn phí ship</span></small>
              </div>
            </label>
            <label class="ship-card" id="deliveryCard">
              <input type="radio" name="shipping_method" value="delivery" />
              <div class="ship-card-icon">🛵</div>
              <div>
                <strong>Giao tận nơi (Ship tận giường 🛌💨)</strong>
                <small>Cước Grab / Be / Xanh SM<br><span class="ship-warn-badge">⚠️ Chưa tính tiền ship vào đơn</span></small>
              </div>
            </label>
          </div>
        </div>

        <!-- Hộp thông báo khi chọn Giao tận nơi -->
        <div class="ship-notice-box" id="deliveryNoticeBox" style="display:none">
          <div class="snb-icon">🛌</div>
          <div class="snb-content">
            <strong>Ship tận giường nhà bạn luôn nè 🛵💨:</strong>
            <p>Bánh ngon sẽ được mang tới tận giường cho bạn, nhưng tổng tiền trên đơn <strong>chưa bao gồm phí ship</strong> đâu nha! Cước ship tính theo app (Grab / Be / Xanh SM) và <strong>bạn thanh toán trực tiếp cho anh shipper khi nhận bánh</strong> nhé 💛.</p>
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
            <button type="button" class="tip-chip" data-tip="10000">10k ☕</button>
            <button type="button" class="tip-chip" data-tip="20000">20k 🌸</button>
            <button type="button" class="tip-chip" data-tip="50000">50k 💫</button>
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
      <div class="summary-title">📋 Tóm tắt đơn hàng</div>
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
          <span class="pay-tab-icon">⚡</span>
          <div>
            <strong>Chuyển khoản VietQR</strong>
            <small>Quét mã QR tự động từ mọi app ngân hàng</small>
          </div>
        </label>
        <label class="pay-tab" id="tabCOD">
          <input type="radio" name="payment_choice" value="cod" />
          <span class="pay-tab-icon">💵</span>
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
            <button type="button" class="btn-copy-code" onclick="copyQRContent()" title="Sao chép nội dung">📋 Sao chép</button>
          </div>
          <div class="qr-note">
            📌 Vui lòng chuyển <strong>đúng số tiền và nội dung</strong> để shop xử lý nhanh nhất
          </div>
        </div>

        <!-- Khung 2: COD Info Box (hiện khi chọn COD) -->
        <div class="cod-box" id="codBox" style="display:none">
          <div class="cod-icon">💵</div>
          <h3>Thanh toán khi nhận hàng (COD)</h3>
          <p>Bạn sẽ thanh toán tiền bánh cho <strong>anh shipper</strong> khi nhận bánh tận nơi (hoặc thanh toán tại quán nếu bạn tự đến lấy).</p>
          <div class="cod-amount-card">
            <span>Tổng tiền bánh cần thanh toán:</span>
            <strong id="codAmountDisplay">0đ</strong>
          </div>
          <div class="cod-reminder">
            💡 <em>Lưu ý: Nếu chọn ship tận giường, cước app vận chuyển bạn vui lòng thanh toán riêng cho shipper theo cước app nhé!</em>
          </div>
        </div>

        <!-- Hành động -->
        <div class="payment-actions">
          <div class="payment-final-summary" id="finalSummary"></div>

          <div class="payment-btns">
            <!-- Nút khi chọn Transfer -->
            <button class="btn-confirm-paid" id="btnConfirmPaid">
              ✅ Tôi đã chuyển khoản xong — Đặt bánh!
            </button>
            <!-- Nút khi chọn COD -->
            <button class="btn-confirm-paid btn-cod-main" id="btnSubmitCOD" style="display:none">
              🛵 Xác nhận đặt bánh (Thanh toán khi nhận hàng) →
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
<script src="assets/js/checkout.js"></script>
</body>
</html>
