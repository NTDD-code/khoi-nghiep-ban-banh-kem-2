<?php
// ============================================================
// LENA BAKERY — API: SUBMIT ORDER
// POST /api/submit-order.php
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['ok' => false, 'error' => 'Method not allowed']));
}

$raw   = file_get_contents('php://input');
$data  = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'Invalid JSON']));
}

// ---- Validate ----
$name    = trim($data['customer_name']  ?? '');
$phone   = trim($data['customer_phone'] ?? '');
$addr    = trim($data['customer_addr']  ?? '');
$method     = in_array($data['shipping_method'] ?? '', ['pickup','delivery']) ? $data['shipping_method'] : 'pickup';
$payMethod  = in_array($data['payment_method'] ?? '', ['transfer','cod']) ? $data['payment_method'] : 'transfer';
$note       = trim($data['note']           ?? '');
$tip        = max(0, (int)($data['tip']    ?? 0));
$items      = $data['items']               ?? [];

if (!$name)             { die(json_encode(['ok' => false, 'error' => 'Thiếu tên khách hàng'])); }
if (!preg_match('/^[0-9+\-\s]{8,15}$/', $phone)) {
    die(json_encode(['ok' => false, 'error' => 'Số điện thoại không hợp lệ']));
}
if ($method === 'delivery' && !$addr) {
    die(json_encode(['ok' => false, 'error' => 'Vui lòng nhập địa chỉ giao hàng']));
}
if (empty($items)) {
    die(json_encode(['ok' => false, 'error' => 'Giỏ hàng trống']));
}

// ---- Tính tiền ----
$products = PRODUCTS;
$toppings = TOPPINGS;
$subtotal = 0;
$itemRows = [];

foreach ($items as $item) {
    $pid     = $item['product_id'] ?? '';
    $flavor  = $item['flavor']     ?? 'cacao';
    $topping = $item['topping']    ?? 'none';
    $qty     = max(1, (int)($item['quantity'] ?? 1));

    if (!isset($products[$pid])) continue;

    $unitPrice = $products[$pid]['price'] + ($toppings[$topping]['price'] ?? 0);
    $rowTotal  = $unitPrice * $qty;
    $subtotal += $rowTotal;

    $itemRows[] = [
        'product_id'   => $pid,
        'product_name' => $products[$pid]['name'],
        'flavor'       => $flavor,
        'topping'      => $toppings[$topping]['name'] ?? 'Không',
        'quantity'     => $qty,
        'unit_price'   => $unitPrice,
        'subtotal'     => $rowTotal,
    ];
}

if (empty($itemRows)) {
    die(json_encode(['ok' => false, 'error' => 'Sản phẩm không hợp lệ']));
}

$shipFee = 0; // Phí ship chưa tính vào hoá đơn, khách thanh toán riêng cho shipper
$total   = $subtotal + $shipFee + $tip;

// ---- Lưu vào DB ----
try {
    $db   = get_db();
    $code = generate_order_code();

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO orders
            (order_code, customer_name, customer_phone, customer_addr,
             shipping_method, shipping_fee, payment_method, subtotal, tip, total, note, status)
        VALUES
            (:code, :name, :phone, :addr,
             :method, :ship_fee, :pay_method, :subtotal, :tip, :total, :note, 'new')
    ");
    $stmt->execute([
        ':code'       => $code,
        ':name'       => $name,
        ':phone'      => $phone,
        ':addr'       => $addr ?: null,
        ':method'     => $method,
        ':ship_fee'   => $shipFee,
        ':pay_method' => $payMethod,
        ':subtotal'   => $subtotal,
        ':tip'        => $tip,
        ':total'      => $total,
        ':note'       => $note ?: null,
    ]);

    $orderId = (int)$db->lastInsertId();

    $itemStmt = $db->prepare("
        INSERT INTO order_items
            (order_id, product_id, product_name, flavor, topping, quantity, unit_price, subtotal)
        VALUES (:oid, :pid, :pname, :flavor, :topping, :qty, :uprice, :sub)
    ");
    foreach ($itemRows as $row) {
        $itemStmt->execute([
            ':oid'    => $orderId,
            ':pid'    => $row['product_id'],
            ':pname'  => $row['product_name'],
            ':flavor' => $row['flavor'],
            ':topping'=> $row['topping'],
            ':qty'    => $row['quantity'],
            ':uprice' => $row['unit_price'],
            ':sub'    => $row['subtotal'],
        ]);
    }

    $db->commit();

    echo json_encode([
        'ok'         => true,
        'order_id'   => $orderId,
        'order_code' => $code,
        'total'      => $total,
    ]);

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi hệ thống, vui lòng thử lại']);
}
