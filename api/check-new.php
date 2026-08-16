<?php
// ============================================================
// LENA BAKERY — API: Check Đơn Hàng & Tin Nhắn Mới (Short Polling)
// GET /api/check-new.php?since=<timestamp>
// Trả về JSON ngay lập tức, không chiếm giữ worker thread của server
// ============================================================

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die(json_encode(['ok' => false, 'error' => 'Forbidden']));
}
session_write_close();

require_once __DIR__ . '/../includes/db.php';

$since = (int)($_GET['since'] ?? (time() - 30));
$now   = time();

try {
    $db = get_db();

    // 1. Đơn hàng mới kể từ $since
    $orderStmt = $db->prepare("
        SELECT id, order_code, customer_name, customer_phone, total, shipping_method, payment_method, status, created_at
        FROM orders
        WHERE created_at > FROM_UNIXTIME(:since)
        ORDER BY id ASC
        LIMIT 20
    ");
    $orderStmt->execute([':since' => $since]);
    $newOrders = $orderStmt->fetchAll();

    // 2. Tin nhắn mới chưa đọc kể từ $since
    $msgStmt = $db->prepare("
        SELECT id, sender_name, sender_phone, LEFT(content, 80) as preview, created_at
        FROM messages
        WHERE is_read = 0 AND created_at > FROM_UNIXTIME(:since)
        ORDER BY id ASC
        LIMIT 10
    ");
    $msgStmt->execute([':since' => $since]);
    $newMsgs = $msgStmt->fetchAll();

    // 3. Số lượng pending & unread hiện tại
    $pending = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='new'")->fetchColumn();
    $unread  = (int)$db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn();

    echo json_encode([
        'ok'       => true,
        'ts'       => $now,
        'orders'   => $newOrders,
        'messages' => $newMsgs,
        'counts'   => [
            'pending' => $pending,
            'unread'  => $unread,
        ],
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database error']);
}
