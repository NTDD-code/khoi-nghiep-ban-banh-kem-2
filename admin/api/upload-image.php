<?php
// ============================================================
// LENA BAKERY — API: UPLOAD ẢNH SẢN PHẨM (Admin)
// ============================================================

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}
session_write_close();

header('Content-Type: application/json; charset=UTF-8');

// --- Cấu hình ---
define('UPLOAD_DIR',      __DIR__ . '/../../assets/images/products/');
define('UPLOAD_URL_BASE', 'assets/images/products/');
define('MAX_FILE_SIZE',   8 * 1024 * 1024);  // 8 MB
define('MAX_WIDTH',       1200);              // px — resize nếu to hơn
define('MAX_HEIGHT',      1200);
define('ALLOWED_TYPES',   ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_EXTS',    ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Tạo thư mục nếu chưa có
if (!is_dir(UPLOAD_DIR)) {
    if (!@mkdir(UPLOAD_DIR, 0755, true)) {
        echo json_encode(['ok' => false, 'error' => 'Không thể tạo thư mục upload. Kiểm tra quyền ghi của server.']);
        exit;
    }
}

// Chỉ nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Kiểm tra file upload
if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errCodes = [
        UPLOAD_ERR_INI_SIZE   => 'File vượt quá giới hạn upload_max_filesize của server.',
        UPLOAD_ERR_FORM_SIZE  => 'File vượt quá giới hạn MAX_FILE_SIZE.',
        UPLOAD_ERR_PARTIAL    => 'File chỉ được upload một phần.',
        UPLOAD_ERR_NO_FILE    => 'Không có file nào được chọn.',
        UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm trên server.',
        UPLOAD_ERR_CANT_WRITE => 'Không ghi được file lên server.',
    ];
    $errCode = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $errMsg  = $errCodes[$errCode] ?? 'Lỗi upload không xác định (code: ' . $errCode . ')';
    echo json_encode(['ok' => false, 'error' => $errMsg]);
    exit;
}

$file    = $_FILES['image'];
$tmpPath = $file['tmp_name'];
$origName= $file['name'];
$size    = $file['size'];

// Kiểm tra kích thước
if ($size > MAX_FILE_SIZE) {
    echo json_encode(['ok' => false, 'error' => 'File quá lớn. Tối đa ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.']);
    exit;
}

// Kiểm tra MIME thực tế (không tin vào $_FILES['type'])
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeReal = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

if (!in_array($mimeReal, ALLOWED_TYPES)) {
    echo json_encode(['ok' => false, 'error' => 'Định dạng file không hợp lệ. Chỉ chấp nhận: JPG, PNG, WEBP, GIF.']);
    exit;
}

// Lấy extension từ tên file gốc
$origExt = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
if (!in_array($origExt, ALLOWED_EXTS)) $origExt = 'jpg';

// Map MIME → ext an toàn
$mimeToExt = [
    'image/jpeg' => 'jpg',
    'image/jpg'  => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
$safeExt = $mimeToExt[$mimeReal] ?? $origExt;

// Tên file an toàn: timestamp + random
$fileName = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
$destPath = UPLOAD_DIR . $fileName;

// --- Resize nếu có extension GD và ảnh quá lớn ---
$resized = false;
if (extension_loaded('gd') && in_array($safeExt, ['jpg','jpeg','png','webp'])) {
    $imgInfo = @getimagesize($tmpPath);
    if ($imgInfo && ($imgInfo[0] > MAX_WIDTH || $imgInfo[1] > MAX_HEIGHT)) {
        $srcW = $imgInfo[0];
        $srcH = $imgInfo[1];

        // Tỉ lệ giảm
        $ratio  = min(MAX_WIDTH / $srcW, MAX_HEIGHT / $srcH);
        $newW   = (int)($srcW * $ratio);
        $newH   = (int)($srcH * $ratio);

        // Load source
        $src = match($safeExt) {
            'png'  => @imagecreatefrompng($tmpPath),
            'webp' => @imagecreatefromwebp($tmpPath),
            default=> @imagecreatefromjpeg($tmpPath),
        };

        if ($src) {
            $dst = imagecreatetruecolor($newW, $newH);

            // Giữ alpha cho PNG
            if ($safeExt === 'png') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

            $saved = match($safeExt) {
                'png'  => imagepng($dst, $destPath, 8),
                'webp' => imagewebp($dst, $destPath, 85),
                default=> imagejpeg($dst, $destPath, 88),
            };

            imagedestroy($src);
            imagedestroy($dst);

            if ($saved) $resized = true;
        }
    }
}

// Nếu không resize được thì move_uploaded_file bình thường
if (!$resized) {
    if (!move_uploaded_file($tmpPath, $destPath)) {
        echo json_encode(['ok' => false, 'error' => 'Không thể lưu file. Kiểm tra quyền ghi thư mục assets/images/products/']);
        exit;
    }
}

$publicPath = UPLOAD_URL_BASE . $fileName;

echo json_encode([
    'ok'      => true,
    'path'    => $publicPath,
    'url'     => $publicPath,
    'name'    => $fileName,
    'resized' => $resized,
    'size'    => filesize($destPath),
]);
