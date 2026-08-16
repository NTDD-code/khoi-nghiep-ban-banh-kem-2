<?php
// ============================================================
// LENA BAKERY — KẾT NỐI DATABASE (PDO)
// ============================================================

require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['ok' => false, 'error' => 'Không thể kết nối cơ sở dữ liệu MySQL. Vui lòng bật MySQL trong bảng điều khiển XAMPP']));
        }
    }
    return $pdo;
}

// ---- Helper functions ----

function generate_order_code(): string {
    $date = date('Ymd');
    $db   = get_db();
    $count = (int)$db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    return 'LB-' . $date . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

function fmt_price(int $vnd): string {
    return number_format($vnd, 0, ',', '.') . 'đ';
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
