<?php
// Admin API: cập nhật trạng thái đơn hàng
session_start();
header('Content-Type: application/json');
if (empty($_SESSION['admin_logged_in'])) { http_response_code(403); die('{}'); }

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/config.php';

$data   = json_decode(file_get_contents('php://input'), true);
$id     = (int)($data['id']     ?? 0);
$status = $data['status'] ?? '';

$valid = array_keys(ORDER_STATUSES);
if (!$id || !in_array($status, $valid)) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'Invalid input']));
}

try {
    $db = get_db();
    $db->prepare("UPDATE orders SET status = :s WHERE id = :id")
       ->execute([':s' => $status, ':id' => $id]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB error']);
}
