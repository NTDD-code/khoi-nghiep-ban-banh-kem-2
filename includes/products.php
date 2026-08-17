<?php
// ============================================================
// LENA BAKERY — PRODUCT CATALOG MANAGER
// ============================================================

define('PRODUCTS_FILE', __DIR__ . '/../database/products.json');

function get_default_products() {
    return [
        'mini' => [
            'id'                  => 'mini',
            'name'                => 'Mini Cup',
            'price'               => 20000,
            'desc'                => 'Nhỏ gọn cho 1 người. Kem Mascarpone ngậy tan, cốt bánh đượm Espresso.',
            'badge'               => null,
            'img'                 => 'assets/images/cake-classic.jpg',
            'flavors'             => ['cacao', 'matcha'],
            'out_of_stock_flavors'=> [],
            'is_out_of_stock'     => false,
            'is_active'           => true,
        ],
        '350ml' => [
            'id'                  => '350ml',
            'name'                => 'Hộp 350ml',
            'price'               => 70000,
            'desc'                => 'Dành cho 2–3 người. Lớp kem dày béo ngậy chuẩn vị.',
            'badge'               => 'Bán chạy',
            'img'                 => 'assets/images/cake-matcha.png',
            'flavors'             => ['cacao', 'matcha'],
            'out_of_stock_flavors'=> [],
            'is_out_of_stock'     => false,
            'is_active'           => true,
        ],
        'tin750' => [
            'id'                  => 'tin750',
            'name'                => 'Hộp thiếc 750ml',
            'price'               => 189000,
            'desc'                => 'Quà tặng giữ lạnh tối ưu, trang trí dâu/cherry tươi cao cấp.',
            'badge'               => 'Sang trọng',
            'img'                 => 'assets/images/cake-berry.jpg',
            'flavors'             => ['cacao', 'matcha'],
            'out_of_stock_flavors'=> [],
            'is_out_of_stock'     => false,
            'is_active'           => true,
        ],
    ];
}

function get_all_products($onlyActive = false) {
    $file = PRODUCTS_FILE;
    $products = [];

    if (file_exists($file)) {
        $content = file_get_contents($file);
        $products = json_decode($content, true);
    }

    if (empty($products) || !is_array($products)) {
        $products = get_default_products();
        save_all_products($products);
    }

    // Đảm bảo các field bắt buộc
    foreach ($products as $k => &$p) {
        if (!isset($p['is_out_of_stock'])) $p['is_out_of_stock'] = false;
        if (!isset($p['is_active'])) $p['is_active'] = true;
        if (!isset($p['flavors']) || !is_array($p['flavors'])) $p['flavors'] = ['cacao', 'matcha'];
        if (!isset($p['out_of_stock_flavors']) || !is_array($p['out_of_stock_flavors'])) $p['out_of_stock_flavors'] = [];
    }

    if ($onlyActive) {
        return array_filter($products, function($p) {
            return !empty($p['is_active']);
        });
    }

    return $products;
}

function save_all_products($products) {
    $dir = dirname(PRODUCTS_FILE);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return file_put_contents(PRODUCTS_FILE, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}
