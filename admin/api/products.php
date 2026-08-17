<?php
// ============================================================
// LENA BAKERY — API: QUẢN LÝ SẢN PHẨM (Admin)
// ============================================================

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}
session_write_close();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../includes/products.php';

$action = $_GET['action'] ?? '';
$products = get_all_products(false); // Lấy toàn bộ kể cả đang ẩn

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['ok' => true, 'products' => array_values($products)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $postAction = $input['action'] ?? $action;

    // 1. Bật/Tắt trạng thái Hết hàng (Instant Toggle — toàn bộ sản phẩm)
    if ($postAction === 'toggle_stock') {
        $pid = trim($input['id'] ?? '');
        if (!isset($products[$pid])) {
            echo json_encode(['ok' => false, 'error' => 'Sản phẩm không tồn tại']);
            exit;
        }

        $newState = !empty($input['is_out_of_stock']);
        $products[$pid]['is_out_of_stock'] = $newState;
        save_all_products($products);

        echo json_encode([
            'ok' => true,
            'message' => 'Đã ' . ($newState ? 'đánh dấu Hết hàng' : 'mở bán Còn hàng') . ' cho ' . $products[$pid]['name'],
            'is_out_of_stock' => $newState
        ]);
        exit;
    }

    // 1b. Bật/Tắt Hết hàng theo từng Vị (cacao / matcha)
    if ($postAction === 'toggle_flavor_stock') {
        $pid    = trim($input['id'] ?? '');
        $flavor = trim($input['flavor'] ?? '');
        $isOut  = !empty($input['is_out_of_stock']);

        if (!isset($products[$pid])) {
            echo json_encode(['ok' => false, 'error' => 'Sản phẩm không tồn tại']);
            exit;
        }
        if (!$flavor) {
            echo json_encode(['ok' => false, 'error' => 'Thiếu tên vị']);
            exit;
        }

        $oos = $products[$pid]['out_of_stock_flavors'] ?? [];
        if ($isOut) {
            if (!in_array($flavor, $oos)) $oos[] = $flavor;
        } else {
            $oos = array_values(array_filter($oos, fn($f) => $f !== $flavor));
        }
        $products[$pid]['out_of_stock_flavors'] = $oos;

        // Nếu toàn bộ vị đều hết → tự động bật is_out_of_stock
        $allFlavors = $products[$pid]['flavors'] ?? ['cacao', 'matcha'];
        $allOut = !empty($allFlavors) && count(array_intersect($allFlavors, $oos)) === count($allFlavors);
        $products[$pid]['is_out_of_stock'] = $allOut;

        save_all_products($products);

        echo json_encode([
            'ok'                   => true,
            'flavor'               => $flavor,
            'is_out_of_stock'      => $isOut,
            'all_out'              => $allOut,
            'out_of_stock_flavors' => $oos
        ]);
        exit;
    }


    // 2. Bật/Tắt Hiển thị (Ẩn/Hiện sản phẩm trên web)
    if ($postAction === 'toggle_active') {
        $pid = trim($input['id'] ?? '');
        if (!isset($products[$pid])) {
            echo json_encode(['ok' => false, 'error' => 'Sản phẩm không tồn tại']);
            exit;
        }

        $newState = !empty($input['is_active']);
        $products[$pid]['is_active'] = $newState;
        save_all_products($products);

        echo json_encode([
            'ok' => true,
            'message' => 'Đã ' . ($newState ? 'hiển thị sản phẩm' : 'ẩn sản phẩm khỏi website') . ' cho ' . $products[$pid]['name'],
            'is_active' => $newState
        ]);
        exit;
    }

    // 3. Thêm mới hoặc Cập nhật sản phẩm (Chỉnh giá, tên, ảnh, mô tả, vị...)
    if ($postAction === 'save') {
        $id = trim($input['id'] ?? '');
        $name = trim($input['name'] ?? '');
        $price = (int)($input['price'] ?? 0);
        $desc = trim($input['desc'] ?? '');
        $badge = trim($input['badge'] ?? '') ?: null;
        $img = trim($input['img'] ?? '') ?: 'assets/images/cake-classic.jpg';
        $flavors = $input['flavors'] ?? ['cacao', 'matcha'];
        $is_out_of_stock = !empty($input['is_out_of_stock']);
        $is_active = isset($input['is_active']) ? !empty($input['is_active']) : true;

        if (!$id || !$name || $price <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập đầy đủ Mã, Tên và Giá bán hợp lệ']);
            exit;
        }

        // Chuẩn hoá ID (chỉ chữ thường, số, gạch nối)
        $id = preg_replace('/[^a-z0-9_-]/', '', strtolower($id));

        if (!is_array($flavors)) {
            $flavors = array_filter(array_map('trim', explode(',', (string)$flavors)));
        }
        if (empty($flavors)) $flavors = ['cacao', 'matcha'];

        $products[$id] = [
            'id'              => $id,
            'name'            => $name,
            'price'           => $price,
            'desc'            => $desc,
            'badge'           => $badge,
            'img'             => $img,
            'flavors'         => $flavors,
            'is_out_of_stock' => $is_out_of_stock,
            'is_active'       => $is_active,
        ];

        save_all_products($products);

        echo json_encode([
            'ok' => true,
            'message' => 'Đã lưu thông tin sản phẩm "' . $name . '" thành công!',
            'product' => $products[$id]
        ]);
        exit;
    }

    // 4. Xoá sản phẩm
    if ($postAction === 'delete') {
        $pid = trim($input['id'] ?? '');
        if (!isset($products[$pid])) {
            echo json_encode(['ok' => false, 'error' => 'Sản phẩm không tồn tại']);
            exit;
        }

        $deletedName = $products[$pid]['name'];
        unset($products[$pid]);
        save_all_products($products);

        echo json_encode([
            'ok' => true,
            'message' => 'Đã xoá sản phẩm "' . $deletedName . '"'
        ]);
        exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Action không hợp lệ']);
