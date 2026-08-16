<?php
// ============================================================
// LENA BAKERY — Server-Sent Events: Thông Báo Đơn Hàng Mới
// GET /api/sse-notify.php?since=<timestamp>
// Admin dashboard kết nối tới đây để nhận realtime notifications
// ============================================================

session_start();
// Chỉ cho phép admin đã login mở SSE stream
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die('Forbidden');
}
// QUAN TRỌNG: Giải phóng session lock ngay lập tức để không block các trang khác
session_write_close();

require_once __DIR__ . '/../includes/db.php';

// SSE headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache, no-transform');
header('X-Accel-Buffering: no');   // Nginx / FastCGI: tắt buffer
header('Connection: keep-alive');

// Ngăn PHP timeout
set_time_limit(0);
ignore_user_abort(false);

$since = (int)($_GET['since'] ?? time());

$db = get_db();

function send_event(string $event, array $data): void {
    echo "event: {$event}\n";
    echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

// Ping ngay khi connect
send_event('ping', ['ts' => time()]);

while (true) {
    if (connection_aborted()) break;

    // Đơn hàng mới trong 30s gần nhất kể từ $since
    $stmt = $db->prepare("
        SELECT id, order_code, customer_name, customer_phone, total, shipping_method, created_at
        FROM orders
        WHERE created_at >= FROM_UNIXTIME(:since)
        ORDER BY id DESC
        LIMIT 10
    ");
    $stmt->execute([':since' => $since]);
    $newOrders = $stmt->fetchAll();

    if ($newOrders) {
        foreach ($newOrders as $order) {
            $since = max($since, strtotime($order['created_at']) + 1);
            send_event('new_order', $order);
        }
    }

    // Tin nhắn mới
    $msgStmt = $db->prepare("
        SELECT id, sender_name, sender_phone, LEFT(content,80) as preview, created_at
        FROM messages
        WHERE is_read = 0 AND created_at >= FROM_UNIXTIME(:since)
        ORDER BY id DESC
        LIMIT 5
    ");
    $msgStmt->execute([':since' => $since]);
    $newMsgs = $msgStmt->fetchAll();
    foreach ($newMsgs as $msg) {
        send_event('new_message', $msg);
    }

    // Keep-alive heartbeat mỗi 25 giây
    send_event('heartbeat', ['ts' => time()]);

    sleep(5); // Poll mỗi 5 giây
}
