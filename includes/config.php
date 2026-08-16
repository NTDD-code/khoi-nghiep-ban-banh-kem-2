<?php
// ============================================================
// LENA BAKERY — CẤU HÌNH CHUNG
// Chỉnh sửa file này khi cần thay đổi thông tin shop
// ============================================================

// --- DATABASE ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'lena_bakery');
define('DB_USER', 'root');        // Đổi thành user MySQL của bạn
define('DB_PASS', '');            // Đổi thành password MySQL của bạn
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
define('SHIP_FEE',     30000);  // VND — phí ship cơ bản (Grab/Be theo cước)
define('SHIP_NOTE',    'Tính theo cước app giao hàng (Grab / Be / Xanh SM)');

// --- SẢN PHẨM ---
// Key = product_id (dùng trong DB và JS)
define('PRODUCTS', [
    'mini' => [
        'name'  => 'Mini Cup',
        'price' => 20000,
        'desc'  => 'Nhỏ gọn cho 1 người. Kem Mascarpone ngậy tan, cốt bánh đượm Espresso.',
        'badge' => null,
        'img'   => 'assets/images/cake-classic.jpg',
    ],
    '350ml' => [
        'name'  => 'Hộp 350ml',
        'price' => 70000,
        'desc'  => 'Dành cho 2–3 người. Lớp kem dày béo ngậy chuẩn vị.',
        'badge' => 'Bán chạy',
        'img'   => 'assets/images/cake-matcha.png',
    ],
    'tin750' => [
        'name'  => 'Hộp thiếc 750ml',
        'price' => 189000,
        'desc'  => 'Quà tặng giữ lạnh tối ưu, trang trí dâu/cherry tươi cao cấp.',
        'badge' => 'Sang trọng',
        'img'   => 'assets/images/cake-berry.jpg',
    ],
]);

// --- TOPPING ---
define('TOPPINGS', [
    'none'   => ['name' => 'Không topping', 'price' => 0],
    'dau'    => ['name' => 'Dâu tây tươi',  'price' => 15000],
    'cherry' => ['name' => 'Cherry nhập khẩu','price'=> 20000],
    'mix'    => ['name' => 'Mix Dâu & Cherry','price'=> 30000],
]);

// --- TRẠNG THÁI ĐƠN HÀNG ---
define('ORDER_STATUSES', [
    'new'       => ['label' => '🆕 Mới',        'color' => '#f59e0b'],
    'confirmed' => ['label' => '✅ Đã xác nhận', 'color' => '#3b82f6'],
    'making'    => ['label' => '👨‍🍳 Đang làm',   'color' => '#8b5cf6'],
    'done'      => ['label' => '🎉 Hoàn thành', 'color' => '#10b981'],
    'cancelled' => ['label' => '❌ Đã huỷ',     'color' => '#ef4444'],
]);
