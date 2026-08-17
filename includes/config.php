<?php
// ============================================================
// LENA BAKERY — CẤU HÌNH CHUNG
// Chỉnh sửa file này khi cần thay đổi thông tin shop
// ============================================================

// --- DATABASE ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'ntddlabz_lenacake');
define('DB_USER', 'ntddlabz_lenacake');
define('DB_PASS', 'UksSWfFedaKXbz6LMK2e');
define('DB_CHARSET', 'utf8mb4');

// --- ADMIN ---
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Lena@2026!'); // ĐỔI NGAY sau khi deploy

// --- THÔNG TIN SHOP ---
define('SHOP_NAME',    'Lena Bakery');
define('SHOP_TAGLINE', 'Tiramisu tươi — làm theo đơn');
define('SHOP_PHONE',   '0906.819.341');
define('SHOP_ADDRESS', 'Phường Bình Trị Đông, TP.HCM');
define('SHOP_FACEBOOK','https://www.facebook.com/caryln.fer');
define('SHOP_ZALO',    'https://zalo.me/0906819341');
define('SHOP_TIKTOK',  'https://www.tiktok.com/@thantientyty123');

// --- THANH TOÁN VIETQR ---
define('BANK_ID',          'sacombank');
define('BANK_ACCOUNT_NO',  '060330115826');
define('BANK_ACCOUNT_NAME','TA TRAN NGOC ANH');

// --- PHÍ SHIP ---
define('SHIP_FEE',     0);      // VND — phí ship chưa tính vào đơn, shop tự giao và thu khi nhận
define('SHIP_NOTE',    'Shop trực tiếp đi giao hàng tận nơi (thanh toán phí ship khi nhận)');

require_once __DIR__ . '/products.php';

// --- SẢN PHẨM ---
// Danh sách sản phẩm động (hỗ trợ bật tắt, hết hàng, sửa giá từ Admin)
define('PRODUCTS', get_all_products(true));

// --- TOPPING ---
define('TOPPINGS', [
    'none'   => ['name' => 'Không topping', 'price' => 0],
    'dau'    => ['name' => 'Dâu tây tươi',  'price' => 15000],
    'cherry' => ['name' => 'Cherry nhập khẩu','price'=> 20000],
    'mix'    => ['name' => 'Mix Dâu & Cherry','price'=> 30000],
]);

// --- TRẠNG THÁI ĐƠN HÀNG ---
define('ORDER_STATUSES', [
    'new'       => ['label' => 'Mới',           'color' => '#f59e0b'],
    'confirmed' => ['label' => 'Đã xác nhận',   'color' => '#3b82f6'],
    'making'    => ['label' => 'Đang làm',      'color' => '#8b5cf6'],
    'done'      => ['label' => 'Hoàn thành',    'color' => '#10b981'],
    'cancelled' => ['label' => 'Đã huỷ',        'color' => '#ef4444'],
]);
