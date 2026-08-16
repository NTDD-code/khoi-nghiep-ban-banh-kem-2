<?php
// ============================================================
// LENA BAKERY — API: GỬI TIN NHẮN (In-App Messaging)
// POST /api/send-message.php
// ============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['ok' => false, 'error' => 'Method not allowed']));
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$name    = trim($data['sender_name']  ?? '');
$phone   = trim($data['sender_phone'] ?? '');
$content = trim($data['content']      ?? '');

if (!$name)    die(json_encode(['ok' => false, 'error' => 'Vui lòng nhập tên']));
if (!$content) die(json_encode(['ok' => false, 'error' => 'Vui lòng nhập nội dung tin nhắn']));
if (mb_strlen($content) > 2000) die(json_encode(['ok' => false, 'error' => 'Tin nhắn quá dài']));

try {
    $db = get_db();
    $db->prepare("
        INSERT INTO messages (sender_name, sender_phone, content)
        VALUES (:name, :phone, :content)
    ")->execute([
        ':name'    => $name,
        ':phone'   => $phone ?: null,
        ':content' => $content,
    ]);

    echo json_encode(['ok' => true, 'message' => 'Tin nhắn đã được gửi!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi hệ thống']);
}
