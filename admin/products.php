<?php
// ============================================================
// LENA BAKERY — ADMIN: QUẢN LÝ SẢN PHẨM & TỒN KHO (HẾT HÀNG)
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
require_once __DIR__ . '/../includes/products.php';

$db = get_db();
$pending = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='new'")->fetchColumn();
$unread  = (int)$db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn();

$allProducts = get_all_products(false);

$totalCount = count($allProducts);
$activeCount = count(array_filter($allProducts, fn($p) => !empty($p['is_active']) && empty($p['is_out_of_stock'])));
$outCount = count(array_filter($allProducts, fn($p) => !empty($p['is_out_of_stock'])));
$hiddenCount = count(array_filter($allProducts, fn($p) => empty($p['is_active'])));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Quản lý Sản phẩm & Hết hàng — <?= SHOP_NAME ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>" />
<style>
.product-thumb {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
  background: var(--surface2);
  border: 1px solid var(--line);
}
.badge-stock {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.badge-in-stock {
  background: rgba(16, 185, 129, 0.12);
  color: #34d399;
}
.badge-out-stock {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
}
.badge-hidden {
  background: rgba(156, 163, 175, 0.15);
  color: #9ca3af;
}

.table-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-action-sm {
  background: var(--surface2);
  border: 1px solid var(--line);
  color: var(--text);
  padding: 6px 10px;
  border-radius: 6px;
  font-family: var(--font);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
  text-decoration: none;
}
.btn-action-sm:hover {
  background: var(--surface-hover);
  border-color: var(--line-strong);
}
.btn-action-sm.btn-del:hover {
  background: rgba(239, 68, 68, 0.15);
  border-color: #ef4444;
  color: #f87171;
}

/* Modal Form Styles */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.form-group-full {
  grid-column: 1 / -1;
}
.form-group label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 6px;
}
.form-control {
  width: 100%;
  background: var(--surface2);
  border: 1px solid var(--line);
  border-radius: 6px;
  padding: 9px 12px;
  font-family: var(--font);
  font-size: 13px;
  color: var(--text);
  outline: none;
  transition: border-color 0.2s;
  box-sizing: border-box;
}
.form-control:focus {
  border-color: var(--accent);
}

/* ===== UPLOAD ZONE ===== */
.upload-zone {
  border: 2px dashed var(--line);
  border-radius: 10px;
  background: var(--surface2);
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  overflow: hidden;
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 8px;
  text-align: center;
  padding: 16px;
}
.upload-zone:hover,
.upload-zone.dragover {
  border-color: var(--accent);
  background: rgba(139, 58, 42, 0.06);
}
.upload-zone input[type="file"] {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
  width: 100%;
  height: 100%;
  font-size: 0;
}
.upload-zone .uz-icon {
  color: var(--text-muted);
  opacity: 0.6;
}
.upload-zone .uz-label {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
  pointer-events: none;
}
.upload-zone .uz-sub {
  font-size: 11px;
  color: var(--text-dim);
  pointer-events: none;
}
.upload-zone .uz-progress {
  display: none;
  width: 90%;
  height: 4px;
  background: var(--line);
  border-radius: 99px;
  overflow: hidden;
  margin-top: 4px;
}
.upload-zone .uz-progress-bar {
  height: 100%;
  background: var(--accent);
  border-radius: 99px;
  width: 0;
  transition: width 0.3s;
}
.upload-preview {
  margin-top: 8px;
  position: relative;
  display: none;
}
.upload-preview img {
  width: 100%;
  max-height: 180px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid var(--line);
  display: block;
}
.upload-preview .prev-clear {
  position: absolute;
  top: 6px;
  right: 6px;
  background: rgba(0,0,0,0.55);
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 26px;
  height: 26px;
  font-size: 15px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  padding: 0;
}
.upload-preview .prev-name {
  font-size: 11px;
  color: var(--text-dim);
  margin-top: 5px;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
}
</style>
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
    <a href="index.php" class="sn-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
      <span>Đơn hàng</span>
      <span class="sn-badge" id="pendingBadge"><?= $pending ?></span>
    </a>
    <a href="products.php" class="sn-link active">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
      <span>Sản phẩm</span>
    </a>
    <a href="messages.php" class="sn-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
      <span>Tin nhắn</span>
      <span class="sn-badge" id="msgBadge"><?= $unread ?></span>
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
      <h1 class="admin-title">Quản lý Mặt hàng & Trạng thái Hết hàng</h1>
      <div class="admin-date-badge">
        <span>Tự động cập nhật trực tiếp trên Website & Checkout</span>
      </div>
    </div>
    <div>
      <button type="button" class="btn-modal-save" onclick="openProductModal()" style="display:inline-flex;align-items:center;gap:6px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>+ Thêm mặt hàng mới</span>
      </button>
    </div>
  </div>

  <!-- Stats cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-header">
        <span class="stat-label">Tổng mặt hàng</span>
        <div class="stat-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path></svg>
        </div>
      </div>
      <div class="stat-val"><?= $totalCount ?></div>
      <div class="stat-label" style="font-size:11.5px;color:var(--text-dim);">Trong danh mục</div>
    </div>

    <div class="stat-card accent">
      <div class="stat-header">
        <span class="stat-label">Đang mở bán</span>
        <div class="stat-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
      </div>
      <div class="stat-val" style="color:#34d399;"><?= $activeCount ?></div>
      <div class="stat-label" style="font-size:11.5px;color:var(--text-dim);">Khách có thể đặt ngay</div>
    </div>

    <div class="stat-card warn">
      <div class="stat-header">
        <span class="stat-label">Tạm hết hàng</span>
        <div class="stat-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        </div>
      </div>
      <div class="stat-val" style="color:#f87171;"><?= $outCount ?></div>
      <div class="stat-label" style="font-size:11.5px;color:var(--text-dim);">Báo hết trên web</div>
    </div>

    <div class="stat-card">
      <div class="stat-header">
        <span class="stat-label">Đang ẩn</span>
        <div class="stat-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
        </div>
      </div>
      <div class="stat-val"><?= $hiddenCount ?></div>
      <div class="stat-label" style="font-size:11.5px;color:var(--text-dim);">Không hiện trên menu</div>
    </div>
  </div>

  <!-- Products table -->
  <div class="orders-wrap">
    <div class="table-responsive">
      <table class="orders-table">
        <thead>
          <tr>
            <th>Ảnh</th>
            <th>Tên sản phẩm & Mã</th>
            <th>Giá bán (VNĐ)</th>
            <th>Vị & Huy hiệu</th>
            <th>Báo Hết hàng (Out of stock)</th>
            <th>Hiển thị trên Web</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allProducts as $pid => $p): ?>
          <?php 
            $isOut = !empty($p['is_out_of_stock']);
            $isActive = !empty($p['is_active']);
          ?>
          <tr id="row_<?= htmlspecialchars($pid) ?>">
            <!-- Ảnh -->
            <td style="width:60px;">
              <img src="../<?= htmlspecialchars($p['img'] ?: 'assets/images/cake-classic.jpg') ?>" class="product-thumb" alt="" />
            </td>

            <!-- Tên & Mã -->
            <td>
              <div style="font-weight:700;color:var(--text);font-size:14px;"><?= htmlspecialchars($p['name']) ?></div>
              <div style="color:var(--accent);font-size:11.5px;font-family:monospace;">ID: <?= htmlspecialchars($pid) ?></div>
              <div style="color:var(--text-dim);font-size:12px;margin-top:2px;max-width:280px;line-height:1.4;">
                <?= htmlspecialchars($p['desc'] ?? '') ?>
              </div>
            </td>

            <!-- Giá -->
            <td>
              <strong style="color:var(--gold);font-size:15px;"><?= number_format($p['price'], 0, ',', '.') ?>đ</strong>
            </td>

            <!-- Vị & trạng thái hết theo vị -->
            <td>
              <?php 
                $oosF = $p['out_of_stock_flavors'] ?? [];
              ?>
              <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:4px;">
                <?php foreach (($p['flavors'] ?? ['cacao', 'matcha']) as $f): 
                  $fIsOut = in_array($f, $oosF);
                ?>
                <button type="button"
                  id="flavorBtn_<?= htmlspecialchars($pid) ?>_<?= htmlspecialchars($f) ?>"
                  onclick="toggleFlavorStock('<?= htmlspecialchars($pid) ?>', '<?= htmlspecialchars($f) ?>', <?= $fIsOut ? 'false' : 'true' ?>)"
                  title="Click để <?= $fIsOut ? 'mở lại' : 'đánh dấu hết' ?> vị <?= ucfirst(htmlspecialchars($f)) ?>"
                  style="border:none;cursor:pointer;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;font-family:var(--font);transition:all 0.2s;
                    <?= $fIsOut 
                      ? 'background:rgba(239,68,68,0.15);color:#f87171;text-decoration:line-through;' 
                      : 'background:var(--surface2);border:1px solid var(--line);color:var(--text);' ?>">
                  <?= $fIsOut ? '✕ ' : '' ?><?= ucfirst(htmlspecialchars($f)) ?>
                </button>
                <?php endforeach; ?>
              </div>
              <?php if (!empty($p['badge'])): ?>
              <span class="badge" style="background:rgba(217,119,6,0.15);color:#fbbf24;font-size:11px;"><?= htmlspecialchars($p['badge']) ?></span>
              <?php endif; ?>
              <div style="font-size:10px;color:var(--text-dim);margin-top:3px;">Click vị để bật/tắt hết hàng</div>
            </td>


            <!-- Bật/Tắt Hết hàng (Instant Toggle Switch) -->
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <label class="switch-toggle">
                  <input type="checkbox" <?= $isOut ? 'checked' : '' ?> onchange="toggleProductStock('<?= htmlspecialchars($pid) ?>', this.checked)">
                  <span class="switch-slider" style="<?= $isOut ? 'background-color:#ef4444;' : '' ?>"></span>
                </label>
                <span id="stockLabel_<?= htmlspecialchars($pid) ?>" class="badge-stock <?= $isOut ? 'badge-out-stock' : 'badge-in-stock' ?>">
                  <span class="status-dot" style="background:<?= $isOut ? '#ef4444' : '#10b981' ?>"></span>
                  <?= $isOut ? 'Tạm hết hàng' : 'Còn hàng' ?>
                </span>
              </div>
            </td>

            <!-- Bật/Tắt Hiển thị -->
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <label class="switch-toggle">
                  <input type="checkbox" <?= $isActive ? 'checked' : '' ?> onchange="toggleProductActive('<?= htmlspecialchars($pid) ?>', this.checked)">
                  <span class="switch-slider"></span>
                </label>
                <span id="activeLabel_<?= htmlspecialchars($pid) ?>" class="badge-stock <?= $isActive ? 'badge-in-stock' : 'badge-hidden' ?>">
                  <?= $isActive ? 'Hiển thị' : 'Đang ẩn' ?>
                </span>
              </div>
            </td>

            <!-- Thao tác Sửa / Xoá -->
            <td>
              <div class="table-actions">
                <button type="button" class="btn-action-sm" onclick='editProduct(<?= json_encode($p, JSON_UNESCAPED_UNICODE) ?>)'>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  <span>Sửa</span>
                </button>
                <button type="button" class="btn-action-sm btn-del" onclick="deleteProduct('<?= htmlspecialchars($pid) ?>', '<?= htmlspecialchars($p['name']) ?>')">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                  <span>Xoá</span>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- MODAL THÊM / SỬA SẢN PHẨM -->
<div class="modal-backdrop" id="productModal" style="display:none;" onclick="if(event.target===this)closeProductModal()">
  <div class="admin-modal-card" style="max-width:580px;">
    <div class="modal-header">
      <div class="modal-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path></svg>
        <span id="modalProductTitle">Thêm mặt hàng mới</span>
      </div>
      <button type="button" class="modal-close-btn" onclick="closeProductModal()">&times;</button>
    </div>
    
    <form id="productForm" onsubmit="handleProductSubmit(event)">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Mã sản phẩm (ID / Slug)*</label>
            <input type="text" class="form-control" id="p_id" name="id" placeholder="vd: mini, 350ml, tin750, cake-gift" required />
          </div>
          <div class="form-group">
            <label>Giá bán (VNĐ)*</label>
            <input type="number" class="form-control" id="p_price" name="price" placeholder="vd: 75000" min="1000" step="1000" required />
          </div>
          <div class="form-group form-group-full">
            <label>Tên mặt hàng*</label>
            <input type="text" class="form-control" id="p_name" name="name" placeholder="vd: Tiramisu Hộp Thiếc Cao Cấp 750ml" required />
          </div>
          <div class="form-group">
            <label>Huy hiệu (Badge nổi bật)</label>
            <input type="text" class="form-control" id="p_badge" name="badge" placeholder="vd: Bán chạy, Mới, Sang trọng..." />
          </div>
          <div class="form-group">
            <label>Các vị (cách nhau bởi dấu phẩy)</label>
            <input type="text" class="form-control" id="p_flavors" name="flavors" placeholder="cacao, matcha, dâu" value="cacao, matcha" />
          </div>
          <div class="form-group form-group-full">
            <label>Ảnh sản phẩm</label>

            <!-- Upload Zone -->
            <div class="upload-zone" id="uploadZone">
              <input type="file" id="p_img_file" accept="image/*" capture="environment" />
              <svg class="uz-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              <span class="uz-label">Chạm để chọn ảnh &nbsp;·&nbsp; Kéo thả vào đây</span>
              <span class="uz-sub">JPG, PNG, WEBP — tối đa 8MB</span>
              <div class="uz-progress"><div class="uz-progress-bar" id="uzProgressBar"></div></div>
            </div>

            <!-- Preview -->
            <div class="upload-preview" id="uploadPreview">
              <img id="uploadPreviewImg" src="" alt="Preview" />
              <button type="button" class="prev-clear" title="Xoá ảnh" onclick="clearUpload()">×</button>
              <div class="prev-name" id="uploadPreviewName"></div>
            </div>

            <!-- Hidden: lưu đường dẫn cuối cùng gửi lên server -->
            <input type="hidden" id="p_img" name="img" value="assets/images/cake-classic.jpg" />
          </div>
          <div class="form-group form-group-full">
            <label>Mô tả ngắn về bánh</label>
            <textarea class="form-control" id="p_desc" name="desc" rows="2" placeholder="Nhỏ gọn cho 1 người. Kem Mascarpone ngậy tan, cốt bánh đượm Espresso..."></textarea>
          </div>
          
          <div class="form-group">
            <label>Trạng thái tồn kho</label>
            <label class="setting-radio-item" style="padding:8px 12px;">
              <input type="checkbox" id="p_out_of_stock" name="is_out_of_stock" />
              <span><strong>Đánh dấu Tạm hết hàng</strong></span>
            </label>
          </div>

          <div class="form-group">
            <label>Hiển thị trên Website</label>
            <label class="setting-radio-item" style="padding:8px 12px;">
              <input type="checkbox" id="p_active" name="is_active" checked />
              <span><strong>Hiển thị trên Menu & Checkout</strong></span>
            </label>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-action-sm" onclick="closeProductModal()" style="margin-right:8px;">Huỷ</button>
        <button type="submit" class="btn-modal-save">Lưu sản phẩm</button>
      </div>
    </form>
  </div>
</div>

<script>
async function toggleProductStock(id, isOutOfStock) {
  try {
    const res = await fetch('api/products.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'toggle_stock', id, is_out_of_stock: isOutOfStock })
    });
    const data = await res.json();
    if (data.ok) {
      const lbl = document.getElementById(`stockLabel_${id}`);
      if (lbl) {
        lbl.className = `badge-stock ${isOutOfStock ? 'badge-out-stock' : 'badge-in-stock'}`;
        lbl.innerHTML = `<span class="status-dot" style="background:${isOutOfStock ? '#ef4444' : '#10b981'}"></span> ${isOutOfStock ? 'Tạm hết hàng' : 'Còn hàng'}`;
      }
    } else {
      alert('Lỗi: ' + (data.error || 'unknown'));
    }
  } catch(e) {
    alert('Lỗi kết nối máy chủ');
  }
}

async function toggleFlavorStock(pid, flavor, isOut) {
  try {
    const res = await fetch('api/products.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'toggle_flavor_stock', id: pid, flavor, is_out_of_stock: isOut })
    });
    const data = await res.json();
    if (data.ok) {
      // Cập nhật nút vị
      const btn = document.getElementById(`flavorBtn_${pid}_${flavor}`);
      if (btn) {
        if (isOut) {
          btn.style.cssText = 'border:none;cursor:pointer;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;font-family:var(--font);transition:all 0.2s;background:rgba(239,68,68,0.15);color:#f87171;text-decoration:line-through;';
          btn.textContent = '✕ ' + flavor.charAt(0).toUpperCase() + flavor.slice(1);
          btn.setAttribute('onclick', `toggleFlavorStock('${pid}','${flavor}',false)`);
          btn.title = 'Click để mở lại vị ' + flavor.charAt(0).toUpperCase() + flavor.slice(1);
        } else {
          btn.style.cssText = 'border:1px solid var(--line);cursor:pointer;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;font-family:var(--font);transition:all 0.2s;background:var(--surface2);color:var(--text);text-decoration:none;';
          btn.textContent = flavor.charAt(0).toUpperCase() + flavor.slice(1);
          btn.setAttribute('onclick', `toggleFlavorStock('${pid}','${flavor}',true)`);
          btn.title = 'Click để đánh dấu hết vị ' + flavor.charAt(0).toUpperCase() + flavor.slice(1);
        }
      }
      // Nếu toàn bộ vị hết → cập nhật badge hết hàng tổng
      if (data.all_out !== undefined) {
        const lbl = document.getElementById(`stockLabel_${pid}`);
        if (lbl) {
          lbl.className = `badge-stock ${data.all_out ? 'badge-out-stock' : 'badge-in-stock'}`;
          lbl.innerHTML = `<span class="status-dot" style="background:${data.all_out ? '#ef4444' : '#10b981'}"></span> ${data.all_out ? 'Tạm hết hàng' : 'Còn hàng'}`;
        }
      }
    } else {
      alert('Lỗi: ' + (data.error || 'unknown'));
    }
  } catch(e) {
    alert('Lỗi kết nối máy chủ');
  }
}


async function toggleProductActive(id, isActive) {
  try {
    const res = await fetch('api/products.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'toggle_active', id, is_active: isActive })
    });
    const data = await res.json();
    if (data.ok) {
      const lbl = document.getElementById(`activeLabel_${id}`);
      if (lbl) {
        lbl.className = `badge-stock ${isActive ? 'badge-in-stock' : 'badge-hidden'}`;
        lbl.textContent = isActive ? 'Hiển thị' : 'Đang ẩn';
      }
    } else {
      alert('Lỗi: ' + (data.error || 'unknown'));
    }
  } catch(e) {
    alert('Lỗi kết nối máy chủ');
  }
}

function openProductModal() {
  document.getElementById('productForm').reset();
  document.getElementById('p_id').readOnly = false;
  document.getElementById('modalProductTitle').textContent = 'Thêm mặt hàng mới';
  document.getElementById('p_active').checked = true;
  document.getElementById('p_out_of_stock').checked = false;
  document.getElementById('p_img').value = 'assets/images/cake-classic.jpg';
  clearUpload();
  document.getElementById('productModal').style.display = 'flex';
}

function closeProductModal() {
  document.getElementById('productModal').style.display = 'none';
}

function editProduct(p) {
  document.getElementById('p_id').value = p.id;
  document.getElementById('p_id').readOnly = true;
  document.getElementById('p_name').value = p.name;
  document.getElementById('p_price').value = p.price;
  document.getElementById('p_badge').value = p.badge || '';
  document.getElementById('p_flavors').value = Array.isArray(p.flavors) ? p.flavors.join(', ') : (p.flavors || 'cacao, matcha');
  document.getElementById('p_desc').value = p.desc || '';
  document.getElementById('p_out_of_stock').checked = !!p.is_out_of_stock;
  document.getElementById('p_active').checked = p.is_active !== false;

  // Hiện preview ảnh hiện tại
  const imgPath = p.img || 'assets/images/cake-classic.jpg';
  document.getElementById('p_img').value = imgPath;
  setPreviewFromUrl('../' + imgPath, imgPath);

  document.getElementById('modalProductTitle').textContent = `Chỉnh sửa mặt hàng: ${p.name}`;
  document.getElementById('productModal').style.display = 'flex';
}

function setPreviewFromUrl(src, label) {
  const preview = document.getElementById('uploadPreview');
  const prevImg = document.getElementById('uploadPreviewImg');
  const prevName = document.getElementById('uploadPreviewName');
  const zone = document.getElementById('uploadZone');
  prevImg.src = src;
  prevName.textContent = label;
  preview.style.display = 'block';
  zone.style.display = 'none';
}

function clearUpload() {
  document.getElementById('uploadPreview').style.display = 'none';
  document.getElementById('uploadZone').style.display = 'flex';
  document.getElementById('uploadPreviewImg').src = '';
  document.getElementById('uploadPreviewName').textContent = '';
  document.getElementById('p_img_file').value = '';
  // Không xoá p_img để giữ ảnh cũ nếu chỉ đóng modal
}

async function handleProductSubmit(e) {
  e.preventDefault();
  const id = document.getElementById('p_id').value.trim();
  const name = document.getElementById('p_name').value.trim();
  const price = +document.getElementById('p_price').value;
  const badge = document.getElementById('p_badge').value.trim();
  const flavors = document.getElementById('p_flavors').value.split(',').map(s=>s.trim()).filter(Boolean);
  const img = document.getElementById('p_img').value.trim() || 'assets/images/cake-classic.jpg';
  const desc = document.getElementById('p_desc').value.trim();
  const is_out_of_stock = document.getElementById('p_out_of_stock').checked;
  const is_active = document.getElementById('p_active').checked;

  try {
    const res = await fetch('api/products.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        action: 'save',
        id, name, price, badge, flavors, img, desc, is_out_of_stock, is_active
      })
    });
    const data = await res.json();
    if (data.ok) {
      alert(data.message);
      window.location.reload();
    } else {
      alert('Lỗi: ' + (data.error || 'unknown'));
    }
  } catch(e) {
    alert('Lỗi kết nối máy chủ');
  }
}

async function deleteProduct(id, name) {
  if (!confirm(`Bạn có chắc chắn muốn xoá mặt hàng "${name}" không?`)) return;

  try {
    const res = await fetch('api/products.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'delete', id })
    });
    const data = await res.json();
    if (data.ok) {
      alert(data.message);
      window.location.reload();
    } else {
      alert('Lỗi: ' + (data.error || 'unknown'));
    }
  } catch(e) {
    alert('Lỗi kết nối máy chủ');
  }
}
</script>

<script>
/* ============================================================
   UPLOAD ZONE — Drag-drop, click, mobile camera, preview
   ============================================================ */
(function() {
  const zone     = document.getElementById('uploadZone');
  const fileInput= document.getElementById('p_img_file');
  const progress = zone ? zone.querySelector('.uz-progress') : null;
  const bar      = document.getElementById('uzProgressBar');

  if (!zone || !fileInput) return;

  // Drag & Drop events
  zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', e => { zone.classList.remove('dragover'); });
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    const files = e.dataTransfer?.files;
    if (files && files[0]) handleFile(files[0]);
  });

  // File input change (click hoặc camera trên điện thoại)
  fileInput.addEventListener('change', () => {
    if (fileInput.files && fileInput.files[0]) handleFile(fileInput.files[0]);
  });

  async function handleFile(file) {
    // Validate client-side
    const ALLOWED = ['image/jpeg','image/jpg','image/png','image/webp','image/gif'];
    if (!ALLOWED.includes(file.type)) {
      showZoneError('Định dạng không hỗ trợ. Chỉ JPG, PNG, WEBP, GIF.');
      return;
    }
    if (file.size > 8 * 1024 * 1024) {
      showZoneError('File quá lớn. Tối đa 8MB.');
      return;
    }

    // Preview ngay lập tức (local blob)
    const blobUrl = URL.createObjectURL(file);
    setPreviewFromUrl(blobUrl, file.name);

    // Hiện progress bar
    if (progress) progress.style.display = 'block';
    animateProgress(0, 40, 600);

    // Upload lên server
    const fd = new FormData();
    fd.append('image', file);

    try {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'api/upload-image.php', true);

      xhr.upload.addEventListener('progress', evt => {
        if (evt.lengthComputable) {
          const pct = Math.round((evt.loaded / evt.total) * 90);
          if (bar) bar.style.width = pct + '%';
        }
      });

      xhr.onload = function() {
        if (bar) bar.style.width = '100%';
        setTimeout(() => { if (progress) progress.style.display = 'none'; if (bar) bar.style.width = '0'; }, 600);

        let data;
        try { data = JSON.parse(xhr.responseText); } catch(e) {
          showZoneError('Lỗi phân tích phản hồi server.');
          return;
        }

        if (data.ok) {
          // Cập nhật hidden input → đường dẫn thực trên server
          document.getElementById('p_img').value = data.path;
          // Cập nhật preview với src từ server (thay blob)
          document.getElementById('uploadPreviewImg').src = '../' + data.path;
          document.getElementById('uploadPreviewName').textContent = data.name + (data.resized ? ' (đã resize)' : '');
        } else {
          // Vẫn giữ preview blob, nhưng báo lỗi
          setPreviewFromUrl(blobUrl, '⚠ Upload thất bại — ' + (data.error || 'Lỗi không xác định'));
          document.getElementById('uploadPreviewName').style.color = '#f87171';
        }
      };

      xhr.onerror = function() {
        showZoneError('Mất kết nối. Kiểm tra mạng và thử lại.');
      };

      xhr.send(fd);
    } catch(err) {
      showZoneError('Lỗi: ' + err.message);
    }
  }

  function animateProgress(from, to, ms) {
    if (!bar) return;
    const start = performance.now();
    function step(now) {
      const t = Math.min((now - start) / ms, 1);
      bar.style.width = (from + (to - from) * t) + '%';
      if (t < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  function showZoneError(msg) {
    if (progress) progress.style.display = 'none';
    clearUpload();
    const sub = zone.querySelector('.uz-sub');
    if (sub) {
      const orig = sub.textContent;
      sub.textContent = '⚠ ' + msg;
      sub.style.color = '#f87171';
      setTimeout(() => { sub.textContent = orig; sub.style.color = ''; }, 4000);
    }
  }
})();
</script>

</body>
</html>
