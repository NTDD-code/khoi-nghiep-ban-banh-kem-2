<?php
// ============================================================
// LENA BAKERY — API: XUẤT FILE EXCEL / CSV ĐƠN HÀNG
// ============================================================

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die('Forbidden - Bạn cần đăng nhập quyền Admin');
}
session_write_close();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/config.php';

$date   = trim($_GET['date'] ?? 'today');
$status = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');
$rule   = trim($_GET['rule'] ?? 'non_cancelled');

$db = get_db();

$where = ["1=1"];
$params = [];

if ($date === 'today') {
    $where[] = "DATE(o.created_at) = CURDATE()";
} elseif ($date === 'yesterday') {
    $where[] = "DATE(o.created_at) = SUBDATE(CURDATE(), 1)";
} elseif ($date === '7days') {
    $where[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($date === 'all') {
    // Không lọc ngày
} elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $where[] = "DATE(o.created_at) = :date";
    $params[':date'] = $date;
}

if ($status !== '') {
    $where[] = "o.status = :status";
    $params[':status'] = $status;
}

if ($search !== '') {
    $where[] = "(o.order_code LIKE :s OR o.customer_name LIKE :s OR o.customer_phone LIKE :s)";
    $params[':s'] = "%$search%";
}

$sql = "
    SELECT o.*,
    (
        SELECT GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.product_name, ' (', oi.flavor, IF(oi.topping != 'none' AND oi.topping IS NOT NULL AND oi.topping != '', CONCAT(' + ', oi.topping), ''), ')') SEPARATOR ' | ')
        FROM order_items oi WHERE oi.order_id = o.id
    ) as items_summary
    FROM orders o
    WHERE " . implode(' AND ', $where) . "
    ORDER BY o.id DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statuses = ORDER_STATUSES;

$filename = 'Don-hang-LenaBakery-' . ($date ? $date : date('Ymd-His')) . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$fp = fopen('php://output', 'w');

// Xuất UTF-8 BOM để Excel hiển thị tiếng Việt chuẩn 100%
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

// Tiêu đề cột
fputcsv($fp, [
    'STT',
    'Mã đơn hàng',
    'Ngày đặt',
    'Tên khách hàng',
    'Số điện thoại',
    'Hình thức nhận',
    'Địa chỉ giao hàng',
    'Phương thức thanh toán',
    'Danh sách món đặt',
    'Ghi chú',
    'Tiền hàng (VNĐ)',
    'Phí ship (VNĐ)',
    'Tip (VNĐ)',
    'Tổng cộng (VNĐ)',
    'Trạng thái'
]);

$stt = 1;
$totalRevenue = 0;

foreach ($orders as $order) {
    $shipText = $order['shipping_method'] === 'pickup' ? 'Nhận tại quán (Pickup)' : 'Giao tận nơi';
    $payText  = ($order['payment_method'] ?? 'transfer') === 'cod' ? 'Thanh toán khi nhận (COD)' : 'Chuyển khoản VietQR';
    $statusText = $statuses[$order['status']]['label'] ?? $order['status'];
    
    // Áp dụng quy tắc cộng doanh thu
    $countRevenue = true;
    if ($rule === 'done_only') {
        $countRevenue = ($order['status'] === 'done');
    } elseif ($rule === 'non_cancelled') {
        $countRevenue = ($order['status'] !== 'cancelled');
    }

    if ($countRevenue) {
        $totalRevenue += (int)$order['total'];
    }

    fputcsv($fp, [
        $stt++,
        $order['order_code'],
        date('d/m/Y H:i:s', strtotime($order['created_at'])),
        $order['customer_name'],
        $order['customer_phone'],
        $shipText,
        $order['customer_addr'] ?: 'Tại quán',
        $payText,
        $order['items_summary'] ?: '',
        $order['note'] ?: '',
        $order['subtotal'],
        $order['shipping_fee'],
        $order['tip'],
        $order['total'],
        $statusText
    ]);
}

// Ghi chú dòng tổng kết
$ruleNote = ' (Không tính đơn huỷ)';
if ($rule === 'done_only') $ruleNote = ' (Chỉ tính đơn hoàn thành)';
if ($rule === 'all') $ruleNote = ' (Tính toàn bộ)';

fputcsv($fp, [
    '',
    'TỔNG DOANH THU' . $ruleNote,
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    $totalRevenue,
    ''
]);

fclose($fp);
exit;
